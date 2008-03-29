<?php

define('AK_SIFR_HELPER_PLUGIN_FILES_DIR', AK_APP_PLUGINS_DIR.DS.'sifr_helper'.DS.'installer'.DS.'sifr_helper_files');

class SifrHelperInstaller extends AkInstaller
{
    function up_1()
    {
        $this->files = Ak::dir(AK_SIFR_HELPER_PLUGIN_FILES_DIR, array('recurse'=> true));
        empty($this->options['force']) ? $this->checkForCollisions($this->files) : null;
        $this->copySifrHelperFiles();
    }

    function down_1()
    {
    }

    function checkForCollisions($directory_structure, $base_path = AK_SIFR_HELPER_PLUGIN_FILES_DIR)
    {
        foreach ($directory_structure as $k=>$node){
            $path = str_replace(AK_SIFR_HELPER_PLUGIN_FILES_DIR, AK_BASE_DIR, $base_path.DS.$node);
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

    function copySifrHelperFiles()
    {
        $this->_copyFiles($this->files);
    }

    function _copyFiles($directory_structure, $base_path = AK_SIFR_HELPER_PLUGIN_FILES_DIR)
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
        $dir = str_replace(AK_SIFR_HELPER_PLUGIN_FILES_DIR, AK_BASE_DIR, $path);
        if(!is_dir($dir)){
            mkdir($dir);
        }
    }

    function _copyFile($path)
    {
        $destination_file = str_replace(AK_SIFR_HELPER_PLUGIN_FILES_DIR, AK_BASE_DIR, $path);
        copy($path, $destination_file);
        $source_file_mode =  fileperms($path);
        $target_file_mode =  fileperms($destination_file);
        if($source_file_mode != $target_file_mode){
            chmod($destination_file,$source_file_mode);
        }
    }

}

?>