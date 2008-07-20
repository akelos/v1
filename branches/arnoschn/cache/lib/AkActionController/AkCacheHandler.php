<?php
require_once(AK_LIB_DIR.DS.'AkCache.php');
class AkCacheHandler extends AkObject
{
    /**
     * @var AkCache
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
     * Sweeper
     */
    var $observe = array();
    
    var $_Sweepers = array();
    
    var $_settings = array();
    
    /**
     * Reads configuration options from AkActionController and the configured
     * constants
     * 
     * AkCache::lookupStore(true) - to detect which cache shall be used
     * $perform_caching - to detect whether caching shall be enabled or not
     *
     * @param AkActionController $parent
     */
    function init(&$parent, $settings = null)
    {
        $this->_action_cache_path = null;
        $this->_action_cache_host = null;
        if ($parent != null) {
            $this->_controller = &$parent;
            
            $this->_configure($settings);

        } else {
            /**
             * We are in pagecache rendering mode
             */
            $this->_loadSettings($settings);
        
        }
    }
    function _loadSettings($settings = null)
    {
        if ($settings == null) {
            $this->_settings = Ak::getSettings('caching', false);
        } else if (is_array($settings)) {
            $this->_settings = $settings;
        } else {
            return;
        }
        $this->_setCacheStore($this->_settings);
    }
    function _configure($settings)
    {
        $configuration_object = &$this->_controller;
        $configuration_options = array('caches_page'=>'_setCachesPage',
                                       'caches_action'=>'_setCachesAction',
                                       'cache_sweeper'=>'_setCacheSweeper',
                                       'page_cache_extension'=>'_setPageCacheExtension');
        /**
         * Load the configured cache store,
         */
        $this->_loadSettings($settings);
        
        if (isset($this->_controller->page_cache_extension)) {
            $this->_page_cache_extension = $this->_controller->page_cache_extension;
        }
        
        if (@$this->_settings['enabled'] == true) {
            $this->_perform_caching = true;
        }
        
        foreach ($configuration_options as $option => $callback) {
            if (isset($configuration_object->$option)) {
                if (is_array($callback)) {
                    call_user_func_array($callback,$configuration_object->$option);
                } else {
                    $this->$callback($configuration_object->$option);
                }
            }
        }
        return;
        /**
         * ######## Page Caching ##########
         */
        if (isset($this->_controller->caches_page)) {
            $this->_cachesPage($this->_controller->caches_page);
        }
        
        /**
         * ######## Action Caching #########
         */
        if (isset($this->_controller->caches_action)) {
            $this->_cachesAction($this->_controller->caches_action);
        }
        /**
         * ######## Sweeping ############
         */
        if (isset($this->_controller->cache_sweeper)) {
            $this->_cacheSweeper($this->_controller->cache_sweeper);
        }
    }
    function _setPageCacheExtension($extension)
    {
        $this->_page_cache_extension = $extension;
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
        if ((is_array($path) && isset($path['lang']) && $path['lang'] == '*') || $language == '*') {
            $langs = AkLocaleManager::getPublicLocales();
            $res = true;
            foreach ($langs as $lang) {
                $res = $res || $this->expirePage($path, $lang);
            }
            return $res;
        }
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
    
    function _setCacheSweeper($options)
    {
        $default_options = array('only'=>array(),
                                 'except'=>array());
        
        Ak::parseOptions($options, $default_options,array(),true);
                                          
        foreach ($options as $sweeper => $params) {
            if (is_int($sweeper)) {
                $sweeper = $params;
                $params = array();
            }
            $this->_initSweeper($sweeper, $params);
        }
    }
    
    function _initSweeper($sweeper, $options = array())
    {
        if (!empty($only) && !in_array($this->_controller->getActionName(), $options['only'])) return;
        if (!empty($except) && !in_array($this->_controller->getActionName(), $options['except'])) return;
        
        $sweeper_class = AkInflector::classify($sweeper);
        
        if (!class_exists($sweeper_class)) {
            $filePath = AK_APP_DIR . DS . 'sweepers' . DS . $sweeper.'.php';
            if (file_exists($filePath)) {
                require_once($filePath);
                if (!class_exists($sweeper_class)) {
                    trigger_error('Cache Sweeper "' . $sweeper_class . '" does not exist in: ' . $filePath, E_USER_ERROR);
                }
            } else if (AK_ENVIRONMENT == 'development') {
                trigger_error('Cache Sweeper file does not exist: ' . $filePath, E_USER_ERROR);
            }
        }
        $this->_Sweepers[] = &new $sweeper_class(&$this);
    }
    
    function _setCachesPage($options)
    {
        if (!$this->_perform_caching) return;
        
        $default_options = array('include_get_parameters'=>array(),
                                 'headers'=> array('X-Cached-By'=>'Akelos'));
        Ak::parseOptions($options, $default_options,array(),true);
        $this->_caches_page = &$options;
        
        $actionName = $this->_controller->getActionName();
        if (isset($this->_caches_page[$actionName])) {

            $this->_include_get_parameters = $this->_caches_page[$actionName]['include_get_parameters'];
            $this->_additional_headers = $this->_caches_page[$actionName]['headers'];

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
        } else if (is_array($path)) {
            $path = $this->_pathFor($path);
        }
        $cacheId = preg_replace('|'.DS.'+|','/',$path);
        $cacheId = rtrim($cacheId,'/');
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
        $cacheId = ($forcedLanguage!=null?$forcedLanguage:Ak::lang()).DS. $cacheId;
        $this->_lastCacheId = preg_replace('|'.DS.'+|','/',$cacheId);
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
    function fragmentCacheKey($options, $parameters = array())
    {
        if (is_array($options)) {
            $options = $this->_pathFor($options);
        } else if ($options==null) {
            $options = $this->_pathFor($this->_controller->params);
        }
        
        $key = AkCache::expandCacheKey($options, isset($parameters['namespace'])?$parameters['namespace']:'fragments');
        
        return $key;
    }
    function _cacheTplRendered($key)
    {
        static $_cached;
        if (empty($_cached)) {
            $_cached = array();
        }
        if (isset($_cached[$key])) {
            return true;
        } else {
            $_cached[$key] = true;
            return false;
        }
        
    }
    function cacheTplFragmentStart($key, $options = array())
    {
        if (!$this->cacheConfigured()) {
            return false;
        }
        $read = $this->readFragment($key, $options);
        if ($read !== false) {
            echo $read;
            $this->_cacheTplRendered($key);
            return true;
        } else {
            ob_start();
            return false;
        }
    }
    
    function cacheTplFragmentEnd($key, $options = array())
    {
        if (!$this->_cacheTplRendered($key)) {
            $contents = ob_get_clean();
            $this->writeFragment($key, $contents, $options);
        }
    }
    
    function writeFragment($key, $content, $options = array())
    {
        if (!$this->cacheConfigured()) return;
        
        $key = $this->fragmentCachekey($key, $options);
        
        return $this->_cache_store->save($content, $key, isset($options['host'])?
                                                  $options['host']:$this->_buildCacheGroup());
    }
    
    function readFragment($key, $options = array())
    {
        if (!$this->cacheConfigured()) return false;
        
        $key = $this->fragmentCachekey($key, $options);
        return $this->_cache_store->get($key, isset($options['host'])?
                                                  $options['host']:$this->_buildCacheGroup());
    }
    
    function expireFragment($key, $options = array())
    {
        if (!$this->cacheConfigured()) return;
        if (is_array($key) && isset($key['lang']) && $key['lang'] == '*') {
            $langs = AkLocaleManager::getPublicLocales();
            $res = true;
            foreach ($langs as $lang) {
                $key['lang'] = $lang;
                $res = $this->expireFragment($key, $options);
            }
            return $res;
        }
        $key = $this->fragmentCachekey($key, $options);
        return $this->_cache_store->remove($key, isset($options['host'])?
                                                  $options['host']:$this->_buildCacheGroup());
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
        if (!empty($this->_action_include_get_parameters)) {
            $getParameters = array();
            foreach ($this->_action_include_get_parameters as $includeGet) {
                if (isset($_GET[$includeGet])) {
                    $getParameters[] = $includeGet.'='.$_GET[$includeGet];
                }
            }
            $getString = implode(DS,$getParameters);
        } else {
            $getString = '';
        }
        if (empty($this->_action_cache_path)) {
            //$this->_action_cache_path = 
            $path = $this->_pathFor($this->_controller->params).(!empty($getString)?DS.$getString:'');
            $this->_action_cache_path = $path;
        }
        $options = array();
        if (!empty($this->_action_cache_host)) {
            $options['host'] = $this->_action_cache_host;
        }
        $options['namespace'] = 'actions';
        if (($content = $this->readFragment($this->_action_cache_path, $options))!==false) {
            $this->_controller->renderText($content);
            $this->_rendered_action_cache = true;
            $this->_controller->performed_render = true;
            $this->_controller->_sendMimeContentType();
            $this->_controller->Response->addHeader('X-Cached-By','Akelos-Action-Cache');
        } else {
            ob_start();
            $this->_rendered_action_cache = false;
        }
        return true;
    }
    
    function afterActionCache()
    {
        if (!$this->_cachingAllowed() || $this->_rendered_action_cache === true) return;
        $this->_controller->handleResponse();
        $contents = ob_get_flush();
        $options = array();
        if (!empty($this->_action_cache_host)) {
            $options['host'] = $this->_action_cache_host;
        }
        $options['namespace'] = 'actions';
        $this->writeFragment($this->_action_cache_path , $contents, $options);
        return true;
    }
    
    function _setCachesAction($options)
    {
        if (!$this->_perform_caching) return;
        
        
        $default_options = array('include_get_parameters'=>array(),
                                 'cache_path'=>'');
        Ak::parseOptions($options, $default_options,array(),true);
        $this->_caches_action = $options;
        
        $actionName = $this->_controller->getActionName();
        
        if (isset($this->_caches_action[$actionName])) {

            $this->_action_include_get_parameters = $this->_caches_action[$actionName]['include_get_parameters'];
            $path = $this->_caches_action[$actionName]['cache_path'];
            $parts = parse_url($path);
            if (isset($parts['host'])) {
                $this->_action_cache_host = $parts['host'];
                $this->_action_cache_path = $parts['path'];
            } else {
                $this->_action_cache_path = $path;
            }
            
            if (!isset($this->_action_cache_host)) {
                $this->_action_cache_host = $this->_controller->Request->getHost();
            }
            $this->_action_cache_path = $this->_actionPath($this->_action_cache_path);
            $this->_controller->prependBeforeFilter(array(&$this,'beforeActionCache'));
            $this->_controller->appendAfterFilter(array(&$this,'afterActionCache'));
        }
        
    }
    
    function _actionPath($options)
    {
        $extension = $this->_controller->Request->getFormat();//$this->_extractExtension($this->_controller->Request->getPath());
        if (is_array($options)) {
            $path = $this->_pathFor($options);
        } else if ($options == null || empty($options)) {
            $path = $this->_pathFor();
        } else {
            $path = $options;
        }
        $path = $this->_normalize($path);
        $path = $this->_addExtension($path, $extension);
        return $path;
    }
    
    function expireAction($options, $params = array())
    {
        $params['namespace'] = 'actions';
        return $this->expireFragment($options, $params);
    }
    function _normalize($path)
    {
        $path = $path == '/' ? '/index':$path;
        return $path;
    }
    function _addExtension($path, $extension)
    {
        if (!empty($extension)) {
            $path = $path.'.'.$extension;
        }
        return $path;
    }
    
    function _extractExtension($file_path)
    {
        preg_match('/^[^\.]+\.(.+)$/',$file_path, $matches);
        return isset($matches[1])?$matches[1]:null;
    }
    function _pathFor($options = array())
    {
        $options = empty($options)?$this->_controller->params:$options;
        $url = $this->_controller->urlFor($options);
        $parts = parse_url($url);
        $path = $parts['path'];
        if (!isset($options['action']) || (isset($options['action']) && $options['action']=='index' && !strstr($path,'/index/'))) {
            $path = rtrim($path,'/');
            $parts = preg_split('/\/+/',$path);
            $parts[] = "index";
            $path = implode('/', $parts);
        }
        $path = rtrim($path,'/');
        return $path;
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
    function _setCacheStore($options=array())
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