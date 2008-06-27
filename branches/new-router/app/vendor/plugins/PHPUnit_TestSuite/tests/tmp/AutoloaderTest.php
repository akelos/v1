<?php

class AutoloaderOne
{
    var $name;
    
    function __construct($name)
    {
        $this->name = $name;    
    }
    
    function autoload($classname)
    {
        self::dump();
        var_dump($classname);
    }
    
    function dump()
    {
        var_dump($this->name);    
        
    }
}

class AutoloaderTwo extends AutoloaderOne 
{
    function dump()
    {
        var_dump('two');
    }
}

class AutoloaderTest extends PHPUnit_Framework_TestCase 
{
    function testOneAutoloader()
    {
        $First = new AutoloaderOne('First');
        $Second = new AutoloaderOne('Second');
        spl_autoload_register(array($Second,'autoload'));
        spl_autoload_register(array($First,'autoload'));
        spl_autoload_register(array($First,'autoload'));
        
        new Unknown();
    }
    
    function _testStaticAutoloader()
    {
        spl_autoload_register(array('AutoloaderOne','autoload'));
        spl_autoload_register(array('AutoloaderTwo','autoload'));

        new Unknown();
    }
}

?>