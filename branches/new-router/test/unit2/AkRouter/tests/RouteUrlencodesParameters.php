<?php
require_once 'Route_TestCase.php';

class RouteUrlencodesParameters extends Route_TestCase 
{

    function setUp()
    {
        $this->withRoute('/author/:name');
    }
    
    function testParametrizeDecodesReturnedParameters()
    {
        $this->get('/author/Martin+L.+Degree')->matches(array('name'=>'Martin L. Degree'));
    }
    
    function testUrlizeEncodesGivenParameters()
    {
        $this->urlize(array('name'=>'Martin L. Degree'))->returns('/author/Martin+L.+Degree');
    }
    
}

?>