<?php
PHPUnit_Akelos_autoload::addFolder(AK_PHPUNIT_TESTSUITE_FIXTURES);

class PersonControllerTest extends PHPUnit_Controller_TestCase 
{
    
    function setUp()
    {
        $this->useModel('Person');
        $this->useController('Person');
    }

    function testIndex()
    {
        $this->get('index');
        
        $People = $this->Person->find('all');
        $this->assertAssign('People',$People);
        
        $this->assertEquals($this->Controller->People[0],$this->People['sigmund']->find());
    }
    
    function testShow()
    {
        $this->get('show',array('id'=>$this->People['sigmund']->id));
        
        $this->assertAssign('Person',$this->People['sigmund']->find());
    }
    
    function testAdd()
    {
        $this->post('add',array('person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
        
        $this->assertFlash('notice','Person was successfully created.');
        $this->assertEquals(3,count($this->Person->find('all')));
    }
    
    function testRedirectAfterAdd()
    {
        $this->expectRedirectTo(array('action'=>'show','id'=>3));
        $this->post('add',array('person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
    }
    
    function testAddViaGetRequestDoesntCreate()
    {
        $this->get('add',array('person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
        
        $this->assertEquals(2,count($this->Person->find('all')));
    }
    
    function testEdit()
    {
        $Fritz = $this->createPerson('first_name: Fitz, last_name: Morgenthaler');
        $this->post('edit',array('id'=>$Fritz->id,'person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
        
        $this->assertFlash('notice','Person was successfully updated.');
        $Fritz->reload();        
        $this->assertEquals('Fritz',$Fritz->first_name);
    }
    
    function testRedirectAfterEdit()
    {
        $Fritz = $this->createPerson('first_name: Fitz, last_name: Morgenthaler');
        $this->expectRedirectTo(array('action'=>'show','id'=>$Fritz->id));
        $this->post('edit',array('id'=>$Fritz->id,'person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
    }
    
    function testRedirectToIndexWhenNoIdIsSet()
    {
        $this->expectRedirectTo(array('action'=>'index'));
        $this->post('edit',array('person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
    }
    
    function testDontUpdateIfNotPostRequest()
    {
        $Fritz = $this->createPerson('first_name: Fitz, last_name: Morgenthaler');
        $this->get('edit',array('id'=>$Fritz->id,'person'=>array('first_name'=>'Fritz','last_name'=>'Morgenthaler')));
        
        $Fritz->reload();        
        $this->assertEquals('Fitz',$Fritz->first_name);
        # but assigned variable is updated
        $this->assertEquals('Fritz',$this->Controller->Person->first_name); 
    }
    
    function testDestroy()
    {
        $this->post('destroy',array('id'=>$this->People['sigmund']->id));
        
        $this->assertEquals(1,count($this->Person->find('all')));
    }
    
    function testRedirectToIndexAfterDestroy()
    {
        $this->expectRedirectTo(array('action'=>'index'));
        $this->post('destroy',array('id'=>$this->People['sigmund']->id));
    }
    
    function testDontDestroyIfNotPostRequest()
    {
        $this->get('destroy',array('id'=>$this->People['sigmund']->id));
        $this->assertEquals(2,count($this->Person->find('all')));
    }
}

?>