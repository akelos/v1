<?php
require_once dirname(dirname(__FILE__)).DIRECTORY_SEPARATOR.'lib'.DIRECTORY_SEPARATOR.'PHPUnit_Akelos.php';

class RoutingTest extends PHPUnit_Routing_TestCase 
{

    function setUp()
    {
        $this->useMap(dirname(__FILE__).DS.'fixtures'.DS.'routes.php');
    }
    
    function testShouldResolveUrl()
    {
        $this->get('blog/add/1');

        $this->assertController('blog');
        $this->assertAction('add');
        $this->assertId(1);
    }
    
    function testShouldResolveToAdminModule()
    {
        $this->get('/admin/user/add/');

        $this->assertParameterEquals('admin','module');
        $this->assertController('user');
        $this->assertParameterNotSet('id');
    }
    
    function testRouteToAdminLogs()
    {
        $this->get('/admin/logs/warnings/show/1');
        $this->assertModule('admin/logs');
        $this->assertController('warnings');
        $this->assertAction('show');
        $this->assertId(1);
    }
    
    function testRouteToArtistAlbumTags()
    {
        $this->get('autechre/quaristice/tags');
        $this->assertController('tags');
        $this->assertArtist('autechre');
        $this->assertAlbum('quaristice');
    }
    
    function testAssertIdIsNotSet()
    {
        $this->get('blog/add/');
        $this->assertParameterNotSet('id');
    }
    
    function testAssertUnrecognizedUrl()
    {
        $this->get('blog/post/something/here/or/leave');
        $this->assert404();
    }
    
    function testShouldMoanAboutWrongController()
    {
        $this->get('blog');
        try {
            $this->assertController('post_or_whatever');
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e){
            $this->assertRegExp('/^Expected controller.*/',$e->getDescription());
            return;
        }
        $this->fail();
    }
    
    function testShouldMoanAboutWrongAction()
    {
        $this->get('blog/add');
        try {
            $this->assertAction('remove_or_whatever');
        }
        catch (PHPUnit_Framework_ExpectationFailedException $e){
            $this->assertRegExp('/^Expected action.*/',$e->getDescription());
            return;
        }
        $this->fail();
    }
    
}

?>