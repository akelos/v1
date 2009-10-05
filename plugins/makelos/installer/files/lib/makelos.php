<?php

!defined('MAKELOS_BASE_DIR') && define('MAKELOS_BASE_DIR', dirname(__FILE__));
!defined('MAKELOS_RUN') && define('MAKELOS_RUN', $_SERVER['PHP_SELF'] == './makelos');

class MakelosRequest
{
    public
    $attributes,
    $tasks,
    $constants = array();

    public function __construct()
    {
        if(php_sapi_name() == 'cli'){
            $this->useCommandLineArguments();
        }
    }

    public function useCommandLineArguments()
    {
        $arguments = $GLOBALS['argv'];
        array_shift($arguments);
        $this->parse($arguments);
    }

    public function parse($arguments)
    {
        $task_set = false;
        while(!empty($arguments)){
            $argument = array_shift($arguments);
            /**
             *  Captures assignments even if there are blank spaces before or after the equal symbol.
             */
            if(isset($arguments[0][0]) && $arguments[0][0] == '='){
                $argument .= $arguments[0];
                array_shift($arguments);
            }
            if(preg_match('/^(-{0,2})((?![\w\d-_:]+\/\/)[\w\d-_:]+ ?)(=?)( ?.*)/', $argument, $matches)){
                $constant_or_attribute = ((strtoupper($matches[2]) === $matches[2]) ? 'constants' : 'attributes');
                $is_constant = $constant_or_attribute == 'constants';
                if(($matches[3] == '=' || ($matches[3] == '' && $matches[4] != ''))){
                    $matches[4] = ($matches[4] === '') ? array_shift($arguments) : $matches[4];
                    if(!empty($task) && !$is_constant){
                        $this->tasks[$task]['attributes'][trim($matches[2], ' :')] = $this->_castValue(trim($matches[4], ' :'));
                    }else{
                        $this->{$constant_or_attribute}[trim($matches[2], ' :')] = trim($matches[4], ' :');
                    }
                }elseif(!$task_set && (empty($matches[1]) || $matches[1] != '-')){
                    $task = trim($matches[2], ' :');
                    $this->tasks[$task] = array();
                    $task_set = true;
                }elseif($matches[1] == '-'){
                    foreach (str_split($matches[2]) as $k){
                        $this->flags[$k] = true;
                    }
                }elseif($task_set){
                    $this->tasks[$task]['attributes'][trim($matches[0], ' :')] = $this->_castValue(trim($matches[0], ' :'));
                }
            }elseif ($task_set) {
                $this->tasks[$task]['attributes'][trim($argument, ' :')] = $this->_castValue(trim($argument, ' :'));
            }
        }
    }

    public function get($name, $type = null)
    {
        if(!empty($type)){
            return isset($this->{$type}[$name]) ? $this->{$type}[$name] : false;
        }else{
            foreach (array('constants', 'flags', 'attributes') as $type){
                return $this->get($name, $type);
            }
        }
    }
    public function flag($name)
    {
        return $this->get($name, __FUNCTION__);
    }
    public function constant($name)
    {
        return $this->get($name, __FUNCTION__);
    }
    public function attribute($name)
    {
        return $this->get($name, __FUNCTION__);
    }

    function defineConstants()
    {
        foreach ($this->constants as $constant => $value){
            if(!preg_match('/^AK_/', $constant)){
                define('AK_'.$constant, $value);
            }
            define($constant, $value);
        }
    }

    private function _castValue($value)
    {
        if(in_array($value, array(true,1,'true','True','TRUE','1','y','Y','yes','Yes','YES'), true)){
            return true;
        }
        if(in_array($value, array(false,0,'false','False','FALSE','0','n','N','no','No','NO'), true)){
            return false;
        }
        return $value;
    }
}

$MakelosRequest = new MakelosRequest();

if(MAKELOS_RUN){
    // Setting constants from arguments before including configurations
    $MakelosRequest->defineConstants();

    include(dirname(__FILE__).DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'config'.DIRECTORY_SEPARATOR.'config.php');
    require_once(AK_LIB_DIR.DS.'Ak.php');
    require_once(AK_LIB_DIR.DS.'AkObject.php');
    require_once(AK_LIB_DIR.DS.'AkInflector.php');
    defined('AK_SKIP_DB_CONNECTION') && AK_SKIP_DB_CONNECTION ? ($dsn='') : Ak::db(&$dsn);
    defined('AK_RECODE_UTF8_ON_CONSOLE_TO') ? null : define('AK_RECODE_UTF8_ON_CONSOLE_TO', false);
    require_once(AK_LIB_DIR.DS.'AkActiveRecord.php');
    require_once(AK_LIB_DIR.DS.'AkActionMailer.php');
    require_once(AK_APP_DIR.DS.'shared_model.php');
    require_once(AK_LIB_DIR.DS.'AkInstaller.php');
    require_once(AK_LIB_DIR.DS.'AkUnitTest.php');

    @ini_set('memory_limit', -1);
    set_time_limit(0);
    //error_reporting(E_ALL);
}

