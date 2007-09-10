<?php

class ActsAsVersionedPlugin extends AkPlugin
{
    function load()
    {
        require_once($this->getPath().DS.'lib'.DS.'ActsAsVersioned.php');
    }
}

?>
