<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php';

class TestModelTest extends PHPUnit_Model_TestCase
{

    function testCreateTableOnTheFly()
    {
        #we take a name that we won't use anywhere else
        $this->createTable('UnusualName','id,title');
        $this->assertTrue(self::table_exists('unusual_names'));
        $this->assertTableHasColumns('unusual_names',array('id','title'));
        
        $this->drop('unusual_names');
    }
    
    function testGenerateModelOnTheFly()
    {
        $this->assertFalse(class_exists('UnusualName',false));
        $this->generateModel('UnusualName');
        $this->assertTrue(class_exists('UnusualName',false));
        
        #we need a table before we can instantiate a ActiveRecord
        $this->createTable('UnusualName','id,title');
        $this->assertType('ActiveRecord',new UnusualName());
        
        $this->drop('unusual_names');
    }
    
    function drop($table_name)
    {
        $Installer = new AkInstaller();
        $Installer->dropTable($table_name);
        $this->assertFalse(self::table_exists($table_name));
    }
    
    function table_exists($table_name)
    {
        $tables = AkDbAdapter::getInstance()->availableTables();
        return in_array($table_name,$tables);        
    }
    
    function assertTableHasColumns($table_name,$columns)
    {
        $column_details = AkDbAdapter::getInstance()->getColumnDetails($table_name);
        self::assertEquals($columns,array_map('strtolower',array_keys($column_details)));
    }
}

?>