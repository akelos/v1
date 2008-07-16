<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2008, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * Native PHP URL rewriting for the Akelos Framework.
 * 
 * @package ActionController
 * @subpackage Router
 * @author Kaste <thdzDOTx a.t gm x N_et>
 * @copyright Copyright (c) 2002-2008, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */


define ('COMPULSORY','COMPULSORY');
define ('OPTIONAL','OPTIONAL');
define ('ANY','ANY');

require_once 'AkRouter'.DS.'AkRoute.php';
require_once 'AkRouter'.DS.'AkRouterHelper.php';

class NoMatchingRouteException extends Exception 
{ }

class AkRouter extends AkObject 
{
    public  $automatic_lang_segment = true;
    public  $generate_helper_functions = AK_GENERATE_HELPER_FUNCTIONS_FOR_NAMED_ROUTES;
    
    private $routes = array();
    
    function connect($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        return $this->connectNamed(null,$url_pattern,$defaults,$requirements,$conditions);
    }
    
    protected function handleApiShortcuts(&$url_pattern,&$defaults,&$requirements)
    {
        $this->addLanguageSegment($url_pattern);
        $this->deprecatedMoveExplicitRequirementsFromDefaultsToRequirements($defaults,$requirements);
        $this->deprecatedMoveImplicitRequirementsFromDefaultsToRequirements($defaults,$requirements);
        $this->deprecatedRemoveDelimitersFromRequirements($requirements);
        $this->deprecatedRemoveExplicitOptional($defaults);
    }
    
    private function addLanguageSegment(&$url_pattern)
    {
        if ($this->automatic_lang_segment) $url_pattern = '/:lang'.$url_pattern;
    }
    
    private function deprecatedRemoveDelimitersFromRequirements(&$requirements)
    {
        foreach ($requirements as &$value){
            if ($value{0}=='/'){
                #Ak::deprecateWarning('Don\'t use delimiters in the requirements of your routes.');
                $value = trim($value,'/');
            }
        }
    }
    
    private function deprecatedMoveImplicitRequirementsFromDefaultsToRequirements(&$defaults,&$requirements)
    {
        foreach ($defaults as $key=>$value){
            if ($value{0}=='/'){
                #Ak::deprecateWarning('Don\'t use implicit requirements in the defaults-array. Move it explicitly to the requirements-array.');
                $requirements[$key] = trim($value,'/');
                unset ($defaults[$key]);
            }
        }
    }
    
    private function deprecatedRemoveExplicitOptional(&$defaults)
    {
        foreach ($defaults as $key=>$value){
            if ($value === OPTIONAL){
                unset ($defaults[$key]);
            }
        }
    }
    
    private function deprecatedMoveExplicitRequirementsFromDefaultsToRequirements(&$defaults,&$requirements)
    {
        if (isset($defaults['requirements'])){
            $requirements = array_merge($defaults['requirements'],$requirements);
            unset($defaults['requirements']);            
        }
    }
    
    function addRoute($name = null,AkRoute $route)
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
            try {
                $params = $route->parametrize($Request);
                $this->currentRoute = $route;
                return $params;
            } catch (RouteDoesNotMatchRequestException $e) {}
        }
        throw new NoMatchingRouteException();
    }
    
    function urlize($params,$name = null)
    {
        if ($name){
            return $this->routes[$name]->urlize($params);
        }
        
        foreach ($this->routes as $route){
            try {
                $url = $route->urlize($params);
                return $url;
            } catch (RouteDoesNotMatchParametersException $e) {}
        }
        throw new NoMatchingRouteException();
    }
    
    function toUrl($params)
    {
        return $this->urlize($params);
    }
    
    /**
     * catches
     *    :name_url($params) and maps to ->urlizeUsingNamedRoute(:name,$params) 
     *    :name($args*)      and maps to ->connectNamed(:name,$args*)
     */
    function __call($name,$args)
    {
        if (preg_match('/^(.*)_url$/',$name,$matches)){
            $args[] = $matches[1];
            return call_user_func_array(array($this,'urlize'),$args);
        }else{
            array_unshift($args,$name);
            return call_user_func_array(array($this,'connectNamed'),$args);
        }
    }

    private function connectNamed($name,$url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->handleApiShortcuts($url_pattern,$defaults,$requirements);       
        $Route = new AkRoute($url_pattern,$defaults,$requirements,$conditions);
        if ($this->generate_helper_functions){
            AkRouterHelper::generateHelperFunctionsFor($name,$Route);
        }
        return $this->addRoute($name,$Route);
    }
    
    static $singleton;
    
    /**
     * @return AkRouter
     */
    static function getInstance()
    {
        if (!self::$singleton){
            $Map = self::$singleton = new AkRouter();
            $Map->loadMap();
        }
        return self::$singleton;
    }
    
    function loadMap($file_name=AK_ROUTES_MAPPING_FILE)
    {
        $Map = $this;
        include(AK_ROUTES_MAPPING_FILE);
    }
    
}

//somehow dirty and therefore outsourced
if (!defined('AK_URL_REWRITE_ENABLED')){
    if (!defined('AK_ENABLE_URL_REWRITE') || AK_ENABLE_URL_REWRITE){
        require_once 'AkRouter'.DS.'AkRouterConfig.php';
        AkRouterConfig::loadUrlRewriteSettings();
    }
}

?>