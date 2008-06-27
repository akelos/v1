<?php

class WildcardSegment extends Segment 
{

    function isCompulsory()
    {
        return $this->default === COMPULSORY || is_int($this->default);    
    }
    
    function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        return "(?:$this->delimiter((?:{$this->getInnerRegEx()}/?)+))$optional_switch";
    }
    
    function addToParams(&$params,$match)
    {
        $params[$this->name] = explode('/',$match);
    }
}

?>