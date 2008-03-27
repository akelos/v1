<?php

define('AK_ADMIN_PLUGIN_FILES_DIR', AK_APP_PLUGINS_DIR.DS.'admin'.DS.'installer'.DS.'admin_files');

class AdminInstaller extends AkInstaller
{
    function install()
    {
        $this->files = Ak::dir(AK_ADMIN_PLUGIN_FILES_DIR, array('recurse'=> true));
        empty($this->options['force']) ? $this->check_for_collisions($this->files) : null;
        $this->copy_admin_files();
        $this->modify_routes();
        $this->promt_for_credentials();
    }

    function down_1()
    {
    }


    function check_for_collisions($directory_structure, $base_path = AK_ADMIN_PLUGIN_FILES_DIR)
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
                        $this->check_for_collisions($items, $path);
                    }
                }
            }
        }
    }

    function copy_admin_files()
    {
        $this->_copyFiles($this->files);
    }

    function modify_routes()
    {
        $path = AK_CONFIG_DIR.DS.'routes.php';
        Ak::file_put_contents($path, str_replace('<?php',"<?php \n\n \$Map->connect('/admin/:controller/:action/:id', array('controller' => 'dashboard', 'action' => 'index', 'module' => 'admin'));",Ak::file_get_contents($path)));
        //
    }

    function promt_for_credentials()
    {
        $command = AK_SCRIPT_DIR.DS.'migrate admin install';
        echo "Running command $command\n";
        echo `$command`;
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