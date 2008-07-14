<?php
require_once(AK_LIB_DIR . DS . 'AkActionController' . DS . 'Caching'. DS . 'Sweeping'.DS.'Proxy.php');

class AkActionControllerCachingSweeping
{
    var $observe = array();
    
    var $__availableObserveEvents = array('create','update','destroy','save');
    var $_observedEvents = array();
    var $_options = array();
    var $_proxy;
    
    var $_caching;
    /**
     * @var AkActionController
     */
    var $_controller;
    
    function init(&$parent)
    {
        $this->_caching = $parent;
        $this->_controller = $parent->getController();
        if (isset($this->_controller->cache_sweeper)) {
            $this->_cacheSweeper($this->_controller->cache_sweeper);
        }
        $this->_initProxy();
        $this->_initModelObserver();
    }
    
    
    function _initProxy()
    {
        $this->_proxy = new AkActionControllerCachingSweepingProxy($this, $this->_observedEvents);
    }
    
    function expireAction($options = null)
    {
        $this->_caching->expireAction($options);
    }
    
    function expirePage($options = null, $response_formats = null)
    {
        $this->_caching->expirePage($options);
    }
    
    function expireFragment($key, $options = null)
    {
        $this->_caching->expireFragment($key, $options);
    }
    function _cacheSweeper($options)
    {
        if (!is_array($options)) {
            $this->_observedEvents = $this->__availableObserveEvents;
        } else if (isset($options['only'])) {
            $observeEvents = is_array($options['only'])?$options['only']:Ak::toArray($options['only']);
            $this->_observedEvents = array_intersect($this->__availableObserveEvents, $observeEvents);
        }
    }
    function __destruct()
    {
        unset($this->_controller);
        unset($this->_parent);
    }
    function _initModelObserver()
    {
        $this->observe = is_array($this->observe)?$this->observe:Ak::toArray($this->observe);
        
        $this->_proxy->setObservedModels($this->observe);
        
    }
}