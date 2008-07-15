<?php
require_once(AK_LIB_DIR.DS.'Ak.php');

class Test_Ak extends AkUnitTest
{
    function test_static_var_set_value_null()
    {
        $null = null;
        $return = Ak::static_var('testVar1',$null);
        $this->assertEqual(null,$return);
    }
    
    function test_static_var_set_value_true()
    {
        $true = true;
        $null = null;
        $return = Ak::static_var('testVar1',$true);
        $this->assertEqual(true,$return);
        $this->assertEqual(true,Ak::static_var('testVar1', $null));
    }
    
    function test_static_var_set_value_false()
    {
        $false = false;
        $return = Ak::static_var('testVar1',$false);
        $this->assertEqual(true,$return);
        $this->assertEqual(false,Ak::static_var('testVar1', $null));
    }
    
    function test_static_var_set_value_array()
    {
        $value = array(1);
        $return = Ak::static_var('testVar1',$value);
        $this->assertEqual(true,$return);
        $this->assertEqual($value,Ak::static_var('testVar1', $null));
        
        $obj1 = new stdClass;
        $obj1->id = 1;
        $value = array(&$obj1);
        $return = Ak::static_var('testObjectArray',$value);
        $this->assertEqual(true,$return);
        $this->assertEqual($value,Ak::static_var('testObjectArray', $null));
        $retrievedObject = &$value[0];
        $this->assertEqual($retrievedObject->id, $obj1->id);
        $obj1->id=2;
        $this->assertEqual($retrievedObject->id, $obj1->id);
        $retrievedObject->id=3;
        $this->assertEqual($retrievedObject->id, $obj1->id);
        
    }
    function test_static_var_set_value_float()
    {
        $value = 13.59;
        $return = Ak::static_var('testVar1',$value);
        $this->assertEqual(true,$return);
        $this->assertEqual($value,Ak::static_var('testVar1', $null));
    }

    function test_static_var_set_value_object_referenced()
    {
        $value = new stdClass;
        $value->id = 1;
        $return = Ak::static_var('testVar1',$value);
        $this->assertEqual(true,$return);
        $storedValue = &Ak::static_var('testVar1', $null);
        $this->assertEqual($value,$storedValue);
        $value->id = 2;
        $this->assertEqual($value->id, $storedValue->id);
    }
    
    function test_static_var_destruct_single_var()
    {
        $value = new stdClass;
        $value->id = 1;
        $return = Ak::static_var('testVar1',$value);
        $this->assertEqual(true,$return);
        $storedValue = &Ak::static_var('testVar1', $null);
        $this->assertEqual($value,$storedValue);
        $null = null;
        Ak::static_var('testVar1', $null,true);
        $storedValue = &Ak::static_var('testVar1', $null);
        $this->assertEqual($null, $storedValue);
        
    }
    
    function test_static_var_destruct_all_vars()
    {
        $value = new stdClass;
        $value->id = 1;
        $return = Ak::static_var('testVar1',$value);
        $this->assertEqual(true,$return);
        
        $value2 = new stdClass;
        $value2->id = 2;
        $return = Ak::static_var('testVar2',$value);
        $this->assertEqual(true,$return);
        
        $value3 = new stdClass;
        $value3->id = 3;
        $return = Ak::static_var('testVar3',$value);
        $this->assertEqual(true,$return);
        
        $null = null;
        Ak::static_var('testVar1', $null,true);
        $storedValue1 = &Ak::static_var('testVar1', $null);
        $this->assertEqual($null, $storedValue1);
        
        $storedValue2 = &Ak::static_var('testVar2', $null);
        $this->assertEqual($value2, $storedValue2);
        
        $storedValue3 = &Ak::static_var('testVar3', $null);
        $this->assertEqual($value3, $storedValue3);
        
        Ak::static_var($null, $null,true);
        $storedValue1 = &Ak::static_var('testVar1', $null);
        $this->assertEqual($null, $storedValue1);
        $storedValue2 = &Ak::static_var('testVar2', $null);
        $this->assertEqual($null, $storedValue2);
        $storedValue3 = &Ak::static_var('testVar3', $null);
        $this->assertEqual($null, $storedValue3);
    }
}