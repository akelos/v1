<?php
PHPUnit_Akelos_autoload::addFolder(__FILE__);

class RouteTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var AkRequest
     */
    private $Request;
    
    /**
     * @var Route
     */
    private $Route;
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
        $this->get('/person/martin/')->matches();
    }
    
    function testStaticRouteReturnsDefaults()
    {
        $this->withRoute('/person/martin',array('controller'=>'person','action'=>'view'));

        $this->get('/person/martin/');
        $this->matches(array('controller'=>'person','action'=>'view'));
    }
    
    function testOptionalSegment()
    {
        $this->withRoute('/person/:name/:age');
        
        $this->get('/person') ->matches();
        $this->get('/person/')->matches();
        
        $this->get('/person/martin')    ->matches(array('name'=>'martin'));
        $this->get('/person/martin/')   ->matches(array('name'=>'martin'));
        $this->get('/person/martin/23') ->matches(array('name'=>'martin','age'=>'23'));
        $this->get('/person/martin/23/')->matches(array('name'=>'martin','age'=>'23'));
    }
    
    function testOptionalSegmentWithDefaults()
    {
        $this->withRoute('/person/:name/:age',array('name'=>'kevin','controller'=>'person'));
        
        $this->get('/person/')      ->matches(array('name'=>'kevin','controller'=>'person'));
        $this->get('/person/martin')->matches(array('name'=>'martin','controller'=>'person'));
    }
    
    function testOptionalSegmentWithRequirement()
    {
        $this->withRoute('/person/:age',array(),array('age'=>'[0-9]+'));
        
        $this->get('/person/abc')->doesntMatch();
        #$this->get('/person/')   ->doesntMatch();
        $this->get('/person/')   ->matches();
        $this->get('/person/23') ->matches(array('age'=>'23'));
        $this->get('/person23/') ->doesntMatch();
    }
    
    function _testRegex()
    {
        $pattern = "|^person(/.*)/?$|";
        $subject = "person";
        var_dump($pattern,$subject);
        var_dump(preg_match($pattern,$subject,$matches));
        var_dump($matches);
    }
    
    function createRequest($url)
    {
        $Request = $this->getMock('AkRequest',array('getRequestedUrl'));
        $Request->expects($this->once())
                ->method('getRequestedUrl')
                ->will($this->returnValue($url));
        
        return $this->Request = $Request;
    }
    
    /**
     * takes the same arguments as the constructor of a Route
     *
     * @return RouteTest
     */
    function withRoute($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->Route = new Route($url_pattern,$defaults,$requirements,$conditions);
        return $this;
    }

    /**
     * @return RouteTest
     */
    function get($url)
    {
        $this->Request = $this->createRequest($url);
        return $this;
    }
    
    function doesntMatch()
    {
        $this->assertFalse($this->Route->match($this->Request));
    }
    
    function matches($params=array())
    {
        $actual = $this->Route->match($this->Request);
        $this->assertEquals($params,$actual);
    }
    
}

?>