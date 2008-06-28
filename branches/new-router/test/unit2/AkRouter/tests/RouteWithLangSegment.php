<?php
require_once 'Route_TestCase.php';

class RouteWithLangSegment extends Route_TestCase
{

    function setUp()
    {
        $this->withRoute('/:lang/person/:name');
    }
    
    function testLangSegmentIsHandledBySegmentFactory()
    {
        $segments = $this->Route->getSegments();
        $this->assertType('AkLangSegment',$segments['lang']);
    }
    
}

?>