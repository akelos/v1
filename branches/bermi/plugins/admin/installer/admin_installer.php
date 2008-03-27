<?php

define('AK_ADMIN_PLUGIN_FILES_DIR', AK_APP_PLUGINS_DIR.DS.'admin'.DS.'installer'.DS.'admin_files');

class AdminInstaller extends AkInstaller
{
    function up_1()
    {
        $this->files = Ak::dir(AK_ADMIN_PLUGIN_FILES_DIR, array('recurse'=> true));
        empty($this->options['force']) ? $this->checkForCollisions($this->files) : null;
        $this->copyAdminFiles();

        echo "\nWe need some details for setting up the admin.\n\n ";
        $this->modifyRoutes();
        $this->runMigration();
        echo "\n\nInstallation completed\n";
    }

    function down_1()
    {
    }


    function checkForCollisions($directory_structure, $base_path = AK_ADMIN_PLUGIN_FILES_DIR)
    {
        foreach ($directory_structure as $k=>$node){
            $path = str_replace(AK_ADMIN_PLUGIN_FILES_DIR, AK_BASE_DIR, $base_path.DS.$node);
            if(is_file($path)){
                trigger_error(Ak::t('File %file exists.', array('%file'=>$path)), E_USER_ERROR);
                exit;
            }elseif(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        $this->checkForCollisions($items, $path);
                    }
                }
            }
        }
    }

    function copyAdminFiles()
    {
        $this->_copyFiles($this->files);
    }

    function modifyRoutes()
    {
        $preffix = '/'.trim($this->promtUserVar('Admin url preffix',  array('default'=>'/admin/')), "\t /").'/';
        $path = AK_CONFIG_DIR.DS.'routes.php';
        Ak::file_put_contents($path, str_replace('<?php',"<?php \n\n \$Map->connect('$preffix:controller/:action/:id', array('controller' => 'dashboard', 'action' => 'index', 'module' => 'admin'));",Ak::file_get_contents($path)));

    }

    function runMigration()
    {
        include_once(AK_APP_INSTALLERS_DIR.DS.'admin_plugin_installer.php');
        $Installer =& new AdminPluginInstaller();

        $Installer->root_details = array(
        'email' => $this->promtUserVar('Master account login. Must be a valid email',  array('default'=>'root@example.com')),
        'password' => $this->promtUserVar('Root password.', array('default'=>'admin')),
        );

        echo "Running the admin plugin migration\n";
        //$Installer->uninstall();
        $Installer->install();


    }

    function promtUserVar($message, $options = array())
    {
        $f = fopen("php://stdin","r");
        $default_options = array(
        'default' => null,
        'optional' => false,
        );

        $options = array_merge($default_options, $options);

        echo "\n".$message.(empty($options['default'])?'': ' ['.$options['default'].']').': ';
        $user_input = fgets($f, 25600);
        $value = trim($user_input,"\n\r\t ");
        $value = empty($value) ? $options['default'] : $value;
        if(empty($value) && empty($options['optional'])){
            echo "\n\nThis setting is not optional.";
            fclose($f);
            return $this->promtUserVar($message, $options);
        }
        fclose($f);
        return empty($value) ? $options['default'] : $value;
    }


    function _copyFiles($directory_structure, $base_path = AK_ADMIN_PLUGIN_FILES_DIR)
    {
        foreach ($directory_structure as $k=>$node){
            $path = $base_path.DS.$node;
            if(is_dir($path)){
                echo 'Creating dir '.$path."\n";
                $this->_makeDir($path);
            }elseif(is_file($path)){
                echo 'Creating file '.$path."\n";
                $this->_copyFile($path);
            }elseif(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        echo 'Creating dir '.$path."\n";
                        $this->_makeDir($path);
                        $this->_copyFiles($items, $path);
                    }
                }
            }
        }
    }

    function _makeDir($path)
    {
        $dir = str_replace(AK_ADMIN_PLUGIN_FILES_DIR, AK_BASE_DIR,$path);
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }

    function _copyFile($path)
    {
        $destination_file = str_replace(AK_ADMIN_PLUGIN_FILES_DIR, AK_BASE_DIR,$path);
        copy($path, $destination_file);
        $source_file_mode =  fileperms($path);
        $target_file_mode =  fileperms($destination_file);
        if($source_file_mode != $target_file_mode){
            chmod($destination_file,$source_file_mode);
        }
    }

}

?>