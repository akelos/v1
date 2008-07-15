<?php

require_once('_HelpersUnitTester.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'cache_helper.php');
require_once(AK_LIB_DIR.DS.'AkActionView.php');
require_once(AK_LIB_DIR.DS.'AkActionController.php');
require_once(AK_LIB_DIR.DS.'AkRequest.php');

ak_generate_mock('AkRequest');


class CacheHelperTests extends HelpersUnitTester 
{
    var $fragment_key;
    
    function setUp()
    {
        $this->controller = &new AkActionController();
        $this->controller->_initCacheHandler();
        $this->controller->Request =& new MockAkRequest($this);
        $this->controller->controller_name = 'test';
        $this->controller->instantiateHelpers();

        $this->cache_helper =& $this->controller->cache_helper;
        
        
    }
    function test_init()
    {
        $this->fragment_key = 'key_'.time()+microtime(true);
        $this->fragment_text = "Test Cache Helper With String Key";
    }
    function test_cache_with_string_key()
    {
        if (!$this->cache_helper->begin($this->fragment_key)) {
            $this->assertTrue(true);
            echo $this->fragment_text;
            $this->cache_helper->end($this->fragment_key);
        } else {
            $this->assertFalse(true,'Should not have been cached');
        }
        $fragment = $this->controller->readFragment($this->fragment_key);
        $this->assertEqual($this->fragment_text, $fragment);
    }

    function test_cache_with_string_key_cached()
    {
        ob_start();
        if (!$this->cache_helper->begin($this->fragment_key)) {
            $this->assertFalse(true,'Should have been cached');
            echo $this->fragment_text;
            $this->cache_helper->end($this->fragment_key);
        } else {
            $this->assertTrue(true);
        }
        $output = ob_get_clean();
        $fragment = $this->controller->readFragment($this->fragment_key);
        $this->assertEqual($this->fragment_text, $fragment);
        $this->assertEqual($this->fragment_text, $output);
    }
}


ak_test('CacheHelperTests');

?>