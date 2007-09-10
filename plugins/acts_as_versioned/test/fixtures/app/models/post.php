<?php

class Post extends ActiveRecord
{
    var $acts_as = array('versioned' => array('limit'=>10));
}

?>
