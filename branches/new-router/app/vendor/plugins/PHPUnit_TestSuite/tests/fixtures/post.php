<?php

class Post extends ActiveRecord
{
    var $has_many = array('comments'=>array('dependent'=>'destroy'));
    var $belongs_to = array('author'=>array('class_name'=>'Person','primary_key_name'=>'written_by'));
}

?>