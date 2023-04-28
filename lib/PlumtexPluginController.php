<?php

include_once(__DIR__ . '/PlumtexHooks.php');
include_once(__DIR__ . '/PlumtexCpanelApi.php');
include_once(__DIR__ . '/PlumtexLogger.php');
require_once(__DIR__ . '/traits/PlumtexFindInstalationsTrait.php');

class PlumtexPluginController {

    use PlumtexFindInstalationsTrait;

    public $logger = null;

    public function __construct($cpanel) {
        $this->cpanel = $cpanel;
        $this->logger = new PlumtexLogger();
    }

    public function install() {

        $cpapi = new PlumtexCpanelApi();

        $settings_from_admin = new PlumtexStorage();
        $settings_from_admin = $settings_from_admin->read();


        $is_symlinked = false;
        if (isset($settings_from_admin['install_type']) and $settings_from_admin['install_type'] == 'symlinked') {
            $is_symlinked = true;
        }

        $adminEmail = $_POST['admin_email'];
        $adminUsername = $_POST['admin_username'];
        $adminPassword = $_POST['admin_password'];
        $dbDriver = $_POST['db_driver'];
        //$dbDriver = 'mysql';
        $dbHost = 'localhost';


        // Prepare data
        $domainData = htmlspecialchars_decode($_POST["domain"]);
        $domainData = @json_decode($domainData);


        // $domainData = json_decode($_POST['domain']);
        $installPath = $domainData->documentroot;
        // $domainData = json_decode($_POST['domain']);


        $user = $this->getUsername();


        $dbNameLength = 5; //without prefix
        $dbPrefix = $cpapi->makeDbPrefixFromUsername($user);
        $dbSuffix = 'site';
        $dbSuffix = substr($dbSuffix, 0, $dbNameLength); //MySQL
        $dbName = $dbPrefix . $dbSuffix;
        $dbUsername = $dbName;
        $dbPass = $cpapi->randomPassword(12);


        $dbHost = $this->cpanel->uapi('Mysql', 'locate_server');
        $dbHost = $dbHost['cpanelresult']['result']['data']['remote_host'];
        if ($_POST['express'] == '0') {
            $dbDriver = $_POST['db_driver'];
            $dbHost = $_POST['db_host'];
            $dbName = $_POST['db_name'];
            $dbUsername = $_POST['db_username'];
            $dbPassword = $_POST['db_password'];
        }

        $domain = $domainData->domain;

        //@todo fix $sourcepath to be from /usr/share
        $sourcepath = $domainData->homedir;
        $sourcepath = $domainData->homedir;
        $sourcepath = '/usr/share/artavolo/latest';

        $installPath = $domainData->documentroot;


        $version_manager = new PlumtexVersionsManager($sourcepath);
        if (!$version_manager->hasDownloaded()) {
            $version_manager->download();
        }
        if (!$version_manager->hasDownloaded()) {
            return;
        }



        // $dbPassword = $dbPass = $cpapi->randomPassword(12);


        if ($dbDriver == 'sqlite') {
            //$this->log('Using sqlite for ' . $dbUsername);
            //$dbHost = 'storage/database.sqlite';
        } else {
            $this->log('Creating database user ' . $dbUsername);
            $cpapi->execUapi(false, 'Mysql', 'create_user', array('name' => $dbUsername, 'password' => $dbPass));


            $this->log('Creating database ' . $dbName);
            $cpapi->execUapi(false, 'Mysql', 'create_database', array('name' => $dbName));

            $this->log('Setting privileges ' . $dbUsername);
            $cpapi->execUapi(false, 'Mysql', 'set_privileges_on_database', array('user' => $dbUsername, 'database' => $dbName, 'privileges' => 'ALL PRIVILEGES'));
        }


        $opts = array();
        $opts['source_folder'] = $sourcepath;
        $opts['public_html_folder'] = $installPath;
        $opts['chown_user'] = $user;
        $opts['user'] = $adminUsername;
        $opts['pass'] = $adminPassword;
        $opts['email'] = $adminEmail;
        $opts['database_driver'] = $dbDriver;
        $opts['database_user'] = $dbUsername;
        $opts['database_password'] = $dbPassword;
        $opts['database_table_prefix'] = $dbPrefix;
        $opts['database_name'] = $dbName;
        $opts['database_host'] = $dbHost;


        //$opts['default_template'] = 'dream'; //@todo get from settings
        //$opts['config_only'] = 1; //@todo get from settings


        $opts['is_symlink'] = $is_symlinked;


        $opts['extra_config'] = $settings_from_admin;





//        $install_opts = array();
//        $opts['options'] = $install_opts;
        $do_install = new PlumtexInstallCommand();
        $do_install = $do_install->install($opts);
        return $do_install;
    }

    public function log($msg) {
        if (is_object($this->logger) and method_exists($this->logger, 'log')) {
            $this->logger->log($msg);
        }
    }

    public function uninstall() {
        // Prepare data
        $domainData = json_decode($_POST['domain']);
        $installPath = $domainData->documentroot;
        $dbUsername = $this->getUsername();

        $dbNameLength = 5; //without prefix
        $dbPrefix = $this->makeDBPrefix();
        $dbSuffix = 'site';
        $dbSuffix = substr($dbSuffix, 0, $dbNameLength); //MySQL
        $dbName = $dbPrefix . $dbSuffix;
        $dbUsername = $dbName;

        // Create empty install directory
        exec("rm -rf $installPath");
        mkdir($installPath);

        // Delete database
        $this->cpanel->uapi('Mysql', 'delete_database', array('name' => $dbName));
        $this->cpanel->uapi('Mysql', 'delete_user', array('name' => $dbUsername));
    }

    public function getUsername() {
        $username = $this->cpanel->exec('<cpanel print="$user">');
        return $username['cpanelresult']['data']['result'];
    }

    public function makeDBPrefix() {
        return $this->cpanel->makeDbPrefixFromUsername(false);
    }

   

}
