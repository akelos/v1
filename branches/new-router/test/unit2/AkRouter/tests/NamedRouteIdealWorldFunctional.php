<?php
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

class NamedRouteIdealWorldFunctional extends PHPUnit_Framework_TestCase
{

    function testEnsureSingletonsAreNull()
    {
        $this->assertNull(AkRouter::$singleton);
        $this->assertNull(AkRequest::$singleton);
    }
    
    function testEnsureHelperFunctionsAreAvailable()
    {
        $this->createRouter();
        $this->assertTrue(function_exists('author_url'));
        $this->assertTrue(function_exists('author_path'));
        $this->assertTrue(function_exists('default_url'));
        $this->assertTrue(function_exists('default_path'));
        $this->assertTrue(function_exists('root_url'));
        $this->assertTrue(function_exists('root_path'));
    }
    
    function testDefaultRoute()
    {
        $url_writer = $this->withRequestTo('/user');
        $this->assertEquals('http://localhost/user/show/1',$url_writer->urlFor(array('action'=>'show','id'=>'1')));
    }
    
    function testFromDefaultToAuthor()
    {
        $this->withRequestTo('/user');
        $this->assertEquals('http://localhost/author/mart',author_url(array('name'=>'mart')));
    }

    function testFromAuthorToRoot()
    {
        $this->withRequestTo('/author/steve');
        $this->assertEquals('http://localhost/',root_url());
    }
    
    function testFromRootToAuthorPath()
    {
        $this->withRequestTo('/');
        $this->assertEquals('/author/steve',author_path(array('name'=>'steve')));
    }
    
    /* = = = = = = TEST - API = = = = = = */

    /* we mock away the singletons!       */
    function tearDown()
    {
        AkRouter   ::$singleton = null;
        AkRequest  ::$singleton = null;
        AkUrlWriter::$singleton = null;
    }
    
    
    /**
     * @return AkUrlWriter
     */
    function withRequestTo($actual_url)
    {
        $Router = $this->createRouter();
        $Request = $this->createRequest($actual_url);
        $Request->checkForRoutedRequests($Router);

        return $this->createUrlWriter($Request,$Router);
    }

    /**
     * @return AkUrlWriter
     */
    function createUrlWriter($Request,$Router)
    {
        $UrlWriter = new AkUrlWriter($Request,$Router);
        
        AkUrlWriter::$singleton = $UrlWriter;
        return $this->UrlWriter = $UrlWriter;
    }
    
    /**
     * @var AkRouter
     */
    private $Router;
    
    /**
     * @var AkRequest
     */
    private $Request;
    
    /**
     * @return AkRouter
     */
    function createRouter()
    {
        $Router = new AkRouter();
        $Router->generate_helper_functions = true;
        $Router->author('/author/:name',array('controller'=>'author','action'=>'show','name'=>COMPULSORY));
        $Router->default('/:controller/:action/:id',array('controller'=>COMPULSORY,'action'=>'index'));
        $Router->root('/',array('controller'=>'blog','action'=>'index'));
        
        AkRouter::$singleton = $Router;
        return $this->Router = $Router;
    }
    
    /**
     * @return AkRequest
     */
    function createRequest($url,$method='get')
    {
        $Request = $this->getMock('AkRequest',array('getRequestedUrl','getMethod','getRelativeUrlRoot'));
        $Request->expects($this->any())
                ->method('getRequestedUrl')
                ->will($this->returnValue($url));
        $Request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($method));
        $Request->expects($this->any())
                ->method('getRelativeUrlRoot')
                ->will($this->returnValue(''));
                
        AkRequest::$singleton = $Request;
        return $this->Request = $Request;
    }
}

?>