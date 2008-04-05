<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_APP_DIR') ? null :
define('AK_APP_DIR', dirname(__FILE__).DS.'fixtures'.DS.'app');
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'ActsAsVersioned.php');


class ActsAsVersionedTestCase extends AkUnitTest
{
    function test_setup()
    {
        $this->installAndIncludeModels('Post');
        $Installer =& new AkInstaller();
        $Installer->dropTable('post_versions');
    }

    function test_should_create_versioned_table()
    {
        $this->assertFalse($this->Post->hasColumn('version'), 'Version column should not be created on migrations for this test');
        $this->Post->versioned->createVersionedTable();
        $this->Post =& new Post();
        $this->assertTrue($this->Post->hasColumn('version'), 'Version column was not created on the posts table.');

        $VersionedPost =& new PostVersion();
        $this->assertEqual($VersionedPost->getTableName(), 'post_versions');
        $this->assertEqual(count($this->Post->getColumns()), count($VersionedPost->getColumns())-1 );
    }

    function test_should_save_the_first_version_only_once()
    {
        //$this->Post->dbug();
        $Post =& $this->Post->create(array('title' => 'The first', 'body' => 'This is the first post'));
        $this->assertFalse($Post->isNewRecord());
        $Post->reload();

        $this->assertEqual($Post->get('version'), 1);

        $Post =& $Post->find($Post->getId());
        
        $this->assertEqual($Post->versioned->getChangedAttributes(), array());
        
        $Post->save();

        $Post->reload();

        $this->assertEqual($Post->get('version'), 1);
    }

    function test_should_save_versioned_copy()
    {
        $Post =& new Post(array('title' => 'Post 1.1', 'body' => 'body 1.1'));
        $this->assertTrue($Post->save());

        $this->assertEqual($Post->get('version'), 1);
        $this->assertEqual(count($Post->versions), 1);
        $this->assertEqual(strtolower(get_class($Post->versions[0])), 'postversion');
    }

    function test_should_save_without_revision()
    {
        $this->assertTrue($Post =& $this->Post->findFirstBy('title', 'Post 1.1'));
        $Post->set('body', 'Body for 1.1');
        $Post->versioned->saveWithoutRevision();

        $Post->reload();
        $this->assertEqual($Post->get('version'), 1);
        $this->assertEqual($Post->get('body'), 'Body for 1.1');
    }

    function test_should_save_versioned_if_different_from_last_version()
    {
        $Post =& $this->Post->findFirstBy('title', 'Post 1.1');
        $Post->set('title', 'Post 1.2');
        $Post->set('body', 'Body for 1.2');
        $Post->save();
        $this->assertTrue($Version =& $Post->versioned->getLatestVersion());
        $this->assertEqual($Version->getVersionedAttributes(), $Post->getAttributes());

        $Post->save();

        $this->assertEqual(2, $Post->get('version'));
    }

    function test_should_save_multiple_versions()
    {
        foreach (range(2,10) as $i){
            $Post =& new Post(array('title' => 'Post '.$i.'.1', 'body' => 'Body '.$i.'.1'));
            $this->assertTrue($Post->save());
            $this->assertEqual($Post->get('version'), 1);
            $this->assertEqual(count($Post->versions), 1);
            foreach (range(2,5) as $e){
                $Post->set('title', 'Post '.$i.'.'.$e.'');
                $Post->set('body', 'Body '.$i.'.'.$e.'');
                $this->assertTrue($Post->save());
                $this->assertEqual($Post->get('version'), $e);
                $this->assertEqual(count($Post->versions), $e);
            }
        }
    }

    function test_should_revert_to_version()
    {
        $Post =& $this->Post->findFirstBy('title', 'Post 5.5');
        $this->assertEqual($Post->get('version'), 5);
        $Post->versioned->revertToVersion(3);
        $this->assertEqual($Post->get('version'), 6);
        $Post->reload();
        $this->assertEqual($Post->get('body'), 'Body 5.3');
    }

    function test_should_fail_when_reverting_to_unexisting_version()
    {
        $Post =& $this->Post->findFirstBy('title', 'Post 6.5');
        $this->assertEqual($Post->get('version'), 5);
        $this->assertFalse($Post->versioned->revertToVersion(6));
        $this->assertEqual($Post->get('version'), 5);
        $Post->reload();
        $this->assertEqual($Post->get('body'), 'Body 6.5');
    }

    function test_should_clean_old_versions()
    {
        $Post =& new Post(array('title' => 'Last Post', 'body' => 'Last body'));
        foreach (range(1,15) as $e){
            $Post->set('title', 'Last post version '.$e.'');
            $this->assertTrue($Post->save());
            $this->assertEqual($Post->get('version'), $e);
            if($e <= 10){
                $this->assertEqual(count($Post->versions), $e);
            }else{
                $this->assertEqual(count($Post->versions), 10);
            }
        }
    }

    function test_should_load_versioned()
    {
        $Post =& $this->Post->findFirstBy('title', 'Last post version 15');
        $this->assertTrue(empty($Post->versions));
        $Post->versioned->load();
        $this->assertEqual(count($Post->versions), 10);
        $this->assertEqual($Post->versions[0]->get('title'), 'Last post version 6');

        unset($Post->versions[0]);
        $Post->versioned->load();
        $this->assertEqual(count($Post->versions), 9);

        $Post->versioned->load(true);
        $this->assertEqual(count($Post->versions), 10);
    }


    function test_versions_should_be_removed_when_the_owner_is_destroyed()
    {
        $Post =& $this->Post->findFirstBy('title', 'Last post version 15');
        $post_id = $Post->getId();
        $Post->destroy();
        $PostVersion =& new PostVersion();
        $this->assertFalse($PostVersion->findAllBy('post_id', $post_id));
    }

    function test_should_find_within_versions()
    {
        $Post =& $this->Post->findFirstBy('title', 'Post 4.5');
        $this->assertTrue($Version3 =& $Post->versioned->find('first', array('conditions' => array('title = ?', 'Post 4.3'))));
        $this->assertEqual($Version3->get('version'), 3);
        $this->assertEqual($Version3->get('title'), 'Post 4.3');
    }

    function test_should_get_previous_version()
    {
        $this->assertTrue($Post =& $this->Post->findFirstBy('title', 'Post 3.5'));
        $Post->versioned->load();
        $this->assertTrue($Version3 =& $Post->versions[3]->getPrevious());
        $this->assertEqual($Version3->get('version'), 3);
    }

    function test_should_get_next_version()
    {
        $this->assertTrue($Post =& $this->Post->findFirstBy('title', 'Post 3.5'));
        $Post->versioned->load();
        $this->assertTrue($Version3 =& $Post->versions[1]->getNext());
        $this->assertEqual($Version3->get('version'), 3);
    }

    function test_should_not_create_new_versions_when_updating_models_without_changes()
    {
        $this->assertTrue($Post =& $this->Post->findFirstBy('title', 'Post 3.5'));
        $Post->versioned->load();
        $expected_versions_count = count($Post->versions);

        $this->assertTrue($Post =& $this->Post->findFirstBy('title', 'Post 3.5'));
        $this->assertTrue($Post->save());
        $Post->versioned->load();
        $this->assertEqual(count($Post->versions), $expected_versions_count);
    }

    function test_should_drop_versioned_table()
    {
        $this->Post->versioned->dropVersionedTable();
        $Installer =& new AkInstaller();
        $this->assertFalse($Installer->tableExists('post_versions'));
    }
}

ak_test('ActsAsVersionedTestCase', true);

?>
