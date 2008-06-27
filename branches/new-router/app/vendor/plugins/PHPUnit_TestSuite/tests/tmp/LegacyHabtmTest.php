<?php

class User extends ActiveRecord 
{
    var $habtm = array(
        'clients' => array(
            'class_name'=>'Customer',
            'table_name'=>'my_customers',
            'join_class_name'=>'Client',
            'join_table'=>'my_clients',
            'foreign_key'=>'my_user_id',
            'association_foreign_key'=>'my_customer_id'
        ) 
    );
    
    function __construct()
    {
        $this->setTableName('my_users');
        $this->init(func_get_args());
    }
}

class Customer extends ActiveRecord
{
    function __construct()
    {
        $this->setTableName('my_customers');
        $this->init(func_get_args());
    }
}

class LegacyHabtmTest extends PHPUnit_Model_TestCase 
{
    
    
    function setUp()
    {
        $this->createTable('MyUser','id,name');
        $this->createTable('MyCustomer','id,name');
        $this->createTable('MyClients','id,my_user_id,my_customer_id');
            
        $this->instantiateModel('User');
        $this->instantiateModel('Customer');
        $this->instantiateModel('Client');
        
        $Bill = $this->createUser('name: Bill');
        $Bob  = $this->createCustomer('name: Bob');
        $AreClients = $this->createClient("my_user_id: $Bill->id,my_customer_id: $Bob->id");
    }
    
    function testSetUp()
    {
        $this->assertThat($this->User->getTableName(),$this->equalTo('my_users'));
        $this->assertThat($this->Customer->getTableName(),$this->equalTo('my_customers'));
        $this->assertThat($this->Client->getTableName(),$this->equalTo('my_clients'));
        
        $this->assertArrayHasKey('clients',$this->User->getAssociated('hasAndBelongsToMany'));
        
        $this->assertContains('my_users',AkDbAdapter::getInstance()->availableTables());
        $this->assertContains('my_customers',AkDbAdapter::getInstance()->availableTables());
        $this->assertContains('my_clients',AkDbAdapter::getInstance()->availableTables());
        
    }
    
    function testTruth()
    {
        $Bill = $this->User->find('first',array('include'=>'clients'));
        $this->assertThat($Bill->clients[0]->name,$this->equalTo('Bob'));
    }
}

?>