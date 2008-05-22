<?php

class PHPUnit_Model_TestCase extends PHPUnit_Framework_TestCase 
{

    function createTable($model_name,$fields = null)
    {
        if ($fields) return $this->createTableOnTheFly($model_name,$fields);
        $this->createTableUsingInstaller($model_name);
    }
    
    function createTableUsingInstaller($model_name)
    {
        $installer_class = $model_name.'Installer';
        $Installer = new $installer_class();
        $Installer->uninstall();
        $Installer->install();
    }
    
    function createTableOnTheFly($model_name,$fields)
    {
        $table_name = AkInflector::tableize($model_name);
        
        $Installer = new AkInstaller();
        $Installer->dropTable($table_name,array('sequence'=>true));
        $Installer->createTable($table_name,$fields,array('timestamp'=>false));
    }
    
    function instantiateModel($model_name)
    {
        return $this->$model_name = new $model_name();
    }
    
    function generateModel($model_name)
    {
        $model_source_code = "class ".$model_name." extends ActiveRecord {} ";
        $has_errors = @eval($model_source_code) === false;
        if ($has_errors) trigger_error(Ak::t('Could not declare the model %modelname.',array('%modelname'=>$model_name)),E_USER_ERROR);
    }
    
    function loadFixture($model_name)
    {
        $Model = new $model_name();
        $Fixture = array();
        
        $items = Ak::convert('yaml','array',file_get_contents($this->findFixtureForModel($model_name)));
        foreach($items as $id => $item){
            $Record = $Model->create($item);
            #we replace the 'id' with the returned value from the db
            $item['id'] = $Record->getId();
            $Fixture[$id] = new FixtureRecord($item);
        }
        return $this->{AkInflector::pluralize($model_name)} = $Fixture;
    }
    
    function findFixtureForModel($model_name)
    {
        $fixture_file_name = AkInflector::tableize($model_name).'.yaml';
        $include_path = array(AK_PHPUNIT_TESTSUITE_FIXTURES,AK_APP_DIR.DS.'data');
        return PHPUnit_Akelos_autoload::searchFileInIncludePath($include_path,$fixture_file_name);
    }
    
}

class FixtureRecord
{
    private $data;
    
    function __construct($data)
    {
        $this->data = $data;
    }
    
    function __get($name)
    {
        return $this->data[$name];
    }
}

?>