class Makelos
{
    public $tasks = array();
    public $task_files = array();
    public $current_task;
    public $settings = array(
    'app_name' => 'Akelos application name'
    );
    public $Request;
    public $Installer;

    public function __construct(&$Request)
    {
        $this->Request = $Request;
        $this->Installer = new AkInstaller();
        $this->Test = new AkUnitTest();

        !defined('AK_TASKS_DIR') && define('AK_TASKS_DIR', AK_BASE_DIR.DS.'lib'.DS.'tasks');
        $this->makefiles = array_merge(array_merge(array_merge(array_merge(array_merge(glob(AK_TASKS_DIR.DS.'makefile.php'), glob(AK_TASKS_DIR.DS.'*/makefile.php')), glob(AK_TASKS_DIR.DS.'*/*/makefile.php')), array(AK_BASE_DIR.DS.'*/*/*/makefile.php')),  array(AK_BASE_DIR.DS.'*/*/*/*/makefile.php')), array(AK_BASE_DIR.DS.'makefile.php'));
    }

    public function loadMakefiles()
    {
        foreach ($this->makefiles as $makefile){
            if(file_exists($makefile)){
                include($makefile);
            }
        }
    }

    public function runTasks()
    {
        $this->message('(in '.MAKELOS_BASE_DIR.')');
        if(!empty($this->Request->tasks)){
            foreach ($this->Request->tasks as $task => $arguments){
                $this->runTask($task, $arguments);
            }
        }else{
            $this->runTask('T', array());
        }
    }

    public function runTask($task_name, $options = array())
    {
        $this->current_task = $task_name;
        if(!isset($this->tasks[$task_name])){
            if(!$this->showBaseTaskDocumentation($task_name)){
                $this->error("\nInvalid task $task_name, use \n\n   $ ./makelos -T\n\nto show available tasks.\n");
            }
        }else{
            //$this->message(@$this->tasks[$task_name]['description']);
            $parameters = $this->getParameters(@$this->tasks[$task_name]['parameters'], @(array)$options['attributes']);
            $this->runTaskFiles($task_name, $parameters);
            $this->runTaskCode(@$this->tasks[$task_name]['run'], $parameters);
        }
    }

    public function showBaseTaskDocumentation($task_name)
    {
        $success = false;
        $this->message(' ');
        foreach ($this->tasks as $task => $details){
            if(preg_match("/^$task_name/", $task)){
                $this->showTaskDocumentation($task);
                $success = true;
            }
        }
        return $success;
    }

    public function showTaskDocumentation($task)
    {
        $this->message(sprintf("%-30s",$task).'  '.@$this->tasks[$task]['description']);
    }

    public function run($task_name, $options = array())
    {
        return $this->runTask($task_name, $options);
    }

    public function runTaskCode($code_snippets = array(), $options = array())
    {
        foreach (@(array)$code_snippets as $language => $code_snippets){
            $code_snippets = is_array($code_snippets) ? $code_snippets : array($code_snippets);
            $language_method = AkInflector::camelize('run_'.$language.'_snippet');

            if(method_exists($this, $language_method)){
                foreach ($code_snippets as $code_snippet){
                    $this->$language_method($code_snippet, $options);
                }
            }else{
                $this->error("Could not find a handler for running $language code on $this->current_task task", true);
            }
        }
    }

    public function runTaskFiles($task_name, $options = array())
    {
        $task_name = str_replace(':', DS, $task_name);
        $Makelos = $this;
        foreach (glob(AK_TASKS_DIR.DS.$task_name.'*.task.*') as $file){
            $pathinfo = @pathinfo($file);
            if(@$pathinfo['extension'] == 'php'){
                include($file);
            }else{
                echo `$file`;
            }
        }
    }

    public function getParameters($parameters_settings, $request_parameters)
    {
        $parameters_settings = Ak::toArray($parameters_settings);

        if(empty($parameters_settings)){
            return $request_parameters;
        }
        $parameters = array();
        foreach ($parameters_settings as $k => $v){
            $options = array();
            $required = true;
            if(is_numeric($k)){
                $parameter_name = $v;
            }else{
                $parameter_name = $k;
                if(is_array($v) && !empty($v['optional'])){
                    $required = false;
                    unset($v['optional']);
                }
            }
            if($required && !isset($request_parameters[$parameter_name])){
                $this->error("\nMissing \"$parameter_name\" parameter on $this->current_task\n", true);
            }
        }
    }

