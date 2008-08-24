<?php

class AccountController extends ApplicationController
{
    var $models = array('User','Sentinel');

    function __construct()
    {
        $this->settings = Ak::getSettings('admin');
    }

    function index()
    {
        $this->redirectToAction('sign_in');
    }

    function sign_in()
    {
    }

    function sign_up()
    {
        if ($this->Request->isPost() && !empty($this->params['user'])){
            $this->User->setAttributes($this->params['user']);

            if($this->User->save()){
                $this->flash_options = array('seconds_to_close' => 10);
                $this->flash['success'] = $this->t('Your account has been successfully created');
                $this->params['ak_login'] = $this->params['user'];
                $this->Sentinel->init($this);
                $this->Sentinel->authenticateUsingPostedVars();
                $this->redirectTo(array('controller'=>'dashboard', 'action' => 'blank_slate', 'module'=>'admin'));
            }
        }
    }

    function is_login_available()
    {
        if(!empty($this->params['login'])){
            $this->User->set('login', $this->params['login']);
            $this->User->validatesUniquenessOf('login');
            if($this->User->getErrorsOn('login')){
                $this->renderText('0');
                return ;
            }
        }
        $this->renderText('1');
    }

    function logout()
    {
        $this->flash['message'] = $this->t("You have successfully logged out.");
        $this->Sentinel->init($this);
        $this->Sentinel->unsetCurrentUser();
        $settings = Ak::getSettings('admin');
        $this->redirectTo(empty($settings['sign_in_url'])? array('action'=>'sign_in') : $settings['sign_in_url']);
    }

    function password_reminder()
    {
    }

    function change_password()
    {
    }
}

?>