<?php

class PHPUnit_Model_TestCase extends PHPUnit_Framework_TestCase 
{

    function useModel($model_name)
    {
        @list($model_name,$table_definition) = $this->splitIntoModelNameAndTableDefinition($model_name);
        
        $this->createTable($model_name,$table_definition);
        return array($this->instantiateModel($model_name), $this->loadFixture($model_name));
    }
    
    function createTable($model_name,$fields = null)
    {
        if ($fields) {
            $this->createTableOnTheFly($model_name,$fields);
        }else{
            $this->createTableUsingInstaller($model_name);
        }
        
        #this is dirty, should be done in AkInstaller
        $this->resetActiveRecordSchemaCache();
    }
    
    function resetActiveRecordSchemaCache()
    {
        if(isset($_SESSION['__activeRecordColumnsSettingsCache'])){
            unset($_SESSION['__activeRecordColumnsSettingsCache']);
        }
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
    
    function splitIntoModelNameAndTableDefinition($mixed_args)
    {
        return array_map('trim',explode('=>',$mixed_args)); 
    }
    
    function instantiateModel($model_name)
    {
        if (!class_exists($model_name)) $this->generateModel($model_name);
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
        if (!$fixture_file_name = $this->findFixtureForModel($model_name)) return false;
        
        $Model = new $model_name();
        $Fixture = array();
        
        $items = Ak::convert('yaml','array',file_get_contents($fixture_file_name));
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