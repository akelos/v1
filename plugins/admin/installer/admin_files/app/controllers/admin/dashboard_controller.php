<?php

class Admin_DashboardController extends AdminController
{
    function index()
    {
    }

    function action_privileges_error()
    {
        $this->Response->addHeader('Status', 405);
        $this->flash_now['error'] = $this->t('You don\'t have enough privileges to perform selected action.');
    }
}

?>
