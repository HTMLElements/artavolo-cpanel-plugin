<?php

//Use for debug
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once('/usr/local/cpanel/php/WHM.php');
require_once(__DIR__ . '/../lib/PlumtexStorage.php');
require_once(__DIR__ . '/../lib/PlumtexView.php');
require_once(__DIR__ . '/../lib/PlumtexVersionsManager.php');
require_once(__DIR__ . '/../lib/PlumtexAdminController.php');
require_once(__DIR__ . '/../lib/PlumtexInstallCommand.php');


$controller = new PlumtexAdminController();
$versions = new PlumtexVersionsManager();
$install_command = new PlumtexInstallCommand();
$storage = new PlumtexStorage();
$keyData = array();
$settings = $storage->read();

// Check white label key


if (isset($_POST['key']) or isset($_POST['save_settings'])) {
    $storage->save($_POST);
    $settings = $storage->read();
}

$user_key = isset($settings['key']) ? $settings['key'] : '';

if ($user_key) {
    $keyData = $controller->getLicenseData($user_key);
}

if (isset($_POST['download_cms'])) {
    $versions->download();
}

if (isset($_POST['remove_current_version'])) {
    $versions->removeCurrentVersion();
}

if (isset($_POST['update_plugin'])) {
    $versions->downloadPlugin();
}
if (isset($_POST['download_userfiles'])) {
    $versions->downloadExtraContent($user_key);
}


if (isset($_POST["_action"])) {
    $_action = $_POST["_action"];
    unset($_POST["_action"]);

    if ($_action == "_do_update") {

        if (isset($_POST["domain"])) {
            $domain_update_data = htmlspecialchars_decode($_POST["domain"]);
            $domain_update_data = @json_decode($domain_update_data, true);

            $update_opts = array();
            $update_opts['public_html_folder'] = $domain_update_data["documentroot"];
            $install_command->update($update_opts);

        }
    }

    if ($_action == "_save_branding") {
        $settings = $storage->read();
        $settings['branding'] = $_POST;
        $storage->save($settings);
    }
}
$branding = false;
if (isset($settings['branding'])) {
    $branding = $settings['branding'];
}


$current_version = $versions->getCurrentVersion();
$latest_version = $versions->getLatestVersion();
$latest_plugin_version = $versions->getLatestPluginVersion();
$current_plugin_version = $versions->getCurrentPluginVersion();

$latest_dl_date = $versions->getCurrentVersionLastDownloadDateTime();


//$autoInstall = isset($storedData->auto_install) && $storedData->auto_install == '1';
//$install_type = isset($storedData->install_type) && $storedData->install_type == 'symlinked';
//$user_key = isset($storedData->key) ? $storedData->key : '';


$domains = $controller->get_installations_across_server();


WHM::header('Plumtex Settings', 1, 1);
?>
<?php
$view = new PlumtexView(__DIR__ . '/../views/header.php');

$view->display();


?>

<div class="alert alert-info js-cms-plugin" style="display: none;">
    <div class="content">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    Your Artavolo version is out of date. Update it!
                </div>
                <div class="col-md-6" style="text-align: right;">
                    <button name="download_cms" value="download_cms" class="btn btn-primary btn-xs">UPDATE ARTAVOLO
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="alert alert-info js-update-plugin" style="display: none;">
    <div class="content">
        <form method="POST">
            <div class="row">
                <div class="col-md-6">
                    Your Artavolo cPanel Plugin is out of date. Update it!
                </div>
                <div class="col-md-6" style="text-align: right;">
                    <button name="update_plugin" value="update_plugin" class="btn btn-primary btn-xs">UPDATE PLUGIN
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<hr>


<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Version</h2>
    </div>
    <div class="panel-body">
        <?php
        $view = new PlumtexView(__DIR__ . '/../views/download.php');
        $view->assign('key', $user_key);
        $view->assign('key_data', $keyData);
        $view->assign('current_version', $current_version);
        $view->assign('latest_version', $latest_version);
        $view->assign('last_download_date', $latest_dl_date);
        $view->assign('latest_plugin_version', $latest_plugin_version);
        $view->assign('current_plugin_version', $current_plugin_version);
        $view->display();
        ?>
    </div>
</div>

<div class="panel panel-default" style="display: none;">
    <div class="panel-heading">
        <h2 class="panel-title">Settings</h2>
    </div>
    <div class="panel-body">
        <?php
        $view = new PlumtexView(__DIR__ . '/../views/settings.php');
        $view->assign('settings', $settings);
        $view->display();
        ?>
    </div>
</div>

<?php /*<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">White label</h2>
    </div>
    <div class="panel-body">
        <?php
        $view = new PlumtexView(__DIR__ . '/../views/white_label.php');
        $view->assign('key', $user_key);
        $view->assign('key_data', $keyData);
        $view->assign('current_version', $current_version);
        $view->assign('latest_version', $latest_version);
        $view->assign('last_download_date', $latest_dl_date);
        $view->assign('latest_plugin_version', $latest_plugin_version);
        $view->assign('current_plugin_version', $current_plugin_version);
        $view->assign('settings', $settings);
        $view->assign('branding', $branding);
        $view->display();
        ?>
    </div>
</div>*/ ?>

<div class="panel panel-default">
    <div class="panel-heading">
        <h2 class="panel-title">Installations</h2>
    </div>
    <div class="panel-body">
        <?php
        $view = new PlumtexView(__DIR__ . '/../views/domains.php');
        $view->assign('domains', $domains);
        $view->assign('admin_view', true);
        $view->display();
        ?>
    </div>
</div>


<?php
$view = new PlumtexView(__DIR__ . '/../views/footer.php');
$view->display();
?>

<?php
WHM::footer();
?>
