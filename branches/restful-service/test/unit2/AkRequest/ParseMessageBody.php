<?php

class ParseMessageBody extends PHPUnit_Framework_TestCase 
{

    private $_save_env;
    
    /**
     * @var AkRequest
     */
    private $Request;
    
    function setUp()
    {
        $this->_save_env = $_SERVER;
    }
    
    function tearDown()
    {
        $_SERVER = $this->_save_env;
    }
    
    function testOurMockReturnsCorrectMessageBody()
    {
        $data = '<person><name>Steve</name></person>';
        $Request = $this->createPutRequest($data);
            
        $this->assertEquals($data,$Request->getMessageBody());
    }
    
    function testGetContentType()
    {
        $Request = $this->createPutRequest('');
        $this->assertEquals('text/xml',$Request->getContentType());
        
    }
    
    function testEmptyMessageReturnsEmptyArray()
    {
        $data = '';
        $Request = $this->createPutRequest($data,'unknown/format');
        
        $this->assertEquals(array(),$Request->getPutParams());
    }
    
    function _testParamsFromPut()
    {
        $data = '<person><name>Steve</name></person>';
        $Request = $this->createPutRequest($data,'unknown/format');
        
        $this->assertEquals(array('put_body'=>$data),$Request->getPutParams());
    }
    


/* = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  = = =  */
    
    /**
     * @return AkRequest
     */
    function createPutRequest($data,$content_type = 'text/xml')
    {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['CONTENT_TYPE']   = $content_type;
        
        $Request = $this->getMock('AkRequest',array('getMessageBody'));
        $Request->expects($this->any())
                ->method('getMessageBody')
                ->will($this->returnValue($data));
                
        return $this->Request = $Request;
    }
}

?>