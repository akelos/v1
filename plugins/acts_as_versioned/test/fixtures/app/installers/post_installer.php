<?php

class PostInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('posts', "
          id,
          title,
          body,
          data binary,
          created_at,
          updated_at
        ");  
    }
    
    function down_1()
    {
        $this->dropTable('posts');  
    }
}

?>