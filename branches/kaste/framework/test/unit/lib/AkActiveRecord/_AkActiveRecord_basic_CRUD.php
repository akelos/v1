<?php

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class test_ActiveRecord_basic_CRUD_Operation extends AkUnitTest
{
    function test_should_return_false_when_destroy_fails()
    {
        $this->installAndIncludeModels('Post');
        
        $Post = new Post(array('title'=>'A Title'));
        $Post->save();
        $this->assertTrue($Post->destroy());
        $this->assertFalse($Post->destroy());
    }
         
}

ak_test('test_ActiveRecord_basic_CRUD_Operation',true);

?>