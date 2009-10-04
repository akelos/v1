<?php

class MakelosInstaller extends AkInstaller
{
    public function up_1()
    {    
        $this->installFiles();
    }

    public function down_1()
    {
    }
}

?>