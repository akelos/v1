<?php

class AcceptHeader extends PHPUnit_Framework_TestCase 
{

    private $_save_env;
    
    /**
     * @var AkRequest
     */
    private $Request;
    
    const typical_opera_header = 'text/html, application/xml;q=0.9, application/xhtml+xml, image/png, image/jpeg, image/gif, image/x-xbitmap, */*;q=0.1'; 
    
    function setUp()
    {
        $this->Request = new AkRequest();
        
        $this->_save_env = $this->Request->env;
        
    }
    
    function tearDown()
    {
        $this->Request->env = $this->_save_env;
    }
    
    function testReorderAcceptHeaders()
    {
        $this->Request->env['HTTP_ACCEPT'] = self::typical_opera_header;
        $acceptables = $this->Request->getAcceptHeader();
        
        $actual = array();
        foreach ($acceptables as $acceptable){
            $actual[] = $acceptable['type'];
        }
        
        $this->assertEquals(array('text/html','application/xhtml+xml', 'image/png', 'image/jpeg', 'image/gif', 'image/x-xbitmap', 'application/xml', '*/*'),$actual);            
    }
    
    function testDeliverHtmlToOpera()
    {
        $this->Request->env['HTTP_ACCEPT'] = self::typical_opera_header;
        $mime_type = $this->Request->getMimeType($this->Request->getAcceptHeader());

        $this->assertEquals('html',$mime_type);
    }
    
    function testDeliverHtmlToInternetExplorerOnFirstRequest()
    {
        //Internet Explorer doesnt prefer anything over anything.
        $this->Request->env['HTTP_ACCEPT'] = '*/*';
        $mime_type = $this->Request->getMimeType($this->Request->getAcceptHeader());
        $this->assertEquals('html',$mime_type);
    }

    function testDeliverHtmlToInternetExplorerOnSubsequentRequests()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'image/gif, image/x-xbitmap, image/jpeg, image/pjpeg, application/x-shockwave-flash, */*';
        $mime_type = $this->Request->getMimeType($this->Request->getAcceptHeader());
        $this->assertEquals('html',$mime_type);
    }
    
    function testDeliverHtmlToFirefox2()
    {
        $this->Request->env['HTTP_ACCEPT'] = 'text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5';
        $mime_type = $this->Request->getMimeType($this->Request->getAcceptHeader());
        $this->assertEquals('html',$mime_type);
    }
}

?>