<?php

defined('AK_DEFAULT_USER_ROLE') ? null : define('AK_DEFAULT_USER_ROLE', 'Registered user');

class User extends ActiveRecord
{
    var $habtm = array('roles' => array('unique'=>true));

    /**
     * @access private
     */
    var $__initial_attributes = array();
    var $__requires_password_confirmation = true;

    /**
     * We need to get initial values when instantiating to know if attributes like password have been changed
     */
    function __construct()
    {
        $attributes = (array)func_get_args();
        $this->__initial_attributes = isset($attributes[1]) && is_array($attributes[1]) ? $attributes[1] : array();
        return $this->init($attributes);
    }


    /**
     * Main authentication method
     * 
     * @param string $email
     * @param string $password
     * @return False if not found or not enabled, User instance is succedes
     */
    function authenticate($email, $password)
    {
        $UserInstance =& new User();
        if($User =& $UserInstance->find('first', array('conditions'=>array('email = ? AND __owner.is_enabled = ? AND _roles.is_enabled = ?', $email, true, true), 'include'=>'role')) && $User->isValidPassword($password)){
            $User->set('last_login_at', Ak::getDate());
            $User->save();
            return $User;
        }
        return false;
    }


    // Validation
    // ---------------

    function validate()
    {
        $this->validatesUniquenessOf('email', array('message'=>$this->t('email %email already in use', array('%email'=>$this->get('email')))));
        $this->validatesPresenceOf(array('email'));
        $this->validatesFormatOf('email', AK_EMAIL_REGULAR_EXPRESSION, $this->t('Invalid email address'));
        $this->validatesLengthOf('password', array('in'=>array(4, 40), 'too_long' => $this->t('pick a shorter password'), 'too_short' => $this->t('pick a longer password')));
    }

    function validatesPassword()
    {
        $requires_password_confirmation = $this->hasAttributeBeenModified('password') ? $this->__requires_password_confirmation : false;
        $this->validatesPresenceOf($requires_password_confirmation ? array('password','password_confirmation') : array('password'));
        $requires_password_confirmation ? $this->validatesConfirmationOf('password', $this->t('Must match confirmation')) : null;
        return strlen($this->getErrorsOn('password').$this->getErrorsOn('password_confirmation')) == 0;
    }

    function needsPasswordLengthValidation()
    {
        return $this->isNewRecord() || !empty($this->password);
    }

    function needsEmailValidation()
    {
        return empty($this->_byspass_email_validation);
    }

    function validatesExistanceOfOriginalPasswordWhenUpdatingEmail()
    {
        if($this->hasAttributeBeenModified('email')){
            if(!$this->isValidPassword($this->get('password'), true, true)){
                $this->addError('email', $this->t('can\' be modified unless you provide a valid password.'));
            }else{
                $this->set('password_confirmation', $this->get('password'));
            }
        }
    }

    function isValidPassword($password, $hash_password = true, $hash_using_original_email = false)
    {
        return $this->getPreviousValueForAttribute('password') == ($hash_password ? $this->sha1($password, $hash_using_original_email) : $password);
    }


    // Triggers
    // ---------------

    function beforeCreate()
    {
        $this->validatesPassword();
        $this->encryptPassword();
        return !$this->hasErrors();
    }

    function beforeDestroy()
    {
        return !$this->hasRootPrivileges();
    }

    function beforeUpdate()
    {
        $this->validatesExistanceOfOriginalPasswordWhenUpdatingEmail();
        $this->validatesPassword();
        $this->_encryptPasswordUnlessEmptyOrUnchanged();
        return !$this->hasErrors();
    }

    function afterSave()
    {
        $this->__initial_attributes = $this->getAttributes();
        return true;
    }

    function afterCreate()
    {
        if(empty($this->roles)){
            $this->role->load();
            $Role =& new Role();
            if($Role =& $Role->findFirstBy('name', AK_DEFAULT_USER_ROLE)){
                $this->role->set($Role);
            }
        }
        return true;
    }



    // Enabling disabling accounts
    // --------------------------


    function enable()
    {
        $this->updateAttribute('is_enabled', true);
    }

    function disable()
    {
        $this->updateAttribute('is_enabled', false);
    }




