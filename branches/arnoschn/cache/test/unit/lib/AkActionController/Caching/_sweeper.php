<?php

require_once(dirname(__FILE__).'/../../../../fixtures/config/config.php');
require_once(AK_LIB_DIR . DS . 'AkActionController'.DS.'Caching'.DS.'AkCacheSweeperProxy.php');
require_once(AK_LIB_DIR . DS . 'AkActionController'.DS.'Caching'.DS.'AkCacheSweeper.php');
require_once(AK_LIB_DIR . DS . 'AkActionController.php');
class TestSweeper extends AkCacheSweeper
{
    var $observe = array();
}
class TestController extends AkActionController
{
    var $cache_sweeper = array("test_sweeper");

}

class _AkActionController_Caching_sweeper_proxy extends AkUnitTest
{

    function setUp()
    {
        $this->TestController = new TestController();
        
    }
   
    function test_receive_update_callback()
    {
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