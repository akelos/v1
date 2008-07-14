<?php
require_once(AK_LIB_DIR . DS . 'AkActionController' . DS . 'Caching'. DS . 'AkCacheSweeperProxy.php');

class AkCacheSweeper
{
    var $observe = array();
    
    var $__availableObserveEvents = array('create','update','destroy','save');
    var $_observedEvents = array();
    var $_controller;
    var $_options = array();
    var $_proxy;
    
    function __construct(&$controller, $options)
    {
        $this->_controller = $controller;
        $this->_options = $options;
        $this->_parseOptions();
        $this->_initProxy();
        $this->_initModelObserver();
    }
    
    function _initProxy()
    {
        $this->_proxy = new AkCacheSweeperProxy($this, $this->_observedEvents);
    }
    
    function expire_action($options = null, $response_formats = null)
    {
        $this->_controller->expire_action($options, $response_formats);
    }
    
    function expire_page($options = null, $response_formats = null)
    {
        $this->_controller->expire_page($options, $response_formats);
    }
    
    function expire_fragment($options = null)
    {
        $this->_controller->expire_fragment($options);
    }
    function _parseOptions()
    {
        if (!is_array($this->_options)) {
            $this->_observedEvents = $this->__availableObserveEvents;
        } else if (isset($this->_options['only'])) {
            $observeEvents = is_array($this->_options['only'])?$this->_options['only']:Ak::toArray($this->_options['only']);
            $this->_observedEvents = array_intersect($this->__availableObserveEvents, $observeEvents);
        }
    }
    
    function _initModelObserver()
    {
        $this->observe = is_array($this->observe)?$this->observe:Ak::toArray($this->observe);
        
        $this->proxy->setObservedModels($this->observe);
        
    }
}