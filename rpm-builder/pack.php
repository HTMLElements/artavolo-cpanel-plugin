<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/config.php';

$workdir = __DIR__ . '/workdir/core/rpm/cpanel-artavolo-' . time();
if(!is_dir($workdir)){
    mkdir_recursive($workdir);
}


$workdir_plugin = __DIR__ . '/workdir/core/rpm/cpanel-plugin/';
if(!is_dir($workdir_plugin)){
    mkdir_recursive($workdir_plugin);
}
$workdir_plugin = realpath($workdir_plugin);
if(!$workdir_plugin or !is_dir($workdir_plugin)){
    exit('error 18');
}
$workdir_plugin = $workdir_plugin.'/';
$cleanup = "rm -Rvf {$workdir_plugin}*";
print $cleanup."\n\n";


exec($cleanup);

//cpanel-plugin-artavolo-main = the main folder of unzipped master.zip
$repo_zip_url = 'https://github.com/HTMLElements/artavolo-cpanel-plugin/archive/master.zip';
$repo_zip_folder = 'artavolo-cpanel-plugin-main';
$wget = "wget -q {$repo_zip_url} -O {$workdir_plugin}master.zip";
print $wget."\n\n";
exec($wget);


$unzip = "unzip -qqo {$workdir_plugin}master.zip -d {$workdir_plugin} ";
print $unzip."\n\n";
exec($unzip);

$spec = new \wapmorgan\rpm\Spec();
$spec
    ->setPackageName("artavolo-cms")
    ->setVersion($rpm_ver)
    ->setDescription("Streamline your work")
    ->setSummary('Artavolo Builder')
    ->setRelease('1')
    ->setUrl('https://artavolo.com')
    ->setPost('bash /usr/local/cpanel/artavolo/install/installer.sh');

$packager = new \wapmorgan\rpm\Packager();

$packager->setOutputPath($workdir);
$packager->setSpec($spec);

//$packager->addMount(__DIR__ . '/rpm-source', '/usr/local/artavolo');
$packager->addMount($workdir_plugin . $repo_zip_folder, '/usr/local/cpanel/artavolo');

//Creates folders using mount points
$packager->run();

//Get the rpmbuild command
shell_exec($packager->build());

//$workdir_plugin