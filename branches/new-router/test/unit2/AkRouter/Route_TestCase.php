<?php
PHPUnit_Akelos_autoload::addFolder(dirname(__FILE__).DS.'lib');
require_once dirname(__FILE__).DS.'lib'.DS.'Router.php';

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

    function createRequest($url,$method='get')
    {
        $Request = $this->getMock('AkRequest',array('getRequestedUrl','getMethod'));
        $Request->expects($this->any())
                ->method('getRequestedUrl')
                ->will($this->returnValue($url));
        $Request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($method));                
        
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
    function get($url,$method='get')
    {
        $this->Request = $this->createRequest($url,$method);
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