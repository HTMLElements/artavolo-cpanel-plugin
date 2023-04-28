<?php
include_once(__DIR__ . '/PlumtexHooks.php');
include_once(__DIR__ . '/PlumtexCpanelApi.php');
include_once(__DIR__ . '/PlumtexLogger.php');
require_once(__DIR__ . '/traits/PlumtexFindInstalationsTrait.php');
require_once(__DIR__ . '/traits/PlumtexLicenseDataTrait.php');


class PlumtexAdminController
{
    use PlumtexFindInstalationsTrait;
    use PlumtexLicenseDataTrait;

    public $logger = null;
    public $cpapi = null;

    public function __construct()
    {
        $this->cpapi = new PlumtexCpanelApi();;
        $this->logger = new PlumtexLogger();

    }

    public function get_installations_across_server()
    {
        $return = array();
        // whmapi1 listaccts search=username searchtype=user
        $accounts = $this->cpapi->execApi1('listaccts', array('search' => '', 'searchtype' => 'user'));
        if ($accounts and isset($accounts['data']) and isset($accounts['data']['acct'])) {
            foreach ($accounts['data']['acct'] as $account) {
                if (isset($account['user'])) {
                    $user_domains = $this->findInstalations($account['user']);
                }
                if ($user_domains) {
                    $return = array_merge($return, $user_domains);

                }
            }
        }
        return $return;
    }






}