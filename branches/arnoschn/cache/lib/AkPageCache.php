<?php
require_once(AK_LIB_DIR.DS.'AkCache.php');

class AkPageCache extends AkCache
{
    var $_driverInstance;
    
    /**
     * if the array contains parameters, these get parameters
     * will be included in the cache ID generation
     * 
     * @var array
     */
    var $_include_get_parameters = array();
    
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
     * @var AkRequest
     */
    var $_request;
    
    var $_base_cache_group = 'page_cache';
    
    var $_header_separator = '#@#';
    
    var $_use_if_modified_since = false;
    
    var $_is_inited = false;
    
    var $_lastCacheId;
    var $_lastCacheGroup;

    function init($options = null, $cache_type = AK_CACHE_HANDLER)
    {
        if ($this->_is_inited === true) return;
        $options = is_int($options) ? array('lifeTime'=>$options) : (is_array($options) ? $options : array());
        isset($options['cacheDir'])?$options['cacheDir'] = realpath($options['cacheDir']).'/':null;
        parent::init($options,$cache_type);

        $this->_include_get_parameters = isset($options['include_get_parameters'])?$options['include_get_parameters']:$this->_include_get_parameters;
        $this->_use_if_modified_since = isset($options['use_if_modified_since'])?$options['use_if_modified_since']:$this->_use_if_modified_since;
        $this->_additional_headers = isset($options['headers'])?$options['headers']:array();
        $this->_is_inited = true;
    }
    function _buildCacheId($path, $forceRefresh = false, $forcedLanguage = null)
    {
        
        $cacheId = preg_replace('/\/+/','/',$path);
        $cacheId = ltrim($cacheId,'/');
        $cacheIdGetPart = '';

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
    function _buildCacheGroup($forceRefresh=false)
    {
        $this->_lastCacheGroup = $this->_convertGroup(AK_HOST);
        return $this->_lastCacheGroup;
    }
    function _autoDetectPath()
    {
        static $_last_url;
        if (empty($_last_url)) {
            $_last_url = array('ak'=>@$_REQUEST['ak']);
        }
        return $_last_url['ak'];
    }
    
    
    function getCachedPage($url = null,$forcedLanguage = null)
    {
        if ($url === null) {
            $url = $this->_autoDetectPath();
        }
        $cacheId = $this->_buildCacheId($url, false, $forcedLanguage);
        $cacheGroup = $this->_buildCacheGroup();
        $cache = $this->get($cacheId,$cacheGroup);
        if ($cache != false) {
            require_once(AK_LIB_DIR.DS.'AkCache'.DS.'AkCachedPage.php');
            return new AkCachedPage($cache, $this->_header_separator, array('use_if_modified_since'=>$this->_use_if_modified_since,
                                                                            'headers'=>$this->_additional_headers));
        } else {
            return false;
        }
    }

    /**
     * saves $modificationTimestamp . $serializedHeaders . $cachedContent
     */
    function save($data, $id = null, $group = 'default')
    {
        require_once(AK_LIB_DIR.DS.'AkResponse.php');
        $response = &AkResponse();
        if (is_a($response,'akresponse')) {
            $headers = $response->_headers_sent;
        } else {
            $headers = array();
        }
        $headerString = serialize($headers);
        $content = time().$this->_header_separator.$headerString . $this->_header_separator . $data;
        return parent::save($content,$id,$group);
        
    }
    function store($data, $url=null, $forcedLanguage = null)
    {
        if ($url === null) {
            $url = $this->_autoDetectPath();
        }
        $cacheId = !empty($this->_lastCacheId)?$this->_lastCacheId:$this->_buildCacheId($url, true, $forcedLanguage);
        $cacheGroup = !empty($this->_lastCacheGroup)?$this->_lastCacheGroup:$this->_buildCacheGroup();
        return $this->save($data,$cacheId,$cacheGroup);
    }
    function delete($url=null,$forcedLanguage = null)
    {
        if ($url === null) {
            $url = $this->_autoDetectPath();
        }
        $cacheId = $this->_buildCacheId($url,false,$forcedLanguage);
        $cacheGroup = $this->_buildCacheGroup();
        return $this->remove($cacheId,$cacheGroup);
    }
    function _convertGroup($group)
    {
        if ($group == '127.0.0.1') return 'localhost';
        else return $group;
    }
    function clean($group = false, $mode = 'ingroup')
    {
        if ($group != false) {
            $group = $this->_convertGroup($group);
        }
        return parent::clean($group,$mode);
    }
    function getInstance()
    {
        static $AkPageCache;
        if (!isset($AkPageCache) || !isset($AkPageCache['singleton'])) {
            $AkPageCache = array();
            $null = null;
            $AkPageCache['singleton'] =& Ak::singleton('AkPageCache', $null);
        }
        return $AkPageCache['singleton'];
    }
}
