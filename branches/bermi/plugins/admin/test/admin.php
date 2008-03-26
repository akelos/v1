<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_APP_DIR') ? null :
define('AK_APP_DIR', dirname(__FILE__).DS.'fixtures'.DS.'app');
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

class AdminTestCase extends AkUnitTest
{
}

ak_test('AdminTestCase', true);

?>
