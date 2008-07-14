<?php
//define('AK_HOST','localhost');
include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
$host = AK_HOST;
if (defined('AK_PAGE_CACHE_ENABLED') && AK_PAGE_CACHE_ENABLED) {
    
    require_once(AK_LIB_DIR . DS . 'AkActionController'.DS.'Caching'.DS.'Pages.php');
    $null = null;
    $pageCache = &Ak::singleton('AkActionControllerCachingPages',$null);
    
    $pageCache->init($null, 'file');
    $options = array('cacheDir'=>dirname(__FILE__).'/../../tmp/cache/',
                           'use_if_modified_since'=>false,
                           'headers'=>array('X-Cached-By: Akelos'));
    if (isset($_GET['allow_get'])) {
        $options['include_get_parameters'] = split(',',$_GET['allow_get']);
    }
    
    if (isset($_GET['use_if_modified_since'])) {
        $options['use_if_modified_since'] = true;
    }
    if ($cachedPage = $pageCache->getCachedPage()) {
        $cachedPage->render();
    }
}
require_once(AK_LIB_DIR . DS . 'AkDispatcher.php');
$Dispatcher =& new AkDispatcher();
$Dispatcher->dispatch();

?>