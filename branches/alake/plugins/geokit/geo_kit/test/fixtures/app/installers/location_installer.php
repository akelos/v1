<?php

class LocationInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('locations', "
          id,
          company_id,
          street,
          city,
          state,
          postal_code,
          lat DECIMAL(15.10),
          lng DECIMAL(15.10)
        ");  
    }
    
    function down_1()
    {
        $this->dropTable('locations');  
    }
}

?>
