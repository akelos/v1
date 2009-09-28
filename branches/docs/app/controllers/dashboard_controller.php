<?php

class DashboardController extends ApplicationController
{
    function listing()
    {
        $this->rendered_posts = $this->_renderControllerAction('post', 'listing');
        $this->rendered_users = $this->_renderControllerAction('user', 'listing');
    }

    function _renderControllerAction($controller_name, $action)
    {
        $controller_name = AkInflector::camelize($controller_name).'Controller';
        require_once(AK_CONTROLLERS_DIR.DS.AkInflector::underscore($controller_name).'.php');
        $Controller = new $controller_name();
        $pagination_key = AkInflector::underscore($Controller->getControllerName()).'_page';
        $Controller->params = $this->params;
        $Controller->params['page'] = @$this->params[$pagination_key];
        $Controller->Template =& $this->Template;
        
        $Controller->default_url_options = array($pagination_key => @$this->params[$pagination_key]);
        $Controller->$action();
        return $Controller->renderTemplate($action);
    }
}


?>