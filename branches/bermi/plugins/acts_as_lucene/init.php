<?php

class ActsAsLucenePlugin extends AkPlugin
{
    function load()
    {
        require_once($this->getPath().DS.'lib'.DS.'ActsAsLucene.php');
    }
}

?>
