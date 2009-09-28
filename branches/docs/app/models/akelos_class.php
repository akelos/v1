<?php

class AkelosClass extends ActiveRecord
{
    var $acts_as = 'tree';
    var $belongs_to = array('file', 'component');
    var $has_many = array('methods'=>array('order'=>'position'), 'categories');

    function validate()
    {
        $this->validatesUniquenessOf('name');
    }


    function &updateClassDetails(&$File, &$Component, &$SourceAnalyzer)
    {
        Ak::import('method');
        $MethodInstance =& new Method();

        $parsed_details = $SourceAnalyzer->getParsedArray($File->body);

        $available_classes = empty($parsed_details['classes']) ? array() : array_keys($parsed_details['classes']);


        if(empty($available_classes)){
            return $available_classes;
        }

        $Classes = array();
        foreach ($available_classes as $class_name){
            $extends = !empty($parsed_details['classes'][$class_name]['extends']) ? $parsed_details['classes'][$class_name]['extends'] : false;

            if($extends){
                $SourceAnalyzer->log('Looking for parent class: '.$extends);
                $ParentClass =& $this->_addOrUpdateClassDetails($extends, $File, $Component, $SourceAnalyzer, array(), true);
            }

            $Class =& $this->_addOrUpdateClassDetails($class_name, $File, $Component, $SourceAnalyzer, $parsed_details['classes'][$class_name]);

            if(!empty($ParentClass)){
                $SourceAnalyzer->log('Setting '.$extends.' as the parent of '.$class_name);
                $ParentClass->tree->addChild($Class);
                $ParentClass->save();
            }

            $Class->methods = array();
            if(!empty($parsed_details['classes'][$class_name]['methods'])){
                foreach ($parsed_details['classes'][$class_name]['methods'] as $method_name => $method_details){
                    $Class->methods[] =& $MethodInstance->updateMethodDetails($Class, $method_name, $method_details, $SourceAnalyzer);
                }
            }

            $Classes[] =& $Class;
        }

        return $Classes;
    }

    function &_addOrUpdateClassDetails($class_name, &$File, &$Component, &$SourceAnalyzer, $class_details, $ExtendedClass = null)
    {
        $class_details = array(
        'name'=> $class_name,
        'file_id' => $File->getId(),
        'component_id' => $Component->getId(),
        'description' => @$class_details['doc']
        );
        /*
        'doc' => trim($this->_latest_docs, "\n\t "),
        'doc_metadata' => $this->_latest_attributes,
        'class_name' => $this->_current_class,
        'extends' => trim($this->_current_class_extends),
        */

        $class_details = array_filter($class_details, 'strlen');

        if(!$Class =& $this->findFirstBy('name', $class_name)){
            $SourceAnalyzer->log('Adding class: '.$class_name);

            if(!empty($ExtendedClass)){
                $class_details = array('name'=>$class_details['name']);
            }

            $Class =& new AkelosClass($class_details);
            $Class->save();

            $SourceAnalyzer->log('Added class: '.$class_name);
        }elseif(empty($ExtendedClass) && $File->getId() != $Class->get('file_id') || $Component->getId() != $Class->get('component_id')){
            $SourceAnalyzer->log('Modifying class details: '.$class_name);
            unset($class_details['name']);
            $Class->setAttributes($class_details);
            $Class->save();
        }
        return $Class;
    }

}

?>