<?php
include('/usr/local/cpanel/php/cpanel.php');

require_once(__DIR__ . '/lib/PlumtexPluginController.php');
require_once(__DIR__ . '/lib/PlumtexCpanelApi.php');
require_once(__DIR__ . '/lib/PlumtexView.php');
$cpapi = new PlumtexCpanelApi();

$cpanel = new CPANEL();


$controller = new PlumtexPluginController($cpanel);
$username = $controller->getUsername();
echo $cpanel->header();

if ($_POST) {
    $action = $_POST['_action'];
    if (isset($_POST["domain"])) {
        $domainData = htmlspecialchars_decode($_POST["domain"]);
        $domainData = @json_decode($domainData, true);
    }

    switch ($action) {
        case 'install':
            //var_dump($_POST);
            $admin = $controller->install();

            if (isset($domainData['domain'])) {
                $txt = "Your website is ready. You can access it from: <a target='_blank' href='http://" . $domainData['domain'] . "'>" . $domainData['domain'] . "</a>";
                print '<div class="alert alert-success"><div class="alert-message">' . $txt . '</div></div>';
            }

            break;

        case 'uninstall':
            $controller->uninstall();
            if (isset($domainData['domain'])) {
                $txt = "Artavolo has been removed from the domain " . $domainData['domain'] . ".";
                print '<div class="alert alert-danger"><div class="alert-message">' . $txt . '</div></div>';
            }

            break;
    }
}

if (isset($_GET['search']) && !$_GET['search']) {
    unset($_GET['search']);
}


$domaindata = $cpanel->uapi('DomainInfo', 'domains_data', array('format' => 'hash'));
$domaindata = $domaindata['cpanelresult']['result']['data'];
$all_domains = array_merge(array($domaindata['main_domain']), $domaindata['addon_domains'], $domaindata['sub_domains']);
$existing_installs = $controller->findInstalations();
?>

<?php
$view = new PlumtexView(__DIR__ . '/views/header.php');

$view->display();
?>
<script>
    function advancedRadioChanged() {
        var express = document.forms[document.forms.length - 1].express.value;
        document.getElementById('advanced').style.display = express == '1' ? 'none' : 'block';
    }
    function askDelete() {
        return !confirm('Are you sure you want to delete this website?');
    }
</script>

<div class="plumtex-plugin">
    <h1 class="page-header">
        <span class="page-icon"> </span>
        <span id="pageHeading">Manager</span>
    </h1>
    <div class="body-content">
        <div id="viewContent">
            <div class="row">
                <div class="col-xs-12 col-sm-8 col-md-6">
                    <p><strong>List of installed PlumTex Stores</strong></p>
                </div>

                <div class="col-xs-12">
                    <form>
                        <div id="search-area" class="form-group">
                            <div class="row input-row">
                                <div class="col-xs-12">
                                    <div class="input-group filter-controls">
                                        <input name="search" class="form-control " placeholder="Search" title="Type in your search filter." value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>">
                                        <span class="input-group-btn">
                                            <button id="search-button" type="submit" class="btn btn-default">
                                                <span class="glyphicon glyphicon-search"></span>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>


            <?php
            $view = new PlumtexView(__DIR__ . '/views/domains.php');
            $view->assign('domains', $existing_installs);
            $view->display();
            ?>


        </div>
    </div>
    <?php
    $view = new PlumtexView(__DIR__ . '/views/add_new.php');
    $view->assign('existing_installs', $existing_installs);
    $view->assign('all_domains', $all_domains);
    $view->display();
    ?>


</div>


<?php
$view = new PlumtexView(__DIR__ . '/views/footer.php');
$view->display();
?>


<?php
echo $cpanel->footer();
$cpanel->end();
