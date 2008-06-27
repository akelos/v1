<?php

class Route extends AkObject 
{

    private $url_pattern;
    private $defaults;
    private $requirements;
    private $regex;
    private $dynamic_segments = array();
    
    function __construct($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->url_pattern  = $url_pattern;    
        $this->defaults     = $defaults;
        $this->requirements = $requirements; 
    }
    
    function match(AkRequest $Request)
    {
        $url = '/'.trim($Request->getRequestedUrl(),'/').'/';
        #var_dump($url);
        
        $params = array();
        $result = preg_match($this->getRegex(),$url,$matches);
        array_shift($matches);
        #var_dump($matches);
        foreach ($matches as $i=>$match){
            if ($match = substr($match,1)) $params[$this->dynamic_segments[$i]] = $match;
        }
        foreach ($this->defaults as $name=>$value){
            if (!isset($params[$name])){
                $params[$name] = $value;
            }
        }
        return (bool)$result ? $params : false;
    }
    
    function getRegex()
    {
        if ($this->regex) return $this->regex;
        
        $segments = explode('/',trim($this->url_pattern,'/'));
        foreach ($segments as &$segment){
            if ($segment{0}==':'){
                $this->dynamic_segments[] = $name = substr($segment,1);
                if (isset($this->requirements[$name])){
                    $segment = "(/{$this->requirements[$name]})?";                    
                }else{
                    $segment = '(/[^/]*)?';
                }
            }else{
                $segment = '/'.$segment;
            }
        }
        
        $regex = '|^'.join('',$segments).'/?$|';
        #var_dump($regex);
        return $this->regex = $regex;        
    }
}

?>