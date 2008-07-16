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
    


/* = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  = = =  */
    
    /**
     * @return AkRequest
     */
    function createPutRequest($data)
    {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['CONTENT_TYPE']   = 'text/xml';
        
        $Request = $this->getMock('AkRequest',array('getMessageBody'));
        $Request->expects($this->any())
                ->method('getMessageBody')
                ->will($this->returnValue($data));
                
        return $this->Request = $Request;
    }
}

?>