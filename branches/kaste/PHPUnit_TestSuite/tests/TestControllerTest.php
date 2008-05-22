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
    
    function testExpectsDefaultRender()
    {
        $this->expectDefaultRender();
        $this->get('index');
    }
    
    function testExpectsExplicitRender()
    {
        $this->expectRender(array('text'=>'Hello world.'));
        $this->get('weRenderText');   
    }
    
    function testAssertsAssignedVariable()
    {
        $this->get('weAssignAVariable');
        $this->assertAssign('AssignedVariable','Hello world.');
    }
    
    function testExpectsFilterInTheChain()
    {
        $this->expectFilterCalled('before_my_action');
        $this->get('weAreFiltered');
    }
    
    function testExpectsFilterNotInTheChain()
    {
        $this->expectFilterNotCalled('before_my_action');
        $this->get('weRenderText');
    }
    
    function testExpectsActionNotCalled()
    {
        $this->expectActionNotCalled();
        $this->get('weNeverReach');
    }
    
    function testExpectsRedirect()
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