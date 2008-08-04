<?php
require_once 'TemplatePicking_TestCase.php';

class TemplatePathsTests extends TemplatePicking_TestCase 
{

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
    
}

?>