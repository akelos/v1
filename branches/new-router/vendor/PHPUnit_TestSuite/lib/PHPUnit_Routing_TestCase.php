<?php

/**
 * @method assertParameter($expected)    f.i. assertController('blog') || assertColor('blue') 
 */
class PHPUnit_Routing_TestCase extends PHPUnit_Framework_TestCase 
{

    /**
     * @var AkRouter
     */
    var $Router;
    
    private $resolvedRequest;
    private $used_map;

    function setUp()
    {
        $this->useDefaultMap();
    }
    
    function tearDown()
    {
        #unset ($this->Router,$this->resolvedRequest,$this->used_map);
    }
    
    function useDefaultMap()
    {
        $this->useMap(AK_ROUTES_MAPPING_FILE);
    }
    
    function useMap($map_file)
    {
        if (!is_file($map_file)) throw new Exception("Could not find $map_file"); 
        $this->used_map = $map_file;
        
        $this->instantiateRouter($map_file);
        #$routes_file_in_test_dir = trim(AK_APP_DIR,strrchr(AK_APP_DIR,DS)).DS.'config'.DS.'routes.php';
        #$default_routes_file = AK_ROUTES_MAPPING_FILE;
    }
    
    private function instantiateRouter($map_file)
    {
        $Map = new AkRouter();
        include $map_file;
        $this->Router = $Map;
    }
    
    function get($url)
    {
        $this->resolvedRequest = $this->Router->toParams($url);
    }
    
    function assert404()
    {
        if ($this->resolvedRequest) $this->fail("Expected a 404, actually got a match.");
    }
    
    function ensureRequestIsSolved()
    {
        if (!$this->resolvedRequest) $this->fail("Expected a match, actually got a 404.");
    }
    
    function assertParameterEquals($expected,$param_name)
    {
        $this->ensureRequestIsSolved();
        $this->ensureParameterIsSet($param_name);
        $this->assertEquals($expected,$this->resolvedRequest[$param_name],"Expected $param_name to be <$expected>, actually is <{$this->resolvedRequest[$param_name]}>.");
    }
    
    function ensureParameterIsSet($param_name)
    {
        $this->assertArrayHasKey($param_name,$this->resolvedRequest,"Router did not set the parameter $param_name.");
    }
    
    function assertParameterNotSet($param_name)
    {
        // we can't use assertArrayNotHasKey because sometimes a parameter is set to an empty string  
        // $this->assertArrayNotHasKey($param_name,$this->resolvedRequest);
        
        if (isset($this->resolvedRequest[$param_name]) && !empty($this->resolvedRequest[$param_name])){
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