<?php

require_once(dirname(__FILE__).'/../../../../fixtures/config/config.php');
require_once(AK_LIB_DIR . DS . 'AkActionController'.DS.'Caching'.DS.'AkCacheSweeperProxy.php');

class TestCallbackObserver
{
    var $_executed = array();
    function reset()
    {
        $this->_executed = array();
    }
    function _handleEvent($time,$event,&$record)
    {
        $this->_executed[] = array($time,$event,&$record);
    }
    
    function getLastExecuted()
    {
       $part = array_pop($this->_executed);
       return $part;
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

class _AkActionController_Caching_sweeper_proxy extends AkUnitTest
{
    var $TestCallback;
    var $Proxy;
   
    function test_receive_update_callback()
    {
        $this->TestCallback = new TestCallbackObserver();
        $Proxy = new AkCacheSweeperProxy($this->TestCallback,array('update'));
        $obj = new stdClass();
        $obj->id = 1;
        $Proxy->afterUpdate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'after');
        $this->assertEqual($executed[1],'update');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(1,$executed[2]->id);
        
        $obj = new stdClass();
        $obj->id = 2;
        $Proxy->beforeUpdate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'before');
        $this->assertEqual($executed[1],'update');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(2,$executed[2]->id);
    }
    
    function test_receive_create_callback()
    {
        $Proxy = new AkCacheSweeperProxy($this->TestCallback,array('create'));
        $obj = new stdClass();
        $obj->id = 1;
        $Proxy->afterCreate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'after');
        $this->assertEqual($executed[1],'create');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(1,$executed[2]->id);
        
        $obj = new stdClass();
        $obj->id = 2;
        $Proxy->beforeCreate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'before');
        $this->assertEqual($executed[1],'create');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(2,$executed[2]->id);
    }
    function test_receive_destroy_callback()
    {
        $Proxy = new AkCacheSweeperProxy($this->TestCallback,array('destroy'));
        $obj = new stdClass();
        $obj->id = 1;
        $Proxy->afterDestroy($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'after');
        $this->assertEqual($executed[1],'destroy');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(1,$executed[2]->id);
        
        $obj = new stdClass();
        $obj->id = 2;
        $Proxy->beforeDestroy($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'before');
        $this->assertEqual($executed[1],'destroy');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(2,$executed[2]->id);
    }
    
    function test_receive_save_callback()
    {
        $Proxy = new AkCacheSweeperProxy($this->TestCallback,array('save'));
        $obj = new stdClass();
        $obj->id = 1;
        $Proxy->afterSave($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'after');
        $this->assertEqual($executed[1],'save');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(1,$executed[2]->id);
        
        $obj = new stdClass();
        $obj->id = 2;
        $Proxy->beforeSave($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual($executed[0],'before');
        $this->assertEqual($executed[1],'save');
        $this->assertIsA($executed[2],'stdclass');
        $this->assertEqual(2,$executed[2]->id);
    }
    function test_ignore_callbacks()
    {
        $Proxy = new AkCacheSweeperProxy($this->TestCallback,array('save'));
        $obj = new stdClass();
        $obj->id = 1;
        $Proxy->afterUpdate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==0);
        
        $obj = new stdClass();
        $obj->id = 2;
        $Proxy->afterCreate($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==0);
        
        $obj = new stdClass();
        $obj->id = 3;
        $Proxy->afterDestroy($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==0);
        
        $obj = new stdClass();
        $obj->id = 4;
        $Proxy->afterSave($obj);
        $executed = $this->TestCallback->getLastExecuted();
        $this->assertTrue(count($executed)==3);
        $this->assertEqual(4,$executed[2]->id);
    }


}

ak_test('_AkActionController_Caching_sweeper_proxy');


?>