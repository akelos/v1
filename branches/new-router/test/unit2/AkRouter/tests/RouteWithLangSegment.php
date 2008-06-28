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
    
    function testLangCanBeOmmittedOnParametrize()
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

    function testLangCanBeOmmittedOnUrlize()
    {
        $this->urlize(array('name'=>'martin'))->returns('/person/martin');
    }
    
    function testCanUrlizeAvailableLocales()
    {
        $this->urlize(array('lang'=>'en'))->returns('/en/person');
        $this->urlize(array('lang'=>'es','name'=>'martin'))->returns('/es/person/martin');
    }
    
    function testBreakUrlizeOnUnknownLocales()
    {
        $this->urlize(array('lang'=>'jp'))->returnsFalse();
    }
    
}

?>