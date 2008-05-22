<?php

class PHPUnit_Controller_TestCase extends PHPUnit_Framework_TestCase 
{
    var $controller_name;
    
    /**
     * @var AkActionController
     */
    var $Controller;
    
    var $Request;
    
    /**
     * @var AkResponse
     */
    var $Response;
    
    function useController($controller)
    {
        $this->controller_name = $controller;
    }
    
    function get($action,$options=array())
    {
        $this->action_name = $action;
        $this->addExpectationsDependendOnActionName($action);
        
        $Request = $this->createRequest('get',$action,$options);
        $Response = $this->createResponse();
        $Controller = $this->createController($this->controller_name);

        $this->setExpectations();
        
        $Controller->process($this->Request,$this->Response);
    }
    
    function post($action,$options=array())
    {
        return $this->createRequest('post',$action,$options);
    }
    
    function createRequest($method,$action,$options)
    {
        $params = array_merge(array('controller'=>$this->controller_name,'action'=>$action),$options);
        return $this->Request = AkTestRequest::createInstance($method,$params);
    }
    
    function createResponse()
    {
        return $this->Response = $this->getMock('AkResponse');
    }
    
    function createController($controller_name)
    {
        $controller_class_name = AkInflector::camelize($controller_name).'Controller';
        $this->Controller = $this->getMock($controller_class_name,$this->getMethodsToMockForController());
        #$this->Controller->Template = $this->getMock('AkActionView');
        return $this->Controller;
    }
    
    function addExpectationsDependendOnActionName($action_name)
    {
        if ($this->expectDefaultRender){
            $this->expectedMethods['renderWithALayout'] = array($action_name);                                     
            #$this->expectedMethods['render'][] = array($action_name,null);                                     
        }
        if ($this->expectActionNotCalled){
            $this->unexpectedMethods[] = $action_name;                                     
        }
    }

    function setExpectations()
    {
        $this->Controller->expects($this->any())
                         ->method('getControllerName')
                         ->will($this->returnValue($this->controller_name));
                         
        foreach ($this->expectedMethods as $method=>$arguments){
            $this->Controller->expects($this->exactly(1))->method($method);
            call_user_func_array(
                array($this->Controller->expects($this->once())->method($method),'with'),
                $arguments
            );
        }
        
        foreach ($this->unexpectedMethods as $method){
            $this->Controller->expects($this->never())->method($method);
        }
        
        $this->clearExpectations();
    }
    
    function clearExpectations()
    {
        unset($this->expectDefaultRender,$this->expectedMethods,$this->unexpectedMethods);
        
    }
    
    private $expectDefaultRender;
    private $expectActionNotCalled;
    private $expectedMethods   = array();
    private $unexpectedMethods = array();
    private $action_name;
    
    /**
     * @return PHPUnit_Controller_TestCase
     */
    function expectDefaultRender()
    {
        $this->expectDefaultRender = true;
        return $this;
    }
    
    /**
     * accepts same arguments as AkActionController->render()
     *
     * @return PHPUnit_Controller_TestCase
     */
    function expectRender($options,$status=null)
    {
        $this->expectedMethods['render'] = func_get_args();
        # since we mock the actual render, performed? is still false, so the defaultRender triggers
        #$this->setPerformedToTrue();
        $this->expectDefaultRender();
        return $this;
    }
    
    function setPerformedToTrue()
    {
        $this->Controller->expects($this->any())->method('_hasPerformed')->will($this->returnValue(true));
    }
    
    function expectFilterCalled($filter_name)
    {
        $this->expectedMethods[$filter_name] = array();
        return $this;
    }
    
    function expectFilterNotCalled($filter_name)
    {
        $this->unexpectedMethods[] = $filter_name;
        return $this;
    }
    
    function expectActionNotCalled()
    {
        $this->expectActionNotCalled = true;
        return $this;
    }
    
    function expectRedirectTo($options)
    {
        $this->expectedMethods['redirectTo'] = array($options);
        $this->expectDefaultRender();
        return $this;
    }
    
    function assertAssign($variable_name,$expected)
    {
        if (!isset($this->Controller->$variable_name)) $this->fail("Variable <$variable_name> not assigned.");
        $this->assertEquals($expected,$this->Controller->$variable_name);
    }

    function getMethodsToMockForController()
    {
        $default_methods = array('_assertExistanceOfTemplateFile','_addInstanceVariablesToAssigns','getControllerName');
        $default_methods = array('render','getControllerName');
        $methods = array_merge (array_keys($this->expectedMethods),$this->unexpectedMethods,$default_methods);
        return array_unique($methods);
    }
    
}


?>