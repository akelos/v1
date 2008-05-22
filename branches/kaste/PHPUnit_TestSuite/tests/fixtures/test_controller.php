<?php
require_once AK_APP_DIR.DS.'application_controller.php';

class TestController extends ApplicationController 
{
    
    function __construct()
    {
        $this->beforeFilter(array('before_my_action' => array('only'=>'weAreFiltered')));
        $this->beforeFilter(array('blocker'          => array('only'=>'weNeverReach')));
        #$this->beforeFilter('blocker',array('only'=>'weNeverReach'));
    }
    
    function index()
    {
    }
    
    function before_my_action()
    {
        
    }
    
    function blocker()
    {
        $this->redirectTo(array('action'=>'somewhere'));
    }
    
    function weAssignAVariable()
    {
        $this->AssignedVariable = "Hello world.";
    }
    
    function weRenderText()
    {
        $this->render(array('text'=>'Hello world.'));
    }
    
    function weAreFiltered()
    {
        $this->render(array('text'=>'Inside weAreFiltered.'));   
    }
    
    function weNeverReach()
    {
        $this->render(array('text'=>'Never reached.'));   
    }
    
    function weRedirect()
    {
        #$this->redirectToAction('here');
        $this->redirectTo(array('action'=>'here'));
    }
    
    function weHaveANotice()
    {
        $this->flash['notice'] = 'You\'ve got mail';
        $this->flash_now['notice'] = 'Wanna read?';
    }
}
?>