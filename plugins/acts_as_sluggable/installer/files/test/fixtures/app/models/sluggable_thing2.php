<?php
class SluggableThing2 extends ActiveRecord
{
    var $acts_as = array('sluggable'=>array('slug_source'=>'generateSlug','slug_target'=>'slug'));
    
    var $_avoidTableNameValidation = true;
    
    function SluggableThing2()
    {
        $this->setModelName("SluggableThing2");
        $attributes = (array)func_get_args();
        $this->setTableName('sluggable_things', true, true);
        $this->init($attributes);
    }
    
    function generateSlug()
    {
        return 'method :'.$this->name;
    }
}
?>