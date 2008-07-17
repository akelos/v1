<?php

require_once(AK_LIB_DIR.DS.'AkCache'.DS.'AkMemcache.php');

class Test_AkMemcache extends  UnitTestCase
{
    /**
     * @var AkMemcache
     */
    var $memcache;
    function setUp()
    {
        $this->memcache=new AkMemcache();
        $this->memcache->init(array('servers'=>array('127.0.0.1:11211')));
    }
    
    function test_init_without_server()
    {
        $this->memcache=new AkMemcache();
        $this->memcache->_defaultOptions = array();
        $res = $this->memcache->init(array());
        $this->assertFalse($res);
        $this->assertError('Need to provide at least 1 server');
    }
    function test_init_with_wrong_server()
    {
        $this->memcache=new AkMemcache();
        
        $res = $this->memcache->init(array('servers'=>'test:121'));
        $this->assertFalse($res);
        $this->assertError('Could not connect to MemCache daemon');
    }
    function test_set_and_get_string()
    {
        $original = 'test';
        $res = $this->memcache->save($original,'test_id_1','strings');
        $stored = $this->memcache->get('test_id_1','strings');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_string($stored));
    }
    
    function test_set_and_get_integer()
    {
        $original = 1111;
        $res = $this->memcache->save($original,'test_id_2','integers');
        $stored = $this->memcache->get('test_id_2','integers');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_int($stored));
    }
    function test_set_and_get_float()
    {
        $original = 11.11;
        $res = $this->memcache->save($original,'test_id_3','floats');
        $stored = $this->memcache->get('test_id_3','floats');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_float($stored));
    }
    function test_set_and_get_array()
    {
        $original = array(0,1,2,3,'test');
        $res = $this->memcache->save($original,'test_id_4','arrays');
        $stored = $this->memcache->get('test_id_4','arrays');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_array($stored));
    }
    
    function test_set_and_get_object()
    {
        $original = new stdClass;
        $original->id = 1;
        $res = $this->memcache->save($original,'test_id_5','objects');
        $stored = $this->memcache->get('test_id_5','objects');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_object($stored));
        $this->assertEqual($original->id, $stored->id);
    }
    
    function test_set_and_get_objects_within_arrays()
    {
        $obj1=new stdClass;
        $obj1->id=1;
        $obj2=new stdClass;
        $obj2->id=2;
        $original = array($obj1,$obj2);
        $res = $this->memcache->save($original,'test_id_6','objects');
        $stored = $this->memcache->get('test_id_6','objects');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_array($stored));
        $this->assertEqual($original[0]->id, $stored[0]->id);
        $this->assertEqual($original[1]->id, $stored[1]->id);
    }
    
    function test_set_and_get_large_strings()
    {
        $original = file_get_contents(__FILE__);
        $res = $this->memcache->save($original,'test_id_7','largestrings');
        $stored = $this->memcache->get('test_id_7','largestrings');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_string($stored));
    }
    
    function test_set_and_get_binary_data()
    {
        $original = file_get_contents(AK_BASE_DIR.DS.'public'.DS.'images'.DS.'akelos_framework_logo.png');
        $res = $this->memcache->save($original,'test_id_8','binary');
        $stored = $this->memcache->get('test_id_8','binary');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_string($stored));
    }
    
    function test_set_and_get_really_large_string()
    {
        $original = $this->_generateLargeString(1000000);
        $res = $this->memcache->save($original,'test_id_9','strings');
        $stored = $this->memcache->get('test_id_9','strings');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_string($stored));
    }
    function test_set_and_get_really_really_large_string()
    {
        $original = $this->_generateLargeString(2000000);
        $res = $this->memcache->save($original,'test_id_10','strings');
        $stored = $this->memcache->get('test_id_10','strings');
        $this->assertEqual($original,$stored);
        $this->assertTrue(is_string($stored));
    }
    
    function test_set_and_remove_key()
    {
        $original = $this->_generateLargeString(1000);
        $res = $this->memcache->save($original,'test_id_11','strings');
        $stored = $this->memcache->get('test_id_11','strings');
        $this->assertEqual($original,$stored);
        $this->memcache->remove('test_id_11','strings');
        $afterDelete = $this->memcache->get('test_id_11','strings');
        $this->assertNotEqual($original,$afterDelete);
        $this->assertEqual(null,$afterDelete);
    }
    
    function test_flush_group()
    {
        $retrieved = $this->memcache->get('test_id_10','strings');
        $this->assertTrue($retrieved!=null);
        
        $this->memcache->clean('strings');
        
        $retrieved = $this->memcache->get('test_id_10','strings');
        $this->assertTrue($retrieved==null);
        $retrieved = $this->memcache->get('test_id_9','strings');
        $this->assertTrue($retrieved==null);
        $retrieved = $this->memcache->get('test_id_8','strings');
        $this->assertTrue($retrieved==null);
        
        $retrieved = $this->memcache->get('test_id_2','integers');
        $this->assertTrue($retrieved!=null);
    }
    
    function _generateLargeString($size)
    {
        $string = '';
        while(strlen($string)<$size) {
            $string .= md5(time());
        }
        return $string;
    }
}


ak_test('Test_AkMemcache');
?>