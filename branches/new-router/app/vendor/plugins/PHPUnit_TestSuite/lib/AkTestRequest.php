<?php

class AkTestRequest extends AkRequest 
{

    private $parameters_from_url;
    
    static function createInstance($method,$params)
    {
        $Request = new AkTestRequest();
        $Request->addParamsToRequest($params);
        $Request->setRequestMethod($method);
        $Request->parameters_from_url = $params;    
        return $Request;
    }
    
    function addParamsToRequest($params)
    {
        foreach ($params as $key=>$value){
            $this->_addParam($key,$value);
            $this->_request[$key] = $value;
        }
    }
    
    function setRequestMethod($method)
    {
        $this->_requestedMethod = $method;
    }
    
    // mocked
    function getMethod()
    {
        return $this->_requestedMethod;
    }
    
    function getRelativeUrlRoot()
    {
        return '';
    }
    
    function getParametersFromRequestedUrl()
    {
        return $this->parameters_from_url;
    }
    
}

?>