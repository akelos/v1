<?php

!defined('AK_TASK_GENERATOR_DIR') && define('AK_TASK_GENERATOR_DIR', AK_PLUGINS_DIR.DS.'makelos'.DS.'generators');

require_once(AK_LIB_DIR.DS.'AkInstaller.php');

class TaskGenerator extends  AkelosGenerator
{
    public $sintags = true;
    public $generators_dir = AK_TASK_GENERATOR_DIR;
    public $command_values = array('(array)tasks');

    public $files = array();
    public $make_files = array();
    public $template_vars = array();
    
    private $_skip_files = array();

    public function cast()
    {
        foreach ($this->tasks as $k => $task){
            $task_levels = array_map(array('AkInflector','underscore'), array_diff(explode(':', $task.':'), array('')));
            $task = join(':', $task_levels);
            $last_level = array_pop($task_levels);
            $makefile = AK_BASE_DIR.DS.'lib'.DS.'tasks'.str_replace(DS.DS, DS, DS.join(DS, (array)@$task_levels).DS).'makefile.php';
            array_push($task_levels, $last_level);
            $task_file = AK_BASE_DIR.DS.'lib'.DS.'tasks'.DS.join(DS, $task_levels).'.task.php';
            $this->make_files[$makefile][$task_file] = $task;
            $this->files[$task_file] = $task_file;
            $this->files[$makefile] = $makefile;
        }
    }

    public function hasCollisions()
    {
        $this->collisions = array();
        $user_answer = 5;
        foreach (array_values($this->files) as $file_name){
            if($user_answer != 3 && file_exists($file_name)){
                if($user_answer == 4){
                    $this->_skip_files[] = $file_name;
                    continue;
                }
                $message = Ak::t('%file_name file already exists',array('%file_name'=>$file_name));
                $user_answer = (int)AkInstaller::promptUserVar($message."\n".
                "Would you like to:\n".
                " 1) overwrite file\n".
                " 2) keep existing file\n".
                " 3) overwrite all\n".
                " 4) keep all\n".
                " 5) abort\n", array('default' => 5));

                if($user_answer == 2 || $user_answer == 4){
                    $this->_skip_files[] = $file_name;
                }elseif($user_answer == 5){
                    die("Aborted\n");
                }
            }
        }
        return count($this->collisions) > 0;
    }

    function generate()
    {
        foreach ($this->make_files as $makefile => $tasks){
            if(!in_array($makefile, $this->_skip_files)){
                $this->assignVarToTemplate('tasks',$tasks);
                $this->save($makefile, $this->render('makefile.tpl', true));
            }
            foreach($tasks as $file => $task){
                if(!in_array($file, $this->_skip_files)){
                    $this->assignVarToTemplate('task',$task);
                    $this->save($file, $this->render('task.tpl', true));
                }
            }
        }
    }

    function ___printLog()
    {
        echo Ak::t("Generated task files %path", array('%path'=>AK_BASE_DIR.DS.'lib'.DS.'tasks'))."\n";
    }
}

?>
