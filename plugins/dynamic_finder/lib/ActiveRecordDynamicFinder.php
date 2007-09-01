<?php

class ActiveRecordDynamicFinder extends AkActiveRecord
{
    private function __call ($method, $args)
    {
        if(substr($method,0,4) == 'find'){
            $finder = substr(AkInflector::underscore($method), 5);
            list($type, $columns) = explode('by_', $finder);
            $callback = strstr($type,'create') ?  'findOrCreateBy' : (strstr($type,'first') || !strstr($type,'all') ? 'findFirstBy' : 'findAllBy');
            $columns = strstr($columns, '_and_') ? explode('_and_', $columns) : array($columns);
            array_unshift($args, join(' AND ', $columns));
            return Ak::call_user_func_array(array(&$this,$callback), $args);
        }

        $backtrace = debug_backtrace();
        trigger_error('Call to undefined method '.__CLASS__.'::'.$method.'() in <b>'.$backtrace[1]['file'].'</b> on line <b>'.$backtrace[1]['line'].'</b> reported ', E_USER_ERROR);
    }
}

?>