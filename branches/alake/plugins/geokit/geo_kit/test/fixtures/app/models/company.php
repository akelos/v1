<?php

class Company extends ActiveRecord
{
    var $fixtures = 'companies.yml';
    var $has_many = array('locations','custom_locations');
}

?>
