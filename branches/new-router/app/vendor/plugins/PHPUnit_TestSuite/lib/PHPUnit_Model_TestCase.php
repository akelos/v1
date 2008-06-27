<?php

abstract class PHPUnit_Model_TestCase extends PHPUnit_Framework_TestCase 
{

    /**
     * Will drop and then create the table for the model, instantiate it and load the fixture if any is present.
     * 
     * ->useModel('Person')
     * will look for an installer-file <person_installer.php> and use it for creating the table. 
     * It will include the Person-model or generate an empty/default model if none is found in the include path.
     * An instance of the model can be found at $this->Person. After all it will look for a fixture named
     * <people.yaml> in the path and create the records/rows in the database. A copy of this data can be found
     * at $this->People as an associative array. 
     * It returns the instance and the fixture-data, so you can do
     *     list($Person,$People) = $this->useModel('Person'); 
     * and use these local variables. 
     * 
     * ->useModel('Person=>id,name')
     * will instead of using an installer-file simply use the columns provided. I.e. it will drop and then create
     * the table <people> with the columns 'id' and 'name'. In fact the table-definition can be the same as in any
     * installer, so you can really quickly prototype your stuff.  
     *   
     *
     * @param string $model_name optionally with table-defintion 
     * @return array an instance of the model and the fixture-data 
     */
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
            $Fixture[$id] = new FixedActiveRecord($item, $Model);
        }
        return $this->{AkInflector::pluralize($model_name)} = $Fixture;
    }
    
    function findFixtureForModel($model_name)
    {
        $fixture_file_name = AkInflector::tableize($model_name).'.yaml';
        $include_path = array(AK_PHPUNIT_TESTSUITE_FIXTURES,AK_APP_DIR.DS.'data');
        return PHPUnit_Akelos_autoload::searchFilenameInPath($include_path,$fixture_file_name);
    }
    
    /**
     * splits the string <a: something,b: antoher thing> into the array('a'=>'something','b'=>'another thing')
     */
    protected function splitIntoDataArray($data_string)
    {
        if (empty($data_string)) return array();
        
        $data_array = array();
        # split on <,> but don't split <\,> 
        $columns = preg_split('/(?<!\\\),/',$data_string); 
        foreach ($columns as $column){
            # split on <:> but only on the first one, resulting in two values
            list ($column_name,$column_value) = array_map('trim',explode(':',$column,2));
            # finally replace <\,> with <,>
            $column_value = str_replace('\\,',',',$column_value);
            $data_array[$column_name] = $column_value;
        }
        return $data_array;
    }
    
    /**
     * catches create<Modelname> and calls ->createRecord(Modelname,args*)
     * 
     * @throws BadMethodCallException
     */
    function __call($name,$args)
    {
        # createArtist(...) => createRecord('Artist',...);
        if (preg_match('/^create([A-Z].+)$/',$name,$matches)){
            array_unshift($args,$matches[1]);
            return call_user_func_array(array($this,'createRecord'),$args);            
        }
        throw new BadMethodCallException("Call to unknown method <$name> in ".__CLASS__.".");
    }
    
    private function createRecord($model_name,$data='')
    {
        if (!isset($this->$model_name)) throw new InvalidArgumentException("Can't find model <$model_name> on <\$this->$model_name>.");

        $data = array_merge($this->getDefaultDataForFixedRecord($model_name),$this->splitIntoDataArray($data));
        return $this->$model_name->create($data);
    }
    
    private function getDefaultDataForFixedRecord($model_name)
    {
        $method_name = "default$model_name";
        if (!method_exists($this,$method_name)) return array();
        return $this->$method_name();
    }
    
    
}

?>