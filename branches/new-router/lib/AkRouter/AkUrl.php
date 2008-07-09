<?php

class AkUrl
{

    private $path;
    private $query_string;
    private $Request;
    private $rewrite_enabled = AK_URL_REWRITE_ENABLED;
    private $options = array('trailing_slash'=>false);
    
    function __construct($path,$query_string = '')
    {
        $this->path = $path;
        $this->query_string = $query_string;    
    }
    
    function setRequest($Request)
    {
        $this->Request = $Request;
    }
    
    function setRewriteEnabled($enable=true)
    {
        $this->rewrite_enabled = $enable;
    }
    
    function setOptions($options)
    {
        $this->options = array_merge($this->options,$options);
    }
    
    function path()
    {
        $path = '';
        $path .= empty($this->options['skip_relative_url_root']) ? $this->Request->getRelativeUrlRoot() : '';
        $path .= $this->rewrite_enabled ? '' : '/?ak=';
        $path .= $this->path;
        $path .= empty($this->options['trailing_slash']) ? '' : '/';
        $path .= $this->query_string ? ($this->rewrite_enabled ? '?' : '&') : '';
        $path .= $this->query_string;
        $path .= empty($this->options['anchor']) ? '' : '#'.$this->options['anchor'];
        
        return $path;
    }
    
    function __toString()
    {
        return $this->path();
    }
}

?>