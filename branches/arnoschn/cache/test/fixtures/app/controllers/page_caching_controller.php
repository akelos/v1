<?php

class PageCachingController extends ApplicationController
{

    var $caches_page = array('index','ok','no_content','found','not_found','simple', 'priority',
                               'skip');

    var $caches_action = array('priority');
    
    function ok()
    {
        
        $this->renderNothing(200);
        
    }
    function index()
    {
        $this->renderText('index');
    }
    function priority()
    {
        $this->renderText($this->getAppliedCacheType());
    }
    
    function simple()
    {
        
        $this->renderText('Simple Text');
        
    }
    
    function skip()
    {
        $this->renderText('Hello<!--CACHE-SKIP-START-->
        
        You wont see me after the cache is rendered.
        
        <!--CACHE-SKIP-END-->');
    }
    
    function no_content()
    {
        $this->renderNothing(204);
    }
    function found()
    {
        $this->redirectToAction('ok');
    }
    
    function not_found()
    {
        $this->renderNothing(404);
    }
    
    function custom_path()
    {
        $this->renderText('Akelos rulez');
        $this->cachePage('Akelos rulez', '/index.html');
    }
    
    function expire_custom_path()
    {
        $this->expirePage('/index.html');
        $this->renderNothing(200);
    }
    
    function trailing_slash()
    {
        $this->renderText('Akelos');
    }
}