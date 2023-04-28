<?php

trait PlumtexFindInstalationsTrait {

    public function findInstalations($username = false) {

        $method = false;
        if (isset($this->cpanel) and is_object($this->cpanel) and method_exists($this->cpanel, 'uapi')) {
            $method = 'cpanel';
        } else if (method_exists($this, 'execUapi')) {
            $method = 'execUapi';
        } else if (isset($this->cpapi) and is_object($this->cpapi) and method_exists($this->cpapi, 'execUapi')) {
            $method = 'cpapiexecUapi';
        }

        if (!$method) {
            return;
        }
        $allDomains = array();
        if ($method == 'cpanel') {
            $domaindata = $this->cpanel->uapi('DomainInfo', 'domains_data', array('format' => 'hash'));
        } else if ($method == 'cpapiexecUapi') {
            $domaindata = $this->cpapi->execUapi($username, 'DomainInfo', 'domains_data', array('format' => 'hash'));
        } else {
            $domaindata = $this->execUapi($username, 'DomainInfo', 'domains_data', array('format' => 'hash'));
        }
        if ($domaindata) {
            if (isset($domaindata['cpanelresult'])) {
                $domaindata = $domaindata['cpanelresult']['result']['data'];
            } elseif (isset($domaindata['result'])) {
                $domaindata = $domaindata['result']['data'];
            }
            if (isset($domaindata['main_domain'])) {
                $allDomains = array_merge($allDomains, array($domaindata['main_domain']));
            }
            if (isset($domaindata['addon_domains'])) {
                $allDomains = array_merge($allDomains, $domaindata['addon_domains']);
            }
            if (isset($domaindata['sub_domains'])) {
                $allDomains = array_merge($allDomains, $domaindata['sub_domains']);
            }
        }

        $return = array();
        foreach ($allDomains as $key => $domain) {
            $mainDir = $domain['documentroot'];
            $find_version = new PlumtexVersionsManager($mainDir);

            //Config File
            $config_file = $mainDir . "/includes/config.php";
            $config = file_exists($config_file);

            //Shop Dir
            $check_for_shop_dir = is_dir($mainDir . "/site/");

            //utilities Dir
            $check_for_utilities_dir = is_dir($mainDir . "/utilities/");

            $is_symlink = $find_version->isSymlinked();
            $symlink_target = false;
            if ($is_symlink) {
                $symlink_target = readlink($mainDir . "/libs");
                if (!isset($symlink_target) AND ! $symlink_target) {
                    $symlink_target = '';
                }
                $symlink_target = dirname($symlink_target);
            }
            if ((!$config) OR ( !$check_for_shop_dir) OR ( !$check_for_utilities_dir)) {
                continue;
            }

            //  echo $stat['ctime'];

            $version = $find_version->getCurrentVersion();
            if ($version) {
                $filectime = filectime($config_file);
                $format = "Y-m-d H:i:s";
                $domain['created_at'] = date($format, $filectime);
                $domain['version'] = $version;
                if ($is_symlink) {
                    $domain['is_symlink'] = 1;
                    $domain['symlink_target'] = $symlink_target;
                } else {
                    $domain['is_symlink'] = 0;
                    $domain['symlink_target'] = false;
                }
                if ($username) {
                    $return[$username] = $domain;
                } else {
                    $return[$key] = $domain;
                }
            }
        }
        return $return;
    }

}
