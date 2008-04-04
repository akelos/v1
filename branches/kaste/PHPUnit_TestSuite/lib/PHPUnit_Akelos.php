<?php
define('AK_PHPUNIT_TESTSUITE_LIB',dirname(__FILE__));

PHPUnit_Akelos_autoload::ensureConfigFileLoaded();
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
        return false; 
    }
    
    static function ensureConfigFileLoaded()
    {
        if (defined('AK_ENVIRONMENT')) return true;

        $DS = DIRECTORY_SEPARATOR;
        $config_file = preg_replace('@((vendor\\'.$DS.'PHPUnit_TestSuite|test\\'.$DS.'unit).*)$@',
                                    'test'.$DS.'fixtures'.$DS.'config'.$DS.'config.php',
                                    __FILE__);
        if (!is_file($config_file) || $config_file == __FILE__) 
            exit ("Whoa! That didnt start too well. config/config.php not found, tried: $config_file");
            
        require_once $config_file;
        return true;
    }
    
}


?>