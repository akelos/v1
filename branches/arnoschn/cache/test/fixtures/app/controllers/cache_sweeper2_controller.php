<?php

class CacheSweeper2Controller extends ApplicationController
{
    var $models = 'person';
    var $caches_action = array('show');
    var $cache_sweeper = array('person_sweeper2'=>array('except'=>'update,show,listing'));
    
    function show()
    {
        $this->Person=$this->Person->findFirstBy('id',@$this->params['id']);
        if (isset($this->Person) && isset($this->Person->id) && $this->Person->id>0) {
            $this->renderText($this->Person->first_name.' '.$this->Person->last_name);
        } else {
            $this->renderText('No such user',404);
        }
    }
    
    function listing()
    {
        $this->Persons = $this->Person->findAll(array('limit',10));
    }
    
    function update()
    {
        $this->Person=$this->Person->findFirstBy('id',@$this->params['id']);
        if (isset($this->Person) && isset($this->Person->id) && $this->Person->id>0) {
            $this->Person->first_name = @$this->params['first_name'];
            $this->Person->last_name = @$this->params['last_name'];
            $res = $this->Person->save();
            if ($res) {
                $this->renderText($this->Person->first_name,200);
            } else {
                $this->renderNothing(502);
            }
        } else {
            $this->renderText('No such user',404);
        }
    }
    
    function create()
    {
        $person = new Person();
        $person->setAttributes($this->params);
        $res = $person->save();
        if ($res) {
            //$this->renderText('User '.$person->first_name.' '.$person->last_name.' created', 201);
            $this->redirectTo($this->urlFor(array('controller'=>'cache_sweeper2','action'=>'show','id'=>$person->id)));
        } else {
            $this->renderText('Error', 502);
        }
    }
    
    function delete()
    {
        $this->Person=$this->Person->findFirstBy('id',@$this->params['id']);
        if (isset($this->Person) && $this->Person->id>0) {
            $this->Person->destroy();
            $this->renderNothing();
        } else {
            $this->renderText('No such user',404);
        }
    }

}