<?php
PHPUnit_Akelos_autoload::addFolder(__FILE__);

class RouterTest extends PHPUnit_Framework_TestCase 
{
    /**
     * @var Router
     */
    private $Router;
    function setUp()
    {
        $this->Router = new Router();
    }

    function testInstantiateRouter()
    {
        $Router = new Router();
    }
    
    function testAddRoute()
    {
        $this->Router->addRoute(null,new Route('person/:name'));
        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array(0),array_keys($this->Router->getRoutes()));
    }
    
    function testAddNamedRoute()
    {
        $this->Router->addRoute('person',new Route('person/:name'));
        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array('person'),array_keys($this->Router->getRoutes()));
    }
    
    function testConnect()
    {
        $this->Router->connect('person/:name');

        $this->assertEquals(1, count($this->Router->getRoutes()));
    }
    
    function testNoMatch()
    {
        $Request = new AkRequest();
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('match')
                    ->with($Request)
                    ->will($this->returnValue(false));
        
        $this->Router->addRoute('person',$PersonRoute);

        $this->setExpectedException('NoMatchingRouteException');
        $this->Router->match($Request);
    }
    
    function testMatch()
    {
        $Request = new AkRequest();
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('match')
                    ->with($Request)
                    ->will($this->returnValue(false));
        $AuthorRoute = $this->getMock('Route',array(),array('author/:name'));
        $AuthorRoute->expects($this->once())
                    ->method('match')
                    ->with($Request)
                    ->will($this->returnValue(true));
        
        $this->Router->addRoute('person',$PersonRoute);
        $this->Router->addRoute('author',$AuthorRoute);
        
        $this->Router->match($Request);
        $this->assertEquals($AuthorRoute,$this->Router->currentRoute);
    }
    
}

?>