<?php

class DynamicFinderInstaller extends AkInstaller
{
    function up_1()
    {
        $new_code = '
    private function __call ($method, $args)
    {
        if(substr($method,0,4) == \'find\'){
            $finder = substr(AkInflector::underscore($method), 5);
            list($type, $columns) = explode(\'by_\', $finder);
            $callback = strstr($type,\'create\') ?  \'findOrCreateBy\' : (strstr($type,\'first\') || !strstr($type,\'all\') ? \'findFirstBy\' : \'findAllBy\');
            $columns = strstr($columns, \'_and_\') ? explode(\'_and_\', $columns) : array($columns);
            array_unshift($args, join(\' AND \', $columns));
            return Ak::call_user_func_array(array(&$this,$callback), $args);
        }

        $backtrace = debug_backtrace();
        trigger_error(\'Call to undefined method \'.__CLASS__.\'::\'.$method.\'() in <b>\'.$backtrace[1][\'file\'].\'</b> on line <b>\'.$backtrace[1][\'line\'].\'</b> reported \', E_USER_ERROR);
    }

';
        $original_class = Ak::file_get_contents(AK_APP_DIR.DS.'shared_model.php');
        if(strstr($original_class, '__call')){
            trigger_error('You seem to have a __call method on your shared model. This plugin can\'t be installed as it will conflict with your existing code.', E_USER_ERROR);
        }

        $modified_class = preg_replace('/ActiveRecord[ \n\t]*extends[ \n\t]*AkActiveRecord[ \n\t]*[ \n\t]*{/i', "ActiveRecord extends AkActiveRecord \n{\n\n$new_code", $original_class);

        Ak::file_put_contents(AK_APP_DIR.DS.'shared_model.php', $modified_class);
    }

    function down_1()
    {
    }

}

?>