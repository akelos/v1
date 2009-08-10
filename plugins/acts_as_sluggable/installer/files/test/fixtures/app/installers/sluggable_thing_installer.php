<?php
class SluggableThingInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('sluggable_things','id,name,slug');
    }
    
    function down_1()
    {
        $this->dropTable('sluggable_things');
    }
}