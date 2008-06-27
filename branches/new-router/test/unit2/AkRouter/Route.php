<?php
define('COMPULSORY','COMPULSORY');

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
        $url = $Request->getRequestedUrl();
        #var_dump($url);
        
        if (!preg_match($this->getRegex(),$url,$matches)) return false;
        array_shift($matches);   //throw away the "all-match", we only need the groups
        #var_dump($matches);

        $params = array();
        foreach ($matches as $i=>$match){
            if (empty($match)) continue;  
            $params[$this->dynamic_segments[$i]] = $match;
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
                $optional_switch = $this->isOptional($name) ? '?': '';
                $segment = "(?:/({$this->innerRegExFor($name)}))$optional_switch";
            }else{
                $segment = '/'.$segment;
            }
        }
        
        $regex = '|^'.join('',$segments).'$|';
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
        if ($name && $name{0}==':') return true;
        return false;
    }
    
    function isOptional($name)
    {
        if (isset($this->defaults[$name]) && $this->defaults[$name]===COMPULSORY) return false;
        return true;
    }
}

?>