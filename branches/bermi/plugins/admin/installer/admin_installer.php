<?php

class AdminInstaller extends AkInstaller
{
    function up_1()
    {
        $this->check_for_collisions();
        $this->copy_admin_files();
        $this->modify_routes();
        $this->promt_for_credentials();
    }

    function down_1()
    {
    }
    
    function check_for_collisions()
    {
    }

    function copy_admin_files()
    {
    }
    
    function modify_routes()
    {
        //$Map->connect('/admin/:controller/:action/:id', array('controller' => 'dashboard', 'action' => 'index', 'module' => 'admin'));
    }
    
    function promt_for_credentials()
    {
    }
} 

?>