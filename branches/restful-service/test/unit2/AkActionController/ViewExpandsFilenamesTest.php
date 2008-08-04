<?php

class ViewExpandsFilenamesTest extends TemplatePicking_TestCase  
{

    function setUp()
    {
        
    }
    
    function testViewAcceptsOtherFormats()
    {
        $View = $this->createView();
        $this->createViewTemplate('index.xml');

        $this->expectRender('tpl','index.xml.tpl');
        $View->renderFile('index.xml',true,array());
    }
    
    function testViewsHandlesHtmlExtension()
    {
        $View = $this->createView();
        $this->createViewTemplate('index.html');

        $this->expectRender('html.tpl','index.html.tpl');
        $View->renderFile('index.html',true,array());
    }

    function testViewShouldAddHtmlExtensionIfAnHtmlTemplateExists()
    {
        $View = $this->createView();
        $this->createViewTemplate('index.html');

        $this->expectRender('html.tpl','index.html.tpl');
        $View->renderFile('index',true,array());
    }
    
    function testForCompatibilityViewDoesntAddHtmlExtensionIfTemplateWouldNotExist()
    {
        $View = $this->createView();
        $this->createViewTemplate('index');

        $this->expectRender('tpl','index.tpl');
        $View->renderFile('index',true,array());
    }
    
    function testForCompatibilityViewRemovesHtmlExtensionIfNecessary()
    {
        $View = $this->createView();
        $this->createViewTemplate('index');

        $this->expectRender('tpl','index.tpl');
        $View->renderFile('index.html',true,array());
    }
    
    /* - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - */
    
    /**
     * @return AkActionView
     */
    function createView()
    {
        $View = $this->getMock('AkActionView',array('renderTemplate'),array(AK_VIEWS_DIR.DS.$this->controller_name));
        $View->_registerTemplateHandler('tpl','AkPhpTemplateHandler');
        $View->_registerTemplateHandler('html.tpl','AkPhpTemplateHandler');
        return $this->Template = $View;
    }

    function expectRender($handler_extension,$view_file)
    {
        $file_name = AK_VIEWS_DIR.DS.$this->controller_name.DS.$view_file;
        $this->Template->expects($this->once())
                       ->method('renderTemplate')
                       ->with($handler_extension,null,$file_name);
    }
    
}

?>