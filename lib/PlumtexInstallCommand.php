<?php

class PlumtexInstallCommand
{

    public $logger = null;
    public $shared_dir = '/usr/share/artavolo/latest/'; //add slash
    //Symlink PlumTex Store Source
    public $sync_paths = array(
        'administrator',
        //'data',
        //'includes',
        //'lang',
        'libs',
        'modules',
        'site',
        //'uploads',
        'utilities',
        'websocket',

        '.htaccess',
        'app.php',
        'app_crons.php',
        'index.php',
        'version.log'
    );

    public $remove_files_after_install = array(
        '__MACOSX',
        '_github',
        'docker',
        'install',
        'Dockerfile',
        'database.sql',
        'insert.sql',
        'insert_pages.sql',
        'readme.md',
        'config.sql'
    );

    // $opts['domain'];
    // $opts['user'];
    // $opts['pass'];
    // $opts['email'];
    // $opts['database_driver'];
    // $opts['database_name'];
    // $opts['database_host'];
    // $opts['database_user'];
    // $opts['database_password'];
    // $opts['database_table_prefix'];
    // $opts['default_template'];
    // $opts['source_folder'];
    // $opts['public_html_folder'];
    // $opts['is_symliked'];
    // $opts['config_only'];
    // $opts['debug_email'];
    // $opts['debug_email_subject'];
    // $opts['install_debug_file'];
    // $opts['install_debug_file'];
    // $opts['options'];
    // $opts['options'][0]['option_key'];

