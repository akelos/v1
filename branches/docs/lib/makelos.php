<?php


!defined('MAKELOS_BASE_DIR') && define('MAKELOS_BASE_DIR', dirname(__FILE__));

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
        while(!empty($arguments)){
            $argument = array_shift($arguments);
            /**
             *  Captures assignments even if there are blank spaces before or after the equal symbol.
             */
            if(isset($arguments[0][0]) && $arguments[0][0] == '='){
                $argument .= $arguments[0];
                array_shift($arguments);
            }
            if(preg_match('/^(-{0,2})([\w\d-_:]+ ?)(=?)( ?.*)/', $argument, $matches)){
                $constant_or_attribute = ((strtoupper($matches[2]) === $matches[2]) ? 'constants' : 'attributes');
                $is_constant = $constant_or_attribute == 'constants';
                if(($matches[3] == '=' || ($matches[3] == '' && $matches[4] != ''))){
                    $matches[4] = ($matches[4] === '') ? array_shift($arguments) : $matches[4];
                    if(!empty($task) && !$is_constant){
                        $this->tasks[$task]['attributes'][trim($matches[2], ' :')] = $this->_castValue(trim($matches[4], ' :'));
                    }else{
                        $this->{$constant_or_attribute}[trim($matches[2], ' :')] = trim($matches[4], ' :');
                    }
                }elseif(empty($matches[1]) || $matches[1] != '-'){
                    $task = trim($matches[2], ' :');
                    $this->tasks[$task] = array();
                }else{
                    foreach (str_split($matches[2]) as $k){
                        $this->flags[$k] = true;
                    }
                }
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


// Setting constants from arguments before including configurations
$MakelosRequest = new MakelosRequest();
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


error_reporting(E_ALL);

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
        $this->task_files = array_merge(glob(AK_TASKS_DIR.DS.'*.task.*'), glob(AK_TASKS_DIR.DS.'*/*.task.*'));
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
            $this->error("\nInvalid task $task_name, use \n\n   $ ./makelos -T\n\nto show available tasks.\n");
        }else{
            $this->message(@$this->tasks[$task_name]['description']);
            $parameters = $this->getParameters(@$this->tasks[$task_name]['parameters'], $options['attributes']);
            return $this->runTaskCode(@$this->tasks[$task_name]['run'], $parameters);
        }
    }
    public function run($task_name, $options = array())
    {
        return $this->runTask($task_name, $options);
    }

    public function runTaskCode($code_snippets = array(), $options = array())
    {
        foreach ($code_snippets as $language => $code_snippets){
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
        print_r($this->task_files);
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

include('makefile.php');

makelos_task('T,tasks', array(
'description' => 'Shows available tasks',
'run' => array(
'php' => <<<PHP
    \$Makelos->displayAvailableTasks();
PHP

)
));


/**
 * Rake 
 *  Task
 *      prequisites
 *      actions
 *      expected parameters
 * 
 *  
 *  Directory functions?!?!
 *  Parallel tasks
 * 
 * 
 rake db:fixtures:load          # Load fixtures into the current environment&#8217;s database. 
                               # Load specific fixtures using FIXTURES=x,y
rake db:migrate                # Migrate the database through scripts in db/migrate. Target 
                               # specific version with VERSION=x
rake db:schema:dump            # Create a db/schema.rb file that can be portably used against 
                               # any DB supported by AR
rake db:schema:load            # Load a schema.rb file into the database
rake db:sessions:clear         # Clear the sessions table
rake db:sessions:create        # Creates a sessions table for use with 
                               # CGI::Session::ActiveRecordStore
rake db:structure:dump         # Dump the database structure to a SQL file
rake db:test:clone             # Recreate the test database from the current environment&#8217;s 
                               # database schema
rake db:test:clone_structure   # Recreate the test databases from the development structure
rake db:test:prepare           # Prepare the test database and load the schema
rake db:test:purge             # Empty the test database

rake doc:app                   # Build the app HTML Files
rake doc:clobber_app           # Remove rdoc products
rake doc:clobber_plugins       # Remove plugin documentation
rake doc:clobber_rails         # Remove rdoc products
rake doc:plugins               # Generate documation for all installed plugins
rake doc:rails                 # Build the rails HTML Files
rake doc:reapp                 # Force a rebuild of the RDOC files
rake doc:rerails               # Force a rebuild of the RDOC files

rake log:clear                 # Truncates all *.log files in log/ to zero bytes

rake rails:freeze:edge         # Lock this application to latest Edge Rails. Lock a specific 
                               # revision with REVISION=X
rake rails:freeze:gems         # Lock this application to the current gems (by unpacking them 
                               # into vendor/rails)
rake rails:unfreeze            # Unlock this application from freeze of gems or edge and return 
                               # to a fluid use of system gems
rake rails:update              # Update both scripts and public/javascripts from Rails
rake rails:update:javascripts  # Update your javascripts from your current rails install
rake rails:update:scripts      # Add new scripts to the application script/ directory

rake stats                     # Report code statistics (KLOCs, etc) from the application

rake test                      # Test all units and functionals
rake test:functionals          # Run tests for functionalsdb:test:prepare
rake test:integration          # Run tests for integrationdb:test:prepare
rake test:plugins              # Run tests for pluginsenvironment
rake test:recent               # Run tests for recentdb:test:prepare
rake test:uncommitted          # Run tests for uncommitteddb:test:prepare
rake test:units                # Run tests for unitsdb:test:prepare

rake tmp:cache:clear           # Clears all files and directories in tmp/cache
rake tmp:clear                 # Clear session, cache, and socket files from tmp/
rake tmp:create                # Creates tmp directories for sessions, cache, and sockets
rake tmp:sessions:clear        # Clears all files in tmp/sessions
rake tmp:sockets:clear         # Clears all ruby_sess.* files in tmp/sessions




script/about            # Information about environenment
script/breakpointer     # starts the breakpoint server
script/console          # interactive Rails Console
script/destroy          # deletes files created by generators
script/generate         # -> generators
script/plugin           # -> Plugins
script/runner           # executes a task in the rails context
script/server           # launches the development server
                        # http://localhost:3000

script/performance/profiler     # profile an expensive method
script/performance/benchmarker  # benchmark different methods

script/process/reaper
script/process/spawner





ruby script/generate model ModellName
ruby script/generate controller ListController show edit
ruby script/generate scaffold ModelName ControllerName 
ruby script/generate migration AddNewTable
ruby script/generate plugin PluginName
ruby script/generate mailer Notification lost_password signup
ruby script/generate web_service ServiceName api_one api_two
ruby script/generate integration_test TestName
ruby script/generate session_migration




rake test:units
 */

Ak::getStaticVar('Makelos')->runTasks();
echo "\n";

?>