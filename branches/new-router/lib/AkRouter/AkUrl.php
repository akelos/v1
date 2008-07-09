<?php

class AkUrl
{

    private $path;
    private $query_string;
    private $Request;
    private $rewrite_enabled = AK_URL_REWRITE_ENABLED;
    private $options = array(
        'trailing_slash'=>false,
        'skip_relative_url_root'=>false,
        'only_path'=>false
    );
    static $common_values = array(
        'relative_url_root'=>'',
        'protocol'=>'',
        'host_with_port'=>''
    );
    
    function __construct($path,$query_string = '')
    {
        $this->path = $path;
        $this->query_string = $query_string;    
    }
    
    static function setCommonValuesFromRequest(AkRequest $Request)
    {
        self::$common_values = array(
            'relative_url_root'=>$Request->getRelativeUrlRoot(),
            'protocol'=>$Request->getProtocol(),
            'host_with_port'=>$Request->getHostWithPort()
        );
        
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
        $options = $this->options;
        
        $path = '';
        $path .= $options['skip_relative_url_root'] ? '' : self::$common_values['relative_url_root'];
        $path .= $this->rewrite_enabled ? '' : '/?ak=';
        $path .= $this->path;
        $path .= $options['trailing_slash'] ? '/' : '';
        $path .= $this->query_string ? ($this->rewrite_enabled ? '?' : '&') : '';
        $path .= $this->query_string;
        $path .= empty($options['anchor']) ? '' : '#'.$options['anchor'];
        
        return $path;
    }
    
    function url()
    {
        $options = $this->options;
        
        $rewritten_url = '';
        $rewritten_url .= !empty($options['protocol']) ? $options['protocol'] : self::$common_values['protocol'];
        $rewritten_url .= empty($rewritten_url) || strpos($rewritten_url,'://') ? '' : '://';
        $rewritten_url .= $this->rewriteAuthentication($options);
        $rewritten_url .= !empty($options['host']) ? $options['host'] : self::$common_values['host_with_port'];
        
        return $rewritten_url.$this->path();
    }

    private function rewriteAuthentication($options)
    {
        if(!isset($options['user']) && isset($options['password'])){
            return urlencode($options['user']).':'.urlencode($options['password']).'@';
        }else{
            return '';
        }
    }
    
    function __toString()
    {
        return $this->options['only_path'] ? $this->path() : $this->url();
    }
}

?>