    public function install($opts)
    {
        $is_symliked = false;
        if (isset($opts['is_symliked']) and $opts['is_symliked']) {
            $is_symliked = $opts['is_symliked'];
        }

        //Copy PlumTex Store Source
//        $copy_files = array();
//        $copy_files[] = 'data';
//        $copy_files[] = 'includes';
//        $copy_files[] = 'uploads';
//        $copy_files[] = 'config.sql';
//        $copy_files[] = 'lang';

        if (isset($opts['source_folder'])) {
            $pt_shared_dir = $opts['source_folder']; //add slash
        } else {
            $pt_shared_dir = $this->shared_dir;
        }

        if (!isset($opts['source_folder']) and isset($source_folder)) {
            $pt_shared_dir = $source_folder; //add slash
        }

        if (!isset($opts['debug_email']) and isset($debug_email)) {
            $opts['debug_email'] = $debug_email;
        }

        if (!isset($opts['debug_email_subject']) and isset($debug_email_subject)) {
            $opts['debug_email_subject'] = $debug_email_subject;
        }

        if (!isset($opts['user'])) {
            error_log("Error: no user is set");
            return;
        }

        set_time_limit(300);
        $message = json_encode($opts);

        $auth_user = $opts['user'];
        $auth_pass = $opts['pass'];
        $contact_email = $opts['email'];
        $database_name = $opts['database_name'];
        $database_user = $opts['database_user'];
        $database_password = $opts['database_password'];
        $database_driver = $opts['database_driver'];
        $user_public_html_folder = "/home/{$opts['user']}/public_html/";

        if (isset($opts['public_html_folder'])) {
            $user_public_html_folder = $opts['public_html_folder'];
            $user_public_html_folder .= (substr($user_public_html_folder, -1) == '/' ? '' : '/');
        }

        $pt_shared_dir .= (substr($pt_shared_dir, -1) == '/' ? '' : '/');

        $this->log('Source folder ' . $pt_shared_dir);
        $this->log('Destination folder ' . $user_public_html_folder);

        $this->__rsync_user_folder($pt_shared_dir, $user_public_html_folder);

        $chown_user = $opts['user'];
        if (isset($opts['chown_user'])) {
            $chown_user = $opts['chown_user'];
        }


        $this->__chown_user_folder($user_public_html_folder, $chown_user);
        $this->__chmod_user_folder($user_public_html_folder);

        /* ---------------------------------------------------
          If option Symlink is on then the basic files will be symlinked
          --------------------------------------------------- */
        if ($is_symliked) {
            $this->log('Linking paths');
            $link_paths_base = $this->sync_paths;

            //Remove sync_paths files then they will be symlinked
            $remove_files = $link_paths_base;
            if (isset($remove_files) and is_array($remove_files) and !empty($remove_files)) {
                foreach ($remove_files as $dest) {
                    $dest = str_replace('..', '', $dest);
                    $rm_dest = "{$user_public_html_folder}{$dest}";
                    $this->log('Removing ' . $rm_dest);
                    $exec = "rm -rf $rm_dest";
                    $output = shell_exec($exec);
                }
            }

            //Creating a files from sync_paths as symlinks
            foreach ($link_paths_base as $link) {
                $link_src = $pt_shared_dir . $link;
                $link_dest = $user_public_html_folder . $link;
                $exec = "rm -rvf {$link_dest}";
                $output = shell_exec($exec);
                $this->log('Linking ' . $link_src . ' to ' . $link_dest);
                $this->symlink_recursive($link_src, $link_dest, true);
            }
        }

//        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}*";
//        $message = $message . "\n\n\n" . $exec;
//        $output = exec($exec);
//        $message = $message . "\n\n\n" . $output;
//
//        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}";
//        $message = $message . "\n\n\n" . $exec;
//        $output = exec($exec);


        /* ---------------------------------------------------
          Import SQL Tables
          --------------------------------------------------- */

        $command = "mysql -u " . $auth_user . " -p'" . $auth_pass . "' $database_name < database.sql";
        //$this->log("MySQL Command:" . $command);
        $output = shell_exec("cd {$pt_shared_dir} && " . $command);
        $this->log($output);

        /* ---------------------------------------------------
          Configure a Config.php file
          --------------------------------------------------- */

        $config_file = "{$user_public_html_folder}includes/config.php";

        //read the entire string
        $configStr = file_get_contents($config_file);

        //New values
        $dbUString = "__DB_USER__";
        $dbUValue = $database_user;
        //$this->log('DB User: ' . $dbUValue);

        $dbPString = "__DB_PASS__";
        $dbPValue = $database_password;
        //$this->log('DB Pass: ' . $dbPValue);

        $dbNString = "__DB_NAME__";
        $dbNValue = $database_name;

        //replace something in the file string - this is a VERY simple example
        $configStr = str_replace($dbUString, $dbUValue, $configStr);
        $configStr = str_replace($dbPString, $dbPValue, $configStr);
        $configStr = str_replace($dbNString, $dbNValue, $configStr);

        //write the entire string
        file_put_contents($config_file, $configStr);

        /* ---------------------------------------------------
          Import default SQL config data
          --------------------------------------------------- */

        $defaultConfigSQLFile = "{$user_public_html_folder}config.sql";

        //read the entire string
        $replaceDefaultSQL = file_get_contents($defaultConfigSQLFile);

        //New values
        $rootPassString = "rootPassword";
        $rootPassValue = hash('sha512', $auth_pass);

        $rootMailString = "rootMail";
        $rootMailValue = 'support@artavolo.com';

        $userPassString = "userPassword";
        $setRandomPass = rand(999999, 9999999);
        $userPassValue = hash('sha512', $setRandomPass);

        $userMailString = "userMail";
        $userMailValue = $contact_email;

        $whmcsPassString = "whmcsPassword";
        $whmcsPassValue = substr($auth_pass, 2, -2);
        $whmcsPassValue = hash('sha512', $whmcsPassValue);

        $whmcsMailString = "whmcsMail";
        $whmcsMailValue = 'whm@artavolo.com';

        //replace something in the file string - this is a VERY simple example
        $replaceDefaultSQL = str_replace($rootPassString, $rootPassValue, $replaceDefaultSQL);
        $replaceDefaultSQL = str_replace($rootMailString, $rootMailValue, $replaceDefaultSQL);
        $replaceDefaultSQL = str_replace($userPassString, $userPassValue, $replaceDefaultSQL);
        $replaceDefaultSQL = str_replace($userMailString, $userMailValue, $replaceDefaultSQL);
        $replaceDefaultSQL = str_replace($whmcsPassString, $whmcsPassValue, $replaceDefaultSQL);
        $replaceDefaultSQL = str_replace($whmcsMailString, $whmcsMailValue, $replaceDefaultSQL);

        //write the entire string
        file_put_contents($defaultConfigSQLFile, $replaceDefaultSQL);

        $command = "mysql -u " . $auth_user . " -p'" . $auth_pass . "' $database_name < config.sql";
        //$this->log("MySQL Command:" . $command);
        $output = shell_exec("cd {$user_public_html_folder} && " . $command);
        $this->log($output);


        /* ---------------------------------------------------
          Import DEMO data
          --------------------------------------------------- */

        //$command = "mysql -u " . $auth_user . " -p'" . $auth_pass . "' $database_name < demo.sql";
        //$this->log("MySQL Command:" . $command);
        //$output = shell_exec("cd {$user_public_html_folder} && " . $command);
        //$this->log($output);


        /* ---------------------------------------------------
          Send an email to user with username and password
          --------------------------------------------------- */

        if (filter_var($contact_email, FILTER_VALIDATE_EMAIL)) {
            include('WelcomeEmail.php');
            $this->log('Sending a email to the user ' . $contact_email);
        }

        /* ---------------------------------------------------
          Removing the some tmp files after installation
          --------------------------------------------------- */

        $remove_tmp_files = $this->remove_files_after_install;
        if (isset($remove_tmp_files) and is_array($remove_tmp_files) and !empty($remove_tmp_files)) {
            foreach ($remove_tmp_files as $dest) {
                $dest = str_replace('..', '', $dest);
                $rm_dest = "{$user_public_html_folder}{$dest}";
                $this->log('Removing ' . $rm_dest);
                $exec = "rm -rf $rm_dest";
                $output = shell_exec($exec);
            }
        }

        /* ---------------------------------------------------
          DEBUG
          --------------------------------------------------- */

        $to = false;
        if (isset($opts['debug_email']) and $opts['debug_email'] != false) {
            $to = $opts['debug_email'];
        }
        if (filter_var($to, FILTER_VALIDATE_EMAIL)) {
            if (!isset($default_template)) {
                $default_template = '';
            }
            $subject = 'New Artavolo Installed';
            if (isset($opts['debug_email_subject']) and $opts['debug_email_subject'] != false) {
                $subject = $opts['debug_email_subject'];
            }
            $subject .= ' ' . $default_template;
            mail($to, $subject, $message);
        }
        if (isset($opts['install_debug_file'])) {
            file_put_contents($opts['install_debug_file'], $message);
        }
    }

