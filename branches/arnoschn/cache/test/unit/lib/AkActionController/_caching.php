<?php

global $test;
defined('ALL_TESTS_CALL') ? null : define("ALL_TESTS_CALL",true);
defined('AK_ENABLE_PROFILER') ? null : define('AK_ENABLE_PROFILER',true);

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');
$runSingle = false;
if(empty($test)){
    $test = &new GroupTest('Akelos Framework Action Controller Tests');
    $runSingle = true;
}



$partial_tests = array(
'pages',

);

foreach ($partial_tests as $partial_test){
    $test->addTestFile(AK_LIB_TESTS_DIRECTORY.DS.'AkActionController'.DS.'Caching'.DS.'_'.$partial_test.'.php');
}

if($runSingle){
    if (TextReporter::inCli()) {
        exit ($test->run(new TextReporter()) ? 0 : 1);
    }
    $test->run(new HtmlReporter());
    @session_start();
}

?>
