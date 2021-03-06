<?php

$database_settings = array(
    'testing' => array(
        'type' => 'sqlite',
        'database_file' => '/Users/bermi/Projects/akelos_framework/tests/database.sqlite',
        'host' => 'localhost',
        'database_name' => 'framework_tests',
        'user' => 'bermi',
        'password' => 'pass',
        'options' => ''
    )
);

$database_settings['development'] = $database_settings['production'] = $database_settings['testing'];

defined('AK_ENVIRONMENT') ? null : define('AK_ENVIRONMENT', 'testing');
defined('AK_BASE_DIR') ? null : define('AK_BASE_DIR', str_replace(DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php','',__FILE__));
defined('AK_TESTING_URL') ? null : define('AK_TESTING_URL', 'http://localhost/framework_tests/test/fixtures/public');
define('AK_LOG_EVENTS', true);

include('fix_htaccess.php');

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'boot.php');

?>