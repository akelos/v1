<?php

require_once(AK_LIB_DIR.DS.'AkPlugin'.DS.'AkPluginManager.php');

class AkPluginManagerTestCase extends AkUnitTest
{
    function test_remove_repositories_config()
    {
        @Ak::file_delete(AK_CONFIG_DIR.DS.'plugin_repositories.txt');
    }

    function setup()
    {
        $this->PluginManager =& new AkPluginManager();
        @Ak::file_delete(AK_TMP_DIR.DS.'plugin_repositories.yaml');
    }

    function test_should_get_available_repositories()
    {
        $repositories = $this->PluginManager->getAvailableRepositories();
        $this->assertTrue(in_array('http://svn.akelos.org/plugins', $repositories));
    }

    function test_should_add_new_repository()
    {
        $this->PluginManager->addRepository('http://svn.editam.com/plugins');
        $this->PluginManager->addRepository('http://svn.example.com/plugins');
        $repositories = $this->PluginManager->getAvailableRepositories();
        $this->assertTrue(in_array('http://svn.akelos.org/plugins', $repositories));
        $this->assertTrue(in_array('http://svn.editam.com/plugins', $repositories));
    }

    function test_should_remove_repository()
    {
        $repositories = $this->PluginManager->getAvailableRepositories(true);
        $this->assertEqual(count($repositories), 3);

        $this->PluginManager->removeRepository('http://svn.editam.com/plugins');
        $repositories = $this->PluginManager->getAvailableRepositories(true);
        $this->assertFalse(in_array('http://svn.editam.com/plugins', $repositories));
        $this->assertEqual(count($repositories), 2);

        $this->PluginManager->removeRepository('http://svn.example.com/plugins');
        $repositories = $this->PluginManager->getAvailableRepositories(true);
        $this->assertEqual(count($repositories), 1);
    }

    function test_should_get_remote_plugin_list()
    {
        $plugins = $this->PluginManager->getPlugins();
        $this->assertEqual($plugins['acts_as_versioned'], 'http://svn.akelos.org/plugins');
    }

    function test_should_install_plugin()
    {
        $this->PluginManager->installPlugin('acts_as_versioned');
        $this->assertTrue(in_array('acts_as_versioned', $this->PluginManager->getInstalledPlugins()));
    }

    function test_should_update_plugin()
    {
        Ak::directory_delete(AK_PLUGINS_DIR.DS.'acts_as_versioned'.DS.'lib');
        $this->assertFalse(file_exists(AK_PLUGINS_DIR.DS.'acts_as_versioned'.DS.'lib'.DS.'ActsAsVersioned.php'));
        $this->PluginManager->updatePlugin('acts_as_versioned');
        $this->assertTrue(file_exists(AK_PLUGINS_DIR.DS.'acts_as_versioned'.DS.'lib'.DS.'ActsAsVersioned.php'));
    }

    function test_should_uninstall_plugin()
    {
        $this->PluginManager->uninstallPlugin('acts_as_versioned');
        $this->assertFalse(is_dir(AK_PLUGINS_DIR.DS.'acts_as_versioned'));
    }

    function test_should_get_remote_repositories_listing()
    {
        $repositories = $this->PluginManager->_getRepositoriesFromRemotePage();
        $this->assertEqual($repositories[0], $this->PluginManager->main_repository);
    }
}

?>
