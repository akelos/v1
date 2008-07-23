<?php
require_once AK_APP_DIR.DS.'application_controller.php';

class ControllerUrlFor extends PHPUnit_Controller_TestCase 
{

    function setUp()
    {
        $this->useController('locale_detection');    
    }
    
    function testUrlFromIndexToList()
    {
        $this->get('index');
        $controller = $this->Controller;
        
        $this->assertEquals('/locale_detection/list',$controller->urlFor(array('action'=>'list','only_path'=>true)));
        $this->assertEquals('http://localhost/locale_detection/list',$controller->urlFor(array('action'=>'list')));
    }
    
    function testUrlFromSessionWithIdToList()
    {
        $this->get('session',array('id'=>'1234'));
        
        $this->assertEquals('/locale_detection/list',$this->Controller->urlFor(array('action'=>'list','only_path'=>true)));
    }
    
    function _testUrlFromSessionWithIdToAnotherId()
    {
        $this->markTestIncomplete('Not implemented.');
        $this->get('session',array('id'=>'123'));
        
        $this->assertEquals('/locale_detection/session/345',$this->Controller->urlFor(array('id'=>'345','only_path'=>true)));
    }
    
    
    
    /* = = = = = = = = Test API = = = = = = = = */
    
    function getMethodsToMockForController()
    {
        $methods = parent::getMethodsToMockForController();
        $methods[] = 'getUrlWriter';
        return $methods;
    }
    
    function setExpectations()
    {
        $this->Controller->expects($this->any())
                         ->method('getUrlWriter')
                         ->will($this->returnValue($this->createUrlWriter()));

        parent::setExpectations();
    }
    
    function createUrlWriter()
    {
        $UrlWriter = new AkUrlWriter($this->Request,$this->createRouter());
        return $UrlWriter;    
    }
    
    function createRouter()
    {
        $Router = new AkRouter();
        $Router->addRoute('default',new AkRoute('/:controller/:action/:id'));
        return $Router;
    }
    
    
}

?>