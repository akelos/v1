<?php

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');


require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');


class test_AkActiveRecord_locking extends  AkUnitTest
{

    function test_start()
    {
        $this->installAndIncludeModels(array('BankAccount'=>'id,balance int(20),lock_version int(20),created_at,updated_at'));
    }

    function Test_of_isLockingEnabled()
    {
        $Account = new BankAccount();
        
        $this->assertTrue($Account->isLockingEnabled());
        
        $Account->lock_optimistically = false;
        
        $this->assertFalse($Account->isLockingEnabled());
    }

    function Test_of_OptimisticLock()
    {
        $Account1 = new BankAccount('balance->',2000); 
        $Account1->save(); // version 1
        
        $Account2 = new BankAccount($Account1->getId()); // version 1
        
        
        $Account1->balance = 5;
        $Account2->balance = 3000000;
        
        $Account1->save(); // version 2
        
        //$Account2->_db->debug =true;
        
        $this->assertFalse(@$Account2->save()); // version 1
        $this->assertFalse(@$Account2->save()); // version 1
        //$Account2->_db->debug = false;
        $this->assertErrorPattern('/stale|modificado/',$Account2->save());
        
        $Account1->balance = 1000; 
        
        $this->assertTrue($Account1->save()); // version 2
        
        $Account3 = new BankAccount($Account1->getId());
        
        $this->assertEqual($Account3->balance, 1000);
    }

}

ak_test('test_AkActiveRecord_locking',true);

?>
