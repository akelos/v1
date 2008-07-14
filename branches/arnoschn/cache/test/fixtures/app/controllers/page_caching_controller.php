<?php

class PageCachingController extends ApplicationController
{
    var $perform_caching=true;
    var $caches_page = array('ok','no_content','found','not_found');
    var $cache_store = 'file';
    function ok()
    {
        
        $this->renderNothing(200);
        
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