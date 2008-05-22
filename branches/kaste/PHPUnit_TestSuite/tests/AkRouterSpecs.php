<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php';

class AkRouterSpecs extends PHPUnit_Routing_TestCase 
{

    function setUp()
    {
        $this->instantiateRouter();
    }
    
    function testShowsTheTestAPI()
    {
    	$this->connect('/:controller/:action');
    	$this->get('/blog/index');
    	$this->assertEquals(array('controller'=>'blog','action'=>'index'),$this->params);
    	$this->get('/blog/index')->resolvesTo(array('controller'=>'blog','action'=>'index'));
    	$this->get('/blog/index')->resolvesTo('blog','index');
    }
    
    function testImplicitOptional()
    {
    	$this->connect('/:controller/:action');
    	$this->get('/blog/')->resolvesTo('blog','');
    	#actually this should be
    	#$this->get('/blog/')->resolvesTo('blog');
    }
    
    function testImplicitOptionalWithDefaults()
    {
    	$this->connect('/:controller/:action',array('controller'=>'page','action'=>'list'));
        $this->get('blog')->resolvesTo('blog','list');
        $this->get('/')   ->resolvesTo('page','list');
    }
    
    function testImplicitOptionalWithRequirements()
    {
    	$this->connect('/:controller/:action',array('controller'=>'page'),array('action'=>'/[a-zA-Z]+/'));
        $this->get('blog/')     ->resolvesTo('blog','');
        $this->get('blog/index')->resolvesTo('blog','index');
        $this->get('blog/123')  ->doesntResolve();        
    }
    
    function testImplicitOptionalWithDefaultsAndRequirements()
    {
    	$this->connect('/:controller/:action',array('controller'=>'page','action'=>'list'),array('action'=>'/[a-zA-Z]+/'));
        $this->get('blog/')     ->resolvesTo('blog','list');
        $this->get('blog/index')->resolvesTo('blog','index');
        $this->get('blog/123')  ->doesntResolve();        
    }
    
    function testExplicitOptional()
    {
    	$this->connect('/:controller/:action',array('controller'=>'page','action'=>OPTIONAL));
        $this->get('/blog/show')->resolvesTo('blog','show');
        $this->get('/blog/')    ->resolvesTo('blog','');
    }
    
    function testExplicitOptionalWithRequirements()
    {
    	$this->connect('/:controller/:action',array('controller'=>'page','action'=>OPTIONAL),array('action'=>'[a-zA-z]+'));
    	$this->get('/blog/show')->resolvesTo('blog','show');
        $this->get('blog/')     ->resolvesTo('blog','');
        $this->get('blog/123')  ->doesntResolve();        
    }
    
    #bug
    function testExplicitCompulsory()
    {
    	$this->markTestIncomplete('BUG #164');
    	$this->connect('/:controller/:action/:id',array('controller'=>'page','id'=>COMPULSORY));
    	$this->get('blog/index/1')->resolvesTo('blog','index',1);
    	$this->get('blog/index')  ->doesntResolve();          #fails
        $this->get('blog/')       ->doesntResolve();          
    	#actual is
        $this->get('blog/index')  ->resolvesTo('blog','index');
    }
    
    function testCompulsoryTestFromBermi()
    {
        #works because of the static 'lists' !
        $this->connect('/lists/:action/:id/:option', array('controller'=>'todo','option'=>COMPULSORY));
        $this->get('/lists/show/123')->doesntResolve();   
    }
    
    #bug
    function testCompulsoryWithRequirement()
    {
        $this->markTestIncomplete('BUG #165');
        $this->connect('/:controller/:action',array('action'=>COMPULSORY),array('action'=>'/[A-Za-z]+/'));
        $this->get('blog/index')->resolvesTo('blog','index');
        $this->get('blog/1')    ->doesntResolve();          #fails
        #actual is
        $this->get('blog/1')->resolvesTo('blog',1);
    }
    
    function testVariableListAtTheEnd()
    {
        $this->connect(':controller/*options');
        $this->get('/control/this')         ->resolvesTo('control',array('this'));
        $this->get('/control/this/and/that')->resolvesTo('control',array('this','and','that'));
    }
    
