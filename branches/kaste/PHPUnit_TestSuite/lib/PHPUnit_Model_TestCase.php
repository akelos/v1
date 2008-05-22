<?php

class PHPUnit_Model_TestCase extends PHPUnit_Framework_TestCase 
{

    function createTable($model_name,$fields)
    {
        $table_name = AkInflector::tableize($model_name);
        
        $Installer = new AkInstaller();
        $Installer->dropTable($table_name,array('sequence'=>true));
        $Installer->createTable($table_name,$fields,array('timestamp'=>false));
    }
    
    function generateModel($model_name)
    {
        $model_source_code = "class ".$model_name." extends ActiveRecord {} ";
        $has_errors = @eval($model_source_code) === false;
        if ($has_errors) trigger_error(Ak::t('Could not declare the model %modelname.',array('%modelname'=>$model_name)),E_USER_ERROR);
    }
    
}

?>