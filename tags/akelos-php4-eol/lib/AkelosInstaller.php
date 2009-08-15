<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package AkelosFramework
 * @subpackage Installer
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

class AkelosInstaller
{
    var $options = array();
    var $errors = array();

    function AkelosInstaller($options)
    {
        $default_options = array(
        'source' => $this->_absolutePath(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'),
        'force' => false,
        'skip' => false,
        'quiet' => false,
        'public_html' => false,
        'dependencies' => false,
        'version'=>false
        );
        $this->options = array_merge($default_options, $options);
        if (isset($this->options['version']) && $this->options['version']!==false) {
            die(file_get_contents('version.txt')."\n");
        }
        $this->options['directory'] = $this->_absolutePath(@$this->options['directory']);

        if(empty($this->options['directory'])){
            trigger_error('You must supply a valid destination path', E_USER_ERROR);
        }

        $this->source_tree = Ak::dir($this->options['source'],array('dirs'=>true,'recurse'=>true));

        if(empty($this->options['dependencies'])){
            $this->framework_dirs = array('lib', 'vendor', 'test');

            foreach ($this->framework_dirs as $framework_dir){
                foreach ($this->source_tree as $k => $v){
                    if(isset($v[$framework_dir])){
                        unset($this->source_tree[$k]) ;
                    }
                }
            }
        }

        $this->destination_tree = Ak::dir($this->options['directory'],array('dirs'=>true,'recurse'=>true));
    }

    function install()
    {
        if(empty($this->destination_tree) || !empty($this->options['force'])){
            if(!is_dir($this->options['directory'])){
                if(!$this->_makeDir($this->options['directory'])){
                    $this->addError("Can't create directory: " . $this->options['directory']);
                    return false;
                }
            }

            $this->_copyFrameworkFiles($this->source_tree, $this->options['source']);

            if(empty($this->options['dependencies'])){
                $this->_setupApplicationTestingEnvironment();
                $this->_linkDependencies();
            }

            $this->runEvironmentSpecificTasks();

            $this->_linkPublicHtmlFolder();

        }else{
            $this->addError('Installation directory is not empty. Add --force if you want to override existing files');
        }


    }


    function _setupApplicationTestingEnvironment()
    {
        $source_test_dir = $this->options['source'].DS.'test';
        $test_dir = $this->options['directory'].DS.'test';

        $this->_makeDir($test_dir);
        $this->_copyFile($source_test_dir.DS.'app.php');

        $this->_makeDir($test_dir.DS.'fixtures');
        $this->_makeDir($test_dir.DS.'fixtures'.DS.'app');

        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'app'.DS.'application_controller.php');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'app'.DS.'base_action_controller.php');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'app'.DS.'shared_model.php');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'app'.DS.'base_active_record.php');

        $this->_makeDir($test_dir.DS.'fixtures'.DS.'config');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'config'.DS.'config.php');

        $this->_makeDir($test_dir.DS.'fixtures'.DS.'data');
        $this->_makeDir($test_dir.DS.'fixtures'.DS.'public');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'public'.DS.'.htaccess');
        $this->_copyFile($source_test_dir.DS.'fixtures'.DS.'public'.DS.'index.php');
    }


    function _linkPublicHtmlFolder()
    {
        if(!empty($this->options['public_html'])){
            if(function_exists('symlink')){
                $this->options['public_html'] = $this->_absolutePath($this->options['public_html']);
                $link_info = @linkinfo($this->options['public_html']);
                if(!is_numeric($link_info) || $link_info < 0){
                    $this->yield("\n    Adding symbolic link ".$this->options['public_html'].' to the public web server.');
                    if(@symlink($this->options['directory'].DS.'public',$this->options['public_html'])){
                        return true;
                    }
                }
            }
            $this->yield("\n    Could not create a symbolic link of ".$this->options['directory'].DS.'public'.' at '.$this->options['public_html']);

        }else{
            $this->_addRootLevelDispatcher();
            $this->_addHtaccessDirectoryProtection();
        }
        return false;
    }

    function _linkDependencies()
    {
        $this->yield("\n    Linking the application with the framework at ".$this->options['source'])."\n";
        foreach (array(
        'config'.DS.'DEFAULT-config.php',
        'app'.DS.'controllers'.DS.'framework_setup_controller.php') as $file){
            if(file_exists($this->options['directory'].DS.$file)){
                $file_contents = str_replace("// defined('AK_FRAMEWORK_DIR') ? null : define('AK_FRAMEWORK_DIR', '/path/to/the/framework');",
                "defined('AK_FRAMEWORK_DIR') ? null : define('AK_FRAMEWORK_DIR', '".addcslashes($this->options['source'],'\\')."');",
                file_get_contents($this->options['directory'].DS.$file));
                file_put_contents($this->options['directory'].DS.$file, $file_contents);
            }
        }
    }

    function _addRootLevelDispatcher()
    {
        $this->_copyFile($this->options['source'].DS.'index.php');
        $this->_copyFile($this->options['source'].DS.'.htaccess');
    }

    function _addHtaccessDirectoryProtection()
    {
        foreach($this->source_tree as $k=>$node){
            if (is_array($node)){
                $folder = array_shift(array_keys($node));
                $path = $this->options['directory'].DS.$folder;
                if(is_dir($path) && !file_exists($path.DS.'.htaccess') && $folder != 'public'){
                    file_put_contents($path.DS.'.htaccess', "order allow,deny\ndeny from all");
                }
            }
        }
    }

    function _copyFrameworkFiles($directory_structure, $base_path = '.')
    {
        foreach ($directory_structure as $k=>$node){

            $path = $base_path.DS.$node;
            if(is_dir($path)){
                $this->_makeDir($path);
            }elseif(is_file($path)){
                $this->_copyFile($path);
            }elseif(is_array($node)){
                foreach ($node as $dir=>$items){
                    $path = $base_path.DS.$dir;
                    if(is_dir($path)){
                        $this->_makeDir($path);
                        $this->_copyFrameworkFiles($items, $path);
                    }
                }
            }

        }
    }

    function _makeDir($path)
    {
        $dir = $this->_getDestinationPath($path);

        if($this->_canUsePath($dir)){
            if(!is_dir($dir)){
                $this->yield("    Creating directory: ".$dir);
                if(!@mkdir($dir))
                return false;
            }
        }
        return true;
    }

    function _copyFile($path)
    {
        $destination_file = $this->_getDestinationPath($path);

        if($this->_canUsePath($destination_file)){
            if(!file_exists($destination_file)){
                $this->yield("    Creating file: ".$destination_file);
                copy($path, $destination_file);
            }elseif(md5_file($path) != md5_file($destination_file)){
                $this->yield("    Modifying file: ".$destination_file);
                copy($path, $destination_file);
            }

            $source_file_mode =  fileperms($path);
            $target_file_mode =  fileperms($destination_file);
            if($source_file_mode != $target_file_mode){
                $this->yield("    Setting $destination_file permissions to: ".(sprintf("%o",$source_file_mode)));
                chmod($destination_file,$source_file_mode);
            }
        }
    }

    /**
     * Computes the destination path
     * 
     * Gicing /path/to/the_framework/lib/Ak.php will rerturn /my/project/path/lib/Ak.php
     * 
     */
    function _getDestinationPath($path)
    {
        return str_replace($this->options['source'].DS, $this->options['directory'].DS, $path);
    }

    /**
     * Returns false if operating on the path is not allowed
     */
    function _canUsePath($path)
    {
        if(is_file($path) || is_dir($path)){
            return !empty($this->options['skip']) ? false : !empty($this->options['force']);
        }

        return true;
    }

    function _absolutePath($path)
    {
        $_path = $path;
        if (!preg_match((AK_OS == 'WINDOWS' ? "/^\w+:/" : "/^\//"), $path )) {
            $current_dir = AK_OS == 'WINDOWS' ? str_replace("\\", DS, realpath('.').DS) : realpath('.').DS;
            $_path = $current_dir . $_path;
        }
        $start = '';
        if(AK_OS == 'WINDOWS'){
            list($start, $_path) = explode(':', $_path, 2);
            $start .= ':';
        }
        $real_parts = array();
        $parts = explode(DS, $_path);
        for ($i = 0; $i < count($parts); $i++ ) {
            if (strlen($parts[$i]) == 0 || $parts[$i] == "."){
                continue;
            }
            if ($parts[$i] == '..'){
                if(count($real_parts) > 0){
                    array_pop($real_parts);
                }
            }else{
                array_push($real_parts, $parts[$i]);
            }
        }
        return $start.DS.implode(DS,$real_parts );
    }

    function yield($message)
    {
        if(empty($this->options['quiet'])){
            echo $message."\n";
        }
    }

    function addError($error)
    {
        $this->errors[$error] = '';
    }

    function getErrors()
    {
        return array_keys($this->errors);
    }

    function hasErrors()
    {
        return !empty($this->errors);
    }


    function runEvironmentSpecificTasks()
    {
        if($evironment = $this->guessEnvironment()){
            $method_name = 'run'.$evironment.'Tasks';
            if(method_exists($this, $method_name)){
                $this->$method_name();
            }
        }
    }

    // Environment specific tasks

    function guessEnvironment()
    {
        if(AK_OS == 'WINDOWS'){
            if(file_exists('C:/xampp/apache/conf/httpd.conf')){
                return 'DefaultXamppOnWindows';
            }
        }
        return false;
    }

    function runDefaultXamppOnWindowsTasks()
    {
        // XAMPP has mod_rewrite disabled by default so we will try to enable it.
        $http_conf = file_get_contents('C:/xampp/apache/conf/httpd.conf');
        if(strstr($http_conf, '#LoadModule rewrite_module')){
            $this->yield('Enabling mod_rewrite');
            file_put_contents('C:/xampp/apache/conf/httpd.conf.akelos', $http_conf);
            file_put_contents('C:/xampp/apache/conf/httpd.conf',
            str_replace(
            '#LoadModule rewrite_module',
            'LoadModule rewrite_module',
            $http_conf
            ));

            $this->yield('Restarting Apache');
            // Stop apache
            exec('C:\xampp\apache\bin\pv -f -k apache.exe -q');
            exec('rm C:\xampp\apache\logs\httpd.pid');

            // Start Apache in the background
            $shell = new COM('WScript.Shell');
            $shell->Run('C:\xampp\apache\bin\apache.exe', 0, false);
        }

        $my_cnf = @file_get_contents('C:/xampp/mysql/bin/my.cnf');
        // InnoDB engine is not enabled by default on XAMPP we need it enabled in order to use transactions
        if(strstr($my_cnf, '#innodb_')){
            $this->yield('Enabling InnoDB MySQL engine.');
            file_put_contents('C:/xampp/mysql/bin/my.cnf.akelos', $my_cnf);
            file_put_contents('C:/xampp/mysql/bin/my.cnf',
            str_replace(
            array('skip-innodb', '#innodb_', '#set-variable = innodb'),
            array('#skip-innodb', 'innodb_', 'set-variable = innodb')
            ,$my_cnf));

            $this->yield('Restarting MySQL server.');
            $shell = new COM('WScript.Shell');
            $shell->Run('C:\xampp\mysql\bin\mysqladmin --user=pma --password= shutdown', 0, false);
            $shell = new COM('WScript.Shell');
            $shell->Run('C:\xampp\mysql\bin\mysqld --defaults-file=C:\xampp\mysql\bin\my.cnf --standalone --console', 0, false);
        }
    }

}
?>