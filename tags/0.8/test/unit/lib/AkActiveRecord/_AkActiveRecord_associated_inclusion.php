<?php

defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);

require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class test_AkActiveRecord_associated_inclusion extends  AkUnitTest
{
    function test_start()
    {
        $this->installAndIncludeModels('Property','Picture', array('instantiate'=>true));
    }

    function test_belongs_to_inclusion_on_find()
    {
        $Apartment =& $this->Property->create('description->','Docklands riverside apartment');
        $Picture =& $this->Picture->create('title->','Views from the living room');

        $Picture->property->assign($Apartment);
        $Picture->save();

        $ViewsPicture =& $this->Picture->find($Picture->id, array('include'=>'property'));
        $this->assertEqual($ViewsPicture->property->description, $Apartment->description);
    }

    function test_collection_inclusion_on_find()
    {
        $Apartment =& $this->Property->findFirstBy('description','Docklands riverside apartment');
        $Picture =& $this->Picture->create('title->','Living room');

        $Picture->property->assign($Apartment);
        $Picture->save();

        $Property =& $this->Property->find($Apartment->id, array('include'=>'pictures'));
        $this->assertEqual($Property->pictures[1]->title, $Picture->title);
    }
}

ak_test('test_AkActiveRecord_associated_inclusion',true);

?>