<?php
require_once(AK_LIB_DIR.DS.'AkInstaller.php');
require_once(AK_LIB_DIR.DS.'AkReflection'.DS.'AkReflectionFile.php');


class AkPluginInstaller extends AkInstaller
{
    var $pluggable_classes = array('BaseActiveRecord'=>'base_active_record.php',
                                   'BaseActionController'=>'base_action_controller.php');

    var $_plugin_definition;
    
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
    function _addMethodToClass2($class,$name,$path,$methodString, $pluginName)
    {
        $targetReflection = new AkReflectionFile($path);
        $classes = $targetReflection->getClasses();
        foreach($classes as $c) {
            $method = $c->getMethod($name);
            if ($method!==false) {
                echo "Method $name already exists on class $class in file $path\n";
                return false;
            }
        }
        $contents = @Ak::file_get_contents($path);
       
            return (Ak::file_put_contents($path, preg_replace('|class '.$class.'(.*?)\n.*?{|i',"class $class\\1
{
/** AUTOMATED START: $pluginName::$name */
$methodString
/** AUTOMATED END: $pluginName::$name */
",$contents))>0?true:'Could not write to '.$path);
        
    }
    function _removeMethodFromClass2($path,$name,$pluginName)
    {
        return Ak::file_put_contents($path, preg_replace("|(\n[^\n]*?/\*\* AUTOMATED START: $pluginName::$name \*/.*?/\*\* AUTOMATED END: $pluginName::$name \*/\n)|s","",Ak::file_get_contents($path)));
    }
    function _removeMethodFromClass1($path, $method_string)
    {
        return Ak::file_put_contents($path, str_replace($method_string,"",Ak::file_get_contents($path)));
    }
    function removeMethodFromBaseAR($name)
    {
        $path = AK_APP_DIR.DS.'base_active_record.php';
        return $this->_removeMethodFromClass($name,$path);
    }

    

    
    
    function installPluginMethods($fromFile,$pluginFile)
    {
        $reflection = new AkReflectionFile($fromFile);
        unset($reflection->tokens);
        $classes = $reflection->getClasses();
        foreach ($classes as $class) {
            $install = $class->getTag('WingsPluginInstallExtension');
            if ($install!==false) {
                $methods = $class->getMethods();
                $installAll = true;
            } else {
                $installAll = false;
                $methods = $class->getMethods(array('tags'=>array('WingsPluginInstallExtension'=>'.*')));
            }
            foreach ($methods as $method) {
                if ($installAll) {
                    $class = $install;
                    $methodAlias = $method->getName();
                } else {
                    $installAs = $method->getTag('WingsPluginInstallExtension');
                    $parts=split('::',$installAs);
                    $class = $parts[0];
                    if (!isset($parts[1])) {
                        $methodAlias = $method->getName();
                    } else {
                        $methodAlias = $parts[1];
                    }
                }
                $class = trim($class);
                $methodAlias = trim($methodAlias);
                $method->setTag('WingsPluginRemoveExtension',basename($pluginFile));
                if (isset($this->pluggable_classes[$class])) {
                    $path = AK_APP_DIR.DS.$this->pluggable_classes[$class];
                    $this->_addMethodToClass2($class,$methodAlias,$path,$method->toString(4,$methodAlias),basename($pluginFile));
                }
            }
        }
    }
    function _buildMethodString($method, $file)
    {
        $targetMethod = $method['install_as']['method'];
        
        $methodString = '/**
 * @WingsPluginRemove '.basename($file)."::$targetMethod\n";
        $methodString.= ' */'."\n";
        $methodString.= (AK_PHP5?$method['visibility'].' ':'');
        $methodString.= (AK_PHP5 && $method['static']?' static ':'');
        $methodString.= 'function ';
        $methodString.=$method['returnByReference']?'&':'';
        $methodString.=$targetMethod;
        $methodString.='(';
        $methodString.=implode(', ',$method['params']);
        $methodString.=')';
        $methodString.="\n";
        $methodString.="{\n";
        $methodString.=$method['code'];
        $methodString.="\n";
        $methodString.="}\n";
        var_dump($methodString);
        return $methodString;
    }
    function removePluginMethods($pluginFile)
    {

        foreach ($this->pluggable_classes as $targetClass=>$baseFile) {
            $file = AK_APP_DIR.DS.$baseFile;
            $reflection = new AkReflectionFile($file);
            $classes = $reflection->getClasses();
            foreach ($classes as $class) {
                $methods = $class->getMethods(array('tags'=>array('WingsPluginRemoveExtension'=>basename($pluginFile))));
                foreach ($methods as $method) {
                    $this->_removeMethodFromClass2($file,$method->getName(),basename($pluginFile));
                }
            }
        }
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