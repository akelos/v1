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
    
    function testLangCanBeOmmitted()
    {
        $this->get('/person/martin')->matches(array('name'=>'martin'));
    }
    
    function testLangHasAutomaticRequirements()
    {
        $this->get('/jp/person/martin')->doesntMatch();
        foreach (Ak::langs() as $lang){
            $this->get("/$lang/person")->matches(array('lang'=>$lang));
        }
    }
}

?>