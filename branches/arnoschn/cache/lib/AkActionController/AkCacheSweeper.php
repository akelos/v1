<?php
require_once(AK_LIB_DIR . DS . 'AkActiveRecord' . DS . 'AkObserver.php');

class AkCacheSweeper extends AkObserver
{
    var $_cache_handler;
    
    function __construct(&$cache_handler)
    {
        $this->_cache_handler = $cache_handler;
        parent::__construct();
    }
    function expirePage($path = null, $language=null)
    {
        return $this->_cache_handler->expirePage($path,$language);
    }
    function expireAction($options, $params = array())
    {
        return $this->_cache_handler->expireAction($options, $params);
    }
    function expireFragment($key, $options = array())
    {
        return $this->_cache_handler->expireFragment($key, $options);
    }
    

}