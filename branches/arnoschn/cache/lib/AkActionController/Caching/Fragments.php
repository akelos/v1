<?php
/**
 * Methods for caching pages
 */
/**
 * @package ActionController
 * @subpackage Caching
 */
class AkActionControllerCachingFragments extends AkObject
{
    var $_caching;
    /**
     * @var AkActionController
     */
    var $_controller;
    
    function init(&$parent)
    {
        $this->_caching = $parent;
        $this->_controller = $parent->getController();
    }
    
    function fragmentCacheKey($options)
    {
        if (is_array($options)) {
            $url = 
        }
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
}