<?php

class AkTestRequest extends AkRequest 
{

    static function createInstance($method,$params)
    {
        $Request = new AkTestRequest();
        $Request->addParamsToRequest($params);
        return $Request;
    }
    
    function addParamsToRequest($params)
    {
        foreach ($params as $key=>$value){
            $this->_addParam($key,$value);
            $this->_request[$key] = $value;
        }
    }
    
}

?>