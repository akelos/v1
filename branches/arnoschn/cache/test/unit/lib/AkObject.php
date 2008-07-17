<?php
require_once(AK_LIB_DIR.DS.'AkObject.php');

class Test_AkObject extends AkUnitTest
{

    function test_setoptions()
    {
        $obj = new AkObject();
        $obj->_defaultOptions = array('test'=>1);
        $obj->_availableOptions = array('test'=>'integer');
        $obj->setOptions(array('test'=>'1','nada'=>'2'));
        $this->assertTrue(isset($obj->_test));
        $this->assertEqual(1,$obj->_test);
        $this->assertTrue(!isset($obj->_nada));
    }
    
    function test_setoptions_with_prefix()
    {
        $obj = new AkObject();
        $obj->_defaultOptions = array('test'=>1);
        $obj->_availableOptions = array('test'=>'integer');
        $obj->setOptions(array('test'=>'1','nada'=>'2'),'_option');
        $this->assertTrue(isset($obj->_optiontest));
        $this->assertEqual(1,$obj->_optiontest);
        $this->assertTrue(!isset($obj->_optionnada));
    }
    function test_setoptions_without_prefix()
    {
        $obj = new AkObject();
        $obj->_defaultOptions = array('test'=>1);
        $obj->_availableOptions = array('test'=>'integer');
        $obj->setOptions(array('test'=>'1','nada'=>'2'),null);
        $this->assertTrue(isset($obj->test));
        $this->assertEqual(1,$obj->test);
        $this->assertTrue(!isset($obj->nada));
    }
    function test_setoptions_all_options_allowed()
    {
        $obj = new AkObject();
        $obj->_defaultOptions = array('test'=>1);
        $obj->_availableOptions = true;
        $obj->setOptions(array('test'=>'1','nada'=>'2'));
        $this->assertTrue(isset($obj->_test));
        $this->assertEqual(1,$obj->_test);
        $this->assertTrue(isset($obj->_nada));
        $this->assertEqual(2,$obj->_nada);
    }
}

ak_test('Test_AkObject');