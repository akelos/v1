<?php

class ActsAsSluggablePlugin extends AkPlugin
{
    function load()
    {
        require_once($this->getPath().DS.'lib'.DS.'ActsAsSluggable.php');
    }
}

?>