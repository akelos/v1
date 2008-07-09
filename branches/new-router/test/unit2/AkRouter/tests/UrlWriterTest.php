<?php
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

class UrlWriterTest extends PHPUnit_Framework_TestCase
{

    function testInstantiateUrlWriter()
    {
        $Request = $this->createRequest(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $UrlWriter = new AkUrlWriter($Request,$this->createRouter());
        
        
    }
    
    function createRequest($params)
    {
        $Request = $this->getMock('AkRequest',array('getParameters'));
        $Request->expects($this->any())
                ->method('getParameters')
                ->will($this->returnValue($params));
        
        return $this->Request = $Request;
    }
    
    function createRouter()
    {
        return $this->Router = new AkRouter();
    }
    
    
}

?>