<?php

class Component extends ActiveRecord
{
    var $acts_as = 'tree';
    var $has_many = array('files', 'methods', 'categories', 'akelos_classes');
        
    function validate()
    {
        $this->set('name', trim($this->get('name')));
        $this->validatesUniquenessOf('name', array('scope'=>'parent_id'));
    }
    

    function &updateComponentDetails(&$File, &$SourceAnalizer)
    {
        $details = $SourceAnalizer->getFileDetails($File->body);
        $package = empty($details['package']) ? false : $details['package'];
        $subpackage = empty($details['subpackage']) ? false : $details['subpackage'];

        if(!$Component =& $this->findFirstBy('name', $package)){
            $SourceAnalizer->log('Adding package: '.$package);
            $Component =& $this->create(array('name'=> $package));
        }

        if(!$SubComponent =& $this->findFirstBy('name AND parent_id', $subpackage, $Component->id)){
            $SubComponent =& $this->create(array('name'=> $subpackage));
            $SourceAnalizer->log('Adding package: '.$subpackage);
        }

        if(empty($File->component_id)){
            $SourceAnalizer->log('Relating file '.$File->path.' to component '.$subpackage);
            $File->component->assign($SubComponent);
            $File->save();
        }

        if($Component && $SubComponent && !in_array($SubComponent->id, $Component->collect($Component->tree->getChildren(),'id','id'))){
            $SourceAnalizer->log('Setting package '.$subpackage.' as a child for '.$package);
            $Component->tree->addChild($SubComponent);
            $Component->save();
        }
        
        return $SubComponent;
    }
}

?>
