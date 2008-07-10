<?php

class AdminPluginInstaller extends AkInstaller
{
    function up_1()
    {
        $this->createTable('users', '
          id,
          login string(40) not null idx,
          email string(50) not null idx,
          password string(40) not null,
          password_salt string(16) not null,
          last_login_at,
          is_enabled bool default 1,
          name_last,
          name_first,
          address_1,
          address_2,
          city,
          postal_code,
          state,
          country_code c(3),
          telephone,
          lang,
          security_question_1,
          security_answer_1,
          security_question_2,
          security_answer_2,
          security_question_3,
          security_answer_3
        ');

        $this->createTable('roles', '
          id,
          name,
          description,
          is_enabled bool default 1,
          parent_id,
          lft integer(8) index,
          rgt integer(8) index,
        ');

        $this->createTable('roles_users', 'id, role_id, user_id', array('timestamp' => false));
        $this->createTable('permissions_roles', 'id, permission_id, role_id', array('timestamp' => false));
        $this->createTable('extensions', 'id, name, is_core, is_enabled');
        $this->createTable('permissions', 'id, name, extension_id');

        if(AK_ENVIRONMENT != 'testing' && empty($this->root_details)){
            echo "Enter data for master account (all fields required unless noted):\n";
            $this->root_details = array(
                'login' => $this->promptUserVar('Login.',  array('default'=>'admin')),
                'email' => $this->promptUserVar('Email.',  array('default'=>'root@example.com')),
                'password' => $this->promptUserVar('Password.', array('default'=>'admin')),
                'name_first' => $this->promptUserVar('First name'),
                'name_last' => $this->promptUserVar('Last name'),
                'address_1' => $this->promptUserVar('Address, line 1 of 2'),
                'address_2' => $this->promptUserVar('Address, line 2 of 2 (optional)',array('optional'=>true)),
                'postal_code' => $this->promptUserVar('Postal code'),
                'city' => $this->promptUserVar('City'),
                'state' => $this->promptUserVar('State (optional)',array('optional'=>true)),
                'country_code' => $this->promptUserVar('Country code'),
                'telephone' => $this->promptUserVar('Telephone'),
                'security_question_1' => $this->promptUserVar('Security question 1'),
                'security_answer_1' => $this->promptUserVar('Security answer 1'),
                'security_question_2' => $this->promptUserVar('Security question 2'),
                'security_answer_2' => $this->promptUserVar('Security answer 2'),
                'security_question_3' => $this->promptUserVar('Security question 3'),
                'security_answer_3' => $this->promptUserVar('Security answer 3'),
            );
        }

        $this->addDefaults();
    }

    function down_1()
    {
        $this->dropTables('users, roles, roles_users, permissions_roles,  permissions, extensions');
    }

    function addDefaults()
    {
        if(AK_ENVIRONMENT == 'testing'){
            return ;
        }
        Ak::import('User', 'Role', 'Permission', 'Extension');
        $this->createExtensions();
        $this->createRoles();
        $this->createAdministrator();
    }

    function createExtensions()
    {
        $Extension =& new Extension();
        $this->AdminUsers =& $Extension->create(array('name'=>'Admin::Users','is_core'=>true, 'is_enabled' => true));
        $this->AdminPermissions =& $Extension->create(array('name'=>'Admin::Permissions','is_core'=>true, 'is_enabled' => true));
        $this->AdminRoles =& $Extension->create(array('name'=>'Admin::Roles','is_core'=>true, 'is_enabled' => true));
        $this->AdminDashboard =& $Extension->create(array('name'=>'Admin::Dashboard','is_core'=>true, 'is_enabled' => true));
        $this->AdminMenuTabs =& $Extension->create(array('name'=>'Admin Menu Tabs','is_core'=>true, 'is_enabled' => true));
    }

    function createRoles()
    {
        $Role =& new Role();
        $ApplicationOwner =& $Role->create(array('name' => 'Application owner'));

        $Administrator =& $ApplicationOwner->addChildrenRole('Administrator');

        foreach (Ak::toArray('add,destroy,edit,index,listing,show') as $action){
            $Administrator->addPermission(array('name'=>$action.' action', 'extension' => $this->AdminUsers));
        }
        $Administrator->addPermission(array('name'=>'Manage Users (users controller)', 'extension' => $this->AdminMenuTabs));
        $Administrator->addPermission(array('name'=>'Accounts (users controller, listing action)', 'extension' => $this->AdminMenuTabs));
        $Administrator->addPermission(array('name'=>'Edit other users', 'extension' => $this->AdminUsers));

        $NormalUser =& $Administrator->addChildrenRole('Registered user');
        $NormalUser->addPermission(array('name'=>'index action', 'extension' => $this->AdminDashboard));
        $NormalUser->addPermission(array('name'=>'Dashboard (dashboard controller)', 'extension' => $this->AdminMenuTabs));
    }

    function createAdministrator()
    {
        $Role =& new Role();
        $ApplicationOwner =& new User(array(
        'login'=>$this->root_details['login'],
        'email'=>$this->root_details['email'],
        'password'=> $this->root_details['password'],
        'password_confirmation'=>$this->root_details['password'],
        'name_first' => $this->root_details['name_first'],
        'name_last' => $this->root_details['name_last'],
        'address_1' => $this->root_details['address_1'],
        'address_2' => $this->root_details['address_2'],
        'postal_code' => $this->root_details['postal_code'],
        'city' => $this->root_details['city'],
        'state' => $this->root_details['state'],
        'country_code' => $this->root_details['country_code'],
        'telephone' => $this->root_details['telephone'],
        'security_question_1' => $this->root_details['security_question_1'],
        'security_answer_1' => $this->root_details['security_answer_1'],
        'security_question_2' => $this->root_details['security_question_2'],
        'security_answer_2' => $this->root_details['security_answer_2'],
        'security_question_3' => $this->root_details['security_question_3'],
        'security_answer_3' => $this->root_details['security_answer_3']));
        $ApplicationOwner->role->add($Role->findFirstBy('name', 'Application owner'));
        $ApplicationOwner->save();
    }
}

?>