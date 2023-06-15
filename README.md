# cPanel Artavolo Plugin

## Installation

### Using RPM

Run the following commands:

```
wget https://api.artavolo.net/downloads/rpm/artavolo-cpanel.rpm
sudo yum install artavolo-cpanel.rpm
```

or

```
wget https://api.artavolo.net/downloads/rpm/artavolo-cpanel.rpm && sudo yum install artavolo-cpanel.rpm
```
 
### Manual installation

* Get the plugin source code from [HTMLElements/artavolo-cpanel-plugin](https://github.com/HTMLElements/artavolo-cpanel-plugin).
* Place the files in `/usr/local/cpanel/artavolo`.
* Run the following script:

```
sh /usr/local/cpanel/artavolo/install/installer.sh
```

## Find The Plugin

* Login to WHM, search for "Artavolo" and open the plugin settings page.
* Add the "Artavolo" feature to plans you wish to have Artavolo installed with them.
* Login to cPanel and open the plugin under "Software". From that page Artavolo can be manually installed to any of the user's domains.


### Update 

```
rm artavolo-cpanel.rpm
wget https://api.artavolo.net/downloads/rpm/artavolo-cpanel.rpm
sudo rpm -Uvh artavolo-cpanel.rpm
```

or 


```
wget https://api.artavolo.net/downloads/rpm/artavolo-cpanel.rpm && sudo rpm -Uvh artavolo-cpanel.rpm
```

### Uninstall
 
* Run the following script:

```
sh /usr/local/cpanel/artavolo/install/uninstall.sh
sudo yum remove artavolo-cms && rm artavolo-cpanel.rpm
```

or

```
sh /usr/local/cpanel/artavolo/install/uninstall.sh && sudo yum remove artavolo-cms && rm artavolo-cpanel.rpm
```

# Usage

### You must set your real hostname
![hostname_change.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/hostname_change.png "")


### Select the feature list you want to edit
Select the feature list, click on "edit" button and add the Artavolo feature

![setup_feature.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/setup_feature.png "")

### Setup EasyApache 4

Install PHP version 7.4 or later

Make sure you have the required php extensions enabled.

You need gd, dom, openssl, zip, curl, mb_string and iconv and other extensions to be enabled.


Then you have to provision the EasyApache Profile.

![easyapache_provision.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/easyapache_provision.png "")

![easyapache_provision_confirm.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/easyapache_provision_confirm.png "")


Please use PHP 7.4 or later.


![easyapache_php_ver.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/easyapache_php_ver.png "")

## Find The Plugin

* Login to WHM, search for "Artavolo" and open the plugin settings page.
* Add the "Artavolo" feature to plans you wish to have Artavolo installed with them.
* Login to cPanel and open the plugin under "Software". From that page Artavolo can be manually installed to any of the user's domains.

### Search for Artavolo in the sidebar
![setup_mw.png](https://raw.githubusercontent.com/microweber-dev/cpanel-plugin/master/assets/setup_mw.png "")

### You now need setup your database type and install type

![setup_install_settings.png](https://raw.githubusercontent.com/microweber-dev/cpanel-plugin/master/assets/setup_install_settings.png "")

* If you select "Automatically install Artavolo on new domains creation" , this will install the system when you create new user.
* If you select "Allow users to Manually install Artavolo from cPanel" , this will allow the users to install manually when they login in their panel
* If you select "Disabled for all users" this will disable the system for all users




### For Symlink setup

If you use Symlink configuration you can save a lot of disk space and use single code-base for all websites

Make sure your check on  And set `Symlink Protection` to "Off" under "Apache Configuration > Global Configuration"



![setup_symlink2.png](https://raw.githubusercontent.com/HTMLElements/artavolo-cpanel-plugin/main/assets/setup_symlink2.png "")

