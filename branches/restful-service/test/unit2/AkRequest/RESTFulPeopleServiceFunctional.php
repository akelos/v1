<?php

/**
 * @group slow
 */
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
    
    function testPutPersonOnTheServerViaXml()
    {
        $person = '<person><name>Steve</name></person>';
        $Http = new AkHttpClient();
        $options = array('header'=>array(
            'content-type'=>'text/xml',
        ));
        $result = $Http->put(AK_TESTING_URL.'/person/1',$options,$person);
        $this->assertEquals('Steve',$result);
    }
    
    function testPutPersonOnTheServerViaWwwForm()
    {
        $person = array('person'=>array('name'=>'Steve'));
        $Http = new AkHttpClient();

        $options['params'] = $person;
        $result = $Http->put(AK_TESTING_URL.'/person/1',$options);
        $this->assertEquals('Steve',$result);
    }

    function testPostPersonOnTheServerViaXml()
    {
        $person = '<person><name>Steve</name></person>';
        $Http = new AkHttpClient();
        $options = array('header'=>array(
            'content-type'=>'text/xml',
        ));
        $result = $Http->post(AK_TESTING_URL.'/person',$options,$person);
        $this->assertEquals('Steve',$result);
    }
    
    function testPostPersonOnTheServerViaWwwForm()
    {
        $person = array('person'=>array('name'=>'Steve'));
        $Http = new AkHttpClient();

        $options['params'] = $person;
        $result = $Http->post(AK_TESTING_URL.'/person',$options);
        $this->assertEquals('Steve',$result);
    }
    
    function testFileUpload()
    {
        $Http = new AkHttpClient();
        $options['params'] = array('photo'=>array('title'=>'My Photo.'));
        $options['file'] = array('inputname'=>'photo','filename'=>__FILE__);
        $result = $Http->post(AK_TESTING_URL.'/person/1/photo',$options);
        
        $this->assertEquals("My Photo.|".basename(__FILE__),$result);
    }
    
}

?>