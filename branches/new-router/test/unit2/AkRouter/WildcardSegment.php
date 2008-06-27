<?php

class WildcardSegment extends Segment 
{

    function isCompulsory()
    {
        return $this->default === COMPULSORY || $this->expectsExactSize();    
    }
    
    function expectsExactSize()
    {
        return is_int($this->default) ? $this->default : false;
    }
    
    function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        $multiplier = ($size = $this->expectsExactSize()) ? '{'. $size .'}' : '+';
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
        if (($size = $this->expectsExactSize()) && count($values) != $size) return false;

        $regex = "|^{$this->getInnerRegEx()}$|";
        foreach ($values as $value){
            if (!(bool) preg_match($regex,$value)) return false;
        }
        return true;
    }
    
}

?>