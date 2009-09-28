<?php

class File extends ActiveRecord
{
    var $has_many = array('methods');
    var $belongs_to = array('component', 'category');
    
    function validate()
    {
        $this->validatesUniquenessOf('path');
    }
}

?>
