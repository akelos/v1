<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php';

class TestRequestTest extends PHPUnit_Framework_TestCase   
{

    /**
     * @var AkTestRequest
     */
    var $Request;
    
    function testInvestigateRequest()
    {
        $Request = new AkRequest();
        $Request->_request['ak'] = 'blog/show/1';
        $Request->_request['q']  = 'wer';
        
        $Router = new AkRouter();
        $Router->connect(':controller/:action/:id');
        $Request->checkForRoutedRequests($Router);

        $this->assertEquals(array(
            'controller'=>'blog',
            'action'=>'show',
            'id'=>1,
            'q'=>'wer',
            'ak'=>'blog/show/1'),$Request->getParameters());     # we don't need the 'ak'-key, do we?
        #var_dump($Request->getRequestUri());    # http://localhost/
        #var_dump($Request->getHost());          # localhost
        #var_dump($Request->getHostWithPort());  # localhost
        #var_dump($Request->getMethod());        # env->request_method
        #var_dump($Request->getLocaleFromUrl()); #
        #var_dump($Request->getPath());          # env->request_uri    
        #var_dump($Request->getPathParameters());# possibly orhpaned       
    }
        
    function testGetRequest()
    {
        $this->useController('blog');
        $this->get('index');
        
        $this->assertEquals(array('controller'=>'blog','action'=>'index'),$this->Request->getParams());
        $this->assertTrue($this->Request->isGet());
    }
    
    function testGetWithAdditionalParameters()
    {
        $this->useController('blog');
        $this->get('show',array('id'=>1,'q'=>'anything'));
        
        $this->assertEquals(
            array('controller'=>'blog','action'=>'show','id'=>1,'q'=>'anything'),
            $this->Request->getParams()
        );
    }
    
    function testPostRequest()
    {
        $this->useController('blog');
        $this->post('add',array('title'=>'A Post','body'=>'With a body.'));
        
        $this->assertEquals(
            array('controller'=>'blog','action'=>'add','title'=>'A Post','body'=>'With a body.'),
            $this->Request->getParams()
        );
        $this->assertTrue($this->Request->isPost());
    }
    
    function get($action,$options=array())
    {
        return $this->doRequest('get',$action,$options);
    }
    
    function post($action,$options=array())
    {
        return $this->doRequest('post',$action,$options);
    }
    
    function doRequest($method,$action,$options)
    {
        $params = array_merge(array('controller'=>$this->controller_name,'action'=>$action),$options);
        return $this->Request = AkTestRequest::createInstance($method,$params);
    }
    
    function useController($controller_name)
    {
        $this->controller_name = $controller_name;
    }
}
?>