<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

/**
 * Public PHP file. This file will launch the framework
 */
if(!defined('AK_CONFIG_INCLUDED')){
    if(!file_exists(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php')){
        define('AK_ENVIRONMENT', 'setup');
        error_reporting(E_ALL);
        @ini_set('display_errors', 1);
        require(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.
        'app'.DIRECTORY_SEPARATOR.'controllers'.DIRECTORY_SEPARATOR.'framework_setup_controller.php');
        exit;
    }else{
        include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
    }
}
/**
 * Check cache here, render cache with headers
*/
$cache_settings = @include AK_CONFIG_DIR.DS.'cache'.DS.AK_ENVIRONMENT.DS.'caching.php';
if ($cache_settings!==false && $cache_settings['enabled']) {
    require(AK_LIB_DIR . DS . 'AkActionController'.DS.'AkCacheHandler.php');
    $null = null;
    $pageCache = new AkCacheHandler();
    $pageCache->init($null,$cache_settings);
    if (($cachedPage = $pageCache->getCachedPage())!==false) {
        global $sendHeaders, $returnHeaders, $exit;
        $sendHeaders = true;
        $returnHeaders = false; 
        $exit = true;
        include $cachedPage;
    }
}
require_once(AK_LIB_DIR . DS . 'AkDispatcher.php');
$Dispatcher =& new AkDispatcher();
$Dispatcher->dispatch();


?>