    #bug
    function testVariablesListAtTheEndWithExpectedCount()
    {
        $this->markTestIncomplete('BUG #166');
        $this->connect('/:controller/*options',array('options'=>3));
        $this->get('/control/this/and/that')    ->resolvesTo('control',array('this','and','that'));
        $this->get('/control/not/this')         ->doesntResolve();    #fails
        $this->get('/control/not/this/nor/that')->doesntResolve();    #fails
        #actual is
        $this->get('/control/not/this')         ->resolvesTo('control',array('not','this',''));
        $this->get('/control/not/this/nor/that')->resolvesTo('control',array('not','this','nor'));
    }
    
    #bug
    function testVariablesListInTheMiddle()
    {
        $this->markTestIncomplete();
        $this->connect('/customize/*options/:action',array('options'=>3));
        $this->get('/customize/blue/green/yellow/clone')        
             ->resolvesTo(array('blue','green','yellow'),'clone');
        $this->get('/customize/blue/green/yellow/magenta/clone')->doesntResolve();
        $this->get('/customize/blue/green/clone')               ->doesntResolve(); #fails
        #actual is
        $this->get('/customize/blue/green/clone')->resolvesTo(array('blue','green','clone'),'');
    }
    
    #bug
    function testVariableListAtTheBeginning()
    {
        $this->markTestIncomplete('BUG #168');
        $this->connect('*options/:action',array('controller'=>'options'));
        #both fail
        $this->get('blue/set')      ->resolvesTo(array('blue'),'set','options');
        $this->get('blue/green/set')->resolvesTo(array('blue','green'),'set','options');
        #actual is
        $this->get('blue/set')       ->resolvesTo(array('blue','set'),'','options');
        $this->get('blue/green/set') ->doesntResolve();
    }

    #bug
    function testVariablesListAtTheBeginningWithExpectedCount()
    {
        $this->markTestIncomplete('BUG #168');
        $this->connect('*options/:action',array('controller'=>'options','options'=>2));
        $this->get('blue/green/set')       ->resolvesTo(array('blue','green'),'set','options');
        $this->get('blue/green/yellow/set')->doesntResolve();
        $this->get('blue/set')             ->doesntResolve();  #fails
        #actual is
        $this->get('blue/set')      ->resolvesTo(array('blue','set'),'','options');
    }
    
    #bug
    function testRequirementShortcut()
    {
        $this->markTestIncomplete('BUG');
        // is this deprecated?
        $this->connect('/:controller/:action',array('controller'=>'list','action'=>'/[a-z]+/'));
        $this->get('/blog/123')->doesntResolve();
        $this->get('/blog/abc')->resolvesTo('blog','abc');
        $this->get('/')        ->resolvesTo('list');             # fails
        
        $this->get('/blog/[a-z]+')->doesntResolve();
        # but
        $this->get('/blog')->resolvesTo('blog','/[a-z] /');      # acts as if :action is optional with default
    }
    
    function testRequirementsShortcut()
    {
        $this->connect('/:controller/:action',array('controller'=>'/[a-z]+/','action'=>OPTIONAL));
        $this->get('/blog')       ->resolvesTo('blog','');
        $this->get('/blog/show')  ->resolvesTo('blog','show');
        $this->get('/123')        ->doesntResolve();
        $this->get('/123/show')   ->doesntResolve();
        $this->get('/')           ->doesntResolve();            # acts as if :controller is compulsory
        $this->get('/[a-z]+/show')->doesntResolve();
    }
    
    function testReq()
    {
        #$GLOBALS['__Kaste'] = true;
        $this->connect('/:controller/:action/:id',array('id'=>'recent'),array('id'=>'/\d+/'));
        $this->get('/blog/show/1')     ->resolvesTo('blog','show','1');
        $this->get('/blog/show/')      ->resolvesTo('blog','show','recent');
        $this->get('/blog/show/recent')->doesntResolve();
        #unset($GLOBALS['__Kaste']);
        
    }
    
    function testYearMonthDayRoute()
    {
        $this->connect('/:year/:month/:day',
            array(
                'controller' => 'articles',
                'action' => 'view_headlines',
                'year' => COMPULSORY,
                'month' => 'all',
                'day' => OPTIONAL),
            array(
                'year'=>'/(20){1}\d{2}/',
                'month'=>'/((1)?\d{1,2}){2}/',
                'day'=>'/(([1-3])?\d{1,2}){2}/')
        );
        $this->get('/2005/10/')->resolvesTo('2005','10','','articles','view_headlines');
        $this->get('/2006/')   ->resolvesTo('2006','all','','articles','view_headlines');
    }
}
?>