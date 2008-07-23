<?php
require_once AK_LIB_DIR.DS.'AkRouter.php';
require_once AK_LIB_DIR.DS.'AkRouter'.DS.'AkUrlWriter.php';

/* The helper generates code like this: 
 * 
function author_url($params=array())
{
    $url_writer = AkUrlWriter::getInstance();
    $my_params = array('use_named_route'=>'author','skip_old_parameters_except'=>array('name'));
    $params = array_merge($my_params,$params);
    return $url_writer->urlFor($params);    
}
*/

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