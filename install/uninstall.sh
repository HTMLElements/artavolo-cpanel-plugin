#!/bin/bash

echo "Uninstalling Artavolo cPanel plugin...";

## Check if being ran by root

username=`whoami`
if [ "$username" != "root" ]; then
    echo "Please run this script as root";
    exit 1
fi

unregister_cp=`/usr/local/cpanel/scripts/uninstall_plugin /usr/local/cpanel/artavolo/install/pt-plugin`

if [ -z "$unregister_cp" ]; then
    echo "Unable to remove cPanel plugin"
    exit 1
fi

unregister_whm=`/usr/local/cpanel/bin/unregister_appconfig /usr/local/cpanel/artavolo/install/artavolo.conf`

if [ -z "$unregister_whm" ]; then
    echo "Unable to remove WHM plugin"
    exit 1
fi

unregister_hooks=`/usr/local/cpanel/bin/manage_hooks delete script /usr/local/cpanel/artavolo/hooks/pt_hooks.php`

if [ -z "$unregister_hooks" ]; then
    echo "Unable to remove hooks"
    exit 1
fi

## Remove symlinks

echo "Removing symlinks...";

step1=`rm -rf /usr/local/cpanel/whostmgr/docroot/cgi/3rdparty/plumtex`

if [ -n "$step1" ]; then
    echo "Unable to complete step 1"
fi

step11=`rm -rf /usr/local/cpanel/base/frontend/paper_lantern/plumtex.live.php`

if [ -n "$step11" ]; then
    echo "Unable to complete step 1-1"
fi
step2=`rm -rf /usr/local/cpanel/whostmgr/docroot/3rdparty/plumtex`

if [ -n "$step2" ]; then
    echo "Unable to complete step 2"
fi

step3=`rm -rf /usr/local/cpanel/whostmgr/docroot/addon_plugins/plumtex.png`

if [ -n "$step3" ]; then
    echo "Unable to complete step 3"
fi

step4=`rm -rf /var/cpanel/plumtex`

if [ -n "$step4" ]; then
    echo "Unable to complete step 4"
fi
