<?php

class SluggableThing extends ActiveRecord
{
    var $acts_as = array('sluggable'=>array('slug_source'=>'name','slug_target'=>'slug'));
}


?>