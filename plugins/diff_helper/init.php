<?php

class DiffHelperPlugin extends AkPlugin 
{
    function load()
    {
        $this->addHelper('DiffHelper');
    }
}

?>