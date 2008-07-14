<?php
require_once(AK_LIB_DIR.DS.'AkActionController'.DS.'AkCacheSweeper.php');

class PersonSweeper extends AkCacheSweeper
{
    var $observe = 'Person';
    
    function after_create(&$record)
    {
        $this->expirePage(array('controller'=>'cache_sweeper','action'=>'listing'));
    }
    
    function after_save(&$record)
    {
        $this->expirePage(array('controller'=>'cache_sweeper','action'=>'listing'),'*');
        $this->expireAction(array('controller'=>'cache_sweeper','action'=>'show','id'=>$record->id,'lang'=>'*'));
    }
    
    function before_destroy(&$record)
    {
        $this->expirePage(array('controller'=>'cache_sweeper','action'=>'show','id'=>$record->id));
    }
}