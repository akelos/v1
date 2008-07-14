<?php
/**
 * Methods for caching pages
 */
/**
 * @package ActionController
 * @subpackage Caching
 */
class AkActionControllerCachingActions extends AkObject
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
        if (isset($this->_controller->caches_action)) {
            $this->_cachesAction($this->_controller->caches_action);
        }
        
    }
    
    function beforeAction()
    {
        
    }
    
    function afterAction()
    {
        
    }
    
    function _cachesAction($options)
    {
        $this->_controller->prependBeforeFilter(array(&$this,'beforeAction'));
        $this->_controller->appendAfterFilter(array(&$this,'afterAction'));
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
    
    function _cachingAllowed()
    {
        return $this->_controller->Request->isGet() && $this->_controller->Response->getStatus()==200;
    }
}