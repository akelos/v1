<?php

include_once 'AkLocalize/AkCountries.php';

class Countries extends AkCountries
{
    function getDesc($abbr)
    {
    $countries = array_flip($this->all());
    return $countries[$abbr];
    } 
}
?>
