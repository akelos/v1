<?php
# we add an folder to the search-path of the autoloader, because this plugin has its own fixtures-folder.
# usually you don't need this, as long as you use the standard-folders; i.e. test/fixtures/app/* and app/*
PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);

class ModelTestExample extends PHPUnit_Model_TestCase 
{

    function testUseModel()
    {
        list($Person,$People) = $this->useModel('Person');
        # that's it: now we have
        $this->assertType('Person',$Person);
        $this->assertType('Person',$this->Person);
        
        # $People and $this->People hold an array of the fixture-data from the data-folder
        # the filename of this fixture is <plural_name_of_the_model.DOT.yaml>, e.g. <people.yaml>
        # $People['sigmund']->id holds the actual id of the inserted row
        # $People['sigmund']->find() returns the ActiveRecord
        $Sigmund = $People['sigmund']->find();
        $this->assertThat($Sigmund,$this->equalTo($Person->find($People['sigmund']->id)));
        $this->assertEquals('Freud',$Sigmund->last_name);
    }
    
    function testGenerateTheModelAndTheTableOnTheFly()
    {
        # if we hadn't neither an Artist-Model nor an Artist-installer
        $this->useModel('Artist=>id,name,tag');
        # would create the table with the columns 'id', 'name' and 'tag' and would have created an 'empty' Model. 
        $this->assertType('ActiveRecord',$this->Artist);

        # we can create an Artist by doing 
        $Super = $this->createArtist('name: Supertramp, tag: super-goofy');
        $this->assertEquals('Supertramp', $Super->name);
        # since this is a comma-seperated list, escape a <,> with <\,> in your strings 

        # the given data will be merged with the array returned by <defaultArtist()>
        $Duran = $this->createArtist('name: Duran Duran');
        $this->assertEquals('so-so',$Duran->tag);
    }
    
    function defaultArtist()
    {
        return array('tag'=>'so-so');
    }
    
}
?>