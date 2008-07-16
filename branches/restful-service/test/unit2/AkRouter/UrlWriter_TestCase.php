<?php
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

abstract class UrlWriter_TestCase extends PHPUnit_Framework_TestCase
{
   
    /**
     * @var AkRequest
     */
    protected $Request;

    private $asked_url_for_parameters;

    /**
     * @return AkRequest
     */
    function withRequestTo($params)
    {
        return $this->Request = $this->createRequest($params);
    }
    
    /**
     * @param array $options same as AkActionController->urlFor
     * @return UrlWriterTest
     */
    function urlFor($options)
    {
        $this->asked_url_for_parameters = $options;
        return $this;
    }
    
    function isRewrittenTo($expected_params)
    {
        $args = array($expected_params);
        $this->Router = $Router = $this->createRouter('urlize',$args);
        $UrlWriter = new AkUrlWriter($this->Request,$Router);
        $UrlWriter->urlFor($this->asked_url_for_parameters);
    }
    
    /**
     * @return AkRequest
     */
    function createRequest($params)
    {
        $Request = $this->getMock('AkRequest',array('getParametersFromRequestedUrl'));
        $Request->expects($this->any())
                ->method('getParametersFromRequestedUrl')
                ->will($this->returnValue($params));
        
        return $this->Request = $Request;
    }
    
    /**
     * @return AkRouter
     */
    function createRouter($method_name,$args=array())
    {
        $Router = $this->getMock('AkRouter',array($method_name));
        $method_object = $Router->expects($this->any())
               ->method($method_name)
               ->will($this->returnValue(new AkUrl('')));
        call_user_func_array(array($method_object,'with'),$args);
               
        return $this->Router = $Router;
    }
    
    
}

?>