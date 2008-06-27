<?php

class Segment 
{
    public  $name;
    private $delimiter;
    public  $default;
    private $requirement;  //default requirement matches all but stops on dashes
    
    static  $DEFAULT_REQUIREMENT='[^/]*';  //default requirement matches all but stops on dashes
    
    function __construct($name,$delimiter,$default=null,$requirement=null)
    {
        $this->name        = $name;
        $this->delimiter   = $delimiter;
        $this->default     = $default;
        $this->requirement = $requirement;
    }
    
    function hasRequirement()
    {
        return $this->requirement ? true : false;
    }
    
    function getInnerRegEx()
    {
        if ($this->hasRequirement()) return $this->requirement;
        return self::$DEFAULT_REQUIREMENT;
    }
    
    function isOptional()
    {
        return !$this->default || $this->default !== COMPULSORY; 
    }
    
    function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        return "(?:$this->delimiter({$this->getInnerRegEx()}))$optional_switch";
    }
    
    function __toString()
    {
        return $this->getRegEx();
    }
    
    function meetsRequirement($value)
    {
        if (!$this->hasRequirement()) return true;
        
        $regex = "|^{$this->getInnerRegEx()}$|";
        return (bool) preg_match($regex,$value);
    }
    
    function insertPieceForUrl($value)
    {
        return $this->delimiter.$value;
    }
}
?>