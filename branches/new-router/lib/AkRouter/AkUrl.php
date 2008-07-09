<?php

class AkUrl
{

    private $path;
    private $query_string;
    private $rewrite_enabled = AK_URL_REWRITE_ENABLED;
    private $options = array('trailing_slash'=>false);
    
    function __construct($path,$query_string = '')
    {
        $this->path = $path;
        $this->query_string = $query_string;    
    }
    
    function setRewriteEnabled($enable=true)
    {
        $this->rewrite_enabled = $enable;
    }
    
    function setOptions($options)
    {
        $this->options = array_merge($this->options,$options);
    }
    
    private function trailing_slash()
    {
        return $this->options['trailing_slash'] ? '/' : '';
    }
    
    private function query_string()
    {
        $concat = $this->query_string ? ($this->rewrite_enabled ? '?' : '&') : '';
        return $concat.$this->query_string;
    }
    
    function path()
    {
        $prefix = $this->rewrite_enabled ? ''  : '/?ak=';
        $path = $prefix.$this->path.$this->trailing_slash().$this->query_string();
        return $path;
    }
    
    function __toString()
    {
        return $this->path();
    }
}

?>