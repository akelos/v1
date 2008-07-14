<?php
/**
 * in here keep all the logic for caching:
 * 
 * create methods in AkActionController, which call methods in here
 */
/**
 * @package ActionController
 * @subpackage Caching
 */
class AkActionControllerCaching extends AkObject
{
    /**
     * @var AkCacheStore
     */
    var $_cache_store = false;
    
    var $_perform_caching = true;
    
    var $_controller;
    /**
     * Reads configuration options from AkACtionController:
     * 
     * $cache_store - to detect which cache shall be used
     * $perform_caching - to detect whether caching shall be enabled or not
     *
     * @param AkActionController $parent
     */
    function init(&$parent)
    {
        $this->_controller = &$parent;
        if (isset($this->_controller->cache_store)) {
            $this->_cacheStore($this->_controller->cache_store);
        }
        if (isset($this->_controller->perform_caching)) {
            $this->_perform_caching = $this->_controller->perform_caching;
        }
        $this->_initModules();
        register_shutdown_function(array(&$this,'__destruct'));
    }
    function &getController()
    {
        $return=&$this->_controller;
        return $return;
    }
    
    function &getCacheStore()
    {
        return $this->_cache_store;
    }
    function __destruct()
    {
        //
        //$this->_controller->__destruct();
        foreach ($this->_ak_modules as $alias => $mod) {
            $mod->__destruct();
            unset($mod);
        }
        unset($this->_controller);
    }
    function _initModules()
    {
        $this->_registerModule('caching::pages','AkActionControllerCachingPages','AkActionController/Caching/Pages.php');
        //$this->_controller = $this->_callModuleMethod('caching::pages','getController');
        $this->_registerModule('caching::fragments','AkActionControllerCachingFragments','AkActionController/Caching/Fragments.php');
        $this->_registerModule('caching::actions','AkActionControllerCachingActions','AkActionController/Caching/Actions.php');
        $this->_registerModule('caching::sweeping','AkActionControllerCachingSweeping','AkActionController/Caching/Sweeping.php');
    }
    
    /**
     * ########################################################################
     * #
     * #               The following methods have to be callable
     * #               from AkActionController   
     * #
     * ########################################################################
     */
    /**
     * Is the Caching module configured and ready for usage?
     *
     * @return boolean
     */
    function cacheConfigured()
    {
        return $this->_cache_store && $this->_perform_caching;
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingPages
     * #
     * ########################################################################
     */
    function cachePage($content, $path = null)
    {
        return $this->_callModuleMethod('caching::pages','cachePage', $content, $path);
    }
    
    function getCachedPage($path = null,$forcedLanguage = null)
    {
        return $this->_callModuleMethod('caching::pages','getCachedPage', $path, $forcedLanguage);
    }
    
    function expirePage($options)
    {
        return $this->_callModuleMethod('caching::pages','expirePage', $options);
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingFragments
     * #
     * ########################################################################
     */
    function fragmentCacheKey($key)
    {
        return $this->_callModuleMethod('caching::fragments','fragmentCacheKey', $key);
    }
    function cacheTplFragmentStart($key, $options = array())
    {
        return $this->_callModuleMethod('caching::fragments','cacheTplFragmentStart', $key, $options);
    }
    
    function cacheTplFragmentEnd($key, $options = array())
    {
        return $this->_callModuleMethod('caching::fragments','cacheTplFragmentEnd', $key, $options);
    }
    
    function writeFragment($key, $content, $options = array())
    {
        return $this->_callModuleMethod('caching::fragments','writeFragment', $key,$content, $options);
    }
    
    function readFragment($key, $options = array())
    {
        return $this->_callModuleMethod('caching::fragments','readFragment', $key,$options);
    }
    
    function expireFragment($key, $options = array())
    {
        return $this->_callModuleMethod('caching::fragments','expireFragment', $key,$options);
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingActions
     * #
     * ########################################################################
     */
    function expireAction($options = array())
    {
        return $this->_callModuleMethod('caching::actions','expireAction', $options);
    }
    /**
     * ########################################################################
     * #
     * #               END OF AkActionController callable methods
     * #
     * #########################################################################
     */
    
    /**
     * Looks up the cache store from the option array
     *
     * @param array $options
     */
    function _cacheStore($options)
    {
        $this->_cache_store = AkCache::lookupStore($options);
    }
    
    
    /**
     * @access protected
     */
    function _cache($key, $options = null)
    {
        $return = false;
        if ($this->cacheConfigured()) {
            $return = $this->_cache_store->fetch(AkCache::expandCacheKey($key, $this->_controller), $options);
        }
        return $return;
    }
    

}