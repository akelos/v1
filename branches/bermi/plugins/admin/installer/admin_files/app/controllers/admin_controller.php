<?php

class AdminController extends ApplicationController
{
    var $app_models = array('user','role','permission','extension');
    var $protect_all_actions = true;
    //var $protected_actions = 'index,show,edit,delete'; // You can protect individual actions

    var $_admin_menu_options = array(
    'Dashboard'   => array('id' => 'dashboard', 'url'=>array('controller'=>'dashboard'), 'link_options'=>array(
            'accesskey'=>'h',
            'title' => 'general status and information'
    )),
    'Manage Users'   => array('id' => 'users', 'url'=>array('controller'=>'users'), 'link_options'=>array(
            'accesskey' => 'u',
            'title' => 'add user, change password, manage user settings'
    ))
    );
    
    var $admin_menu_options = array();
    var $controller_menu_options = array();
    
    function __construct()
    {
        $this->beforeFilter('authenticate');
        !empty($this->protected_actions) ? $this->beforeFilter('_protectAction') : null;
        !empty($this->protect_all_actions) ? $this->beforeFilter(array('_protectAllActions' => array('except'=>'action_privileges_error'))) : null;
    }

    function authenticate()
    {
        if(empty($_SESSION['__CurrentUser'])){
            if($this->CurrentUser =& $this->_authenticateOrRequestWithHttpBasic($this->t('Application Administration'), new User())){
                $_SESSION['__CurrentUser'] = serialize($this->CurrentUser);
            }
        }else{
            $this->CurrentUser = unserialize($_SESSION['__CurrentUser']);
        }
        if($result = !empty($_SESSION['__CurrentUser'])){
            User::_setCurrentUser($this->CurrentUser);
        }
        return $result;
    }


    function access_denied()
    {
        header('HTTP/1.0 401 Unauthorized');
        echo "HTTP Basic: Access denied.\n";
        exit;
    }

    function _protectAction()
    {
        $protected_actions = Ak::toArray($this->protected_actions);
        $action_name = $this->getActionName();
        if(in_array($action_name, $protected_actions) && !$this->CurrentUser->can($action_name.' action', 'Admin::'.$this->getControllerName())){
            $this->redirectTo(array('action'=>'protected_action'));
        }
    }

    function _protectAllActions()
    {
        if(!$this->CurrentUser->can($this->getActionName().' action', 'Admin::'.$this->getControllerName())){
            $this->redirectTo(array('action'=>'action_privileges_error', 'controller'=>'dashboard'));
        }
    }

    function _loadCurrentUserRoles()
    {
        if($this->CurrentUser->role->load()){
            $this->Roles = array();
            foreach (array_keys($this->CurrentUser->roles) as $k){
                $this->Roles = array_merge($this->CurrentUser->roles[$k]->nested_set->getFullSet(), $this->Roles);
            }
        }
        if (empty($this->Roles)){
            $this->flash['notice'] = $this->t('It seems like you don\'t have Roles on your site. Please fill in the form below in order to create your first role.');
            $this->redirectTo(array('controller' => 'role', 'action' => 'add'));
        }
    }
}

?>