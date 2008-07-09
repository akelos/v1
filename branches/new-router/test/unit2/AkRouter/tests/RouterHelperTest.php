<?php
require_once AK_LIB_DIR.DS.'AkRouter.php';
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

class RouterHelperClass extends PHPUnit_Framework_TestCase
{
    
    function testGenerateHelperFunctions()
    {
        $name = 'namespaced_name';
        $Route = new AkRoute('/author/:name');

        AkRouterHelper::generateHelperFunctionsFor($name,$Route);

        $this->assertTrue(function_exists('namespaced_name_url'));
        $this->assertTrue(function_exists('namespaced_name_path'));
    }
    
}

?>