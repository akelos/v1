<?php

class PHPUnitTestSuiteInstaller extends AkInstaller 
{
    function up_1()
    {
        $source = dirname(__FILE__).DS.'phpunit_test.php';
        $target = AK_BASE_DIR.DS.'script'.DS.'phpunit_testsuite.php';

        copy($source,$target);
        $source_file_mode = fileperms($path);
        $target_file_mode = fileperms($destination_file);
        if($source_file_mode != $target_file_mode){
            chmod($destination_file,$source_file_mode);
        }
        
    }
    
    function down_1()
    {
        $target = AK_BASE_DIR.DS.'script'.DS.'phpunit_testsuite.php';
        unlink($target);        
    }
}

?>