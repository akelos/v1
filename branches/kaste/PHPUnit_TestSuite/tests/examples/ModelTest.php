<?php
require_once preg_replace('|(tests\\'.DIRECTORY_SEPARATOR.'.*$)|','lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php',__FILE__);

class ModelTestExample extends PHPUnit_Model_TestCase 
{

    function testUseModel()
    {
        list($Person,$People) = $this->useModel('Person');
        # that's it: now we have
        $this->assertType('Person',$Person);
        $this->assertType('Person',$this->Person);
        
        # $People and $this->People hold an array of the fixture-data from the data-folder
        # $People['sigmund']->id holds the actual id of the inserted row
        # $People['sigmund']->find() returns the ActiveRecord
        $Sigmund = $People['sigmund']->find();
        $this->assertThat($Sigmund,$this->equalTo($Person->find($People['sigmund']->id)));
        $this->assertEquals('Freud',$Sigmund->last_name);
    }
    
    function testGenerateTheModelAndTheTableOnTheFly()
    {
        # if we hadn't neither an Artist-Model nor an Artist-installer
        $this->useModel('Artist=>id,name');
        # would create the table with the columns 'id' and 'name' and would have created an empty Model. 
        $this->assertType('ActiveRecord',$this->Artist);
        
        $this->Artist->create(array('name'=>'Supertramp'));
    }
    
}
?>