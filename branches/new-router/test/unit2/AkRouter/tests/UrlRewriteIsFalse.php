<?php
require_once 'Route_TestCase.php';

class UrlRewriteIsFalse extends Route_TestCase
{

    function setUp()
    {
        $this->withRoute('/author/:name');
    }
    
    function testUrlizeReturnsHttpQuery()
    {
        $this->urlize(array('name'=>'martin'),false)->returns('/?ak=/author/martin');
    }
    
    function testUrlizeAppendsAdditionalParametersWithAnAmpersand()
    {
        $this->urlize(array('name'=>'martin','age'=>'23'),false)->returns('/?ak=/author/martin&age=23');
    }
    
}

?>