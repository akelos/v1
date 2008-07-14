<?php
require_once(AK_LIB_DIR.DS.'AkUnitTest'.DS.'AkTestApplication.php');
require_once(AK_LIB_DIR.DS.'AkCache.php');

class Test_AkActionControllerCachingPages extends AkTestApplication
{

    var $lastModified;
    
    
    function testRequest()
    {
        $this->_flushCache('www.example.com');
        $this->setIp('212.121.121.121');
        $this->get('http://www.example.com/');
        $this->assertText('Test::page::index');
        $this->assertResponse(200);
    }
    
    function _flushCache($host)
    {
        $fileCache=AkCache::lookupStore('file');
        if ($fileCache!==false) {
            $fileCache->clean($host);
        }
    }
    
    function test_should_cache_get_with_ok_status()
    {
        $this->setIp('212.121.121.121');
        $this->get('http://www.example.com/page_caching/ok');

        $this->assertText('/^\s*$/');
        $this->assertResponse(200);
        $this->_assertPageCached('/page_caching/ok');
        
        
    }
    function _getCachedPage($path)
    {
        $controller=$this->getController();
        if ($controller) {
            $cachedPage = $controller->getCachedPage($path);
        } else {
            $pageCache = &Ak::singleton('AkActionControllerCachingPages',$null);
            $null = null;
            $pageCache->init($null, 'file');
            $cachedPage=$pageCache->getCachedPage($path);
        }
        return $cachedPage;
    }
    function _assertPageCached($path, $message = false)
    {
        $cachedPage = $this->_getCachedPage($path);
        $this->assertTrue($cachedPage!=false,$message);
        $this->assertIsA($cachedPage,'AkCachedPage');
    }
    function _assertPageNotCached($path, $message = '%s')
    {
        $cachedPage = $this->_getCachedPage($path);
        $this->assertTrue($cachedPage==false,$message);
    }
    function test_last_modified()
    {
        $this->setIp('212.121.121.121');
        $this->addIfModifiedSince('Sat, 12 Jul 2008 15:59:46 GMT');
        $this->get('http://www.example.com/page_caching/ok');
        $this->assertHeader('X-Cached-By','Akelos');
        $this->assertHeader('Last-Modified',null);
        $this->lastModified = $this->getHeader('Last-Modified');
    }
    
    function test_if_modified_since_304()
    {
        $this->setIp('212.121.121.121');
        $this->addIfModifiedSince($this->lastModified);
        $this->get('http://www.example.com/page_caching/ok');
        $this->assertHeader('X-Cached-By','Akelos');
        $this->assertResponse(304);
    }
    
    function test_should_cache_with_custom_path()
    {
        $this->setIp('212.121.121.121');
        $this->get('http://www.example.com/page_caching/custom_path');
        $this->assertText('Akelos rulez');
        $this->_assertPageCached('/index.html');
    }
    
    function test_should_expire_cache_with_custom_path()
    {
        $this->get('http://www.example.com/page_caching/custom_path');
        $this->_assertPageCached('/index.html');
        
        $this->get('http://www.example.com/page_caching/expire_custom_path');
        $this->_assertPageNotCached('/index.html');
    }
    
    function test_should_cache_without_trailing_slash_on_url()
    {
        $controller=$this->getController();
        $controller->cachePage('cached content', '/page_caching_test/trailing_slash');
        $this->_assertPageCached('/page_caching_test/trailing_slash.html');
    }
    
    function test_should_cache_with_trailing_slash_on_url()
    {
        $controller=$this->getController();
        $controller->cachePage('cached content', '/page_caching_test/trailing_slash/');
        $this->_assertPageCached('/page_caching_test/trailing_slash.html');
    }
    
    function test_caches_only_get_and_ok()
    {
        $methods = array('get','post','put','delete');
        $actions = array('ok','no_content','found','not_found');
        foreach ($actions as $action) {
            foreach ($methods as $method) {
                $path='/page_caching/'.$action;
                $this->$method($path);
                if ($this->getHeader('Status') == 200 && $method=='get') {
                    $this->_assertPageCached($path, 'action ok with GET request should be cached');
                } else {
                    $this->_assertPageNotCached($path,' action '.$action.' with '.strtoupper($method).' should not be cached');
                }
            }
        }
    }
}