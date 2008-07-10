<?php

class Admin_DashboardController extends AdminController
{
    function index()
    {
        $this->need_app_owner = '';
        $rec_count = $this->User->countBySql("SELECT COUNT(*) FROM users");
        if($rec_count == 0) {
            $this->need_app_owner = 'true';
            $this->redirectTo(array('controller'=>'users','action'=>'add'));
        } else {
            if(empty($_SESSION['__CurrentUser'])){
                $this->redirectTo(array('controller'=>'users','action'=>'login'));
            } else {
                $this->user = $_SESSION['__CurrentUser'];
                $this->redirectTo(array('controller'=>'users','action'=>'home'));
            }
        }
    }

    function action_privileges_error()
    {
        $this->Response->addHeader('Status', 405);
        $this->flash_now['error'] = $this->t('You don\'t have enough privileges to perform selected action.');
    }
}

?>
