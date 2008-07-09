<?php
require_once AK_LIB_DIR.DS.'AkRouter.php';

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
    
    private $values_from_request;
    
    function valuesFromRequest(AkRequest $Request)
    {
        if (!$this->values_from_request){
            $this->values_from_request = array(
                'relative_url_root' => $Request->getRelativeUrlRoot(),
                'protocol'          => $Request->getProtocol(),
                'host'              => $Request->getHostWithPort()
            );
        }
        return $this->values_from_request;
    }
    
    function urlFor($options = array())
    {
        return $this->rewrite($options);
    }
    
    function rewrite($options = array())
    {
        list($params,$options) = $this->extractOptionsFromParameters($options);
        $this->rewriteParameters($params);
        return (string)$this->Router->urlize($params)
                            ->setOptions(array_merge($this->valuesFromRequest($this->Request),$options));
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
    
    private function rewriteParameters(&$params)
    {
        $this->injectParameters($params); 
        $this->extractModuleFromControllerIfGiven($params);       
        $this->fillInLastParameters($params);
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
        $last_params = $this->Request->getParametersFromRequestedUrl();
        $this->handleLocale($params,$last_params);

        $old_params = array();
        foreach ($last_params as $k=>$v){
            if (array_key_exists($k,$params)){
                if (is_null($params[$k])) unset($params[$k]);
                break;
            }
            $old_params[$k] = $v;
        }
        $params = array_merge($old_params,$params);
    }

    private function handleLocale(&$params,&$last_params)
    {
        if (!empty($params['skip_url_locale'])){
            unset($last_params['lang']);
        }
        unset($params['skip_url_locale']);
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