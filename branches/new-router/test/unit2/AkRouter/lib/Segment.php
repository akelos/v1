<?php

class Segment 
{
    public     $name;
    protected  $delimiter;
    public     $default;
    protected  $requirement;  //default requirement matches all but stops on dashes
    
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
        return !$this->isCompulsory();
    }
    
    function isCompulsory()
    {
        return $this->default === COMPULSORY;
    }
    
    function __toString()
    {
        return $this->getRegEx();
    }
    
}

?>