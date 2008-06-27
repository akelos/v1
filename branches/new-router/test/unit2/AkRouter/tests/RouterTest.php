<?php
PHPUnit_Akelos_autoload::addFolder(dirname(dirname(__FILE__)).DS.'lib');

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
    
    function testMatchThrowsAnExcpetionIfRequestCannotBeSolved()
    {
        $Request = new AkRequest();
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('parametrize')
                    ->with($Request)
                    ->will($this->returnValue(false));
        
        $this->Router->addRoute('person',$PersonRoute);

        $this->setExpectedException('NoMatchingRouteException');
        $this->Router->match($Request);
    }
    
    function testMatchTraversesAllRegisteredRoutesIfFalseIsReturned()
    {
        $Request = new AkRequest();
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('parametrize')
                    ->with($Request)
                    ->will($this->returnValue(false));
        $AuthorRoute = $this->getMock('Route',array(),array('author/:name'));
        $AuthorRoute->expects($this->once())
                    ->method('parametrize')
                    ->with($Request)
                    ->will($this->returnValue(true));
        
        $this->Router->addRoute('person',$PersonRoute);
        $this->Router->addRoute('author',$AuthorRoute);
        
        $this->Router->match($Request);
        $this->assertEquals($AuthorRoute,$this->Router->currentRoute);
    }
    
    function testUrlizeTraversesAllRegisteredRoutesWhileFalseIsReturned()
    {
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('name'=>'martin'))
                    ->will($this->returnValue(false));
        $AuthorRoute = $this->getMock('Route',array(),array('author/:name'));
        $AuthorRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('name'=>'martin'))
                    ->will($this->returnValue('/author/martin'));
        
        $this->Router->addRoute('person',$PersonRoute);
        $this->Router->addRoute('author',$AuthorRoute);
        
        $this->assertEquals('/author/martin',$this->Router->urlize(array('name'=>'martin')));
    }
    
    function testUrlizeThrowsAnExceptionIfItCantFindARoute()
    {
        $PersonRoute = $this->getMock('Route',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('not'=>'found'))
                    ->will($this->returnValue(false));
        $this->Router->addRoute('person',$PersonRoute);

        $this->setExpectedException('NoMatchingRouteException');
        $this->Router->urlize(array('not'=>'found'));
    }
    
}

?>