<?php
require_once 'Route_TestCase.php';

class RouteTest extends Route_TestCase
{

    function testWithRouteInstantiatesARoute()
    {
        $this->withRoute('/person/:name');
        $this->assertType('Route',$this->Route);
    }
    
    function testMockedRequestCanBeAskedAboutRequestedUrl()
    {
        $Request = $this->createRequest('/person/martin');
        $this->assertEquals('/person/martin',$Request->getRequestedUrl());
    }
    
    function testStaticRouteDoesNotMatchAgainstRoot()
    {
        $this->withRoute('/person');
        $this->get('/')->doesntMatch();
    }
    
    function testStaticRouteMatchesAgainstExactUrl()
    {
        $this->withRoute('/person/martin');
        $this->get('/person/martin')->matches();
    }
    
    function testStaticRouteReturnsDefaults()
    {
        $this->withRoute('/person/martin',array('controller'=>'person','action'=>'view'));

        $this->get('/person/martin');
        $this->matches(array('controller'=>'person','action'=>'view'));
    }
    
    function testRootMatchesAndReturnsDefaults()
    {
        $this->withRoute('/',array('controller'=>'person','action'=>'list'));
        
        $this->get('/')->matches(array('controller'=>'person','action'=>'list'));
    }
    
    function testOptionalSegment()
    {
        $this->withRoute('/person/:name/:age');
        
        $this->get('/person')->matches();
        
        $this->get('/person/martin')    ->matches(array('name'=>'martin'));
        $this->get('/person/martin/23') ->matches(array('name'=>'martin','age'=>'23'));
    }
    
    function testOptionalSegmentWithDefaults()
    {
        $this->withRoute('/person/:name/:age',array('name'=>'kevin','controller'=>'person'));
        
        $this->get('/person')      ->matches(array('name'=>'kevin','controller'=>'person'));
        $this->get('/person/martin')->matches(array('name'=>'martin','controller'=>'person'));
    }
    
    function testOptionalSegmentWithRequirement()
    {
        $this->withRoute('/person/:age',array(),array('age'=>'[0-9]+'));
        
        $this->get('/person/abc')->doesntMatch();
        #$this->get('/person/')   ->doesntMatch();
        $this->get('/person')    ->matches();
        $this->get('/person/23') ->matches(array('age'=>'23'));
        $this->get('/person23')  ->doesntMatch();
    }
    
    function testCompulsoryVariableSegment()
    {
        $this->withRoute('/person/:age',array('age'=>COMPULSORY),array('age'=>'[0-9]+'));
        
        $this->get('/')      ->doesntMatch();
        $this->get('/person')->doesntMatch();
        $this->get('/person/123')->matches(array('age'=>'123'));
    }
    
    function testRouteWithOnlyOptionalSegmentsMatchesAgainstRoot()
    {
        $this->withRoute('/:person/:name/:age',array('controller'=>'person'));
        
        $this->get('/')->matches(array('controller'=>'person'));
    }
    
    function testUrlizeWithOptionalSegment()
    {
        $this->withRoute('/person/:age');
        
        $this->urlize()->returns('/person');
        $this->urlize(array('age'=>'23'))->returns('/person/23');
    }
    
    function testUrlizeWithOptionalSegmentAndDefaults()
    {
        $this->withRoute('/person/:name',array('name'=>'martin'));
        
        $this->urlize()->returns('/person');
        $this->urlize(array('name'=>'steve'))->returns('/person/steve');
        $this->urlize(array('name'=>'martin'))->returns('/person');
    }
    
    function testUrlizeWithMultipleOptionalSegments()
    {
        $this->withRoute('/person/:name/:age',array('name'=>'martin'),array('name'=>'[a-z]+'));
        
        $this->urlize()->returns('/person');
        $this->urlize(array('name'=>'steve'))            ->returns('/person/steve');
        $this->urlize(array('name'=>'steve','age'=>'34'))->returns('/person/steve/34');
    }
    
    function testUrlizeChecksForRequirements()
    {
        $this->withRoute('/person/:name/:age',array(),array('name'=>'[a-z]+','age'=>'[0-9]+'));
        
        $this->urlize(array('name'=>'123'))->returnsFalse();
        $this->urlize(array('age' =>'abc'))->returnsFalse();
        $this->urlize(array('name'=>'123','age'=>'12'))->returnsFalse();
        $this->urlize(array('name'=>'abc','age'=>'ab'))->returnsFalse();
        $this->urlize(array('name'=>'abc','age'=>'0'))->returns('/person/abc/0');
    }
    
    function testOptionalSegmentFollowedByAnotherOptionalSegmentActuallyIsCompulsory()
    {
        $this->withRoute('/person/:name/:age',array('name'=>'martin'),array('name'=>'[a-z]+'));
        
        $this->get('/person/24')         ->doesntMatch();
        $this->urlize(array('age'=>'34'))->returnsFalse();
    }
    
    function _testRegex()
    {
        $pattern = "|^person(/.*)/?$|";
        $subject = "person";
        var_dump($pattern,$subject);
        var_dump(preg_match($pattern,$subject,$matches));
        var_dump($matches);
    }
    
}

?>