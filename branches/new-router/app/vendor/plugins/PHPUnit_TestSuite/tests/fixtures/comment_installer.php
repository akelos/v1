<?php
class CommentInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('comments', "id,name,body,post_id");
    }

    function down_1()
    {
        $this->dropTable('comments', array('sequence'=>true));
    }
}

?>