    // Inspecting original values
    // --------------------------


    function hasAttributeBeenModified($attribute)
    {
        return $this->getPreviousValueForAttribute($attribute) != $this->get($attribute);
    }

    function getPreviousValueForAttribute($attribute)
    {
        return $this->hasColumn($attribute) && isset($this->__initial_attributes[$attribute]) ? $this->__initial_attributes[$attribute] : null;
    }


    // Hashing
    // -----------------------

    function encryptPassword()
    {
        $this->set('password', $this->sha1($this->get('password')));
    }

    function sha1($phrase, $use_original_email = false)
    {
        $email = $use_original_email ? $this->getPreviousValueForAttribute('email') : $this->get('email');
        empty($this->password_salt) ? $this->set('password_salt', Ak::randomString(16)) : null;
        return sha1($this->password_salt.$phrase.$email);
    }

    function getToken()
    {
        return $this->sha1($this->sha1($this->get('updated_at').$this->get('email')).$this->get('password'));
    }

    function isTokenValid($token)
    {
        return $this->getToken() == $token;
    }

    function _encryptPasswordUnlessEmptyOrUnchanged()
    {
        if($this->hasAttributeBeenModified('password') || $this->get('password') == ''){
            $this->encryptPassword();
        }else{
            $this->set('password', $this->getPreviousValueForAttribute('password'));
        }
    }


    // Permissions
    // ----------------------
    function &getPermissions()
    {
        $this->role->load();
        $Permissions = array();
        if(!empty($this->roles)){
            foreach (array_keys($this->roles) as $k){
                $Permissions = array_merge($Permissions, $this->roles[$k]->getPermissions());
            }
        }
        return $Permissions;
    }

    function can($task, $extension = null, $force_reload = false)
    {
        if(!isset($this->_activeRecordHasBeenInstantiated)){
            $User =& User::getCurrentUser();
            return $User->can($task, $extension, $force_reload);
        }

        static $Permissions;
        if(!isset($Permissions) || $force_reload){
            $Permissions = array();
            $UserPermissions =& $this->getPermissions();
            foreach (array_keys($UserPermissions) as $k){
                $extension_id = $UserPermissions[$k]->get('extension_id');
                $Permissions[(empty($extension_id)?'core':$extension_id)][] = $UserPermissions[$k]->get('name');
            }
        }
        $extension_id = $this->_getExtensionId($extension);
        return (!empty($Permissions[$extension_id]) && in_array($task, $Permissions[$extension_id])) ? true : $this->_addRootPermission($task, $extension_id);
    }

    function hasRootPrivileges()
    {
        $this->role->load();
        return isset($this->roles[0]) ? $this->roles[0]->nested_set->isRoot() : false;
    }

    function _addRootPermission($task, $extension_id)
    {
        if($this->hasRootPrivileges()){
            $Permission =& new Permission();
            $Permission =& $Permission->findOrCreateBy('name AND extension_id', $task, $extension_id);
            $this->roles[0]->addPermission($Permission);
            return true;
        }
        return false;
    }

    function _getExtensionId($extension, $force_reload = false)
    {
        static $extenssion_ids = array();
        if(is_string($extension) && !is_numeric($extension)){
            if(isset($extenssion_ids[$extension]) && $force_reload == false){
                return $extenssion_ids[$extension];
            }
            $extension_key = $extension;
            Ak::import('Extension');
            $ExtensionInstance =& new Extension();
            $extension =& $ExtensionInstance->findOrCreateBy('name', $extension);
        }
        $extension = is_object($extension) ? $extension->getId() : (empty($extension)?'core':$extension);
        isset($extension_key) ? $extenssion_ids[$extension_key] = $extension : null;
        return $extension;
    }

    function getCurrentUser()
    {
        return User::_setCurrentUser(false);
    }

    function _setCurrentUser($CurrentUser)
    {
        static $_cached;
        if(!empty($CurrentUser)){
            $_cached = $CurrentUser;
        }elseif (empty($_cached)){
            trigger_error(Ak::t('Current user has not been set yet.'), E_USER_ERROR);
        }
        return $_cached;
    }

}


?>
