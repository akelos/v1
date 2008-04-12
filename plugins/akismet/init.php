<?php

class AkismetHelperPlugin extends AkPlugin 
{
    function load()
    {
        $this->addHelper('AkismetHelper');
    }
}

?>