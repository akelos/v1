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
        $this->saveOriginalRequest();
        if(!$User = $this->getUserFromSession()){
            if($User = $this->getAuthenticatedUser()){
                $this->restoreOriginalRequest();
            }
        }

        if($User){
            $this->setCurrentUser($User);
        }
        return $User;
    }

    function getAuthenticatedUser()
    {
        return $this->{$this->getAuthenticationMethod()}();
    }

    function getAuthenticationMethod()
    {
        if(!empty($this->Controller->params['ak_login']) || $this->shouldDefaultToPostAuthentication()){
            return 'authenticateUsingPostedVars';
        }

        return 'authenticateUsingHttpBasic';
    }

    function authenticateUsingPostedVars()
    {
        $UserInstance =& new User();
        $login = @$this->Controller->params['ak_login'];
        $result =  $UserInstance->authenticate(@$login['login'], @$login['password']);
        if(!$result){
            if(!empty($this->Controller->params['ak_login'])){
                $this->Controller->flash['error'] = Ak::t('Invalid user name or password, please try again', null, 'account');
            }
            $this->redirectToSignInScreen();
        }
        return $result;
    }


    function saveOriginalRequest($force = false)
    {
        if(empty($this->Session['__OriginalRequest']) || $force){
            $this->Session['__OriginalRequest'] = serialize($this->Controller->Request);
        }
    }

    function restoreOriginalRequest()
    {
        if(!empty($this->Session['__OriginalRequest'])){
            $this->Controller->Request = unserialize($this->Session['__OriginalRequest']);
            $this->Controller->params = $this->Controller->Request->getParams();
            unset($this->Session['__OriginalRequest']);
        }
    }

    function redirectToSignInScreen()
    {
        $settings = Ak::getSettings('admin');
        $this->Controller->redirectTo($settings['sign_in_url']);
    }

    function authenticateUsingHttpBasic()
    {
        $settings = Ak::getSettings('admin');
        return $this->Controller->_authenticateOrRequestWithHttpBasic(Ak::t($settings['http_auth_realm'], null, 'account'), new User());
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

    function removeCurrentUserFromSession()
    {
        if($this->hasUserOnSession()){
            $this->Session['__CurrentUser'] = null;
        }
    }

    function setCurrentUserOnController($User)
    {
        $this->Controller->CurrentUser =& $User;
    }

    function removeCurrentUserFromController()
    {
        $this->Controller->CurrentUser = null;
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

    function unsetCurrentUser()
    {
        $this->CurrentUser = null;
        $this->removeCurrentUserFromController();
        $this->removeCurrentUserFromSession();
        User::unsetCurrentUser();
    }

    function shouldDefaultToPostAuthentication()
    {
        $settings = Ak::getSettings('admin');
        if(!empty($settings['default_authentication_method']) &&
        $settings['default_authentication_method'] == 'post'){
            return $this->isWebBrowser();
        }
    }

    function isWebBrowser()
    {
        return preg_match('/Mozilla|MSIE|Gecko|Opera/i',@$this->Controller->Request->env['HTTP_USER_AGENT']);
    }



}

?>