<?php

class StoreInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('stores', "
          id,
          address,
          lat DECIMAL(15.10),
          lng DECIMAL(15.10)
        ");  
    }
    
    function down_1()
    {
        $this->dropTable('stores');  
    }
}

?>
