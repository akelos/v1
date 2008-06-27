<?php
PHPUnit_Akelos_autoload::addFolder(__FILE__);

class RouteTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var AkRequest
     */
    private $Request;
    function testInstantiateRoute()
    {
        $Route = new Route('/person/:name');
    }
    
    function testMockedRequest()
    {
        $Request = $this->createRequest('/person/martin');
        $this->assertEquals('/person/martin',$Request->getRequestedUrl());
    }
    
    function testStaticRouteDoesNotMatchAgainstRoot()
    {
        $Route = new Route('/person');
        $Request = $this->createRequest('/');
        
        $this->assertFalse($Route->match($Request));
    }
    
    function testStaticRouteMatchesAgainstExactUrl()
    {
        $Route = new Route('/person/martin');
        $Request = $this->createRequest('/person/martin/');

        $this->assertEquals(array(),$Route->match($Request));
    }
    
    function testStaticRouteReturnsDefaults()
    {
        $Route = new Route('/person/martin',array('controller'=>'person','action'=>'view'));
        $Request = $this->createRequest('/person/martin/');

        $this->assertEquals(array('controller'=>'person','action'=>'view'),$Route->match($Request));
    }
    
    function testOptionalSegment()
    {
        $Route = new Route('/person/:name/:age');
        
        $this->assertEquals(array(),$Route->match($this->createRequest('/person')));
        $this->assertEquals(array(),$Route->match($this->createRequest('/person/')));
        $this->assertEquals(array('name'=>'martin'),$Route->match($this->createRequest('/person/martin')));
        $this->assertEquals(array('name'=>'martin'),$Route->match($this->createRequest('/person/martin/')));
        $this->assertEquals(array('name'=>'martin','age'=>'23'),$Route->match($this->createRequest('/person/martin/23')));
        $this->assertEquals(array('name'=>'martin','age'=>'23'),$Route->match($this->createRequest('/person/martin/23/')));
    }
    
    function testOptionalSegmentWithDefaults()
    {
        $Route = new Route('/person/:name/:age',array('name'=>'kevin','controller'=>'person'));
        $this->assertEquals(array('name'=>'kevin','controller'=>'person'),$Route->match($this->createRequest('/person/')));
        $this->assertEquals(array('name'=>'martin','controller'=>'person'),$Route->match($this->createRequest('/person/martin')));
    }
    
    function testOptionalSegmentWithRequirement()
    {
        $Route = new Route('/person/:age',array(),array('age'=>'[0-9]+'));
        
        $this->assertFalse($Route->match($this->createRequest('/person/abc')));
        #$this->assertFalse($Route->match($this->createRequest('/person/')));
        $this->assertEquals(array(),$Route->match($this->createRequest('/person/')));
        $this->assertEquals(array('age'=>'23'),$Route->match($this->createRequest('/person/23')));
        $this->assertFalse($Route->match($this->createRequest('/person23/')));
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
}

?>