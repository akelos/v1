<?php

class Property extends ActiveRecord
{
    var $hasMany = array(
    'pictures' => array('dependent' => 'destroy'),
    'panoramas');
    var $hasAndBelongsToMany = array('property_types' => array('unique'=>true)); // Unique still needs to be implemented
}

?>