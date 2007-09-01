<?php

if(AK_PHP5){

    class DynamicFinderPlugin extends AkPlugin
    {
        var $priority = 100;
        var $repository = 'http://svn.akelos.org/plugins/';

        function load()
        {
            $this->extendClassWithCode('AkActiveRecord', 'lib/ActiveRecordDynamicFinder.php');
        }
    }

}

?>