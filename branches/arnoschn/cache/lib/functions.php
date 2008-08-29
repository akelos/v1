<?php

defined('AK_FUNCTIONS_LOADED')?null:define('AK_FUNCTIONS_LOADED',true);

/**
 * Getting the temporary directory
 */
function ak_get_tmp_dir_name(){
    if(!defined('AK_TMP_DIR')){
        if(defined('AK_BASE_DIR') && is_writable(AK_BASE_DIR.DS.'tmp')){
            return AK_BASE_DIR.DS.'tmp';
        }
        if(!function_exists('sys_get_temp_dir')){
            $dir = empty($_ENV['TMP']) ? (empty($_ENV['TMPDIR']) ? (empty($_ENV['TEMP']) ? false : $_ENV['TEMP']) : $_ENV['TMPDIR']) : $_ENV['TMP'];
            if(empty($dir) && $fn = tempnam(md5(rand()),'')){
                $dir = dirname($fn);
                unlink($fn);
            }
        }else{
            $dir = sys_get_temp_dir();
        }
        if(empty($dir)){
            trigger_error('Could not find a path for temporary files. Please define AK_TMP_DIR in your config.php', E_USER_ERROR);
        }
        $dir = rtrim(realpath($dir), DS).DS.'ak_'.md5(AK_BASE_DIR);
        if(!is_dir($dir)){
            mkdir($dir);
        }
        return $dir;
    }
    return AK_TMP_DIR;
}



// Now some static functions that are needed by the whole framework

function translate($string, $args = null, $controller = null)
{
    return Ak::t($string, $args, $controller);
}


function ak_test($test_case_name, $use_sessions = false)
{
    if(!defined('ALL_TESTS_CALL')){
        $use_sessions ? @session_start() : null;
        $test = &new $test_case_name();
        if (defined('AK_CLI') && AK_CLI || TextReporter::inCli() || (defined('AK_CONSOLE_MODE') && AK_CONSOLE_MODE) || (defined('AK_WEB_REQUEST') && !AK_WEB_REQUEST)) {
            $test->run(new TextReporter());
        }else{
            $test->run(new HtmlReporter());
        }
    }
}

function ak_compat($function_name)
{
    if(!function_exists($function_name)){
        require_once(AK_VENDOR_DIR.DS.'pear'.DS.'PHP'.DS.'Compat'.DS.'Function'.DS.$function_name.'.php');
    }
}

function ak_generate_mock($name)
{
    static $Mock;
    if(empty($Mock)){
        $Mock = new Mock();
    }
    $Mock->generate($name);
}

/**
 * This function sets a constant and returns it's value. If constant has been already defined it
 * will reutrn its original value. 
 * 
 * Returns null in case the constant does not exist
 *
 * @param string $name
 * @param mixed $value
 */
function ak_define($name, $value = null)
{
    $name = strtoupper($name);
    $name = substr($name,0,3) == 'AK_' ? $name : 'AK_'.$name;
    return  defined($name) ? constant($name) : (is_null($value) ? null : (define($name, $value) ? $value : null));
}

AK_PHP5 || function_exists('clone') ? null : eval('function clone($object){return $object;}');

?>