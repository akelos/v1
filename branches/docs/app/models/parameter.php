<?php

Ak::import('data_type');

class Parameter extends ActiveRecord
{
    var $belongs_to = "method";
    var $acts_as = array('list' => array('scope'=>'method_id'));

    function &updateParameterDetails(&$Method, $details)
    {
        $Parameter =& $this->findOrCreateBy('name AND method_id', $details['name'], $Method->getId());

        $attributes = array(
        'default_value' => $details['value']
        );

        if(!empty($details['type'])){
            $Type =& new DataType();
            $Type =& $Type->findOrCreateBy('name', $details['type']);
            $attributes['data_type_id'] = $Type->getId();
        }

        $Parameter->setAttributes($attributes);
        $Parameter->save();

        // parameters doc_metadata  category_id

        return $Method;
    }
}

?>
