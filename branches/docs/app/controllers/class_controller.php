<?php

class ClassController extends ApplicationController
{
    var $models = 'akelos_class,method,component';
    
    function show ()
    {
        $this->AkelosClass =& $this->AkelosClass->findFirstBy('name', @$this->params['name'], array('include'=>array('methods','component','file')));
    }

    function listing ()
    {
    }
    
    function edit()
    {
        if($this->Request->isAjax() && $Class =& $this->AkelosClass->find(@$this->params['id'])){
            $Class->set('description', @$this->params['value']);
            $Class->save();
            $Class->reload();
            $this->renderText(TextHelper::markdown($Class->get('description')));
        }else{
            $this->renderNothing(400);
        }
    }
}

?>
