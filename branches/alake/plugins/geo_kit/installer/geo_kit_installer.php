<?php
# This class is accessed by the makelos file
class GeoKitInstaller
{
    function install()
    {
        $config_file = AK_BASE_DIR.DS.'config'.DS.'config.php';
        $backup = str_replace('config.php','BEFORE_geo_kit-config.php',$config_file);
        if(file_exists($backup)) {
            echo "\ngeo_kit is already installed\n";
            return;
        }

        #Back up app_home/config/config.php.
        if(!copy($config_file,$backup)) {
            echo "\ngeo_kit installation failed.  Backup of $config_file failed\n";
            return;
        }
        $config = file_get_contents($config_file);

        #Insert an require_once for the plugins/geo_kit/config/config.php in
        #    app_home/config/config.php just before the end.
        $plugin_dir = AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'geo_kit';
        $plugin_config_file = $plugin_dir.DS.'config'.DS.'config.php';
        $require_stmt = "require_once ($plugin_config_file)";
        $config = str_replace("\n?>",$geo_kit_config."\n?>",$config);

        if(!file_put_contents($config_file,$config)) {
            echo "\ngeo_kit installation failed.  Creation of new $config_file failed\n";
            return;
        }

        # Copy the plugin program files from plugins/geo_kit/lib to app_home/lib.
        $plugin_lib = $plugin_dir.DS."lib".DS;
        $cmd = "cp -R $plugin_lib ".AK_BASE_DIR;
        exec($cmd);
        if(!file_exists(AK_BASE_DIR.DS.'lib')) {
            if(!mkdir(AK_BASE_DIR.DS.'lib',775)) {
                echo "\ngeo_kit installation failed during attempt to copy ";
                echo " \"lib\" directory files\n";
                return;
            }
        echo "\ngeo_kit is now installed\n";
    } // function install

    function uninstall()
    {
        # Restore <project>/config/config.php to what it was before geo_kit
        # was installed.
        $config_file = AK_BASE_DIR.DS.'config'.DS.'config.php';
        $backup = str_replace('config.php','BEFORE_geo_kit-config.php',$config_file);
        $config = file_get_contents($config_file);
        if(!file_exists($backup)) {
            echo "\ngeo_kit is not installed\n";
            return;
        }
        if(!rename($backup,$config_file)) {
            echo "geo_kit uninstallation failed.  Attempt to rename $backup to $config_file failed\n";
            return;
        }

        # Delete app_home/lib/geo_kit and the files in it.
        $lib_dir = AK_BASE_DIR.DS.'lib'.DS.'geo_kit';
        exec("rm -rf $lib_dir");
        echo "\ngeo_kit has been uninstalled\n";
    }
}
?>
