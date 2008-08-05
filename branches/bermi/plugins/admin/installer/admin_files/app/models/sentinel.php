<?php

/**
 * The Sentinel is the agent who controls the credentials in the admin plugin.
 */
class Sentinel
{
    var $Controller;
    var $CurrentUser;
    var $Session;

    function init(&$Controller)
    {
        $this->Controller =& $Controller;
        $this->Session =& $this->Controller->Request->getSession();
    }

    function authenticate()
    {
        if(!$User = $this->getUserFromSession()){
            $User = $this->getAuthenticatedUser();
        }

        if($User){
            $this->setCurrentUser($User);
        }
        if($result = !empty($_SESSION['__CurrentUser'])){

        }
        return $result;
    }

    function getAuthenticatedUser()
    {
        return $this->{$this->getAuthenticationMethod()}();
    }

    function getAuthenticationMethod()
    {
        return 'authenticateUsingHttpBasic';
    }

    function authenticateUsingHttpBasic()
    {
        return $this->Controller->_authenticateOrRequestWithHttpBasic($this->t('Application Administration'), new User());
    }

    function hasUserOnSession()
    {
        return !empty($this->Session['__CurrentUser']);
    }

    function getUserFromSession()
    {
        return $this->hasUserOnSession() ? unserialize($this->Session['__CurrentUser']) : false;
    }

    function setCurrentUserOnSession($User, $force = false)
    {
        if(!$this->hasUserOnSession() || $force){
            $this->Session['__CurrentUser'] = serialize($User);
        }
    }
    function setCurrentUserOnController($User)
    {
        $this->Controller->CurrentUser =& $User;
    }

    function getCurrentUser()
    {
        return $this->CurrentUser;
    }

    function setCurrentUser(&$User)
    {
        $this->CurrentUser =& $User;
        $this->setCurrentUserOnController($User);
        $this->setCurrentUserOnSession($User);
        User::setCurrentUser($User);
    }



}

?>