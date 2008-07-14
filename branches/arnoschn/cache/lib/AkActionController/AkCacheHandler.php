<?php

class AkCacheHandler extends AkObject
{
        /**
     * @var AkCacheStore
     */
    var $_cache_store = false;
    
    var $_perform_caching = true;
    
    var $_page_cache_extension = '.html';
    
    var $_controller;
    
    
    /**
     * ########### Start: Page Caching ###########
     */
    
        
    var $_lastCacheGroup;
    
    var $_lastCacheId;
    
    var $_include_get_parameters = array();
    
    var $_caches_page = array();
    
    var $_additional_headers = array();
    
    var $_header_separator = '@#@';
    
    /**
     * ########### End: Page Caching ###########
     */
    
    /**
     * Max key size on memcache is 250 chars,
     * to support memcache, we need to md5() the keysize in case it becomes too long
     * 
     * @var int
     */
    var $_max_cache_id_length = 240;
    /*
     * @var int
     */
    var $_max_url_length = 120;
    
    /**
     * Reads configuration options from AkACtionController:
     * 
     * $cache_store - to detect which cache shall be used
     * $perform_caching - to detect whether caching shall be enabled or not
     *
     * @param AkActionController $parent
     */
    function init(&$parent, $cache_store=false)
    {
        if ($parent != null) {
            $this->_controller = &$parent;
            if (isset($this->_controller->cache_store)) {
                $this->_cacheStore($this->_controller->cache_store);
            }
            if (isset($this->_controller->perform_caching)) {
                $this->_perform_caching = $this->_controller->perform_caching;
            }
            /**
             * ######## Page Caching ##########
             */
            if (isset($this->_controller->caches_page)) {
                $this->_cachesPage($this->_controller->caches_page);
            }
            if (isset($this->_controller->page_cache_extension)) {
                $this->_page_cache_extension = $this->_controller->page_cache_extension;
            }
            /**
             * ######## Action Caching #########
             */
            if (isset($this->_controller->caches_action)) {
                $this->_cachesAction($this->_controller->caches_action);
            }
        } else {
            /**
             * We are in pagecache rendering mode
             */
            $this->_cache_store = &AkCache::lookupStore($cache_store);
        
        }
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
    function expirePage($path = null, $language=null)
    {
        if (!$this->_perform_caching || !$this->_cache_store) return;
        $cacheId = $this->_buildCacheId($path, $language);
        $cacheGroup = $this->_buildCacheGroup();
        return $this->_cache_store->remove($cacheId,$cacheGroup);
    }
    function cachePage($content, $path = null, $language = null)
    {
        if (!($this->_cachingAllowed() && $this->_perform_caching)) return;
        
        $cacheId = $this->_buildCacheId($path, $language);
        $cacheGroup = $this->_buildCacheGroup();
        $content = $this->_modifyCacheContent($content);
        return $this->_cache_store->save($content,$cacheId,$cacheGroup);
      
    }
    function _modifyCacheContent($content)
    {
        $headers = $this->_controller->Response->_headers_sent;
        $headerString = serialize($headers);
        $content = time().$this->_header_separator.$headerString . $this->_header_separator . $content;
        return $content;
    }
    function _cachesPage($options)
    {
        if (!$this->_perform_caching) return;
        
        $this->_caches_page = is_array($options)?$options:Ak::toArray($options);
        $actionName = $this->_controller->getActionName();
        if (($hasOptions = isset($this->_caches_page[$actionName])) ||
            in_array($actionName, $this->_caches_page)) {
            if ($hasOptions) {
                $this->_include_get_parameters = isset($this->_caches_page[$actionName]['include_get_parameters'])?
                                                    is_array($this->_caches_page[$actionName]['include_get_parameters'])?
                                                       $this->_caches_page[$actionName]['include_get_parameters']: Ak::toArray($this->_caches_page[$actionName]['include_get_parameters'])
                                                    :array();
                $this->_additional_headers = isset($this->_caches_page[$actionName]['headers'])?
                                                    is_array($this->_caches_page[$actionName]['headers'])?
                                                       $this->_caches_page[$actionName]['headers']: Ak::toArray($this->_caches_page[$actionName]['headers'])
                                                    :array();
            }
            $this->_controller->prependBeforeFilter(array(&$this,'beforePageCache'));
            $this->_controller->appendAfterFilter(array(&$this,'afterPageCache'));
        }
    }

    function beforePageCache()
    {
        ob_start();
        return true;
    }
    
    function afterPageCache()
    {
        $this->_controller->handleResponse();
        $contents = ob_get_flush();
        
        $this->cachePage($contents);
        return true;
        
    }
    
    function _buildCacheId($path, $forcedLanguage = null)
    {
        if ($path == null) {
            $path = @$_REQUEST['ak'];
        }
        $cacheId = preg_replace('/\/+/','/',$path);
        $cacheId = ltrim($cacheId,'/');
        $parts = split('/',$cacheId);
        $hasExtension = preg_match('/.+\..{3,4}/',$parts[count($parts)-1]);
        if (!$hasExtension) {
            $cacheId.= $this->_page_cache_extension;
        }

        $getParameters = $_GET;
        unset($getParameters['ak']);
        if (is_array($this->_include_get_parameters) && !empty($this->_include_get_parameters) && !empty($getParameters)) {
            $cacheableGetParameters = array();
            foreach ($this->_include_get_parameters as $include_get) {
                if (isset($getParameters[$include_get])) {
                    $cacheableGetParameters[] = $include_get .DS.$getParameters[$include_get];
                }
            }
            $cacheIdGetPart = implode(DS,$cacheableGetParameters);
            $cacheId .= DS . $cacheIdGetPart;
        }
        $cacheId=strlen($cacheId)>$this->_max_url_length?md5($cacheId):$cacheId;
        $this->_lastCacheId = $forcedLanguage!=null?$forcedLanguage:Ak::lang().DS. $cacheId;
        return $this->_lastCacheId;
    }
    
    function &getCachedPage($path = null,$forcedLanguage = null)
    {
        $false = false;
        if (!$this->_cachingAllowed()) return $false;
        $false = false;
        if ($this->_cache_store!=false) {
            if ($path === null) {
                $path = @$_REQUEST['ak'];
            }
            $cacheId = $this->_buildCacheId($path, $forcedLanguage);
            $cacheGroup = $this->_buildCacheGroup();
            $cache = $this->_cache_store->get($cacheId, $cacheGroup);
            if ($cache != false) {
                require_once(AK_LIB_DIR.DS.'AkCache'.DS.'AkCachedPage.php');
                $page = &new AkCachedPage($cache, $this->_header_separator, array('use_if_modified_since'=>true,
                                                                                'headers'=>array('X-Cached-By: Akelos')));
                return $page;
            } else {
                
                return $false;
            }
        } else {
            return $false;
        }
    }
    
    function _buildCacheGroup()
    {
        $this->_lastCacheGroup = $this->_convertGroup(isset($_SERVER['AK_HOST'])?$_SERVER['AK_HOST']:AK_HOST);
        return $this->_lastCacheGroup;
    }

    function _cachingAllowed()
    {
        if (isset($this->_controller)) {
            return $this->_controller->Request->isGet() && $this->_controller->Response->getStatus()==200;
        } else {
            return empty($_POST) && empty($_ENV['HTTP_RAW_POST_DATA']) && (isset($_SERVER['REQUEST_METHOD']) && strtolower($_SERVER['REQUEST_METHOD'])=='get');
        }
    }
    
    function _convertGroup($group)
    {
        if ($group == '127.0.0.1') return 'localhost';
        else return $group;
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
        
    }
    
    function cacheTplFragmentStart($key, $options = array())
    {
        
    }
    
    function cacheTplFragmentEnd($key, $options = array())
    {
        
    }
    
    function writeFragment($key, $content, $options = array())
    {
        
    }
    
    function readFragment($key, $options = array())
    {
        
    }
    
    function expireFragment($key, $options = array())
    {
        
    }
    /*
     * ########################################################################
     * #
     * #               From AkActionControllerCachingActions
     * #
     * ########################################################################
     */
    function beforeActionCache()
    {
        
    }
    
    function afterActionCache()
    {
        
    }
    
    function _cachesAction($options)
    {
        $this->_controller->prependBeforeFilter(array(&$this,'beforeActionCache'));
        $this->_controller->appendAfterFilter(array(&$this,'afterActionCache'));
    }
    function expireAction($options)
    {
        
    }
    function _normalize($path)
    {
        
    }
    function _add_extension($path, $extension)
    {
        
    }
    
    function _extract_extension($file_path)
    {
        
    }
    function _pathFor($options)
    {
        
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