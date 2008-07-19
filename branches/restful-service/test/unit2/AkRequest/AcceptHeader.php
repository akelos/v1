<?php

class AcceptHeader extends PHPUnit_Framework_TestCase 
{

    private $_save_env;
    
    /**
     * @var AkRequest
     */
    private $Request;
    
    function setUp()
    {
        $this->_save_env = $_SERVER;
        $_SERVER['REQUEST_METHOD'] = 'get';
        $this->Request = new AkRequest();
    }
    
    function tearDown()
    {
        $_SERVER = $this->_save_env;
    }
    
    function testAssumeQOfOneIfNoneIsPresent()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/html';
        $this->assertEquals(array('type'=>'text/html','q'=>'1.0'),array_pop($this->Request->getAcceptHeader()));
    }
    
    function testPreserveOriginalOrderIfQIsEqual()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/html, application/html';
        $accepts = $this->Request->getAcceptHeader();
        array_walk($accepts,array('self','only_type'));
        
        $this->assertEquals(array('text/html','application/html'),$accepts);
    }
    
    function testReorderAcceptHeaders()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/html, application/xml;q=0.9, application/xhtml+xml, */*;q=0.1';
        $accepts = $this->Request->getAcceptHeader();
        array_walk($accepts,array('self','only_type'));
        
        $this->assertEquals(array('text/html','application/xhtml+xml', 'application/xml', '*/*'),$accepts);            
    }
    
    function testDeliverHtmlToOpera()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1';
        $this->assertEquals('html',$this->Request->getFormat());
    }
    
    function testDeliverHtmlToInternetExplorerOnFirstRequest()
    {
        //Internet Explorer doesnt prefer anything over anything.
        $this->Request->env['HTTP_ACCEPT'] = '*/*';
        $this->assertEquals('html',$this->Request->getFormat());
    }

    function testDeliverHtmlToInternetExplorerOnSubsequentRequests()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, */*';
        $this->assertEquals('html',$this->Request->getFormat());
    }
    
    function testDeliverHtmlToFirefox2()
    {
        //Firefox prefers xml over html
        $this->Request->env['HTTP_ACCEPT'] = 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
        $this->assertEquals('html',$this->Request->getFormat());
    }
    
    function testExplicitlyRequestedFormatOverrulesAnyAcceptHeader()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/xml';
        $this->Request->_request['format'] = 'html';
        
        $this->assertEquals('html',$this->Request->getFormat());
    }

    function testContentTypeGetsParsed()
    {
        $this->Request->env['CONTENT_TYPE'] = 'text/xml;charset=utf-8';
        $this->assertEquals('text/xml',$this->Request->getContentType());
    }
    
    function testGetFormatReturnsContentTypeOnPostRequests()
    {
        $this->Request->env['REQUEST_METHOD'] = 'post';
        $this->Request->env['CONTENT_TYPE'] = 'text/xml;charset=utf-8';
        
        $this->assertEquals('xml',$this->Request->getFormat());
    }
    
    function testGetFormatReturnsContentTypeOnPutRequests()
    {
        $this->Request->env['REQUEST_METHOD'] = 'put';
        $this->Request->env['CONTENT_TYPE'] = 'text/xml;charset=utf-8';
        
        $this->assertEquals('xml',$this->Request->getFormat());
    }
    
    /* ============= ============== =========== */
    
    private static function only_type(&$a)
    {
        $a = $a['type'];
    }
    
}

?>