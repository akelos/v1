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
    
    function testUnknownContentTypeThrowsNotAcceptableException()
    {
        $this->setExpectedException('NotAcceptableException');
        $data = '<person><name>Steve</name></person>';
        $Request = $this->createPutRequest($data,'unknown/format');
        $Request->getPutParams();
    }
    
    function testEmptyMessageReturnsEmptyArray()
    {
        $data = '';
        $Request = $this->createPutRequest($data);
        
        $this->assertEquals(array(),$Request->getPutParams());
    }
    
    function testXmlIsAutomaticallyMergedIntoParams()
    {
        $data = '<person><name>Steve</name></person>';
        $Request = $this->createPutRequest($data,'text/xml');

        $this->assertEquals(array('person'=>array('name'=>'Steve')),$Request->getPutParams());
    }
    
    function testJsonIsAutomaticallyMergedIntoParams()
    {
        $data = '{"person":{"name":"Steve"}}';
        $Request = $this->createPutRequest($data,'text/x-json');
        
        $this->assertEquals(array('person'=>array('name'=>'Steve')),$Request->getPutParams());
    }
    
    function testWwwFormIsAutomaticallyMergedIntoParams()
    {
        $data = 'person%5Bname%5D=Steve';
        $Request = $this->createPutRequest($data,'application/x-www-form-urlencoded');
        
        $this->assertEquals(array('person'=>array('name'=>'Steve')),$Request->getPutParams());
    }
    


/* = = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =  = = =  */
    
    /**
     * @return AkRequest
     */
    function createPutRequest($data,$content_type = 'text/xml')
    {
        $_SERVER['REQUEST_METHOD'] = 'put';
        $_SERVER['CONTENT_TYPE']   = $content_type;
        
        $Request = $this->getMock('AkRequest',array('getMessageBody'),array(),'',false);
        $Request->expects($this->any())
                ->method('getMessageBody')
                ->will($this->returnValue($data));
        $Request->env = $_SERVER;
                
        return $this->Request = $Request;
    }
}

?>