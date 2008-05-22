<?php
PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);

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
    
    function testCreateTableUsingAnInstaller()
    {
        $this->createTable('Person');
        
        $this->assertTrue(self::table_exists('people'));
        $this->assertTableHasColumns('people',array('id','first_name','last_name','updated_at','created_at'));
        
        $this->drop('people');
    }
    
    function testInstantiateModel()
    {
        $this->createTable('Person');
        $this->instantiateModel('Person');
        
        $this->assertType('Person',$this->Person);

        $this->drop('people');
    }
    
    function testShouldAutomaticallyGenerateModelOnInstantiation()
    {
        $this->createTable('AnotherUnusualName','id,name');
        $this->instantiateModel('AnotherUnusualName');
        
        $this->drop('another_unusual_names');
    }
    
    function testPopulateModel()
    {
        $this->createTable('Person');
        $Person = $this->instantiateModel('Person');
        $People = $this->loadFixture('Person');
        
        $Sigmund = $Person->find($People['sigmund']->id);
        $this->assertEquals('Sigmund',$Sigmund->first_name);
        $this->assertEquals('Freud',$Sigmund->last_name);
        $this->assertEquals($People['sigmund']->first_name,$Sigmund->first_name);
        $this->assertEquals($People['sigmund']->last_name,$Sigmund->last_name);

        $this->drop('people');
    }
    
    function testShouldNotMoanAboutUnpresentFixture()
    {
        $this->assertFalse($this->loadFixture('Unknown'));
    }
    
    function testUseModel()
    {
        list($Model,$Fixture) = $this->useModel('Person');
        
        $this->assertTrue(self::table_exists('people'));
        $this->assertType('Person',$this->Person);
        $this->assertTrue(is_array($this->People));
        
        $this->assertSame($Model,$this->Person);
        $this->assertSame($Fixture,$this->People);
        
        $this->drop('people');
    }
    
    function testUseModelAndUseTableDefinition()
    {
        $this->useModel('Person=>id,new_name');
        $this->assertTableHasColumns('people',array('id','new_name'));

        $this->drop('people');
    }
    
    function testFindOnAFixtureObjectShouldReturnTheActiveRecord()
    {
        list($Person,$People) = $this->useModel('Person');
        $Sigmund = $People['sigmund']->find();
        
        $this->assertType('FixedActiveRecord',$People['sigmund']);
        $this->assertType('ActiveRecord',$Sigmund);
        $this->assertEquals($Sigmund,$Person->find($People['sigmund']->id));
    }
    
    function testSplitIntoModelAndDefinition()
    {
        $this->assertEquals(array('Artist','id,name'),$this->splitIntoModelNameAndTableDefinition('Artist=>id,name'));
        $this->assertEquals(array('Artist','id,name'),$this->splitIntoModelNameAndTableDefinition('Artist  => id,name '));
        $this->assertEquals(array('Artist'),$this->splitIntoModelNameAndTableDefinition('Artist'));
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