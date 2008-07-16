<?php

class RESTFulPeopleServiceFunctional extends PHPUnit_Framework_TestCase
{
    
    function testIndexRespondsWithXmlContent()
    {
        $Http = new AkHttpClient();
        $options = array('header'=>array('accept'=>'text/xml'));
        $result = $Http->get(AK_TESTING_URL.'/people',$options);
        $headers = $Http->getResponseHeaders();
        
        $this->assertEquals('text/xml',$headers['content-type']);
        #var_dump($Http->getResponseHeaders());
        #var_dump($result);
    }
    
    function testArrayToXml()
    {

    }
    
    function testXmlToArray()
    {
        
    }
}

?>