<?php
require_once 'UrlWriter_TestCase.php';

class UrlWriterTest extends UrlWriter_TestCase
{
    
    function testUsesControllerFromGivenRequest()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array('action'=>'list'))->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }
    
    function testFiltersSetOptions()
    {
        $keywords = array('anchor', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root');
        
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array_flip($keywords))
             ->isRewrittenTo(array('controller'=>'author'));
        
    }
    
    function testAlgoExtractOptions()
    {
        $keywords = array('anchor', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root');
        $asked = array('only_path'=>true,'anchor'=>'blub','name'=>'blib','id'=>1);
        
        $options = array_intersect_key($asked,array_flip($keywords));
        $asked   = array_diff_key($asked,$options);
        
        #var_dump($asked);
        #var_dump($options);
        $this->assertEquals(array('name'=>'blib','id'=>'1'),$asked);
        $this->assertEquals(array('only_path'=>true,'anchor'=>'blub'),$options);
    }

    
}

?>