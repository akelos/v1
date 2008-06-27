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
        $multiplier = is_int($this->default) ? '{'. $this->default .'}' : '+';
        return "((?:$this->delimiter{$this->getInnerRegEx()})$multiplier)$optional_switch";
    }
    
    function addToParams(&$params,$match)
    {
        $match = substr($match,1); // the first char is the delimiter
        $params[$this->name] = explode('/',$match);
    }
    
    function insertPieceForUrl($value)
    {
        return $this->delimiter.join('/',$value);
    }
    
    function meetsRequirement($values)
    {
        if (!$this->hasRequirement()) return true;
        if (is_int($this->default) && count($values) != $this->default) return false;

        $regex = "|^{$this->getInnerRegEx()}$|";
        foreach ($values as $value){
            if (!(bool) preg_match($regex,$value)) return false;
        }
        return true;
    }
    
    
}

?>