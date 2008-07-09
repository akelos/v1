<?php

class AkUrl
{

    private $path;
    private $query_string;
    private $rewrite_enabled = AK_URL_REWRITE_ENABLED;
    
    function __construct($path,$query_string = '')
    {
        $this->path = $path;
        $this->query_string = $query_string;    
    }
    
    function setRewriteEnabled($enable=true)
    {
        $this->rewrite_enabled = $enable;
    }
    
    function path()
    {
        $prefix = $this->rewrite_enabled ? ''  : '/?ak=';
        $concat = $this->query_string ? ($this->rewrite_enabled ? '?' : '&') : '';
        $path = $prefix.$this->path.$concat.$this->query_string;
        return $path;
    }
    
    function __toString()
    {
        return $this->path();
    }
}

?>