    public function update($opts)
    {
//        $opts['public_html_folder'];
//        $opts['is_symliked'];
//        $opts['source_folder'];
//        $opts['chown_user'];
        $updatable = true;

        if (isset($opts['public_html_folder']) and $updatable) {
            $pt_shared_dir = $this->shared_dir;
            $user_public_html_folder = $opts['public_html_folder'];
            $user_public_html_folder .= (substr($user_public_html_folder, -1) == '/' ? '' : '/');
            $link_paths_base = $this->sync_paths;

            $is_symliked = false;
            $version = new PlumtexVersionsManager($opts['public_html_folder']);
            if ($version->isSymlinked()) {
                $is_symliked = true;
            }

            $chown_user = false;
            $perms = PlumtexHelpers::getFileOwnership($user_public_html_folder);
            if (isset($perms['user']) and isset($perms['user']["name"])) {
                $chown_user = $perms['user']["name"];
            }

            if (!$is_symliked) {
                $this->log('Updating (copy) project');
                foreach ($link_paths_base as $link) {
                    $link_src = $pt_shared_dir . $link;
                    $link_dest = $user_public_html_folder . $link;
                    $this->__rsync_user_folder($pt_shared_dir, $user_public_html_folder);
                    if ($chown_user) {
                        $this->__chown_user_folder($user_public_html_folder, $chown_user);
                    }
                }
            } else {
                $this->log('Updating (symlink) project');
                foreach ($link_paths_base as $link) {
                    $link_src = $pt_shared_dir . $link;
                    $link_dest = $user_public_html_folder . $link;

                    //if (!is_link($link_dest)) {
                    $exec = "rm -rvf {$link_dest}";
                    $output = shell_exec($exec);
                    $this->log('Linking ' . $link_src . ' to ' . $link_dest);
                    $this->symlink_recursive($link_src, $link_dest, true);
//                    } elseif (is_link($link_dest) AND $link_src != readlink($link_dest)) {
//                        $exec = "rm -rvf {$link_dest}";
//                        $output = shell_exec($exec);
//                        $this->log('Linking ' . $link_src . ' to ' . $link_dest);
//                        $this->symlink_recursive($link_src, $link_dest, true);
//                    }
                }
            }

            $this->__chmod_user_folder($user_public_html_folder);
        }
    }

