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
        $this->urlize()->returns('/');
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
    
    function testUrlizeBreaksIfACompulsorySegmentIsNotSet()
    {
        $this->withRoute('/person/:name/:age',array('age'=>COMPULSORY));
        
        $this->urlize()                      ->returnsFalse();
        $this->urlize(array('name'=>'lewis'))->returnsFalse();
        $this->urlize(array('name'=>'lewis','age'=>'45'))->returns('/person/lewis/45');
    }
    
    function testUrlizeAppendsAnyAdditionalParameters()
    {
        $this->withRoute('/person/:name/:age');
        
        $this->urlize(array('format'=>'xml'))->returns('/person?format=xml');
        $this->urlize(array('name'=>'steve','format'=>'xml'))->returns('/person/steve?format=xml');
    }
    
    function testUrlizeBreaksIfParameterTriesToOverrideADefaultWithoutMatchingNamedSegment()
    {
        $this->withRoute('/person/:name/:age',array('controller'=>'person'));
        
        $this->urlize(array('controller'=>'author'))->returnsFalse();
    }
    
    function testWildcardSegmentImplicitOptional()
    {
        $this->withRoute('/set/*options');
        
        $this->get('/set')              ->matches();
        $this->get('/set/this')         ->matches(array('options'=>array('this')));
        $this->get('/set/this/and/that')->matches(array('options'=>array('this','and','that')));
    }
    
    function testWilcardSegmentAtTheBeginning()
    {
        $this->withRoute('/*parameters');
        
        $this->get('/unknown')    ->matches(array('parameters'=>array('unknown')));
        $this->get('/unknown/url')->matches(array('parameters'=>array('unknown','url')));
    }
    
    function testWildcardSegmentAtTheBeginningStaticFollowing()
    {
        $this->withRoute('/*parameters/set');
        
        $this->get('/set')->matches();
        $this->get('/style=custom/set')->matches(array('parameters'=>array('style=custom')));
    }
    
    function testWildcardSegmentsSurroundedByStatics()
    {
        $this->withRoute('/set/*options/now');
        
        $this->get('/set/this/now')->matches(array('options'=>array('this')));
        $this->get('/set/now')     ->matches();
    }
    
    function testCompulsoryWildcardSegment()
    {
        $this->withRoute('/set/*options/now',array('options'=>COMPULSORY));
        
        $this->get('/set/this/now')->matches(array('options'=>array('this')));
        $this->get('/set/now')->doesntMatch();
    }
    
    function testVariableSegmentFollowedByWildcardSegment()
    {
        $this->withRoute(':controller/*options');
        
        $this->get('/admin')->matches(array('controller'=>'admin'));
        $this->get('/admin/style=blue')->matches(array('controller'=>'admin','options'=>array('style=blue')));
    }
    
    function testWildcardSegmentWithRequirements()
    {
        $this->withRoute('/numbers/*numbers',array(),array('numbers'=>'[0-9]+'));
        
        $this->get('/numbers')->matches();
        $this->get('/numbers/12/345/6/789')->matches(array('numbers'=>array('12','345','6','789')));
        $this->get('/numbers/12/stop/789') ->doesntMatch();
    }
    
    function testCompulsoryWildcardSegmentWithRequirements()
    {
        $this->withRoute('/numbers/*numbers',array('numbers'=>COMPULSORY),array('numbers'=>'[0-9]+'));
        
        $this->get('/numbers')->doesntMatch();
        $this->get('/numbers/12/345/6/789')->matches(array('numbers'=>array('12','345','6','789')));
        $this->get('/numbers/12/stop/789') ->doesntMatch();
    }
    
    function _testRegex()
    {
        $pattern = "|^person(/.*)/?$|";
        #"(?:$this->delimiter({$this->getInnerRegEx()}))$optional_switch";
        $pattern = "|^/set(?:/([^/]*))?$|";
        $pattern = "|^/set
                (?:/((?:/?[^/]*)+))?
                /steve
            $|x";
        $delimiter = '/';
        $inner =  '[^/]+';
        
        $pattern = "|^/set
                (?:$delimiter((?:$inner/?)+))?
                /steve
            $|x";
        $subject = "/set/martin/dave/steve";
        $subject = "/set/steve";
        var_dump($pattern,$subject);
        var_dump(preg_match($pattern,$subject,$matches));
        var_dump($matches);
    }
    
}

?>