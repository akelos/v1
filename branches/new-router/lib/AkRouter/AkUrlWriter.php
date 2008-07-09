<?php

class AkUrlWriter
{

    /**
     * @var AkRequest
     */
    private $Request;
    
    /**
     * @var AkRouter
     */
    private $Router;
    
    function __construct($Request, AkRouter $Router=null)
    #function __construct(AkRequest $Request, AkRouter $Router)
    {
        if (!$Router){
            $Router = AkRouter::getInstance();
        }
        $this->Request = $Request;
        $this->Router  = $Router;    
    }
    
    function urlFor($options = array())
    {
        return $this->rewrite($this->rewriteOptions($options));
    }
    
    /**
     * This methods are required for retrieving available controllers for URL Routing
     */
    function rewriteOptions($options)
    {
        $last_parameters = $this->Request->getParameters();
        if(!empty($options['controller']) && strstr($options['controller'], '/')){
            $options['module'] = substr($options['controller'], 0, strrpos($options['controller'], '/'));
            $options['controller'] = substr($options['controller'], strrpos($options['controller'], '/') + 1);
        }
        $options['controller'] = empty($options['controller']) ? $last_parameters['controller'] : $options['controller'];
        return $options;
    }
    
    function rewrite($options = array())
    {
        return $this->_rewriteUrl($this->_rewritePath($options), $options);
    }

    /**
     * Given a path and options, returns a rewritten URL string
     */
    function _rewriteUrl($path, $options)
    {
        $rewritten_url = '';
        if(empty($options['only_path'])){
            $rewritten_url .= !empty($options['protocol']) ? $options['protocol'] : $this->Request->getProtocol();
            $rewritten_url .= empty($rewritten_url) || strpos($rewritten_url,'://') ? '' : '://';
            $rewritten_url .= $this->_rewriteAuthentication($options);
            $rewritten_url .= !empty($options['host']) ? $options['host'] : $this->Request->getHostWithPort();
            $options = Ak::delete($options, array('user','password','host','protocol'));
        }

        $rewritten_url .= empty($options['skip_relative_url_root']) ? $this->Request->getRelativeUrlRoot() : '';

        if(empty($options['skip_url_locale'])){
            $locale = $this->Request->getLocaleFromUrl();
            if(empty($options['lang'])){
                $rewritten_url .= (empty($locale) ? '' : '/').$locale;
            }

        }

        $rewritten_url .= (substr($rewritten_url,-1) == '/' ? '' : (AK_URL_REWRITE_ENABLED ? '' : (!empty($path[0]) && $path[0] != '/' ? '/' : '')));
        $rewritten_url .= $path;
        $rewritten_url .= empty($options['trailing_slash']) ? '' : '/';
        $rewritten_url .= empty($options['anchor']) ? '' : '#'.$options['anchor'];

        return $rewritten_url;
    }

    function _rewriteAuthentication($options)
    {
        if(!isset($options['user']) && isset($options['password'])){
            return urlencode($options['user']).':'.urlencode($options['password']).'@';
        }else{
            return '';
        }
    }

    function _rewritePath($options)
    {
        if(!empty($options['params'])){
            foreach ($options['params'] as $k=>$v){
                $options[$k] = $v;
            }
            unset($options['params']);
        }
        if(!empty($options['overwrite_params'])){
            foreach ($options['overwrite_params'] as $k=>$v){
                $options[$k] = $v;
            }
            unset($options['overwrite_params']);
        }
        foreach (array('anchor', 'params', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root') as $k){
            unset($options[$k]);
        }
        #$path = AkRouter::getInstance()->urlize($options);
        $path = $this->Router->urlize($options);
        #$path = Ak::toUrl($options);
        return $path;
    }

    
    
}

?>