<?php
require_once(AK_LIB_DIR . DS . 'AkActiveRecord' . DS . 'AkObserver.php');

class AkActionControllerCachingSweepingProxy extends AkObserver
{
    var $_events = array();
    var $_sweeper;
    
    function __construct(&$sweeper, $events)
    {
        $this->_sweeper = &$sweeper;
        $this->_events = $events;
    }

    function _handleEvent($time,$event,&$record)
    {
        
        if (!in_array($event,$this->_events)) return;
        if (method_exists($this->_sweeper, $time.$event)) {
            $this->_sweeper->{$time.$event}($record);
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