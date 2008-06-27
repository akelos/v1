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

    /**
     * @return AkRequest
     */
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
     * @return Route_TestCase
     */
    function withRoute($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->Route = new Route($url_pattern,$defaults,$requirements,$conditions);
        return $this;
    }

    /**
     * @return Route_TestCase
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
    
    /**
     * @return Route_TestCase
     */
    function urlize($params = array(),$rewrite_enabled=AK_URL_REWRITE_ENABLED)
    {
        $this->params = $params;
        $this->rewrite_enabled = $rewrite_enabled;
        return $this;
    }
    
    function returns($url)
    {
        $this->assertEquals($url,$this->Route->urlize($this->params,$this->rewrite_enabled));
    }
    
    function returnsFalse()
    {
        $this->assertFalse($this->Route->urlize($this->params,$this->rewrite_enabled));
    }
    
}

?>