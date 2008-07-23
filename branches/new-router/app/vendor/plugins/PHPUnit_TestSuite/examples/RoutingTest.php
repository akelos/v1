<?php

class RoutingTestExample extends PHPUnit_Routing_TestCase 
{
    // if you dont setUp() anything the default map in your config/routes.php will be loaded
    // ->useMap($map_file) to load a different one
    
    function testStandardRoute()
    {
        // after parametrizing the url, check if the router 
        // can build the same given url from these parameters 
        $this->checkReciprocity(); 
        
        $this->get('/blog');
        $this->assertController('blog');
        $this->assertAction('index');
    }
    
    function test404()
    {
        $this->get('/should/not/match/anything/or');
        $this->assert404();
    }
    
    function testAddAndTestANewRoute()
    {
        $this->instantiateRouter(); # we need a fresh router here
        $this->connect('/:artist/:album',array('controller'=>'artist','action'=>'list'));

        $this->get('/autechre/quaristice')->resolvesTo('autechre','quaristice','artist','list');
        
        $this->get('/boys/girls');
        $this->assertController('artist');
        $this->assertAction('list');
        $this->assertArtist('boys');
        $this->assertAlbum('girls');
    }
    
}
?>