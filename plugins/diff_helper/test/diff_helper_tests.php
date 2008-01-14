<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'AkActionViewHelper.php');
require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'diff_helper.php');

class DiffHelperTestCase extends AkUnitTest
{

    function test_setup()
    {
        $this->diff_helper =& new DiffHelper();
    }

    function test_should_join_contiguous_words()
    {
        $original = 'open source';
        $modified = 'Open Source';
        $expected_diff = '<del>open source</del><ins>Open Source</ins>';

        $generated_diff = $this->diff_helper->diff($original, $modified);
        $this->assertEqual($generated_diff, $expected_diff);
    }

    function test_should_return_diff_html()
    {
        $original = file_get_contents(dirname(__FILE__).DS.'..'.DS.'test'.DS.'original.txt');
        $modified = file_get_contents(dirname(__FILE__).DS.'..'.DS.'test'.DS.'modified.txt');
        $expected_diff = file_get_contents(dirname(__FILE__).DS.'..'.DS.'test'.DS.'diff.html');

        $generated_diff = $this->diff_helper->diff($original, $modified);
        $this->assertEqual($generated_diff, $expected_diff);
    }

}

ak_test('DiffHelperTestCase');

?>