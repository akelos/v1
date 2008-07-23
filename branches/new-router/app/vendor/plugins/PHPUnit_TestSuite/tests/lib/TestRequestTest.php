<?php

class TestRequestTest extends PHPUnit_Framework_TestCase   
{

    /**
     * @var AkRequest
     */
    var $Request;
    
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

        $Request = $this->getMock('AkRequest',array('getMethod','getParametersFromRequestedUrl'),array(),'',false);
        $Request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($method));
        $Request->expects($this->any())
                ->method('getParametersFromRequestedUrl')
                ->will($this->returnValue($params));
                
        // HACK  fix ->getParams
        foreach ($params as $k=>$v){
            $Request->_request[$k] = $v;
        }//HACK
        return $this->Request = $Request;
    }
    
    function useController($controller_name)
    {
        $this->controller_name = $controller_name;
    }
}
?>