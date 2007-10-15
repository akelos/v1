<?php

class CalendarHelperPlugin extends AkPlugin 
{
    function load()
    {
        $this->addHelper('CalendarHelper');
    }
}

?>