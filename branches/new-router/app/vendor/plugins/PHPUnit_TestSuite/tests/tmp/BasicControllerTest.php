<?php

class BasicControllerTest extends PHPUnit_Framework_TestCase 
{
    
    function testControllerName()
    {
        $Controller = new TestController();
        $this->assertEquals('Test',$Controller->getControllerName());
    }
    
    function testInstantiatePostControllerWhichShouldBeIncludedAutomatically()
    {
        $Controller = new PostController();
        $this->assertEquals('Post',$Controller->getControllerName());
    }
    
    function testInstantiateHelpers()
    {
        $Controller = new PostController();
        var_dump($Controller->instantiateHelpers());        
    }
}
?>