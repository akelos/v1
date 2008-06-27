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
        
        if (!preg_match($this->getRegex(),$url,$matches)) return false;

        $params = array();
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
        return $params;
    }
    
    function getRegex()
    {
        if ($this->regex) return $this->regex;
        
        $segments = explode('/',trim($this->url_pattern,'/'));
        foreach ($segments as &$segment){
            if ($this->isVariableSegment($segment)){
                $this->dynamic_segments[] = $name = substr($segment,1);
                $segment = "(/{$this->innerRegExFor($name)})?";
            }else{
                $segment = '/'.$segment;
            }
        }
        
        $regex = '|^'.join('',$segments).'/?$|';
        #var_dump($regex);
        return $this->regex = $regex;        
    }
    
    function innerRegExFor($name)
    {
        if (isset($this->requirements[$name])) return $this->requirements[$name];
        return '[^/]*';  //default requirement matches all but stops on dashes
    }
    
    function isVariableSegment($name)
    {
        if ($name{0}==':') return true;
        return false;
    }
}

?>