<?php

defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);

require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class AkDbAdapter_TestCase extends  AkUnitTest
{

    function test_should_generate_sequence_ids()
    {
        $db =& AkDbAdapter::getConnection(array('type'=>'sqlite'),false);
    }
    
    function test_should_report_errors()
    {
        $db =& AkDbAdapter::getConnection();
        $db->debug();
        $db->sqlexecute('selct wrong sql statement');
        $this->assertError();
        //$db->debug(false);
    }
    
    function test_execute_should_handle_bindings()
    {
        $db =& AkDbAdapter::getConnection(null,false);
        Mock::generate('ADOConnection');
        $connection =& new MockADOConnection();
        $connection->setReturnValue('Execute',true);
        $connection->expectAt(0,'Execute',array('SELECT * FROM articles WHERE id=1'));
        $connection->expectAt(1,'Execute',array('SELECT * FROM articles WHERE id=?',array(1)));
        $db->connection =& $connection;
        $db->sqlexecute('SELECT * FROM articles WHERE id=1');
        $db->sqlexecute(array('SELECT * FROM articles WHERE id=?',1));
    }
}

ak_test('AkDbAdapter_TestCase',true);

?>
