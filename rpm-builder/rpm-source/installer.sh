#!/bin/bash

echo "Installing Artavolo cPanel plugin...";

## Check if being ran by root
username=`whoami`
if [ "$username" != "root" ]; then
    echo "Please run this script as root";
    exit 1
fi

## Check if the WGET path is set
path_wget=`which wget`
if [ -z "$path_wget" ]; then
    echo "Missing WGET. Aborting execution"
    exit 1
fi

chmod=`chmod +x /usr/local/cpanel/bin/artavolo.cpanelplugin`

if [ -z "chmod" ]; then
    echo "Unable to CHMOD cPanel plugin"
    exit 1
fi

register=`/usr/local/cpanel/bin/register_cpanelplugin /usr/local/cpanel/bin/artavolo.cpanelplugin`

if [ -z "register" ]; then
    echo "Unable to register cPanel plugin"
    exit 1
fi

#download=`$path_wget https://api.artavolo.net/downloads/versions/latest.zip -O /var/artavolo/latest.zip`
#
#if [ -z "$download" ]; then
#    echo "Unable to retrieve latest version"
#    exit 1
#fi