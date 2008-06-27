<?php

class AutoloaderTest extends PHPUnit_Framework_TestCase 
{
    function testAddFolderToAutoloader()
    {
        PHPUnit_Akelos_autoload::deleteExtraFolders();
        PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);
        $this->assertEquals(array(AK_PHPUNIT_TESTSUITE_FIXTURES),PHPUnit_Akelos_autoload::$extra_folders);
    }
    
    function testCantAddFolderTwice()
    {
        PHPUnit_Akelos_autoload::deleteExtraFolders();
        PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);
        PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);
        $this->assertEquals(array(AK_PHPUNIT_TESTSUITE_FIXTURES),PHPUnit_Akelos_autoload::$extra_folders);
    }
    
    function testAddingFilenameWillActuallyAddTheParentFolder()
    {
        PHPUnit_Akelos_autoload::deleteExtraFolders();
        PHPUnit_Akelos_autoload::addFolder(__FILE__);
        $this->assertEquals(array(dirname(__FILE__)),PHPUnit_Akelos_autoload::$extra_folders);
    }
    
    function testAllowAddingMultipleFoldersAsArgumentsList()
    {
        PHPUnit_Akelos_autoload::deleteExtraFolders();
        PHPUnit_Akelos_autoload::addFolder(__FILE__,AK_PHPUNIT_TESTSUITE_FIXTURES);
        $this->assertEquals(array(dirname(__FILE__),AK_PHPUNIT_TESTSUITE_FIXTURES),PHPUnit_Akelos_autoload::$extra_folders);
    }
}

?>