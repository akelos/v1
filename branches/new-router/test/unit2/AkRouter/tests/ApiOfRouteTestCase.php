<?php
require_once 'Route_TestCase.php';

class ApiOfRouteTestCase extends Route_TestCase
{

    function testWithRouteInstantiatesARoute()
    {
        $this->withRoute('/person/:name');
        $this->assertType('AkRoute',$this->Route);
    }
    
    function testMockedRequestCanBeAskedAboutRequestedUrl()
    {
        $Request = $this->createRequest('/person/martin');
        $this->assertEquals('/person/martin',$Request->getRequestedUrl());
    }
    
    function testMockedRequestCanBeAskedAboutRequestedMethod()
    {
        $Request = $this->createRequest('/person/martin','post');
        $this->assertEquals('post',$Request->getMethod());
    }
    
}

?>