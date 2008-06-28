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

class RouteDoesNotMatchRequestException extends Exception 
{ }

require_once 'AkSegment.php';
require_once 'AkVariableSegment.php';
require_once 'AkLangSegment.php';
require_once 'AkWildcardSegment.php';

class AkRoute extends AkObject 
{

    private $url_pattern;
    private $defaults;
    private $requirements;
    private $conditions;
    private $regex;
    private $segments;
    
    function __construct($url_pattern, $defaults = array(), $requirements = array(), $conditions = array())
    {
        $this->url_pattern  = $url_pattern;    
        $this->defaults     = $defaults;
        $this->requirements = $requirements;
        $this->conditions   = $conditions;
    }
    
    /**
     * @throws RouteDoesNotMatchRequestException
     * @param AkRequest $Request
     * @return array $params
     */
    function parametrize(AkRequest $Request)
    {
        if (!$this->ensureRequestMethod($Request->getMethod())) throw new RouteDoesNotMatchRequestException();
        
        $params = array();
        if ($this->addUrlSegments($params,$Request->getRequestedUrl())===false) throw new RouteDoesNotMatchRequestException();
        $this->addDefaults($params);
        
        $params = $this->urlDecode($params);
        return $params;
    }
    
    function addUrlSegments(&$params,$url)
    {
        if ($url=='/') $url = '';
        
        if (!preg_match($this->getRegex(),$url,$matches)) return false;
        array_shift($matches);   //throw away the "all-match", we only need the groups

        $skipped_optional = false;
        foreach ($matches as $name=>$match){
            if (is_int($name)) continue;  // we use named-subpatterns, anything else we throw away
            if (empty($match)) {
                if (!$this->segments[$name]->isOmitable()){
                    $skipped_optional = true;
                }
                continue;  
            }
            if ($skipped_optional) return false;
            $params[$name] = $this->segments[$name]->addToParams($match);
        }
    }
    
    function addDefaults(&$params)
    {
        foreach ($this->defaults as $name=>$value){
            if (!isset($params[$name])){
                $params[$name] = $value;
            }
        }
    }
    
    function ensureRequestMethod($method)
    {
        if (!isset($this->conditions['method'])) return true;
        if ($this->conditions['method'] === ANY) return true;

        if (strstr($this->conditions['method'],$method)) return true;
        return false;
    }

    function urlize($params,$rewrite_enabled=AK_URL_REWRITE_ENABLED)
    {
        $params = $this->urlEncode($params);

        if (!$url = $this->buildUrlFromSegments($params)) return false;

        // $params now holds additional values which are not present in the url-pattern as 'dynamic-segments'
        $key_value_list = $this->getAdditionalKeyValueListForUrl($params);
        if ($key_value_list===false) return false;

        $prefix = $rewrite_enabled ? ''  : '/?ak=';
        $concat = $key_value_list  ? ($rewrite_enabled ? '?' : '&') : '';
        $url = $prefix.$url.$concat.$key_value_list;
        return $url;
    }
    
    function buildUrlFromSegments(&$params)
    {
        $url_pieces    = array();
        $omit_defaults = true;
        foreach (array_reverse($this->getSegments()) as $segment){
            if ($segment instanceof AkSegment){
                $name = $segment->name;
                if (!isset($params[$name])){
                    if ($segment->isCompulsory()) return false;
                    if (!$omit_defaults && !$segment->isOmitable()) return false;
                }else{
                    $desired_value = $params[$name];
                    if ($omit_defaults && $segment->default == $desired_value) continue;
                    
                    if (!$segment->meetsRequirement($desired_value)) return false;
                    $url_pieces[] = $segment->insertPieceForUrl($desired_value);
                    unset ($params[$name]); 
                    $omit_defaults = false;
                }
            }else{
                $url_pieces[] = $segment;
            }
        }
        $url = join('',array_reverse($url_pieces));
        if ($url=='') $url = '/';
        return $url;
    }
    
    function getAdditionalKeyValueListForUrl($params)
    {
        if (empty($params)) return '';
        
        $key_value_pairs = array();
        foreach ($params as $name=>$value){
            if (isset($this->defaults[$name])){
                // don't override defaults that don't correspond to dynamic segments, but break
                if ($this->defaults[$name] != $value) return false;
                // don't append defaults
                continue;
            }
            $key_value_pairs[] = "$name=$value";
        }
        return join('&',$key_value_pairs);
    }
    
    function getRegex()
    {
        if ($this->regex) return $this->regex;
        return $this->regex = '@^'.join('',$this->getSegments()).'$@';      
    }
    
    function getSegments()
    {
        if ($this->segments) return $this->segments;
        return $this->segments = $this->buildSegments($this->url_pattern,$this->defaults,$this->requirements);
    }
    
    function buildSegments($url_pattern,$defaults,$requirements)
    {
        $segments = array();
        $url_parts = explode('/',trim($url_pattern,'/'));
        foreach ($url_parts as $url_part){
            if (empty($url_part)) continue;
            
            $name = substr($url_part,1);
            switch ($this->segmentType($url_part)) {
            	case ':':
            	    switch ($name){
            	        case 'lang':
                            $segments[$name] = new AkLangSegment($name,'/',@$defaults[$name],@$requirements[$name]);
                            break;
            	        default:
                            $segments[$name] = new AkVariableSegment($name,'/',@$defaults[$name],@$requirements[$name]);
                            break;
            	    }
            	    break;
            	case '*':
                    $segments[$name] = new AkWildcardSegment($name,'/',@$defaults[$name],@$requirements[$name]);
            	    break;
            	default:
                    $segments[] = '/'.$url_part;
                    break;
            }
        }
        return $segments;        
    }
    
    function segmentType($name)
    {
        if ($name) return $name{0};
        return false;
    }
    
    /**
    * Url decode a string or an array of strings
    */
    function urlDecode($input)
    {
        if (is_scalar($input)){
            return urldecode($input);
        }elseif (is_array($input)){
            return array_map(array($this,'urlDecode'),$input);
        }
    }

    /**
    * Url encodes a string or an array of strings
    */
    function urlEncode($input)
    {
        if (is_scalar($input)){
            return urlencode($input);
        }elseif (is_array($input)){
            return array_map(array($this,'urlEncode'),$input);
        }
    }
    
}

?>