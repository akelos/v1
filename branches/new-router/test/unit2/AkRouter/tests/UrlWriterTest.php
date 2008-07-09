<?php
require_once 'UrlWriter_TestCase.php';

class UrlWriterTest extends UrlWriter_TestCase
{

    function testUseLastRequestToFillParameters()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array())->isRewrittenTo(array('controller'=>'author','action'=>'show','name'=>'martin'));        
    }
    
    function testAGivenParameterOverridesTheOldOne()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show'));
        $this->urlFor(array('action'=>'list'))->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }
    
    function testDontFillBeyondAGivenParameter()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array('action'=>'list'))->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }
    
    function testAParameterSetToNullWillBeUnset()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array('action'=>null))->isRewrittenTo(array('controller'=>'author'));
    }
    
    function testOverwriteParametersOptionShouldNotStopTheFilling()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','name'=>'martin'));
        $this->urlFor(array('overwrite_params'=>array('action'=>'edit')))
             ->isRewrittenTo(array('controller'=>'author','action'=>'edit','name'=>'martin'));        
    }
    
    function testFiltersSetOptions()
    {
        $keywords = array('anchor', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root');
        
        $this->withRequestTo(array('controller'=>'author'));
        $this->urlFor(array_flip($keywords))
             ->isRewrittenTo(array('controller'=>'author'));
    }
    
    function testFiltersLangSettingByDefault()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','lang'=>'en'));
        $this->urlFor(array('action'=>'list'))
             ->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }
    
    function testPassThroughLangSettingIfOptionIsSet()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show','lang'=>'en'));
        $this->urlFor(array('action'=>'list','skip_url_locale'=>false))
             ->isRewrittenTo(array('controller'=>'author','action'=>'list','lang'=>'en'));
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