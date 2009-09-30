<?php

class AkelosDoc_ComponentController extends AkelosDocController
{
    var $models = 'component,akelos_class';
    var $app_helpers = 'layout';
    
    function index ()
    {
        $this->redirectToAction('listing');
    }

    function listing()
    {
        $this->Components =& $this->Component->findAll(array('order'=>'name'));
    }
    
    function show()
    {
        if(!empty($this->params['name'])){
            $this->Component =& $this->Component->findFirstBy('name',$this->params['name']);
        }
        $this->Component->akelos_class->load();
    }
    
}

?>
