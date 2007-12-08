<?php

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class test_AkActiceRecord_datatypes extends  AkUnitTest
{

    function test_installer_should_handle_integers()
    {
        $this->installAndIncludeModels(array('Hybrid'=>'id,title,price integer'));
        $columns = $this->Hybrid->getColumnSettings();
        $this->assertEqual($columns['price']['type'],'integer');
    }
    
    function test_should_save_and_load_integers()
    {
        $Product =& $this->Hybrid->create(array('title'=>'Dollar','price'=>198));
        $Product =& $this->Hybrid->findFirst(array('title'=>'Dollar'));
        $this->assertEqual($Product->price,198);
    }
    
    function test_installer_should_handle_decimals()
    {
        $this->installAndIncludeModels(array('Hybrid'=>'id,title,price decimal(10.2)'));
        $columns = $this->Hybrid->getColumnSettings();
        $this->assertEqual($columns['price']['type'],'decimal');
    }
    
    function test_should_save_and_load_decimals()
    {
        $Product =& new Hybrid(array('title'=>'apple','price'=>10.99));
        $Product->save();
        
        $Product =& $this->Hybrid->find('first',array('title'=>'apple'));
        $this->assertEqual($Product->price,10.99);
    }
    
    function test_should_round_decimal()
    {
        $Product =& $this->Hybrid->create(array('title'=>'BigBlueStock','price'=>12.9888));
        $Product =& $this->Hybrid->find('first',array('title'=>'BigBlueStock'));
        $this->assertEqual($Product->price,12.99);
    }
    
    function test_should_handle_zero_value()
    {
        $Product =& new Hybrid(array('title'=>'chocolada','price'=>0));
        $Product->save();
        
        $Product =& $this->Hybrid->find('first',array('title'=>'chocolada'));
        $this->assertEqual($Product->price,0);
    }
    
    function test_should_handle_null_value()
    {
        $Product =& new Hybrid(array('title'=>'easter-egg','price'=>null));
        $Product->save();
        
        $Product =& $this->Hybrid->find('first',array('title'=>'easter-egg'));
        $this->assertNull($Product->price);
    }
    
    function test_installer_should_handle_booleans()
    {
        $this->installAndIncludeModels(array('Hybrid'=>'id,title,celebrity boolean'));
        $columns = $this->Hybrid->getColumnSettings();
        $this->assertEqual($columns['celebrity']['type'],'boolean');
    }
    
    function test_datatype_boolean_should_handle_true()
    {
        $Celebrity =& new Hybrid(array('title'=>'Kate','celebrity'=>true));
        $Celebrity->save();
        
        $Celebrity =& $this->Hybrid->find('first',array('title'=>'Kate'));
        $this->assertTrue($Celebrity->celebrity);
    }
    
    function test_datatype_boolean_should_handle_false()
    {
        $Celebrity =& new Hybrid(array('title'=>'Vinnie','celebrity'=>false));
        $Celebrity->save();
        
        $Celebrity =& $this->Hybrid->find('first',array('title'=>'Vinnie'));
        $this->assertFalse($Celebrity->celebrity);
    }
    
    function test_null_should_not_be_casted_as_false()
    {
        $Celebrity =& new Hybrid(array('title'=>'Franko','celebrity'=>null));
        $Celebrity->save();
        
        $Celebrity =& $this->Hybrid->find('first',array('title'=>'Franko'));
        $this->assertNull($Celebrity->celebrity);
    }
    
    function test_findBy_should_cast_booleans()
    {
        $Celebrity =& $this->Hybrid->findBy('celebrity','true');
        //$Celebrity =& $this->Hybrid->find('all',array('celebrity'=>true));
        $this->assertTrue($Celebrity[0]->celebrity);
        $this->assertEqual($Celebrity[0]->title,'Kate');
     
    }
    
    function _test_should_handle_small_integers()
    {
        $this->installAndIncludeModels(array('Hybrid'=>'id,title,price l(1)'));
        $columns = $this->Hybrid->getColumnSettings();
        var_dump($columns);
        var_dump($this->Hybrid->_db->getColumnDetails('hybrids'));
        $this->assertEqual($columns['price']['type'],'integer');
    }

    function test_should_handle_big_integers()
    {
        $this->installAndIncludeModels(array('Hybrid'=>'id,title,price I8'));
        $columns = $this->Hybrid->getColumnSettings();
        $this->assertEqual($columns['price']['type'],'integer');
        
        $Product =& $this->Hybrid->create(array('title'=>'Dollar','price'=>9876543210));
        $Product =& $this->Hybrid->findFirst(array('title'=>'Dollar'));
        $this->assertEqual($Product->price,9876543210);
    }
    
    
     
}

ak_test('test_AkActiceRecord_datatypes',true);
?>