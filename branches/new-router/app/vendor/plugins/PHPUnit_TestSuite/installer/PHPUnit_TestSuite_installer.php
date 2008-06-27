<?php

class PHPUnitTestSuiteInstaller extends AkInstaller 
{
    function install()
    {
        $source = dirname(__FILE__).DS.'phpunit_test.php';
        $target = AK_BASE_DIR.DS.'script'.DS.'phpunit_testsuite.php';

        if (copy($source,$target)) {
            echo "Copied script to your ./script folder.\n\r";
            $source_file_mode = fileperms($source);
            $target_file_mode = fileperms($target);
            if($source_file_mode != $target_file_mode){
                chmod($destination_file,$source_file_mode);
            }
        }
        echo "Be sure to read the README.\n\r";
        echo "We're now on version: ".Ak::file_get_contents(dirname(dirname(__FILE__)).DS.'VERSION');
    }
    
    function uninstall()
    {
        $target = AK_BASE_DIR.DS.'script'.DS.'phpunit_testsuite.php';
        unlink($target);        
    }
}

?>