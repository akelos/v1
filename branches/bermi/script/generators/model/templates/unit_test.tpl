<?='<?php'?>

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');
require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');
require_once(AK_APP_DIR.DS.'shared_model.php');
require_once(AK_MODELS_DIR.DS.'<?=AkInflector::underscore($class_name)?>.php');

class <?=$class_name?>Test extends  UnitTestCase
{
    function test_setup()
    {
        require_once(AK_APP_DIR.DS.'installers'.DS.'<?=AkInflector::underscore($class_name)?>_installer.php');
        $installer = new <?=$class_name?>Installer();
        $installer->uninstall();
        $installer->install();    
    }
    
    function test_<?=$class_name?>()
    {
        $this->assertTrue(false,'Unit test for <?=$class_name?> not implemented');
    }
}


Ak::test('<?=$class_name?>Test',true);

?>
