<?php

class FrameworkInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('cache', '
        id string(65) not null index primary key unique,
        cache_group string(50) index,
        cache_data binary,
        expire datetime'
        );
                
        $this->createTable('sessions', '
        id string(32) not null index primary key,
        expire datetime,
        value text'
        );
        
        $this->createTable('var', '
        id string(50) not null index primary key,
        val text'
        );
    }

    function down_1()
    {
        $this->dropTable('cache');
        $this->dropTable('sessions');
        $this->dropTable('var');
    }
}

?>