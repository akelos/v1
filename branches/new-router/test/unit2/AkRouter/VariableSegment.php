<?php

class VariableSegment extends Segment 
{

    function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        return "(?:$this->delimiter({$this->getInnerRegEx()}))$optional_switch";
    }

    function addToParams(&$params,$match)
    {
        $params[$this->name] = $match;
    }

    function insertPieceForUrl($value)
    {
        return $this->delimiter.$value;
    }
    
    function meetsRequirement($value)
    {
        if (!$this->hasRequirement()) return true;
        
        $regex = "|^{$this->getInnerRegEx()}$|";
        return (bool) preg_match($regex,$value);
    }

}

?>