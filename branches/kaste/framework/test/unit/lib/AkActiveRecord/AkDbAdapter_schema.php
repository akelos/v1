<?php

defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);

require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class AkDbAdapter_schema_TestCase extends  AkUnitTest
{

    function test_should_rename_column_mysql()
    {
        $db =& AkDbAdapter::getConnection();
        if ($db->type() !== 'mysql') return;
        
        $this->installAndIncludeModels(array(
            'RenameColumn'=>"id,namen string(55),postcunt int not null default 0,help string default 'none'"
        ));
        $table_name = 'rename_columns';
        $this->mysql_rename($db, $table_name,'namen','name');
        $this->mysql_rename($db, $table_name,'help','nohelp');
        $this->mysql_rename($db, $table_name,'postcunt','postcount');
    }
    
    function mysql_rename($db, $table_name,$old_name,$new_name)
    {
        $old = $db->select("SHOW COLUMNS FROM $table_name LIKE '$old_name'");
        
        $db->renameColumn($table_name,$old_name,$new_name);
        
        $this->assertFalse($db->select("SHOW COLUMNS FROM $table_name LIKE '$old_name'"));
        $new = $db->select("SHOW COLUMNS FROM $table_name LIKE '$new_name'");
        unset($old[0],$old['Field'],$new[0],$new['Field']);
        $this->assertEqual($old,$new);
        
    }

}

ak_test('AkDbAdapter_schema_TestCase',true);

?>
