<?php

/**
 * @method assertParameter($expected)    f.i. assertController('blog') || assertColor('blue') 
 */
abstract class PHPUnit_Routing_TestCase extends PHPUnit_Framework_TestCase 
{

    /**
     * @var AkRouter
     */
    var $Router;
    
    private $params;

    function setUp()
    {
        $this->useDefaultMap();
    }
    
    function useDefaultMap()
    {
        $this->useMap(AK_ROUTES_MAPPING_FILE);
    }
    
    function useMap($map_file)
    {
        if (!is_file($map_file)) throw new Exception("Could not find $map_file"); 
        
        $Map = $this->instantiateRouter();
        include $map_file;
        #$routes_file_in_test_dir = trim(AK_APP_DIR,strrchr(AK_APP_DIR,DS)).DS.'config'.DS.'routes.php';
        #$default_routes_file = AK_ROUTES_MAPPING_FILE;
    }
    
    function instantiateRouter()
    {
        return $this->Router = new AkRouter();
    }
    
    function get($url)
    {
        $this->params = $this->Router->toParams($url);
    }
    
    function assert404()
    {
        $this->ensureNoMatch();
    }
    
    private function ensureNoMatch()
    {
        if ($this->params) $this->fail("Expected no match, actually got a match.");
    }
    
    private function ensureMatch()
    {
        if (!$this->params) $this->fail("Expected a match, actually got no match.");
    }
    
    function assertParameterEquals($expected,$param_name)
    {
        $this->ensureMatch();
        $this->ensureParameterIsSet($param_name);
        $this->assertEquals($expected,$this->params[$param_name],"Expected $param_name to be <$expected>, actually is <{$this->params[$param_name]}>.");
    }
    
    private function ensureParameterIsSet($param_name)
    {
        $this->assertArrayHasKey($param_name,$this->params,"Router did not set the parameter $param_name.");
    }
    
    function assertParameterNotSet($param_name)
    {
        // we can't use assertArrayNotHasKey because sometimes a parameter is set to an empty string  
        // $this->assertArrayNotHasKey($param_name,$this->params);
        if (isset($this->params[$param_name]) && !empty($this->params[$param_name])){
            $this->fail("Parameter $param_name was set, but was not expected.");
        }
    }
    
    // though __call would match these, we define some basic assertions for type-h(i|u)nting IDE's  
    function assertController($controller_name)
    {
        $this->assertParameterEquals($controller_name,'controller');
    }
    
    function assertAction($action_name)
    {
        $this->assertParameterEquals($action_name,'action');
    }
    
    function assertId($id)
    {
        $this->assertParameterEquals($id,'id');
    }
    
    function __call($method_name,$args)
    {
        if (preg_match('/^assert(.*)$/',$method_name,$matches)){
            $param = strtolower($matches[1]);
            return $this->assertParameterEquals($args[0],$param);
        }
        throw new BadMethodCallException("Call to unknown method <$method_name> in ".__CLASS__.".");
    }
    
}

?>