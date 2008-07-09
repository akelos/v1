<?php
require_once AK_APP_DIR.DS.'application_controller.php';

class ControllerUrlFor extends PHPUnit_Controller_TestCase 
{

    function setUp()
    {
        $this->useController('LocaleDetection');    
    }
    
    function testUrlFromIndexToList()
    {
        $this->get('index');
        $controller = $this->Controller;
        
        $this->assertEquals('/locale_detection/list',$controller->urlFor(array('action'=>'list','only_path'=>true)));
        $this->assertEquals('http://localhost/locale_detection/list',$controller->urlFor(array('action'=>'list')));
    }
}

?>