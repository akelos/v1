<?php
PHPUnit_Akelos_autoload::addFolder(__FILE__);

abstract class Route_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var AkRequest
     */
    protected $Request;
    
    /**
     * @var Route
     */
    protected $Route;

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
        $this->assertFalse($this->Route->parametrize($this->Request));
    }
    
    function matches($params=array())
    {
        $actual = $this->Route->parametrize($this->Request);
        $this->assertEquals($params,$actual);
    }
    
    function urlize($params = array())
    {
        $this->params = $params;
        return $this;
    }
    
    function returns($url)
    {
        $this->assertEquals($url,$this->Route->urlize($this->params));
    }
    
    function returnsFalse()
    {
        $this->assertFalse($this->Route->urlize($this->params));
    }
    
    
    
}

?>