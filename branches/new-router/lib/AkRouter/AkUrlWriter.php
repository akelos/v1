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
        return $this->rewrite($options);
    }
    
    function rewrite($options = array())
    {
        list($params,$options) = $this->extractOptionsFromParameters($options);
        $this->rewriteParameters($params);
        return $this->_rewriteUrl($this->_rewritePath($params), $options);
    }
    
    function extractOptionsFromParameters($params)
    {
        $keywords = array('anchor', 'only_path', 'host', 'protocol', 'trailing_slash', 'skip_relative_url_root');
        
        $options = array_intersect_key($params,array_flip($keywords));
        $params  = array_diff_key($params,$options);
        
        if (isset($params['password']) && isset($params['user'])){
            $options['user'] = $params['user'];
            $options['password'] = $params['password'];
            unset($params['user'],$params['password']);
        }
        
        return array($params,$options);
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
        }

        $rewritten_url .= empty($options['skip_relative_url_root']) ? $this->Request->getRelativeUrlRoot() : '';

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
        #$path = Ak::toUrl($options);
        return $this->Router->urlize($options);
    }
    
    private function rewriteParameters(&$params)
    {
        $this->injectParameters($params); 
        $this->extractModuleFromControllerIfGiven($params);       
        $this->fillInLastParameters($params);
        $this->handleLocale($params);
        $this->overwriteParameters($params);
    }
    
    private function injectParameters(&$params)
    {
        if(!empty($params['params'])){
            $params = array_merge($params,$params['params']);
            unset($params['params']);
        }
    }
    
    private function extractModuleFromControllerIfGiven(&$params)
    {
        if(!empty($params['controller']) && strstr($params['controller'], '/')){
            $params['module'] = substr($params['controller'], 0, strrpos($params['controller'], '/'));
            $params['controller'] = substr($params['controller'], strrpos($params['controller'], '/') + 1);
        }
    }
    
    private function fillInLastParameters(&$params)
    {
        $old_params = array();
        foreach ($this->Request->getParametersFromRequestedUrl() as $k=>$v){
            if (array_key_exists($k,$params)){
                if (is_null($params[$k])) unset($params[$k]);
                break;
            }
            $old_params[$k] = $v;
        }
        $params = array_merge($old_params,$params);
    }

    private function handleLocale(&$params)
    {
        if (isset($params['skip_url_locale'])){
            if (!$params['skip_url_locale'] && empty($params['lang'])){
                $old_params = $this->Request->getParametersFromRequestedUrl();
                isset($old_params['lang']) ? $params['lang'] = $old_params['lang'] : null;
            }
            unset($params['skip_url_locale']);
        }
    }
    
    private function overwriteParameters(&$params)
    {
        if(!empty($params['overwrite_params'])){
            $params = array_merge($params,$params['overwrite_params']);
            unset($params['overwrite_params']);
        }
    }
    
}

?>