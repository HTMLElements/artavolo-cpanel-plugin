<?php

require_once(__DIR__ . '/PlumtexHelpers.php');

class PlumtexVersionsManager {

    private $sharedDir = '/usr/share/artavolo/latest';
    private $pluginDir = '/usr/local/cpanel/artavolo';
    private $tempZipFile = null;
    private $tempZipFilePlugin = null;

    private $mainZipFolder = 'artavolo-main';

    public function __construct($sharedDir = null) {
        if ($sharedDir) {
            $this->sharedDir = $sharedDir;
        }

        $this->tempZipFile = $this->sharedDir . '/pt-download.zip';
        $this->tempZipFilePlugin = $this->sharedDir . '/pt-cpanel-plugin.rpm';
    }

    public function getCurrentVersionLastDownloadDateTime() {
        $version_file = file_exists($this->sharedDir . "/version.log");

        if ($version_file) {
            $version = filectime($this->sharedDir . "/version.log");
            $version = str_replace('Installed Artavolo v-', '', $version);
            if ($version) {
                return date('d.m.Y H:i:s', $version);
            }
        }
    }

    public function getCurrentVersion() {

        $version_file = file_exists($this->sharedDir . "/version.log");
        $version = 'unknown';
        if ($version_file) {
            $version = file_get_contents($this->sharedDir . "/version.log");
            $version = str_replace('Installed Artavolo v-', '', $version);
            $version = strip_tags($version);
        }
        return $version;
    }

    public function getCurrentPluginVersion() {

        $f = __DIR__ . DIRECTORY_SEPARATOR . '../version.log';
        $version_file = file_exists($f);
        $version = 'unknown';
        if ($version_file) {
            $version = file_get_contents($f);
            $version = strip_tags($version);
        }
        return $version;
    }

    public function getLatestVersion($force_check = false) {
        $data = $this->getLatestVersionData($force_check);
        if (isset($data['version'])) {
            return $data['version'];
        }
    }

    public function getLatestPluginVersion($force_check = false) {
        $data = $this->getLatestVersionData($force_check);
        if (isset($data['plugin']) and isset($data['plugin']) and isset($data['plugin']['version'])) {
            return $data['plugin']['version'];
        }
    }

    public function getLatestVersionData($force_check = false) {
        $cache_file = '/usr/share/artavolo/version_check_cache.txt';

        $current_time = time();

        $update_cache = false;
        if ($force_check) {
            $update_cache = true;
        } elseif (!is_file($cache_file)) {
            $update_cache = true;
        } else if (filemtime($cache_file) and ( filemtime($cache_file) < $current_time - 3600)) {
            $update_cache = true;
        }


        if (!$update_cache) {

            if (is_file($cache_file) and ! is_writable($cache_file)) {
                $update_cache = false;
            } else {

                $data = file_get_contents($cache_file);

                if (!$data) {
                    $update_cache = true;
                }
            }
        }

        if ($update_cache) {
            $url = 'https://api.artavolo.net/?action=get_cms_version';
            $data = file_get_contents($url);
            if (!$data) {
                return false;
            }
            $data = @json_decode($data, true);

            $url2 = 'https://api.artavolo.net/?action=get_cpanel_plugin_version';
            $data2 = file_get_contents($url2);
            $data2 = @json_decode($data2, true);

            if ($data and $data2) {
                $data['plugin'] = $data2;
            }
            $data = json_encode($data);

            if (is_file($cache_file) and $data) {
                $fp = fopen($cache_file, 'w+');
                fwrite($fp, $data);
                fclose($fp);
            }
        }


        if (!$data)
            return false;

        $data = @json_decode($data, true);

        return $data;
    }

    public function download() {
        if ($this->hasDownloaded()) {
            exec("rm -rf {$this->sharedDir}");
        }

        if (!is_dir($this->sharedDir)) {
            PlumtexHelpers::mkdirRecursive($this->sharedDir);
        }


        $latest = $this->getLatestVersionData();
        if (!isset($latest['url'])) {
            return;
        }

        PlumtexHelpers::download($latest['url'], $this->tempZipFile);
        
        exec("unzip -o {$this->tempZipFile} -d {$this->sharedDir}");
        unlink($this->tempZipFile);
        exec("cd {$this->sharedDir} && mv -f {$this->mainZipFolder}/{.,}* {$this->sharedDir}/ && rm -rf {$this->mainZipFolder}");
        exec("cd {$this->sharedDir}/libs && git clone https://github.com/HTMLElements/artavolo-vendor.git && mv {$this->sharedDir}/libs/artavolo-vendor {$this->sharedDir}/libs/vendor");
    }
    
     public function removeCurrentVersion() {       
        exec("rm -rf {$this->sharedDir}");
        
        if (!is_dir($this->sharedDir)) {
            PlumtexHelpers::mkdirRecursive($this->sharedDir);
        }
    }

    public function hasDownloaded() {
        return is_dir($this->sharedDir) && file_exists("{$this->sharedDir}/version.log");
    }

    public function isSymlinked() {
        $is_symlink = false;
        if (!is_file($this->sharedDir . '/.standalone') AND ( is_link($this->sharedDir . "/libs") OR ( !is_dir($this->sharedDir . "/libs")))) {
            $is_symlink = true;
        }

        return $is_symlink;
    }

    public function downloadPlugin() {


        $data = $this->getLatestVersionData();

        if (isset($data['plugin'])
                and isset($data['plugin'])
                and isset($data['plugin']['version'])
                and isset($data['plugin']['url'])
        ) {
            if (is_file($this->tempZipFilePlugin)) {
                unlink($this->tempZipFilePlugin);
            }
            $url = $data['plugin']['url'];

            PlumtexHelpers::download($url, $this->tempZipFilePlugin);


            if (is_file($this->tempZipFilePlugin)) {
                $update = 'rpm -Uvh --force ' . $this->tempZipFilePlugin;
                exec($update);
            }
        }
    }

}
