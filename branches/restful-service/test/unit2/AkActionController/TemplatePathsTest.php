<?php

class TemplatePathsTests extends PHPUnit_Framework_TestCase 
{

    function setUp()
    {
        
    }
    
    function tearDown()
    {
        $this->cleanUp();
    }
    
    function testSettingLayoutToFalseMeansYouDontWantALayout() 
    {
        $this->createViewFor('index');
        $this->createTemplate('layouts/application.tpl');
        $controller = $this->createControllerFor('index');
        $controller->layout = false;
        
        $this->expectRender(array('index'));
        $controller->defaultRender();
    }
    
    function testPickApplicationLayoutIfWeDontHaveAControllerLayout()
    {
        $this->createViewFor('index');
        $this->createTemplate('layouts/application.tpl');
        $controller = $this->createControllerFor('index');
        
        $this->expectRender(array('index',AK_VIEWS_DIR.DS.'layouts/application.tpl'));
        $controller->defaultRender();
    }
    
    function testDontPickAnyLayoutIfNoneIsPresent()
    {
        $this->createViewFor('index');
        $controller = $this->createControllerFor('index');
        
        $this->expectRender(array('index'));
        $controller->defaultRender();
    }
    
    function testPickControllerLayoutIfPresent()
    {
        $this->createViewFor('index');
        $this->createTemplate('layouts/template_paths.tpl');
        $controller = $this->createControllerFor('index');
        
        $this->expectRender(array('index',AK_VIEWS_DIR.DS.'layouts/template_paths.tpl'));
        $controller->defaultRender();
    }
    
    function testPickExplicitlySetLayout()
    {
        $this->createViewFor('index');
        $this->createTemplate('render_tests/my_layout.tpl');
        $controller = $this->createControllerFor('index');
        $controller->setLayout('render_tests/my_layout');
        
        $this->expectRender(array('index',AK_VIEWS_DIR.DS.'render_tests/my_layout.tpl'));
        $controller->defaultRender();
    }
    
    /* - - - - -  Test Helper */
    
    private $created_files = array();
    
    /**
     * @var ApplicationController
     */
    private $Controller;
    
    /**
     * @var AkActionView
     */
    private $Template;
    private $action_name;
    
    private $controller_name = 'template_paths';
    
    function expectRender($arg_list)
    {
        $method = 'renderFile';
        
        $this->Template->expects($this->exactly(count($arg_list)))->method($method);
        foreach ($arg_list as $i=>$args){
            $method_invoker = $this->Template->expects($this->at($i))->method($method);
            $args           = is_array($args) ? $args : array($args);
            $args[0] = str_replace('/',DS,$args[0]); // @args[0] we must have a string, the template_path
            call_user_func_array(array($method_invoker,'with'),$args);
        }
    }
    
    function assertLayout($expected,$actual=null)
    {
        $expected = str_replace('/',DS,$expected);
        if (!$actual){
            $actual = $this->Controller->_pickLayout(false,$this->action_name,null);
        }
        $this->assertEquals(AK_VIEWS_DIR.DS.$expected,$actual);
    }
    
    function assertNoLayout()
    {
        $this->assertFalse($this->Controller->_pickLayout(false,$this->action_name,null));
    }
    
    /**
     * @return TemplatePathsController
     */
    function createControllerFor($action_name,$mime_type='html')
    {
        $controller_class_name = AkInflector::camelize($this->controller_name).'Controller';
        $controller = new $controller_class_name();
        
        $Request = $this->createGetRequest($action_name,$mime_type);
        $Response = $this->getMock('AkResponse',array('outputResults'));
        $controller->setRequestAndResponse($Request,$Response);
        $this->Template = $controller->Template = $this->getMock('AkActionView',array('renderFile'),array(AK_VIEWS_DIR.DS.$this->controller_name));
        
        $this->action_name = $action_name;
        return $this->Controller = $controller;
    }
    
    function createViewFor($action_name)
    {
        $view_for_action = $this->controller_name.DS.$action_name.'.tpl';
        $this->createTemplate($view_for_action);
    }
    
    function createTemplate($file_name,$content='Dummy')
    {
        $file_name = str_replace('/',DS,$file_name);
        $file_name = AK_VIEWS_DIR.DS.$file_name;
        $this->assertTrue((boolean)Ak::file_put_contents($file_name,$content));
        $this->created_files[] = $file_name;
    }
    
    function cleanUp()
    {
        foreach ($this->created_files as $file_name){
            $this->assertTrue(Ak::file_delete($file_name));
        }
    }
    
    function createGetRequest($action_name,$format)
    {
        $request_method = 'get';
        $controller_name = 'template_paths';
        
        $Request = $this->getMock('AkRequest',array('getMethod','getFormat','getAction','getController','getParams'),array(),'',false);
        $Request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($request_method));
        $Request->expects($this->any())
                ->method('getFormat')
                ->will($this->returnValue($format));
        $Request->expects($this->any())
                ->method('getAction')
                ->will($this->returnValue($action_name));
        $Request->expects($this->any())
                ->method('getController')
                ->will($this->returnValue($controller_name));
        $Request->expects($this->any())
                ->method('getParams')
                ->will($this->returnValue(array('controller'=>$controller_name,'action'=>$action_name)));
                
        return $Request;
    }
    
}

?>