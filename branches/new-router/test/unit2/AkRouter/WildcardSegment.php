<?php

class WildcardSegment extends Segment 
{
    
    function addToParams(&$params,$match)
    {
        $params[$this->name] = array($match);
    }
}

?>