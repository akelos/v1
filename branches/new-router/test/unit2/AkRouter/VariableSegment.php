<?php

class VariableSegment extends Segment 
{

    function addToParams(&$params,$match)
    {
        $params[$this->name] = $match;
    }
}

?>