<?php
include_once 'helpers.php';

$rpm_ver = trim(file_get_contents(dirname(__DIR__) . '/version.log'));
$rpm_package_name = "artavolo-cms";

$workdir = __DIR__ . '/../../workdir/core/rpm/cpanel-artavolo';
$yum_repo = __DIR__ . '/../../public_html/rpm';

if(!is_dir($yum_repo)){
    mkdir_recursive($yum_repo);
}
