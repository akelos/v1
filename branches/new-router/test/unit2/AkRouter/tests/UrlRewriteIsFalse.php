<?php
require_once 'Route_TestCase.php';

class UrlRewriteIsFalse extends Route_TestCase
{

    function testUrlizeReturnsHttpQuery()
    {
        $url = new AkUrl('/author/martin');
        $url->setRewriteEnabled(false);
        
        $this->assertEquals('/?ak=/author/martin',$url->path());
    }
    
    function testUrlizeAppendsAdditionalParametersWithAnAmpersand()
    {
        $url = new AkUrl('/author/martin','age=23');
        $url->setRewriteEnabled(false);

        $this->assertEquals('/?ak=/author/martin&age=23',$url->path());
    }
    
    function testTrailingSlash()
    {
        $url = new AkUrl('/author/martin');
        $url->setOptions(array('trailing_slash'=>true));
        
        $this->assertEquals('/author/martin/',$url->path());
    }
    
}

?>