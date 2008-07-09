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
    
    function testSplitGivenControllerIntoModuleAndControllerPart()
    {
        $this->withRequestTo(array('controller'=>'author','action'=>'show'));
        $this->urlFor(array('controller'=>'admin/user'))
             ->isRewrittenTo(array('module'=>'admin','controller'=>'user'));
    }
    
    function testFiltersSetOptions()
    {
        $keywords = array('anchor', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root');
        
        $this->withRequestTo(array('controller'=>'author'));
        $this->urlFor(array_flip($keywords))
             ->isRewrittenTo(array('controller'=>'author'));
    }
    
    function testPassThroughLangSettingByDefault()
    {
        $this->withRequestTo(array('lang'=>'en','controller'=>'author','action'=>'show'));
        $this->urlFor(array('action'=>'list'))
             ->isRewrittenTo(array('lang'=>'en','controller'=>'author','action'=>'list'));
    }
    
    function testPassThroughLangSettingIfOptionIsFalse()
    {
        $this->withRequestTo(array('lang'=>'en','controller'=>'author','action'=>'show'));
        $this->urlFor(array('action'=>'list','skip_url_locale'=>false))
             ->isRewrittenTo(array('lang'=>'en','controller'=>'author','action'=>'list'));
    }

    function testFilterLangSettingIfOptionIsTrue()
    {
        $this->withRequestTo(array('lang'=>'en','controller'=>'author','action'=>'show'));
        $this->urlFor(array('action'=>'list','skip_url_locale'=>true))
             ->isRewrittenTo(array('controller'=>'author','action'=>'list'));
    }
    
    function testOnlyUseSpecifiedParametersFromOldRequest()
    {
        $this->withRequestTo(array('lang'=>'en','controller'=>'author','action'=>'show'));
        $this->urlFor(array('skip_old_parameters_except'=>array('controller')))
             ->isRewrittenTo(array('controller'=>'author'));
    }
    
    function testUseNamedRouteIfSpecified()
    {
        $this->withRequestTo(array('lang'=>'en','controller'=>'author','action'=>'show'));
        $asked_url_for_parameters = array('lang'=>'es','use_named_route'=>'default');
        $rewritten_parameters     = array('lang'=>'es');
        
        $args = array($rewritten_parameters,'default');
        $Router = $this->createRouter('urlize',$args);
        $UrlWriter = new AkUrlWriter($this->Request,$Router);
        $UrlWriter->urlFor($asked_url_for_parameters);
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