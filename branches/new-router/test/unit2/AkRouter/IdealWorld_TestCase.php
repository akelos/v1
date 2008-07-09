<?php
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

abstract class IdealWorld_TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * :name=>:args pairs defining the routes the router connects to
     * 
     * array(
     *  'author'=>array('/author/:name',array('controller'=>'author','action'=>'show'))
     * )
     * 
     * results in
     * 
     * $Map->author(:args);
     *
     */
    public $Routes = array();
    
    // we mock away the singletons!
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
        foreach ($this->Routes as $name=>$args){
            call_user_func_array(array($Router,$name),$args);
        }
        
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