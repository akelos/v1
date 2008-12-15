<?php

class CompanyInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('companies', "
          id,
          name
        ");  
    }
    
    function down_1()
    {
        $this->dropTable('companies');  
    }
}

?>
