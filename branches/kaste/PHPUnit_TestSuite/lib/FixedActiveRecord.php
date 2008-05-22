<?php

class FixedActiveRecord
{
    private $data;
    private $Model;
    
    function __construct($data,$Model)
    {
        $this->data = $data;
        $this->Model = $Model;
    }
    
    function __get($name)
    {
        return $this->data[$name];
    }
    
    function find()
    {
        return $this->Model->find($this->id);
    }
}

?>