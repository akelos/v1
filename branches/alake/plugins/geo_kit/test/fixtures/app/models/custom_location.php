<?php

class CustomLocation extends ActiveRecord
{
    var $fixtures = 'custom_locations.yml';
    var $belongs_to = 'company';
}

?>
