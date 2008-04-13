<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
defined('AK_APP_DIR') ? null :
define('AK_APP_DIR', dirname(__FILE__).DS.'fixtures'.DS.'app');
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'ActsAsLuceneTestCase.php');


class ActsAsLuceneTestCase extends AkUnitTest
{
    function test_setup()
    {
    }
    
    function test_should_create_

}

ak_test('ActsAsLuceneTestCase', true);

?>
