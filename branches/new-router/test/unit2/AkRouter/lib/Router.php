<?php
define ('OPTIONAL','OPTIONAL');

class NoMatchingRouteException extends Exception 
{ }

class Router extends AkObject 
{

    private $routes = array();
    
    function connect($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->handleDeprecatedApi($defaults,$requirements);
        return $this->addRoute(null,new Route($url_pattern,$defaults,$requirements,$conditions));
    }
    
    function handleDeprecatedApi(&$defaults,&$requirements)
    {
        $this->deprecatedMoveExplicitRequirementsFromDefaultsToRequirements($defaults,$requirements);
        $this->deprecatedMoveImplicitRequirementsFromDefaultsToRequirements($defaults,$requirements);
        $this->deprecatedRemoveDelimitersFromRequirements($requirements);
        $this->deprecatedRemoveExplicitOptional($defaults);
    }
    
    function deprecatedRemoveDelimitersFromRequirements(&$requirements)
    {
        foreach ($requirements as &$value){
            $trimmed = trim($value,'/');
            #if ($trimmed !== $value) Ak::deprecateWarning('Don\'t use delimiters in the requirements of your routes.');
            $value = $trimmed;
        }
    }
    
    function deprecatedMoveImplicitRequirementsFromDefaultsToRequirements(&$defaults,&$requirements)
    {
        foreach ($defaults as $key=>$value){
            if (trim($value,'/') !== $value){
                #Ak::deprecateWarning('Don\'t use implicit requirements in the defaults-array. Move it explicitly to the requirements-array.');
                $requirements[$key] = $value;
                unset ($defaults[$key]);
            }
        }
    }
    
    function deprecatedRemoveExplicitOptional(&$defaults)
    {
        foreach ($defaults as $key=>$value){
            if ($value === OPTIONAL) unset ($defaults[$key]);
        }
    }
    
    function deprecatedMoveExplicitRequirementsFromDefaultsToRequirements(&$defaults,&$requirements)
    {
        foreach ($defaults as $key=>$value){
            if ($key == 'requirements'){
                $requirements = array_merge($value,$requirements);
                unset($defaults[$key]);            
            }
        }
    }
    
    function addRoute($name = null,Route $route)
    {
        $name ? $this->routes[$name] = $route : $this->routes[] = $route;
        return $route;
    }
    
    function getRoutes()
    {
        return $this->routes;
    }
    
    function match(AkRequest $Request)
    {
        foreach ($this->routes as $route){
            $params = $route->parametrize($Request);
            if ($params){
                $this->currentRoute = $route;
                return $params;
            }
        }
        throw new NoMatchingRouteException();
    }
    
    function urlize($params)
    {
        foreach ($this->routes as $route){
            $url = $route->urlize($params);
            if ($url) return $url;
        }
        throw new NoMatchingRouteException();
    }
    
    /**
     * catches
     *    :name_url($params) and maps to ->urlizeUsingNamedRoute(:name,$params) 
     *    :name($args*)      and maps to ->connectNamed(:name,$args*)
     */
    function __call($name,$args)
    {
        if (preg_match('/^(.*)_url$/',$name,$matches)){
            array_unshift($args,$matches[1]);
            return call_user_func_array(array($this,'urlizeUsingNamedRoute'),$args);
        }else{
            array_unshift($args,$name);
            return call_user_func_array(array($this,'connectNamed'),$args);
        }
    }

    private function connectNamed($name,$url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->handleDeprecatedApi($defaults,$requirements);        
        return $this->addRoute($name,new Route($url_pattern,$defaults,$requirements,$conditions));
    }
    
    private function urlizeUsingNamedRoute($name,$params)
    {
        $url = $this->routes[$name]->urlize($params);
        if (!$url) throw new NoMatchingRouteException();
        return $url;
    }
    
}

?>