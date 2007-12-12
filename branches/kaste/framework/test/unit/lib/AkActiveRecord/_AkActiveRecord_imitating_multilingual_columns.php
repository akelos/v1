<?php

defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
require_once(dirname(__FILE__).'/../../../fixtures/config/config.php');

class test_AkActiveRecord_imitating_multilingual_columns extends AkUnitTest
{
    /**
     * @var InternationalPost
     */
    var $InternationalPost;
    
    function setUp()
    {
    	$this->installAndIncludeModels(array('InternationalPost'=>'id,en_title,de_title'));
    }
    
    function test_should_ensure_installer_didnt_added_some_columns()
    {
        $expected = array('id','en_title','de_title');
        $got = array_keys($this->InternationalPost->getColumnNames());
        $this->assertEqual($expected,$got);
    }
    
    function test_should_set_the_titles()
    {
        $Post =& new InternationalPost();
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post->set('titles',$titles);
        
        $this->assertEqual($Post->en_title,'Something');
        $this->assertEqual($Post->getAttribute('en_title'),$Post->en_title);
        $this->assertEqual($Post->de_title,'Etwas');
        $this->assertEqual($Post->getAttribute('de_title'),$Post->de_title);
    }
    
    function test_should_save_titles_in_multiple_columns()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost(array('titles'=>$titles));
        $Post->save();
        
        $Reloaded = $this->InternationalPost->find($Post->getId());
        
        $post_attributes     = $Post->getAttributes();
        $reloaded_attributes = $Reloaded->getAttributes();
        unset($post_attributes['id']);
        unset($reloaded_attributes['id']);
        $this->assertEqual($post_attributes,$reloaded_attributes);
    }
    
    function test_should_get_the_titles_as_an_array()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost(array('titles'=>$titles));
        $Post->save();
        
        $Reloaded = $this->InternationalPost->find($Post->getId());

        $loaded_titles = $Reloaded->getTitles();
        $this->assertEqual($loaded_titles,$titles);
    }

    function test_should_get_the_current_title_according_to_the_locale()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost(array('titles'=>$titles));
        $Post->save();
        
        $Reloaded = $this->InternationalPost->find($Post->getId());
        
        $current_lang = $this->InternationalPost->getCurrentLocale();
        $current_title = $Reloaded->getTitle();
        $this->assertEqual($current_title,$titles[$current_lang]);
        
    }
    
    function test_should_set_only_the_current_title_according_to_the_locale()
    {
        $current_lang = $this->InternationalPost->getCurrentLocale();
        $Post =& new InternationalPost();
        $Post->set('title','Something');
        
        $this->assertEqual($Post->get('en_title'),'Something');
    }
    
    /* 1st refactoring */
    function test_should_set_international_columns_array()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost();
        $Post->setInternationalizedColumnsFromArray('title',$titles);
        
        $this->assertEqual($Post->en_title,'Something');
        $this->assertEqual($Post->getAttribute('en_title'),$Post->en_title);
        $this->assertEqual($Post->de_title,'Etwas');
        $this->assertEqual($Post->getAttribute('de_title'),$Post->de_title);
    }
    
    function test_should_ignore_locales_for_which_we_dont_have_columns()
    {
        $titles = array('es'=>'qu','jp'=>'haiku');
        $Post =& new InternationalPost();
        $Post->setInternationalizedColumnsFromArray('title',$titles);
        $this->assertFalse(isset($Post->es_title));
        $this->assertNull($Post->getAttribute('es_title'));
        $this->assertNull($Post->getAttribute('jp_title'));
    }
    
    function test_should_save_internationalized_column_in_multiple_column()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost();
        $Post->setInternationalizedColumnsFromArray('title',$titles);
        $Post->save();
        
        $Reloaded =& $this->InternationalPost->find($Post->getId());

        $post_attributes     = $Post->getAttributes();
        $reloaded_attributes = $Reloaded->getAttributes();
        unset($post_attributes['id']);
        unset($reloaded_attributes['id']);
        $this->assertEqual($post_attributes,$reloaded_attributes);
    }

    function test_should_get_all_internationalized_columns_in_one_array()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost();
        $Post->setInternationalizedColumnsFromArray('title',$titles);
        $Post->save();
        
        $Reloaded =& $this->InternationalPost->find($Post->getId());
        
        $reloaded_titles = $Reloaded->getInternationalizedColumnsArray('title');
        $this->assertEqual($reloaded_titles,$titles);
    }
    
    function test_should_get_the_current_locale_of_a_internationalized_column()
    {
        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost();
        $Post->setInternationalizedColumnsFromArray('title',$titles);
        $Post->save();
        
        $Reloaded =& $this->InternationalPost->find($Post->getId());
        $current_locale = $this->InternationalPost->getCurrentLocale();
        $title = $Reloaded->getCurrentLocaleFromInternationalizedColumn('title');
        $this->assertEqual($title,$titles[$current_locale]);        
    }
    
    function test_should_set_the_column_according_to_the_current_locale_setting()
    {
        $current_locale = $this->InternationalPost->getCurrentLocale();
        $Post =& new InternationalPost();
        $Post->setCurrentLocaleFromInternationalizedColumn('title','Something');
        
        $this->assertEqual($Post->get('en_title'),'Something');
        
    }

    /* 2nd refactoring */
    function test_should_ensure_that_we_have_a_internationalized_column()
    {
        $this->InternationalPost->addVirtualAttributesForInternationalizedColumn('title');
        $this->assertFalse(empty($this->InternationalPost->_internationalizedColumns));
    }
    
    function test_should_cache_available_locales_for_each_column()
    {
        $this->assertTrue (empty($this->InternationalPost->_internationalizedColumns['title']));

        $available_locale = array('en','de');
        $this->assertEqual($this->InternationalPost->_getAvailableLocaleForColumn('title'),$available_locale);
        $this->assertFalse(empty($this->InternationalPost->_internationalizedColumns['title']));
    }
    
    function test_should_singularize_attribute_names()
    {
        $this->assertEqual($this->InternationalPost->_getSingularAttributeName('bodies'),'body');
        $this->assertFalse($this->InternationalPost->_getSingularAttributeName('body'));
    }
    
    function test_automatic_setters_for_internationalized_columns()
    {
        $this->installAndIncludeModels(array('InternationalPost'=>'id,en_title,de_title,en_body,de_body'));
        $this->InternationalPost->addVirtualAttributesForInternationalizedColumn('body');
        $this->assertFalse(empty($this->InternationalPost->_internationalizedColumns));
        
        $available_locale = array('en','de');
        $this->assertEqual($this->InternationalPost->_getAvailableLocaleForColumn('body'),$available_locale);
        
        $Post =& new InternationalPost();
        $Post->set('body','Something');
        $this->assertEqual($Post->en_body,'Something');
        $this->assertEqual($Post->get('body'),'Something');

        $titles = array('en'=>'Something','de'=>'Etwas');
        $Post =& new InternationalPost();
        $Post->set('bodies',$titles);
        $this->assertEqual($Post->en_body,'Something');
        $this->assertEqual($Post->get('body'),'Something');
        
    }
    
    
}

ak_test('test_AkActiveRecord_imitating_multilingual_columns',true);

?>