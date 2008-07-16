<?php
require_once 'Route_TestCase.php';

class UrlTest extends Route_TestCase
{

    function testUrlizeReturnsHttpQuery()
    {
        $url = $this->createUrl('/author/martin');
        $url->setRewriteEnabled(false);
        
        $this->assertEquals('/?ak=/author/martin',$url->path());
    }
    
    function testUrlizeAppendsAdditionalParametersWithAnAmpersand()
    {
        $url = $this->createUrl('/author/martin','age=23');
        $url->setRewriteEnabled(false);

        $this->assertEquals('/?ak=/author/martin&age=23',$url->path());
    }
    
    function testTrailingSlash()
    {
        $url = $this->createUrl('/author/martin');
        $url->setOptions(array('trailing_slash'=>true));
        
        $this->assertEquals('/author/martin/',$url->path());
    }
    
    function testTrailingSlashWithQueryString()
    {
        $url = $this->createUrl('/author/martin','age=23');
        $url->setOptions(array('trailing_slash'=>true));
        
        $this->assertEquals('/author/martin/?age=23',$url->path());
    }
    
    function testAddAnchor()
    {
        $url = $this->createUrl('/author/martin');
        $url->setOptions(array('anchor'=>'field'));
        
        $this->assertEquals('/author/martin#field',$url->path());
    }
    
    function testRelativeUrlPart()
    {
        $Request = $this->getMock('AkRequest',array('getRelativeUrlRoot'));
        $Request->expects($this->any())
                ->method('getRelativeUrlRoot')
                ->will($this->returnValue('/subfolder'));
                
        $url = new AkUrl('/author/martin');
        $url->setOptions(array('skip_relative_url_root'=>false,'relative_url_root'=>'/subfolder'));
            
        $this->assertEquals('/subfolder/author/martin',$url->path());
    }
    
    function testUrl()
    {
        $url = $this->createUrl('/author');
        
        $this->assertEquals('http://localhost/author',$url->url());
    }
    
    function testToStringMethodDecidesIfOnlyThePathWillBeReturned()
    {
        $url = $this->createUrl('/author');
        $this->assertEquals('http://localhost/author',"$url");

        $url->setOptions(array('only_path'=>true));
        $this->assertEquals('/author',"$url");
    }
    
    /**
     * @return AkUrl
     */
    function createUrl($path,$query='')
    {
        $Request = $this->getMock('AkRequest',array('getRelativeUrlRoot','getProtocol','getHostWithPort'));
        $Request->expects($this->any())
                ->method('getRelativeUrlRoot')
                ->will($this->returnValue(''));
        $Request->expects($this->any())
                ->method('getProtocol')
                ->will($this->returnValue('http'));
        $Request->expects($this->any())
                ->method('getHostWithPort')
                ->will($this->returnValue('localhost'));
        $url = new AkUrl($path,$query);
        $url->setOptions(array('relative_url_root'=>'','protocol'=>'http','host'=>'localhost'));
        
        return $this->Url = $url;
    }
    
}

?>