<?php
require_once('phing/Task.php');

class CiRewriteUrlTask extends Task
{
    var $url;
    var $property;
    public function setUrl($url)
    {
        $this->url = $url;
    }
    public function setProperty($prop)
    {
        $this->property = $prop;
    }
    public function main()
    {
        $parts = parse_url($this->url);
        $rewriteBase = $parts['path'];
        $this->project->setProperty($this->property,$rewriteBase);
    }
}


?>