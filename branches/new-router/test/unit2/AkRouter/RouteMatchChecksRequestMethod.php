<?php
require_once 'Route_TestCase.php';

class RouteMatchChecksRequestMethod extends Route_TestCase 
{

    function testMatchesPostRequest()
    {
        $this->withRoute('/author/:name',array(),array(),array('method'=>'post'));
        
        $this->get('/author/martin','post')->matches(array('name'=>'martin'));
        $this->get('/author/martin','get') ->doesntMatch();
    }
    
    function testMatchesAnyRequestedMethod()
    {
        $this->withRoute('/author/:name',array(),array(),array('method'=>ANY));
        
        $this->get('/author/martin','get')->matches(array('name'=>'martin'));
        $this->get('/author/martin','post')->matches(array('name'=>'martin'));
        $this->get('/author/martin','delete')->matches(array('name'=>'martin'));
        $this->get('/author/martin','put')->matches(array('name'=>'martin'));
        $this->get('/author/martin','head')->matches(array('name'=>'martin'));
    }
    
    function testMatchesPostAndPutRequest()
    {
        $this->withRoute('/author/:name',array(),array(),array('method'=>'post,put'));
        
        $this->get('/author/martin','post')->matches(array('name'=>'martin'));
        $this->get('/author/martin','put') ->matches(array('name'=>'martin'));
        $this->get('/author/martin','get') ->doesntMatch();
    }
    
}

?>