<?php

defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);

require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class AkActiveRecord_connection_handling_TestCase extends  AkUnitTest
{

    function test_should_establish_a_connection()
    {
        $this->installAndIncludeModels(array('EmptyModel'=>'id'));
        
        $Model =& $this->EmptyModel;
        $default_connection =& AkDbAdapter::getConnection();
        unset ($Model->_db);
        $this->assertTrue($Model->establishConnection()===$default_connection);
        $this->assertFalse($Model->establishConnection('development')===$default_connection);

        $this->assertFalse($Model->establishConnection('not_specified_profile'));
        $this->assertError("Could not find the database profile 'not_specified_profile' in config/config.php.");
        
    }

}

ak_test('AkActiveRecord_connection_handling_TestCase',true);

?>
