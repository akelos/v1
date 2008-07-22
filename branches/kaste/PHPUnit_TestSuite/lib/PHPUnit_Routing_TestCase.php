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
    
    protected $params;
    private   $reciprocity = false;
    private   $errors = false;

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

    function connect($url_pattern, $options = array(), $requirements = array())
    {
        $this->Router->connect($url_pattern, $options, $requirements);
    }
    
    function testReciprocity($bool = true)
    {
        return $this->reciprocity = $bool;
    }
    
    /**
     * @param string $url
     * @return PHPUnit_Routing_TestCase
     */
    function get($url)
    {
        $Request = $this->createRequest($url);
        try {
            $this->params = $this->Router->match($Request);
        }catch (NoMatchingRouteException $e){
            $this->errors = true;
        }
        #$this->params = $this->Router->match($url);
        if ($this->reciprocity && $this->params){
            $this->assertEquals($this->encloseWithSlashes($url), $this->Router->toUrl($this->params));
        }
        return $this;
    }
    
    private function createRequest($url,$method='get')
    {
        $Request = $this->getMock('AkRequest',array('getMethod','getRequestedUrl'),array(),'',false);
        $Request->expects($this->any())
                ->method('getMethod')
                ->will($this->returnValue($method));
        $Request->expects($this->any())
                ->method('getRequestedUrl')
                ->will($this->returnValue($url));
                
        return $Request;
    }
    
    private function encloseWithSlashes($string)
    {
        return $string == '/' || $string == '' ? '/' : '/'.trim($string,'/').'/';
    }
    
    /**
     * ->resolvesTo(array('controller'=>'blog','action'=>'index'))
     * ->resolvesTo('blog','index')
     * @param mixed a hash (arg[0]) or a list of values (args*)
     */
    function resolvesTo()
    {
        $this->ensureMatch();

        $params = func_get_args();
        if ($this->is_hash($params[0])){ // ->resolvesTo(array('controller'=>'blog','action'=>'index'));
            return $this->assertEquals($params[0],$this->params);
        }else{                           // ->resolvesTo('blog','index');
            return $this->assertSameValues($params,$this->params);
        }
    }
    
    function doesntResolve()
    {
        $this->ensureNoMatch();
    }

    function assert404()
    {
        $this->ensureNoMatch();
    }
    
    private function ensureNoMatch()
    {
        if (!$this->hasErrors()) $this->fail("Expected no match, actually got a match.");
    }
    
    private function ensureMatch()
    {
        if ($this->hasErrors()) $this->fail("Expected a match, actually got no match.");
    }
    
    private function hasErrors()
    {
        return $this->errors;
    }
    
    private function ensureParameterIsSet($param_name)
    {
        $this->assertArrayHasKey($param_name,$this->params,"Router did not set the parameter $param_name.");
    }
    
    function assertParameterEquals($expected,$param_name)
    {
        $this->ensureMatch();
        $this->ensureParameterIsSet($param_name);
        $this->assertEquals($expected,$this->params[$param_name],"Expected $param_name to be <$expected>, actually is <{$this->params[$param_name]}>.");
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

    /**
     * Compares the values of the array and the values of the hash ignoring its keys
     */
    function assertSameValues($array,$hash)
    {
        $k = 0;
        foreach ($hash as $key=>$value){
            if (!isset($array[$k])){
                return $this->fail("Parameter <$key> not expected, but in actual.");
            }
            $this->assertEquals($array[$k++],$value);
        }
        if ($k < count($array)){
            return $this->fail("Expected <{$array[$k]}>, not in actual.");
        }
        return true;        
    }

    private function is_hash($hash)
    {
        return is_array($hash) && !isset($hash[0]);
    }
    
}

?>