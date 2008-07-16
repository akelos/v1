<?php

class MethodNotAllowedException extends Exception 
{ } 

class PersonController extends ApplicationController
{
    /**
     * @var AkRequest
     */
    var $Request;
    
    function index()
    {
        #$this->renderText('Person::index');
        switch ($this->respondTo()) {
        	case 'xml':
        	    $this->renderXml(var_export($_SERVER,true));
            	break;
        	default:
        	    throw new MethodNotAllowedException();
        	    break;
        }
    }
    
    protected function renderXml($xml_string,$status=null,$location=null)
    {
        $this->Response->addHeader(array('content-type'=>'text/xml'));
        $this->renderText($xml_string,$status);
    }
    
    protected function respondTo()
    {
        return $this->Request->getFormat();
    }
}

?>