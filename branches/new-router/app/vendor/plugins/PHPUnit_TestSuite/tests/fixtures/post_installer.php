<?php

class PostInstaller extends AkInstaller
{
    function install()
    {
        $this->createTable('posts', 'id, title, body, written_by');
    }

    function uninstall()
    {
        $this->dropTable('posts', array('sequence'=>true));
    }
}

?>