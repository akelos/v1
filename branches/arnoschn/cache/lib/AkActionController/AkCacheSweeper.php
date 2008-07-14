<?php
require_once(AK_LIB_DIR . DS . 'AkActiveRecord' . DS . 'AkObserver.php');

class AkCacheSweeper extends AkObserver
{
    var $_events = array();
    var $_sweeper;
    var $observe = array();
    var $_cache_handler;
    
    function __construct(&$cache_handler, $events)
    {
        $this->_cache_handler = $cache_handler;
        $this->_events = $events;
        $this->_initModelObserver();
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
    function _initModelObserver()
    {
        $this->observe = is_array($this->observe)?$this->observe:Ak::toArray($this->observe);
        
        $this->setObservedModels($this->observe);
        
    }

    function _handleEvent($time,$event,&$record)
    {
        
        if (!in_array($event,$this->_events)) return;
        if (method_exists($this, $time.'_'.$event)) {
            $this->{$time.'_'.$event}($record);
        }
    }
    
    function afterSave(&$record)
    {
        $this->_handleEvent('after','save',$record);
    }
    function beforeSave(&$record)
    {
        $this->_handleEvent('before','save',$record);
    }
    function afterUpdate(&$record)
    {
        $this->_handleEvent('after','update',$record);
    }
    function beforeUpdate(&$record)
    {
        $this->_handleEvent('before','update',$record);
    }
    function beforeDestroy(&$record)
    {
        $this->_handleEvent('before','destroy',$record);
    }
    function afterDestroy(&$record)
    {
        $this->_handleEvent('after','destroy',$record);
    }
    
    function afterCreate(&$record)
    {
        $this->_handleEvent('after','create',$record);
    }
    
    function beforeCreate(&$record)
    {
        $this->_handleEvent('before','create',$record);
    }
}