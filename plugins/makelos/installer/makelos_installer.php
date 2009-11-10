<?php

class MakelosInstaller extends AkPluginInstaller
{
    public function up_1()
    {    
        $this->installFiles();
    }

    public function down_1()
    {
    }
}
