<?php

class SifrHelperPlugin extends AkPlugin 
{
    function load()
    {
        $this->addHelper('SifrHelper');
    }
}

?>