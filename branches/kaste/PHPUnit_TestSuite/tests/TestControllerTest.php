<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php';

class TestControllerTest extends PHPUnit_Controller_TestCase 
{
    function setUp()
    {
        $this->useController('Test');
    }
    
    function testUseController()
    {
        $this->assertEquals('Test',$this->controller_name);
    }
    
    function testMockedControllerName()
    {
        $this->get('index');
        $this->assertEquals('Test',$this->Controller->getControllerName());
    }
    
    function testExpectedView()
    {
        $this->expectDefaultRender();
        $this->get('index');
    }
    
    function testExpectedRenderedText()
    {
        $this->expectRender(array('text'=>'Hello world.'));
        $this->get('weRenderText');   
    }
    
    function testForAssignedVariable()
    {
        $this->get('weAssignAVariable');
        $this->assertAssign('AssignedVariable','Hello world.');
    }
    
    function testExpectedFilter()
    {
        $this->expectFilterCalled('before_my_action');
        $this->get('weAreFiltered');
    }
    
    function testFilterNotCalled()
    {
        $this->expectFilterNotCalled('before_my_action');
        $this->get('weRenderText');
    }
    
    function testActionNotCalled()
    {
        $this->expectActionNotCalled();
        $this->get('weNeverReach');
    }
    
    function testRedirect()
    {
        $this->expectRedirectTo(array('action'=>'here'));
        $this->get('weRedirect');
    }
    
    function saveGlobals()
    {
        $vars= array();
        $vars['GET']      =$_GET;
        $vars['POST']     =$_POST;
        @$vars['SESSION'] =$_SESSION;
        $vars['COOKIE']   =$_COOKIE;
        return $this->___SAVED_GLOBALS = $vars;
    }
    
    function restoreGlobals()
    {
        $_GET     =$this->___SAVED_GLOBALS['GET'];
        $_POST    =$this->___SAVED_GLOBALS['POST'];
        $_SESSION =$this->___SAVED_GLOBALS['SESSION'];
        $_COOKIE  =$this->___SAVED_GLOBALS['COOKIE'];
    }
}
?>