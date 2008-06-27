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
        $name = 'Martin L. Degree';
        $this->get('/author/'.urlencode($name))->matches(array('name'=>$name));
    }
    
    function testUrlizeEncodesGivenParameters()
    {
        $name = 'Martin L. Degree';
        $this->urlize(array('name'=>$name))->returns('/author/'.urlencode($name));
    }
    
}

?>