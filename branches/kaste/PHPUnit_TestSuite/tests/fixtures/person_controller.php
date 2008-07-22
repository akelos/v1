<?php
require_once AK_APP_DIR.DS.'application_controller.php';

class PersonController extends ApplicationController
{
    function index()
    {
        $this->People = $this->Person->find('all');
    }

    function show()
    {
    }

    function add()
    {
        if(!empty($this->params['person'])){
            $this->Person->setAttributes($this->params['person']);
            if ($this->Request->isPost() && $this->Person->save()){
                $this->flash['notice'] = $this->t('Person was successfully created.');
                $this->redirectTo(array('action' => 'show', 'id' => $this->Person->getId()));
            }
        }
    }
    
    function edit()
    {
        if (empty($this->params['id'])){
         $this->redirectToAction('index');
        }
        if(!empty($this->params['person']) && !empty($this->params['id'])){
            $this->Person->setAttributes($this->params['person']);
            if($this->Request->isPost() && $this->Person->save()){
                $this->flash['notice'] = $this->t('Person was successfully updated.');
                $this->redirectTo(array('action' => 'show', 'id' => $this->Person->getId()));
            }
        }
    }
        
    function destroy()
    {
        if(!empty($this->params['id'])){
            $this->Person = $this->Person->find($this->params['id']);
            if($this->Request->isPost()){
                $this->Person->destroy();
                $this->redirectTo(array('action' => 'index'));
            }
        }
    }  
}

?>