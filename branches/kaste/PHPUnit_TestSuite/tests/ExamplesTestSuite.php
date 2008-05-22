<?php

class ExamplesTestSuite extends PHPUnit_Framework_TestSuite 
{
    
    public static function suite()
    {
        $class_name  = substr(basename(__FILE__),0,-4);
        $test_folder = strtolower(substr($class_name,0,-9));
        
        $suite = new ExamplesTestSuite(AkInflector::titleize($class_name));
        $path = dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.$test_folder;
        
        $directory = new PHPUnit_Runner_IncludePathTestCollector(array($path));
        $suite->addTestFiles($directory->collectTests());
        
        return $suite; 
    }
}
?>