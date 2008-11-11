<?php

class Location extends ActiveRecord
{
    var $fixtures = 'locations.yml';
    var $belongs_to = 'company';
}

?>
