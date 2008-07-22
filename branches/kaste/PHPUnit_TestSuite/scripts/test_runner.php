<?php
require_once dirname(dirname(__FILE__)).'/lib/PHPUnit_Akelos.php';
require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

class PHPUnit_TestRunner
{
    private $test_files = array();
    private $test_suite;
    private $options = array('verbose'=>false);
    private $filename_filter;
    
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
            if ($this->addFile($this->suite(),$arg)) continue;
            else switch ($arg){
                case '-v':
                    $this->options['verbose'] = true;
                    break;
                case '-?':
                    $this->drawHelp();
                    break;
                case '--c':
                case '--cover':
                    if (!extension_loaded('xdebug')) continue;
                    $this->options['reportDirectory'] = array_shift($args);
                    break;
                case '-':
                case '+':
                    $this->addFilter($arg.array_shift($args));
                    break;
                default:
                    $this->addFilter($arg);
                    break;
            }
        }
    }
    
    private function addFilter($arg)
    {
        switch ($arg{0}){
            case '-': $mode = 'sub'; break;
            case '+': $mode = 'add'; break;
            default: return false; 
        }

        $param = substr($arg,1);
        switch ($this->typeOfFilter($param)){
            case 'method':
                $pattern = str_replace('*','.*',$param);
                if ($mode=='add'){
                    $pattern = "/^$pattern/";
                    $this->options['filter'] = $pattern;
                }else{
                    //conditional regex-pattern: 
                    //if <$pattern> matches, the actual method-name must begin with 'tset' which is always false
                    $pattern = "/^(?(?=$pattern)tset)/";
                    $this->options['filter'] = $pattern;
                }
                break;
            case 'group':
                $mode == 'add' ? $this->options['groups'][] = $param : $this->options['excludeGroups'][] = $param;
                break;
            case 'filename':
                $pattern = str_replace(array('\\','.','*'),array('\\\\','\.','.*'),$param);
                
                $this->filename_filter = $mode == 'add' ? "/$pattern$/" : "/^(?(?=.*$pattern$).*hph$)/";  
                break;
        }
    }
    
    private function typeOfFilter($param)
    {
        if (substr($param,0,4)=='test') return 'method';
        if (substr($param,-4)=='.php')  return 'filename';
        return 'group';
    }
    
    private function addFile(PHPUnit_Framework_TestSuite &$suite,$file)
    {
        if (is_file($file)){
            if (!$this->ensureValidFilename((string)$file)) return false;
            $suite->addTestFile((string)$file,false);
        }elseif (is_dir($file)){
            $suite->addTestSuite($this->createSuiteForDirectory((string)$file));
        }else{
            return false;
        }
        return true;
    }
    
    private function ensureValidFilename($file)
    {
        if ($file{0}=='_') return false;
        
        if (substr($file,-13)=='_TestCase.php'){
            require_once $file;
            return false;
        }
        
        if (substr($file,-4)!='.php') return false;
        
        if ($this->filename_filter){
            if (preg_match($this->filename_filter,$file)) return true;
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
            $this->addFile($suite,$file);
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

test_runner [-v|?] [-|+group] [-|+method] [-|+filename] <filenames|folders>
   -v              verbose
   -?              this help
   -+group         see below
   -+method
   -+file
   --cover folder  write html-coverage-report to the specified folder 

This script creates TestSuites on-the fly and runs them. 
It will exclude filenames or folders which start with an underscore. As a 
convention it will not run filenames which end with <_TestCase.php> but instead
include them. This is so because 'TestCases' use to be abstract classes which 
contain common methods or a special Test-Api.

You can exclude or include groups, methods and/or files.
A minus [-] means 'except', a plus means 'only'. E.g.: 

>  test_runner -v -slow -test*Postgre + DbAdap*.php tests/

this will search through all files in the folder <tests/> and its subfolders, 
include the one which filename begins with <DbAdap> and exclude the tests which
belong to a group called <slow> or which method name matches <test.*Postgre>.

So how does this work. We take the sign -|+ to decide if we exclude or include 
the following parameter. The parameter gets parsed: if it begins with <test> we
take it as an method-name, if it ends with <.php> we say its a filename, 
otherwise it should be a group name. You can use a <*> to match "any character".
You can specify multiple groups, but only one methodname or filename pattern.

> test_runner tests/ -postgre -sqlite
> test_runner tests/ +mysql
> test_runner tests/ + Request*.php
> test_runner tests/AkRequest/ tests/AkRouter/
> test_runner tests/ +test*Cast*Null


BANNER;
        echo '(xdebug '.(extension_loaded('xdebug') ? 'enabled)' : 'disabled)');
        exit;
        
    }
}

#array_push($argv,'../ExamplesTestSuite.php');
#array_push($argv,'../Examples');
#array_push($argv,'-v');

array_shift($argv);
PHPUnit_TestRunner::main($argv);
?>