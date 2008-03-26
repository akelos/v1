<?php

class ExtensionTestCase extends AkUnitTest
{
    var $module = 'admin';
    var $insert_models_data = true;

    function test_setup()
    {
        $this->uninstallAndInstallMigration('Admin');
        $this->installAndIncludeModels('Extension');
        $this->includeAndInstatiateModels('Permission');
    }

    function test_should_disable_enabled_extension()
    {
        $Page = $this->Extension->findFirstBy('name', 'page');
        $this->assertTrue($Page->is_enabled);
        $Page->disable();
        $Page->reload();
        $this->assertFalse($Page->is_enabled);
        $this->assertFalse($GLOBALS['page_extension_installed']);
    }

    function test_should_enable_disabled_extension()
    {
        $Page = $this->Extension->findFirstBy('name', 'page');
        $this->assertFalse($Page->is_enabled);
        $Page->enable();
        $Page->reload();
        $this->assertTrue($Page->is_enabled);
        $this->assertTrue($GLOBALS['page_extension_installed']);
    }

    function test_should_not_allow_duplicated_extensions()
    {
        $Test = $this->Extension->create(array('name' => 'test', 'description' => 'Testing permission', 'is_enabled' => false));
        $Test = $this->Extension->create(array('name' => 'test', 'description' => 'Testing permission', 'is_enabled' => false));
        $this->assertTrue($Test->hasErrors() && $Test->isNewRecord());
    }

    function test_should_add_extension_permission_avoiding_duplicates()
    {
        $Page =& $this->Extension->findFirstBy('name', 'page');
        $this->assertTrue($Page->permission->create(array('name' => 'Create pages')));
        $this->assertTrue($Page->save());


        // This one is a duplicated permission
        $this->assertTrue($Page->permission->create(array('name' => 'Create pages')));
        $this->assertFalse($Page->save());

        $Page =& $this->Extension->findFirstBy('name', 'page', array('include' => 'permissions'));
        $this->assertEqual(count($Page->permissions), 1);
        $this->assertEqual($Page->permissions[0]->get('name'), 'Create pages');
    }

    function test_should_add_extension_preference()
    {
        $Page =& $this->Extension->findFirstBy('name', 'page');
        $this->assertTrue($Page->preference->create(array('name' => 'default_locale', 'value' => 'en')));
        $this->assertTrue($Page->save());


        // This one is a duplicated preference
        $this->assertTrue($Page->preference->create(array('name' => 'default_locale', 'value' => 'es')));
        $this->assertFalse($Page->save());

        $Page =& $this->Extension->findFirstBy('name', 'page', array('include' => 'preferences'));
        $this->assertEqual(count($Page->preferences), 1);
        $this->assertEqual($Page->preferences[0]->get('name'), 'default_locale');
        $this->assertEqual($Page->preferences[0]->get('value'), 'en');
    }

}

?>
