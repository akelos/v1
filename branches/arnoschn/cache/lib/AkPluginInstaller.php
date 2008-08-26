<?php
require_once(AK_LIB_DIR.DS.'AkInstaller.php');

class AkPluginInstaller extends AkInstaller
{
    function _addMethodToClass($class,$path,$name,$methodString)
    {
        $contents = @Ak::file_get_contents($path);
        if (!preg_match('/function\s+'.$name.'/i',$contents) && !preg_match("|/\*\* AUTOMATED START: $name \*/|", $contents)) {
        
        return (Ak::file_put_contents($path, preg_replace('|class '.$class.'(.*?)\n.*?{|i',"class $class\\1
{
    /** AUTOMATED START: $name */
$methodString
    /** AUTOMATED END: $name */
",$contents))>0?true:'Could not write to '.$path);
        } else {
            return "Method $name already exists on $class in file $path.\n";
        }
    }
    
    function _removeMethodFromClass($name,$path)
    {
        return Ak::file_put_contents($path, preg_replace("|(\n[^\n]*?/\*\* AUTOMATED START: $name \*/.*?/\*\* AUTOMATED END: $name \*/\n)|s","",Ak::file_get_contents($path)));
    }
    
    function removeMethodFromBaseAR($name)
    {
        $path = AK_APP_DIR.DS.'base_active_record.php';
        return $this->_removeMethodFromClass($name,$path);
    }
    
    function removeMethodFromBaseController($name)
    {
        $path = AK_APP_DIR.DS.'base_application_controller.php';
        return $this->_removeMethodFromClass($name,$path);
    }
    
    function addMethodToBaseAR($name,$methodString)
    {
        $path = AK_APP_DIR.DS.'base_active_record.php';
        return $this->_addMethodToClass('BaseActiveRecord',$path,$name,$methodString);
    }
    
    function addMethodToBaseController($name,$methodString)
    {
        $path = AK_APP_DIR.DS.'base_application_controller.php';
        return $this->_addMethodToClass('BaseApplicationController',$path,$name,$methodString);
    }
}
?>