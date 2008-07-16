<?php
require_once dirname(dirname(__FILE__)).'/lib/PHPUnit_Akelos.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class PHPUnit_TestRunner
{
    private $test_files = array();
    private $test_suite;
    private $options = array('verbose'=>false);
    
    static function main($args=null)
    {
        if (!$args){
            global $argv;
            $args = $argv;
        }
        
        $self = new PHPUnit_TestRunner($args);
        $self->run()->wasSuccessful() ? exit(0) : exit(1);
    }
    
    function __construct($args)
    {
        $this->test_suite = $this->createTestSuite($this->getMainName());
        $this->parseArgs($args);    
    }
    
    function getMainName()
    {
        $name = substr(AK_BASE_DIR,strrpos(AK_BASE_DIR,DS)+1);
        return AkInflector::humanize($name,'all').' - Unit Tests';
    }
    
    /**
     * @return PHPUnit_Framework_TestSuite
     */
    function createTestSuite($name)
    {
        $suite = new PHPUnit_Framework_TestSuite();
        $suite->setName($name);
        return $suite;
    }
    
    
    /**
     * @return PHPUnit_Framework_TestSuite
     */
    function suite()
    {
        return $this->test_suite;
    }
    
    function parseArgs($args)
    {
        while (count($args) > 0){
            $arg = array_shift($args);
            if ($this->tryToAddToSuite($this->suite(),$arg)) continue;
            else switch ($arg){
                case '-v':
                    $this->options['verbose'] = true;
                    break;
                case '-?':
                default:
                    $this->drawHelp();
                    break;
            }
        }
    }
    
    function tryToAddToSuite(PHPUnit_Framework_TestSuite &$suite,$file)
    {
        if (substr($file,0,1)=='_'){
            return false;
        }elseif (is_file($file)){
            $suite->addTestFile((string)$file,false);
        }elseif (is_dir($file)){
            $suite->addTestSuite($this->createSuiteForDirectory((string)$file));
        }else{
            return false;
        }
        return true;
    }
    
    /**
     * @return PHPUnit_Framework_TestSuite
     */
    function createSuiteForDirectory($path)
    {
        $suite = $this->createTestSuite(AkInflector::humanize($path));
        $files = new RecursiveDirectoryIterator($path);
        foreach ($files as $file){
            $this->tryToAddToSuite($suite,$file);
        }
        return $suite;
    }
    
    function run()
    {
        return PHPUnit_TextUI_TestRunner::run($this->suite(),$this->options);
    }
    
    function drawHelp()
    {
        echo <<<BANNER
Usage:

test_runner [-v] [tests/test-suites/folders]
   -v   verbose
   -?   this help

This script creates TestSuites on-the fly and runs them. It will exclude filenames or folders which start with an underscore.

BANNER;
        exit;
        
    }
}

#array_push($argv,'../ExamplesTestSuite.php');
#array_push($argv,'../Examples');
#array_push($argv,'-v');

array_shift($argv);
PHPUnit_TestRunner::main($argv);
?>