#!/bin/bash

echo "Installing Artavolo cPanel plugin...";

## Check if being ran by root

username=`whoami`
if [ "$username" != "root" ]; then
    echo "Please run this script as root";
    exit 1
fi

## Create symlinks

echo "Creating dirs...";

step1=`mkdir -p /usr/local/cpanel/whostmgr/docroot/cgi/3rdparty/artavolo/`
if [ -n "$step1" ]; then
    echo "Unable to complete step mkdir /usr/local/cpanel/whostmgr/docroot/cgi/3rdparty/artavolo/"
    echo "$step1"
fi

step12=`mkdir -p /usr/local/cpanel/artavolo/`
if [ -n "$step12" ]; then
    echo "Unable to complete step mkdir /usr/local/cpanel/artavolo/"
    echo "$step1"
fi

step13=`mkdir -p /usr/local/cpanel/artavolo/storage`
if [ -n "$step12" ]; then
    echo "Unable to complete step mkdir /usr/local/cpanel/artavolo/storage"
    echo "$step1"
fi

chmod_files=`chmod +x -R /usr/local/cpanel/artavolo`

if [ -n "$chmod_files" ]; then
    echo "Unable to CHMOD the cPanel plugin"
fi



unregister_cp=`/usr/local/cpanel/scripts/uninstall_plugin /usr/local/cpanel/artavolo/install/pt-plugin`

if [ -z "$unregister_cp" ]; then
    echo "Cleaning up cPanel plugin"
    exit 1
fi

unregister_whm=`/usr/local/cpanel/bin/unregister_appconfig /usr/local/cpanel/artavolo/install/artavolo.conf`

if [ -z "$unregister_whm" ]; then
    echo "Cleaning up WHM plugin"
    exit 1
fi

unregister_hooks=`/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/artavolo/hooks/pt_hooks.php`

if [ -z "$unregister_hooks" ]; then
    echo "Cleaning up hooks"
    exit 1
fi




register_cp=`/usr/local/cpanel/scripts/install_plugin /usr/local/cpanel/artavolo/install/pt-plugin`

if [ -z "$register_cp" ]; then
    echo "Unable to register cPanel plugin"
    exit 1
fi

register_whm=`/usr/local/cpanel/bin/register_appconfig /usr/local/cpanel/artavolo/install/artavolo.conf`

if [ -z "$register_whm" ]; then
    echo "Unable to register WHM plugin"
    exit 1
fi




step2=`ln -s /usr/local/cpanel/artavolo/whm/index.cgi /usr/local/cpanel/whostmgr/docroot/cgi/3rdparty/artavolo/index.cgi`

if [ -n "$step2" ]; then
    echo "Unable to complete step 2"
fi

step21=`ln -s /usr/local/cpanel/artavolo/artavolo.live.php /usr/local/cpanel/base/frontend/paper_lantern/artavolo.live.php`

if [ -n "$step21" ]; then
    echo "Unable to complete step 2-1"
fi

step3=`mkdir /usr/local/cpanel/whostmgr/docroot/3rdparty/artavolo`

if [ -n "$step3" ]; then
    echo "Unable to complete step 3"
fi

step4=`ln -s /usr/local/cpanel/artavolo/whm/admin.php /usr/local/cpanel/whostmgr/docroot/3rdparty/artavolo/admin.php`

if [ -n "$step4" ]; then
    echo "Unable to complete step 4"
fi

step5=`ln -s /usr/local/cpanel/artavolo/install/pt-plugin/artavolo.png /usr/local/cpanel/whostmgr/docroot/addon_plugins/artavolo.png`

if [ -n "$step5" ]; then
    echo "Unable to complete step 5"
fi

step6=`ln -s /usr/local/cpanel/artavolo/hooks /var/cpanel/artavolo`

if [ -n "$step6" ]; then
    echo "Unable to complete step 6"
fi

## Register WHM hooks

register_hooks=`/usr/local/cpanel/bin/manage_hooks add script /usr/local/cpanel/artavolo/hooks/pt_hooks.php`

if [ -z "$register_hooks" ]; then
    echo "Unable to register hooks"
    exit 1
fi