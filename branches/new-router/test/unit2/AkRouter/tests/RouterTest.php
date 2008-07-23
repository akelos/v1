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
        $this->Router = new AkRouter();
    }

    function testInstantiateRouter()
    {
        $Router = new AkRouter();
    }
    
    function testAddRoute()
    {
        $this->Router->addRoute(null,new AkRoute('person/:name'));
        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array(0),array_keys($this->Router->getRoutes()));
    }
    
    function testAddNamedRoute()
    {
        $this->Router->addRoute('person',new AkRoute('person/:name'));
        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array('person'),array_keys($this->Router->getRoutes()));
    }
    
    function testConnectAddsUnnamedRoute()
    {
        $this->Router->connect('person/:name');

        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array(0),array_keys($this->Router->getRoutes()));
    }
    
    function testInterceptCallToUnknownMethodsAndAddNamedRoute()
    {
        $this->Router->person('person/:name');
        
        $this->assertEquals(1, count($this->Router->getRoutes()));
        $this->assertEquals(array('person'),array_keys($this->Router->getRoutes()));
    }
    
    function testMatchThrowsAnExcpetionIfRequestCannotBeSolved()
    {
        $Request = $this->getMock('AkRequest',array(),array(),'',false);
        $PersonRoute = $this->getMock('AkRoute',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('parametrize')
                    ->with($Request)
                    ->will($this->throwException(new RouteDoesNotMatchRequestException));
        
        $this->Router->addRoute('person',$PersonRoute);

        $this->setExpectedException('NoMatchingRouteException');
        $this->Router->match($Request);
    }
    
    function testMatchTraversesAllRegisteredRoutesIfFalseIsReturned()
    {
        $Request = $this->getMock('AkRequest',array(),array(),'',false);
        $PersonRoute = $this->getMock('AkRoute',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('parametrize')
                    ->with($Request)
                    ->will($this->throwException(new RouteDoesNotMatchRequestException));
        $AuthorRoute = $this->getMock('AkRoute',array(),array('author/:name'));
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
        $PersonRoute = $this->getMock('AkRoute',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('name'=>'martin'))
                    ->will($this->throwException(new RouteDoesNotMatchParametersException));
        $AuthorRoute = $this->getMock('AkRoute',array(),array('author/:name'));
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
        $PersonRoute = $this->getMock('AkRoute',array(),array('person/:name'));
        $PersonRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('not'=>'found'))
                    ->will($this->throwException(new RouteDoesNotMatchParametersException));
        $this->Router->addRoute('person',$PersonRoute);

        $this->setExpectedException('NoMatchingRouteException');
        $this->Router->urlize(array('not'=>'found'));
    }
    
    function testUrlizeUsingAnNamedRoute()
    {
        $AuthorRoute = $this->getMock('AkRoute',array(),array('author/:name'));
        $AuthorRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('name'=>'martin'))
                    ->will($this->returnValue('/author/martin'));
        
        $this->Router->addRoute('author',$AuthorRoute);
        
        $this->assertEquals('/author/martin',$this->Router->author_url(array('name'=>'martin')));
    }
    
    function testUrlizeUsingAnNamedRouteThrowsIfNotApplicable()
    {
        $AuthorRoute = $this->getMock('AkRoute',array(),array('author/:name'));
        $AuthorRoute->expects($this->once())
                    ->method('urlize')
                    ->with(array('name'=>'martin'))
                    ->will($this->throwException(new RouteDoesNotMatchParametersException));
        
        $this->Router->addRoute('author',$AuthorRoute);
        
        $this->setExpectedException('RouteDoesNotMatchParametersException');
        $this->Router->author_url(array('name'=>'martin'));
    }
    
    function testRequirementsShouldntHaveRegexDelimiters()
    {
        $Router = $this->getMock('AkRouter',array('addRoute'));
        $Router->expects($this->once())
               ->method('addRoute')
               ->with(null,new AkRoute('/author/:name',array(),array('name'=>'[a-z]+')));
               
        $Router->automatic_lang_segment = false;
        $Router->connect('/author/:name',array(),array('name'=>'/[a-z]+/'));
    }
    
    function testDefaultsShouldntBeUsedForRequirements()
    {
        $Router = $this->getMock('AkRouter',array('addRoute'));
        $Router->expects($this->once())
               ->method('addRoute')
               ->with(null,new AkRoute('/author/:name',array(),array('name'=>'[a-z]+')));
               
        $Router->automatic_lang_segment = false;
        $Router->connect('/author/:name',array('name'=>'/[a-z]+/'));
    }

    function testSegmentsShouldntBeDeclaredOptional()
    {
        $Router = $this->getMock('AkRouter',array('addRoute'));
        $Router->expects($this->once())
               ->method('addRoute')
               ->with(null,new AkRoute('/author/:name',array()));
               
        $Router->automatic_lang_segment = false;
        $Router->connect('/author/:name',array('name'=>OPTIONAL));
    }
    
    function testDefaultsShouldntBeUsedForRequirementsAsAnExplicitOption()
    {
        $Router = $this->getMock('AkRouter',array('addRoute'));
        $Router->expects($this->once())
               ->method('addRoute')
               ->with(null,new AkRoute('/author/:name',array(),array('name'=>'[a-z]+')));
               
        $Router->automatic_lang_segment = false;
        $Router->connect('/author/:name',array('requirements'=>array('name'=>'/[a-z]+/')));
    }
    
}

?>