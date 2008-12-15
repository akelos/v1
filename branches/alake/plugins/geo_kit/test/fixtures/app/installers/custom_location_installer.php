<?php

class CustomLocationInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('custom_locations', "
          id,
          company_id,
          street,
          city,
          state,
          postal_code,
          latitude DECIMAL(15.10),
          longitude DECIMAL(15.10)
        ");  
    }
    
    function down_1()
    {
        $this->dropTable('custom_locations');  
    }
}

?>
