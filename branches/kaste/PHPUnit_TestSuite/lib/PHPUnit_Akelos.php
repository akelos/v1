<?php
define('AK_TEST_DATABASE_ON',true);
PHPUnit_Akelos_autoload::ensureConfigFileLoaded();

define('AK_PHPUNIT_TESTSUITE_LIB',dirname(__FILE__));
define('AK_PHPUNIT_TESTSUITE_BASE',dirname(dirname(__FILE__)));
#define('AK_PHPUNIT_TESTSUITE_FIXTURES','');
define('AK_PHPUNIT_TESTSUITE_FIXTURES',AK_PHPUNIT_TESTSUITE_BASE.DS.'tests'.DS.'fixtures');

spl_autoload_register(array('PHPUnit_Akelos_autoload','__autoload'));

class PHPUnit_Akelos_autoload
{
    static function __autoload($classname)
    {
        $filename = AK_PHPUNIT_TESTSUITE_LIB.DS."$classname.php";
        if (is_file($filename)) {
            require_once $filename;
            return true;
        }
        if (preg_match('/(^Ak).*$/',$classname,$matches)){
    	   $filename = AK_LIB_DIR.DS.$classname.'.php';
    	   if (is_file($filename)){
    	       require_once $filename;
    	       return true;
    	   }
    	   return false;
        }
        if (preg_match('/(Controller$|Installer$|$)/',$classname,$matches)){
            switch ($matches[1]){
                case 'Controller': 
                    $search_path = self::CONTROLLER_FOLDERS(); 
                    break;
                case 'Installer':
                    $search_path = self::INSTALLER_FOLDERS();
                    break;
                default:
                    $search_path = self::MODEL_FOLDERS();
                    break;
            }
            return self::includeClass($search_path,$classname);
        }
        var_dump('******');
        return false;
    }
    
    /**
     * @return boolean
     */
    static function includeClass($search_path,$class_name)
    {
        $file_name = AkInflector::underscore($class_name).'.php';
        if ($full_filename = self::searchFilenameInPath($search_path,$file_name)){
            require_once $full_filename;
            return true;
        }
        return false;
    }
    
    static function CONTROLLER_FOLDERS()
    {
        return array(AK_PHPUNIT_TESTSUITE_FIXTURES,AK_CONTROLLERS_DIR,AK_BASE_DIR.DS.'app'.DS.'controllers');
    }
    
    static function INSTALLER_FOLDERS()
    {
        return array(AK_PHPUNIT_TESTSUITE_FIXTURES,AK_APP_DIR.DS.'installers',AK_BASE_DIR.DS.'app'.DS.'installers');
    }

    static function MODEL_FOLDERS()
    {
        return array(AK_PHPUNIT_TESTSUITE_FIXTURES,AK_MODELS_DIR,AK_BASE_DIR.DS.'app'.DS.'models');
    }
    
    static function searchFilenameInPath($folders,$filename)
    {
        foreach ($folders as $folder){
            $full_filename = $folder.DS.$filename;
            if (is_file($full_filename)) return $full_filename;
        }
        return false;
    }
    
    static function ensureConfigFileLoaded()
    {
        if (defined('AK_ENVIRONMENT')) return true;

        define(DS,DIRECTORY_SEPARATOR);
        
        $quoted_path = preg_quote(DS.'app'.DS.'vendor'.DS);
        defined(AK_BASE_DIR) ? null : define(AK_BASE_DIR,preg_replace('@'.$quoted_path.'.*$@','',__FILE__));

        $config_file = AK_BASE_DIR.DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php';
        if (!is_file($config_file) || $config_file == __FILE__) 
            exit ("Whoa! That didnt start too well. config/config.php not found!\n\r defined AK_BASE_DIR=".AK_BASE_DIR.",\n\r accordingly tried:  $config_file");
            
        require_once $config_file;
        return true;
    }
    
}


?>