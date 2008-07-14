<?php
require_once(AK_LIB_DIR.DS.'AkCache.php');
/**
 * Methods for caching pages
 */
/**
 * @package ActionController
 * @subpackage Caching
 */
class AkActionControllerCachingPages extends AkObject
{
    var $_caching;
    /**
     * @var AkActionController
     */
    var $_controller;
    
    /**
     * @var AkCache
     */
    var $_cache_store;
    
    var $_lastCacheGroup;
    
    var $_lastCacheId;
    
    var $_perform_caching = true;
    
    var $_include_get_parameters = array();
    
    var $_caches_page = array();
    
    var $_additional_headers = array();
    
    var $_header_separator = '@#@';
    
        
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
    
    function init(&$parent, $cache_store = false)
    {
        if ($parent != null) {
            /**
             * we are in pagecache generation mode
             */
            $this->_caching = $parent;
            $this->_controller = &$parent->getController();
            if (isset($this->_controller->caches_page)) {
                $this->_cachesPage($this->_controller->caches_page);
            }
            if (isset($this->_controller->perform_caching)) {
                $this->_perform_caching = $this->_controller->perform_caching;
            }
            $this->_cache_store = &$this->_caching->getCacheStore();
        } else {
            /**
             * We are in pagecache rendering mode
             */
            $this->_cache_store = &AkCache::lookupStore($cache_store);
        }
    }
    
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
            $this->_controller->prependBeforeFilter(array(&$this,'startOutputBuffer'));
            $this->_controller->appendAfterFilter(array(&$this,'endOutputBuffer'));
        }
    }
    function __destruct()
    {
        unset($this->_controller);
        unset($this->_parent);
    }
    function startOutputBuffer()
    {
        ob_start();
        return true;
    }
    
    function endOutputBuffer()
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
            $cacheId.='.html';
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
            return empty($_POST) && empty($_ENV['HTTP_RAW_POST_DATA']) && strtolower($_SERVER['REQUEST_METHOD'])=='get';
        }
    }
    
    function _convertGroup($group)
    {
        if ($group == '127.0.0.1') return 'localhost';
        else return $group;
    }
}