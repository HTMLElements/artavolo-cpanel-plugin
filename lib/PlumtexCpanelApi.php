<?php

require_once(__DIR__ . '/traits/PlumtexFindInstalationsTrait.php');

class PlumtexCpanelApi {

    use PlumtexFindInstalationsTrait;

    public function checkIfFeatureEnabled($user) {
        //$user = $this->input->data->user;
        $account = $this->execApi1('accountsummary', compact('user'));
        // $account = json_decode(json_encode($account, JSON_FORCE_OBJECT));
        //    var_dump($account);
        //   exit;

        $account = $account['data']['acct'][0];
        $pkg = $account['plan'];
        $package = $this->execApi1('getpkginfo', compact('pkg'));


        $package = $package['data']['pkg'];
        $featurelist = $package['FEATURELIST'];
        $featurelistData = $this->execApi1('get_featurelist_data', compact('featurelist'));
        $featureHash = $featurelistData['data']['features'];


        if (!$featureHash) {
            return false;
        }

        foreach ($featureHash as $hash) {
            if ($hash['id'] == 'artavolo') {
                $disabled = intval($hash['is_disabled']);
                $enabled = intval($hash['value']);
                if ($disabled == 1) {
                    return false;
                }
                if ($enabled == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    public function execUapi($user, $module, $function, $args = array()) {
        // $user = $this->input->data->user;
        $argsString = '';
        foreach ($args as $key => $value) {
            $argsString .= escapeshellarg($key) . '=' . escapeshellarg($value) . ' ';
        }
        if ($user) {
            $command = "/usr/bin/uapi --user=$user --output=json $module $function $argsString";
        } else {
            $command = "/usr/bin/uapi --output=json $module $function $argsString";
        }
        $json = shell_exec($command);
        //file_put_contents('/home/logs/error.txt', $command, FILE_APPEND); //Debug
        return @json_decode($json, true);
    }

    public function execApi1($function, $args = array()) {
        $argsString = '';
        foreach ($args as $key => $value) {
            $argsString .= escapeshellarg($key) . '=' . escapeshellarg($value) . ' ';
            ;
        }
        //$command = "whmapi1 --output=json $function $argsString";
        $command = "/usr/sbin/whmapi1 --output=json $function $argsString";
        $json = shell_exec($command);
        return @json_decode($json, true);
    }

    public function getMysqlServerType() {
        $serv = 'mysql';
        $db_type_data = $this->execApi1('current_mysql_version');
        if (isset($db_type_data['data'])) {
            if (isset($db_type_data['data']['server'])) {
                $serv = $db_type_data['data']['server'];
            }
        }
        return $serv;
    }

    public function getMysqlRestrictions($user) {
        $data = $this->execUapi($user, 'Mysql', 'get_restrictions');
        return $data ["result"]['data'];
    }

    public function makeDbPrefixFromUsername($user = false) {
        $restriction = $this->getMysqlRestrictions($user);

        return $restriction['prefix'];
    }

    public function randomPassword($length = 16) {
        $alphabet = '!@#abcdef^@%^&*[]-ghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $length; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

//    public function findInstalations($username = false)
//    {
//
//        $allDomains = array();
//        $domaindata = $this->execUapi($username, 'DomainInfo', 'domains_data', array('format' => 'hash'));
//        if ($domaindata) {
//            $domaindata = $domaindata['cpanelresult']['result']['data'];
//            if (isset($domaindata['main_domain'])) {
//                $allDomains = array_merge($allDomains, $domaindata['main_domain']);
//            }
//            if (isset($domaindata['addon_domains'])) {
//                $allDomains = array_merge($allDomains, $domaindata['addon_domains']);
//            }
//            if (isset($domaindata['sub_domains'])) {
//                $allDomains = array_merge($allDomains, $domaindata['sub_domains']);
//            }
//        }
//
//
//        $return = array();
//        foreach ($allDomains as $key => $domain) {
//
//            $mainDir = $domain['documentroot'];
//            $config = file_exists("$mainDir/config/plumtex.php");
//            $version_file = file_exists("$mainDir/version.log");
//            if (!$config) {
//                continue;
//            }
//            if (!$version_file) {
//                $version = 'unknown';
//            } else {
//                $version = file_get_contents("$mainDir/version.log");
//
//            }
//            $domain['version'] = $version;
//            $return[$key] = $domain;
//        }
//        return $return;
//    }
}
