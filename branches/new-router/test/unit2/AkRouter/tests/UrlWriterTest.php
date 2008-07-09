<?php
require_once 'UrlWriter_TestCase.php';

class UrlWriterTest extends UrlWriter_TestCase
{
    
    function testUsesControllerGivenRequest()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array('action'=>'list'))->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }

    
}

?>