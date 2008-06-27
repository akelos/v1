<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2008, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * Native PHP URL rewriting for the Akelos Framework.
 * 
 * @package ActionController
 * @subpackage Router
 * @author Kaste <thdzDOTx a.t gm x N_et>
 * @copyright Copyright (c) 2002-2008, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */


class RouterConfig
{
    /**
    * This method tries to determine if url rewrite is enabled on this server.
    * It has only been tested on apache.
    * It is strongly recomended that you manually define the constant 
    * AK_URL_REWRITE_ENABLED on your config file to the avoid overload
    * this function causes and to prevent from missfunctioning
    */
    static function loadUrlRewriteSettings()
    {
        static $result;
        if(isset($result)){
            return $result;
        }

        if(defined('AK_URL_REWRITE_ENABLED')){
            $result = AK_URL_REWRITE_ENABLED;
            return AK_URL_REWRITE_ENABLED;
        }
        if(AK_DESKTOP){
            if(!defined('AK_URL_REWRITE_ENABLED')){
                define('AK_URL_REWRITE_ENABLED',false);
                $result = AK_URL_REWRITE_ENABLED;
                return false;
            }
        }
        if(defined('AK_ENABLE_URL_REWRITE') && AK_ENABLE_URL_REWRITE == false){
            if(!defined('AK_URL_REWRITE_ENABLED')){
                define('AK_URL_REWRITE_ENABLED',false);
            }
            $result = AK_URL_REWRITE_ENABLED;
            return false;
        }

        $url_rewrite_status = false;

        //echo '<pre>'.print_r(get_defined_functions(), true).'</pre>';

        if( isset($_SERVER['REDIRECT_STATUS'])
        && $_SERVER['REDIRECT_STATUS'] == 200
        && isset($_SERVER['REDIRECT_QUERY_STRING'])
        && strstr($_SERVER['REDIRECT_QUERY_STRING'],'ak=')){

            if(strstr($_SERVER['REDIRECT_QUERY_STRING'],'&')){
                $tmp_arr = explode('&',$_SERVER['REDIRECT_QUERY_STRING']);
                $ak_request = $tmp_arr[0];
            }else{
                $ak_request = $_SERVER['REDIRECT_QUERY_STRING'];
            }
            $ak_request = trim(str_replace('ak=','',$ak_request),'/');

            if(strstr($_SERVER['REDIRECT_URL'],$ak_request)){
                $url_rewrite_status = true;
            }else {
                $url_rewrite_status = false;
            }
        }

        // We check if available by investigating the .htaccess file if no query has been set yet
        elseif(function_exists('apache_get_modules')){

            $available_modules = apache_get_modules();

            if(in_array('mod_rewrite',(array)$available_modules)){

                // Local session name is changed intentionally from .htaccess
                // So we can see if the file has been loaded.
                // if so, we restore the session.name to its original
                // value
                if(ini_get('session.name') == 'AK_SESSID'){
                    $session_name = defined('AK_SESSION_NAME') ? AK_SESSION_NAME : get_cfg_var('session.name');
                    ini_set('session.name',$session_name);
                    $url_rewrite_status = true;

                    // In some cases where session.name cant be set up by htaccess file,
                    // we can check for modrewrite status on this file
                }elseif (file_exists(AK_BASE_DIR.DS.'.htaccess')){
                    $htaccess_file = Ak::file_get_contents(AK_BASE_DIR.DS.'.htaccess');
                    if(stristr($htaccess_file,'RewriteEngine on')){
                        $url_rewrite_status = true;
                    }
                }
            }

            // If none of the above works we try to fetch a file that should be remaped
        }elseif (isset($_SERVER['REDIRECT_URL']) && $_SERVER['REDIRECT_URL'] == '/' && isset($_SERVER['REDIRECT_STATUS']) && $_SERVER['REDIRECT_STATUS'] == 200){
            $url_rewrite_test_url = AK_URL.'mod_rewrite_test';
            if(!empty($_SERVER['PHP_AUTH_USER']) && !empty($_SERVER['PHP_AUTH_PW'])){
                $url_rewrite_test_url = AK_PROTOCOL.$_SERVER['PHP_AUTH_USER'].':'.$_SERVER['PHP_AUTH_PW'].'@'.AK_HOST.'/mod_rewrite_test';
            }

            $url_rewrite_status = strstr(@file_get_contents($url_rewrite_test_url), 'AK_URL_REWRITE_ENABLED');
            $AK_URL_REWRITE_ENABLED = "define(\\'AK_URL_REWRITE_ENABLED\\', ".($url_rewrite_status ? 'true' : 'false').");\n";

            register_shutdown_function(create_function('',"Ak::file_put_contents(AK_CONFIG_DIR.DS.'config.php',
            str_replace('<?php\n','<?php\n\n$AK_URL_REWRITE_ENABLED',Ak::file_get_contents(AK_CONFIG_DIR.DS.'config.php')));"));
        }

        if(!defined('AK_URL_REWRITE_ENABLED')){
            define('AK_URL_REWRITE_ENABLED', $url_rewrite_status);
        }
        $result = AK_URL_REWRITE_ENABLED;
        return AK_URL_REWRITE_ENABLED;
    }
    
}

?>