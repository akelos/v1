<?php

class NoMatchingRouteException extends Exception 
{
    
}

class Router extends AkObject 
{

    private $routes = array();
    
    function connect($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        return $this->addRoute(null,new Route($url_pattern,$defaults,$requirements,$conditions));
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
    
}

?>