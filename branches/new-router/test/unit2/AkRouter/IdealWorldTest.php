<?php
require_once 'IdealWorld_TestCase.php';

class IdealWorldTest extends IdealWorld_TestCase 
{
    
    public $Routes = array(
        'clutter_namespace'=>array('/nothing/:here')
    );
    
    function testEnsureSingletonsAreNull()
    {
        $this->assertNull(AkRouter::$singleton);
        $this->assertNull(AkRequest::$singleton);
    }
    
    function testEnsureHelperFunctionsAreAvailable()
    {
        $this->createRouter();
        $this->assertTrue(function_exists('clutter_namespace_url'));
        $this->assertTrue(function_exists('clutter_namespace_path'));
    }
    
}

?>