    public function runPhpSnippet($code, $options = array())
    {
        $fn = create_function('$options, $Makelos', $code.';');
        return $fn($options, $this);
    }

    public function runSystemSnippet($code, $options = array())
    {
        $code = trim($code);
        return $this->message(`$code`);
    }

    public function defineTask($task_name, $options = array())
    {
        $default_options = array();
        $task_names = strstr($task_name, ',') ? array_map('trim', explode(',', $task_name)) : array($task_name);
        foreach ($task_names as $task_name) {
            $task_files = glob(AK_TASKS_DIR.DS.str_replace(':',DS, $task_name.'.task*.*'));
            if(empty($options['run']) && empty($task_files)){
                $this->error("No task file found for $task_name in ".AK_TASKS_DIR, true);
            }
            $this->tasks[$task_name] = $options;
        }
    }

    public function addSettings($settings)
    {
        $this->settings = array_merge($this->settings, $settings);
    }

    public function displayAvailableTasks()
    {
        $this->message("\nShowing tasks avalable at ".AK_TASKS_DIR.":\n");
        foreach ($this->tasks as $task => $details){
            $this->showTaskDocumentation($task);
        }
        $this->message("\nYou can perform taks by running:\n");
        $this->message("    ./makelos task:name");
        $this->message("\nOptionally you can define contants or pass attributes to the tasks:\n");
        $this->message("    ./makelos task:name ENVIROMENT=production parameter=value -flag --param=value");
    }

    public function error($message, $fatal = false)
    {
        $this->message($message);
        if($fatal){
            die();
        }
    }
    public function message($message)
    {
        if(!empty($message)){
            echo $message."\n";
        }
    }
}

Ak::setStaticVar('Makelos', new Makelos($MakelosRequest));

function makelos_task($task_name, $options = array()){
    Ak::getStaticVar('Makelos')->defineTask($task_name, $options);
}

function makelos_setting($settings = array()){
    Ak::getStaticVar('Makelos')->addSettings($settings);
}


/**
 * @todo 
 *  
 *  Task
 *      prequisites
 *      actions
 *      expected parameters
 * 
 *  
 *  Directory functions
 *  Parallel tasks
 * 

 ./makelos db:fixtures:load         # Load fixtures into the current environment&#8217;s database. 
                                    # Load specific fixtures using FIXTURES=x,y
./makelos db:migrate                # Migrate the database through scripts in db/migrate. Target 
                                    # specific version with VERSION=x
./makelos db:structure:dump         # Dump the database structure to a SQL file
./makelos db:test:clone             # Recreate the test database from the current environment&#8217;s 
                                    # database schema
./makelos db:test:clone_structure   # Recreate the test databases from the development structure
./makelos db:test:prepare           # Prepare the test database and load the schema
./makelos db:test:purge             # Empty the test database

./makelos doc:app                   # Build the app HTML Files
./makelos doc:app:remove            # Remove app documentation
./makelos doc:plugins:remove        # Remove plugin documentation
./makelos doc:akelos:remove         # Remove akelos documentation
./makelos doc:plugins               # Generate documation for all installed plugins
./makelos doc:akelos                # Build the akelos HTML Files

./makelos log:clear                 # Truncates all *.log files in log/ to zero bytes

./makelos akelos:update             # Update both scripts and public/javascripts from Akelos
./makelos akelos:update:javascripts # Update your javascripts from your current akelos install
./makelos akelos:update:scripts     # Add new scripts to the application script/ directory

./makelos stats                     # Report code statistics (KLOCs, etc) from the application

./makelos test                      # Test all units and functionals
./makelos test:functionals          # Run tests for functionalsdb:test:prepare
./makelos test:integration          # Run tests for integrationdb:test:prepare
./makelos test:plugins              # Run tests for pluginsenvironment
./makelos test:recent               # Run tests for recentdb:test:prepare
./makelos test:uncommitted          # Run tests for uncommitteddb:test:prepare
./makelos test:units                # Run tests for unitsdb:test:prepare

./makelos tmp:cache:clear           # Clears all files and directories in tmp/cache
./makelos tmp:clear                 # Clear session, cache, and socket files from tmp/
./makelos tmp:create                # Creates tmp directories for sessions, cache, and sockets
./makelos tmp:sessions:clear        # Clears all files in tmp/sessions
./makelos tmp:sockets:clear         # Clears all ruby_sess.* files in tmp/sessions
 */

if(MAKELOS_RUN){
    Ak::getStaticVar('Makelos')->loadMakefiles();
    Ak::getStaticVar('Makelos')->runTasks();
    echo "\n";
}

?>