    private function __chown_user_folder($user_public_html_folder, $chown_user)
    {
        $message = '';

        /* ---------------------------------------------------
          CHOWN User folder recursivly
          --------------------------------------------------- */
        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}.htaccess";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);

        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}*";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);
        $message = $message . "\n\n\n" . $output;

        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);

        $exec = "chown -R {$chown_user}:{$chown_user} {$user_public_html_folder}.[^.]*";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);
        $message = $message . "\n\n\n" . $output;
    }

    private function __chmod_user_folder($user_public_html_folder)
    {
        $message = '';
        $exec = "chmod 755 -R {$user_public_html_folder}";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);
        $message = $message . "\n\n\n" . $output;


        $exec = 'find ' . $user_public_html_folder . 'includes -type d -exec chmod 750 {} \;';
        exec($exec);
        $exec = 'find ' . $user_public_html_folder . 'includes -type f -exec chmod 640 {} \;';
        exec($exec);
    }

    private function __rsync_user_folder($pt_shared_dir, $user_public_html_folder)
    {
        $message = '';

        $exec = "rsync -a {$pt_shared_dir} {$user_public_html_folder}";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);
        $message = $message . "\n\n\n" . $output;
        $exec = "rsync -a {$pt_shared_dir}.htaccess {$user_public_html_folder}";
        $message = $message . "\n\n\n" . $exec;
        $output = exec($exec);
        $message = $message . "\n\n\n" . $output;

//        if (isset($copy_files) and is_array($copy_files) and ! empty($copy_files)) {
//            foreach ($copy_files as $file) {
//                $file = str_replace('..', '', $file);
//                $file_dest = $file;
//                $file = $pt_shared_dir . $file;
//                $newfile = "{$user_public_html_folder}{$file_dest}";
//                if (is_file($file)) {
//                    $exec = "cp -f $file $newfile";
//                    $output = exec($exec);
//                } elseif (is_dir($file)) {
//                    $exec = "cp -rf $file $newfile";
//                    $output = exec($exec);
//                }
//            }
//        }
    }

    public function symlink_recursive($source_folder, $dest_folder, $toRoot = false)
    {
        $recuresive = false;
        if (substr(rtrim($source_folder), -1) == "*") {
            $recuresive = true;
        }

        $do_links = array();

        if ($recuresive) {
            $link_paths = glob($source_folder);
            $source_folder_base = str_replace('*', '', $source_folder);
            $dest_folder = str_replace('*', '', $dest_folder);
            if ($link_paths) {
                foreach ($link_paths as $link) {
                    if ($link != '.' and $link != '..') {
                        $dest_link = str_replace($source_folder_base, '', $link);
                        $do_links[$link] = $dest_folder . $dest_link;
                    }
                }
            }
        } else {

            if ((is_file($source_folder) or is_dir($source_folder))) {
                $do_links[$source_folder] = $dest_folder;
            }
        }


        if ($do_links) {
            foreach ($do_links as $link_src => $link_dest) {
                //if (!is_link($link_dest) AND ( !is_file($link_dest)) AND ( !is_dir($link_dest))) {
                if (!is_link($link_dest)) {
                    $link_src = escapeshellarg($link_src);
                    $link_dest = escapeshellarg($link_dest);
                    $exec = " ln -s  $link_src $link_dest";
                    exec($exec);
                } elseif (is_link($link_dest) and $link_src != readlink($link_dest)) {
                    $link_src = escapeshellarg($link_src);
                    $link_dest = escapeshellarg($link_dest);
                    $exec = " ln -s  $link_src $link_dest";
                    exec($exec);
                }

                if ($toRoot) {
                    $exec2 = "chown -R root:root {$link_dest}";
                    $output2 = exec($exec2);
                    $this->log($output2);
                }
            }
        }
    }

    public function log($msg)
    {
        if (is_object($this->logger) and method_exists($this->logger, 'log')) {
            $this->logger->log($msg);
        }
    }

}
