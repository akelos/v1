<?php

class AkRouterTest extends PHPUnit_Routing_TestCase
{

    function setUp()
    {
        $this->instantiateRouter();    
        $this->testReciprocity();
    }
    
    function testToParams()
    {
        $this->connect('/lists/:action/:id/:option', array('controller'=>'todo','option'=>COMPULSORY));
        $input_value = '/lists/show/123/featured-1';
        $expected = array('controller'=>'todo','action'=>'show','id'=>123,'option'=>'featured-1');
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams2()
    {
        $this->connect('/lists/:action/:id/:option', array('controller'=>'todo','option'=>COMPULSORY));
        $this->connect('/:controller/:action/:id');
        $input_value = '/lists/show/123';
        $expected = array('controller'=>'lists','action'=>'show','id'=>123);
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams3()
    {
        $this->connect('/redirect/:url',array('controller'=>'redirect'));
        $input_value = '/redirect/'.urlencode('http://www.akelos.com/buscar_dominio');
        $expected = array('controller'=>'redirect','url'=>'http://www.akelos.com/buscar_dominio');
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams4()
    {
        $this->connect('/regex/:text/:int',array('text'=>'/[A-Za-z]+/','int'=>'/[0-9]+/','controller'=>'regex'));
        $input_value = '/regex/abc/123';
        $expected = array('controller'=>'regex','text'=>'abc','int'=>'123');
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams5()
    {
        $this->connect('/regex/:text/:int',array('text'=>'/[A-Za-z]+/','int'=>'/[0-9]+/','controller'=>'regex'));
        $input_value = '/regex/abc1/123';
        $not_expected = array('controller'=>'regex','text'=>'abc1','int'=>'123');
        $this->get($input_value)->doesntResolve();
    }
    
    function testToParams6()
    {
        $this->connect('/regex/:text/:int',array('text'=>'/[A-Za-z]+/','int'=>'/[0-9]+/','controller'=>'regex'));
        $not_expected = array('controller'=>'regex','text'=>'abc1','int'=>'123');
        $input_value = '/regex/abc/text';
        $not_expected = array('controller'=>'regex','text'=>'abc','int'=>'text');
        $this->get($input_value)->doesntResolve();

    }
    
    function testToParams7()
    {
        $this->connect('/:webpage', array('controller' => 'page', 'action' => 'view_page', 'webpage' => 'index'),array('webpage'=>'/(\w|_)+/'));
        $input_value = '/contact_us';
        $expected = array('controller'=>'page','action'=>'view_page','webpage'=>'contact_us');
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams8()
    {
        $this->connect('/:webpage', array('controller' => 'page', 'action' => 'view_page', 'webpage' => 'index'),array('webpage'=>'/(\w|_)+/'));
        $input_value = '/';
        $expected = array('controller'=>'page','action'=>'view_page','webpage'=>'index');
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams9()
    {
        $this->connect('/:webpage', array('controller' => 'page', 'action' => 'view_page', 'webpage' => 'index'),array('webpage'=>'/(\w|_)+/'));
        $input_value = '';
        $expected = array('controller'=>'page','action'=>'view_page','webpage'=>'index');
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams10()
    {
        $this->connect('/blog/:action/:id',array('controller'=>'post','action'=>'list','id'=>OPTIONAL, 'requirements'=>array('id'=>'/\d{1,}/')));
        $input_value = '/blog/';
        $expected = array('controller'=>'post','action'=>'list','id'=>null);
        $this->get($input_value)->resolvesTo($expected);


    }
    
    function testToParams11()
    {
        $this->connect('/blog/:action/:id',array('controller'=>'post','action'=>'list','id'=>OPTIONAL, 'requirements'=>array('id'=>'/\d{1,}/')));
        $input_value = '/blog/view';
        $expected = array('controller'=>'post','action'=>'view','id'=>null);
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams12()
    {
        $this->connect('/blog/:action/:id',array('controller'=>'post','action'=>'list','id'=>OPTIONAL, 'requirements'=>array('id'=>'/\d{1,}/')));
        $input_value = '/blog/view/10/';
        $expected = array('controller'=>'post','action'=>'view','id'=>'10');
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams13()
    {
        $this->connect('/blog/:action/:id',array('controller'=>'post','action'=>'list','id'=>OPTIONAL, 'requirements'=>array('id'=>'/\d{1,}/')));
        $this->connect('/:controller/:action/:id');
        $input_value = '/blog/view/newest/';
        $expected = array('controller'=>'blog','action'=>'view','id'=>'newest');
        $this->get($input_value)->resolvesTo($expected);

    }
    
    function testToParams14()
    {
        $this->connect('/:year/:month/:day',
            array('controller' => 'articles','action' => 'view_headlines','year' => COMPULSORY,'month' => 'all','day' => OPTIONAL) ,
            array('year'=>'/(20){1}\d{2}/','month'=>'/((1)?\d{1,2}){2}/','day'=>'/(([1-3])?\d{1,2}){2}/'));
        $input_value = '/2005/10/';
        $expected = array('controller' => 'articles','action' => 'view_headlines','year' => '2005','month' => '10', 'day' => null);
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams15()
    {
        $this->connect('/:year/:month/:day',
            array('controller' => 'articles','action' => 'view_headlines','year' => COMPULSORY,'month' => 'all','day' => OPTIONAL) ,
            array('year'=>'/(20){1}\d{2}/','month'=>'/((1)?\d{1,2}){2}/','day'=>'/(([1-3])?\d{1,2}){2}/'));
        $input_value = '/2006/';
        $expected = array('controller' => 'articles','action' => 'view_headlines','year' => '2006','month' => 'all', 'day' => null);
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams16()
    {
        $this->connect('/:controller/:action/:id');
        $input_value = '/user/list/12';
        $expected = array('controller' => 'user','action' => 'list','id' => '12');
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams17()
    {
        $this->connect('/setup/*config_settings',array('controller'=>'setup'));
        $input_value = '/setup/themes/clone/12/';
        $expected = array('controller' => 'setup','config_settings' => array('themes','clone','12'));
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams18()
    {
        $this->connect('/customize/*options/:action',array('controller'=>'themes','options'=>3));
        $input_value = '/customize/blue/css/sans_serif/clone/';
        $expected = array('controller' => 'themes','options' => array('blue','css','sans_serif'), 'action'=>'clone');
        $this->get($input_value)->resolvesTo($expected);
    }
    
    function testToParams19()
    {
        $this->connect('/customize/*options/:action',array('controller'=>'themes','options'=>3));
        $input_value = '/customize/blue/css/invalid/sans_serif/clone/';
        $not_expected = array('controller' => 'themes','options' => array('blue','css','invalid','sans_serif'), 'action'=>'clone');
        $this->get($input_value)->doesntResolve();
    }

    function _test_toUrl()
    {
        $input_value = array('controller'=>'page','action'=>'view_page','webpage'=>'index');
        $expected = '/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'page','action'=>'view_page','webpage'=>'contact_us');
        $expected = $this->url_prefix.'/contact_us/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'post','action'=>'list','id'=>null);
        $expected = $this->url_prefix.'/blog/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'post','action'=>'view','id'=>null);
        $expected = $this->url_prefix.'/blog/view/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'error','action'=>'database', 'id'=>null);
        $expected = $this->url_prefix.'/error/database/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'post','action'=>'view','id'=>'10');
        $expected = $this->url_prefix.'/blog/view/10/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'blog','action'=>'view','id'=>'newest');
        $expected = $this->url_prefix.'/blog/view/newest/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'blog','action'=>'view','id'=>'newest','format'=>'printer_friendly');
        $expected = AK_URL_REWRITE_ENABLED ? '/blog/view/newest/?format=printer_friendly' : '/?ak=/blog/view/newest/&format=printer_friendly';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller' => 'articles','action' => 'view_headlines','year' => '2005','month' => '10', 'day' => null);
        $expected = $this->url_prefix.'/2005/10/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller' => 'articles','action' => 'view_headlines','year' => '2006','month' => 'all', 'day' => null);
        $expected = $this->url_prefix. '/2006/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller' => 'user','action' => 'list','id' => '12');
        $expected = $this->url_prefix.'/user/list/12/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller' => 'setup','config_settings' => array('themes','clone','12'));
        $expected = $this->url_prefix.'/setup/themes/clone/12/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller' => 'themes','options' => array('blue','css','sans_serif'), 'action'=>'clone');
        $expected = $this->url_prefix.'/customize/blue/css/sans_serif/clone/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

    }

    function test_url_with_optional_variables()
    {
        $this->connect('/topic/:id', array('controller' => 'topic', 'action'=>'view', 'id'=>COMPULSORY), array('id'=>'[0-9]+'));
        $this->connect('/topic/:id/unread', array('controller' => 'topic','action'=>'unread','id'=>COMPULSORY), array('id'=>'[0-9]+'));
        
        $input_value = array('controller'=>'topic','action'=>'view', 'id'=>4);
        $expected = '/topic/4/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'topic','action'=>'unread', 'id'=>4);
        $expected = '/topic/4/unread/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

    }
}

class Test_for_default_routes extends PHPUnit_Routing_TestCase
{

    function setUp()
    {
        $this->instantiateRouter();

        $this->connect('/:controller/:action/:id', array('controller' => 'page', 'action' => 'index'));
        $this->connect('/', array('controller' => 'page', 'action' => 'index'));
    }


    function test_connect()
    {
        $this->assertEquals(count($this->Router->getRoutes()) , 2,'Wrong number of routes loaded. We expected 12');
    }

    function test_toUrl()
    {
        $input_value = array('controller'=>'page','action'=>'listing');
        $expected = '/page/listing/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);
    }

}

# Fixes issue 27 reported by Jacek Jedrzejewski
class Tests_for_url_constants_named_as_url_variables extends PHPUnit_Routing_TestCase
{

    function setUp()
    {
        $this->instantiateRouter();
    }

    function test_same_pieces_1()
    {
        $this->connect('/foo/id/:id', array('controller'=>'some'), array('id'=>'[0-9]+'));
        $this->get('/foo/id/1')->resolvesTo(array('controller'=>'some', 'id'=>'1'));
    }

    function test_same_pieces_4()
    {
        $this->connect('/foo/bar/*bar', array('controller'=>'some'));
        $this->get('/foo/bar/foobar')
             ->resolvesTo(array('bar'=>array(0=>'foobar'),'controller'=>'some'));
        $this->get('/foo/bar/foobar/foobar2')
             ->resolvesTo(array('controller'=>'some', 'bar'=>array(0=>'foobar',1=>'foobar2')));
    }

    function test_same_pieces_5()
    {
        $this->connect('/foo/bar/*bar', array('controller'=>'some', 'bar'=>1));
        $this->get('/foo/bar/foobar')->resolvesTo(array('controller'=>'some', 'bar'=>array(0=>'foobar')));
    }

    function test_same_pieces_6()
    {
        $this->connect('/foo/:bar',	array('variable'=>'ok'));
        $this->connect('/baz/:bar',	array('variable2'=>'ok', 'bar'=>COMPULSORY));
        $this->connect('/:controller');

        $this->get('/foo/baz')->resolvesTo(array('variable'=>'ok','bar'=>'baz'));
        $this->get('/abc')    ->resolvesTo(array('controller'=>'abc'));
        $this->get('/fooabc') ->resolvesTo(array('controller'=>'fooabc'));
        $this->get('/baz/bar')->resolvesTo(array('variable2'=>'ok','bar'=>'bar'));
        $this->get('/bazabc') ->resolvesTo(array('controller'=>'bazabc'));
    }
}


class Test_for_middle_optional_values_when_generating_urls extends  PHPUnit_Routing_TestCase 
{

    function test_middle_values()
    {
        $this->instantiateRouter();
        $this->connect('/news/feed/:type/:category',
            array('controller'=>'news','action'=>'feed','type'=>'atom','category'=>'all'));


        $input_value = array('controller'=>'news','action'=>'feed','type'=>'atom','category'=>'foobar');
        $expected = '/news/feed/atom/foobar/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);

        $input_value = array('controller'=>'news','action'=>'feed');
        $expected = '/news/feed/';
        $this->assertEquals($this->Router->toUrl($input_value),$expected);
    }
}



class Test_router_conflicts extends PHPUnit_Routing_TestCase 
{

    function test_should_allow_variables_with_slashes()
    {
        $this->instantiateRouter();
        $this->connect('/:controller/:action/:value');
        $this->testReciprocity();

        $this->get('/page/redirect/http%3A%2F%2Fakelos.org%2Fdownload%2F/')
             ->resolvesTo(array('controller'=>'page','action'=>'redirect', 'value'=>'http://akelos.org/download/'));
        #$this->assertEqual($this->Router->toUrl($params), $url);
        #$this->assertEqual($this->Router->toParams($this->url_prefix.$url), $params);
    }

    function _test_should_trigger_error_on_forbidden_router_variable()
    {
        $this->instantiateRouter();
        $this->connect('/:this');
        #$this->assertErrorPattern('/reserved word this/');
    }
}

?>
