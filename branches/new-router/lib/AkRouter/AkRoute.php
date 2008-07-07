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
class RouteDoesNotMatchParametersException extends Exception 
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
     */
    function parametrize(AkRequest $Request)
    {
        $this->ensureRequestMethod($Request->getMethod());
        
        $params = $this->extractParamsFromUrl($Request->getRequestedUrl());
        $this->addDefaults($params);
        
        $params = $this->urlDecode($params);
        return $params;
    }
    
    protected function extractParamsFromUrl($url)
    {
        if ($url=='/') $url = '';
        
        if (!preg_match($this->getRegex(),$url,$matches)) throw new RouteDoesNotMatchRequestException();
        array_shift($matches);   //throw away the "all-match", we only need the groups

        $params = array();
        $skipped_optional = false;
        foreach ($matches as $name=>$match){
            if (is_int($name)) continue;  // we use named-subpatterns, anything else we throw away
            if (empty($match)) {
                if (!$this->segments[$name]->isOmitable()){
                    $skipped_optional = true;
                }
                continue;  
            }
            if ($skipped_optional) throw new RouteDoesNotMatchRequestException();
            $params[$name] = $this->segments[$name]->addToParams($match);
        }
        return $params;
    }
    
    protected function addDefaults(&$params)
    {
        foreach ($this->defaults as $name=>$value){
            if (!isset($params[$name])){
                $params[$name] = $value;
            }
        }
    }
    
    protected function ensureRequestMethod($method)
    {
        if (!isset($this->conditions['method'])) return true;
        if ($this->conditions['method'] === ANY) return true;

        if (strstr($this->conditions['method'],$method)) return true;
        throw new RouteDoesNotMatchRequestException();
    }

    /**
     * @throws RouteDoesNotMatchParametersException
     */
    function urlize($params,$rewrite_enabled=AK_URL_REWRITE_ENABLED)
    {
        $params = $this->urlEncode($params);

        $url = $this->buildUrlFromSegments($params);

        // $params now holds additional values which are not present in the url-pattern as 'dynamic-segments'
        $key_value_list = $this->getAdditionalKeyValueListForUrl($params);

        $prefix = $rewrite_enabled ? ''  : '/?ak=';
        $concat = $key_value_list  ? ($rewrite_enabled ? '?' : '&') : '';
        $url = $prefix.$url.$concat.$key_value_list;
        return $url;
    }
    
    protected function buildUrlFromSegments(&$params)
    {
        $url_pieces    = array();
        $omit_defaults = true;
        foreach (array_reverse($this->getSegments()) as $segment){
            if ($segment instanceof AkSegment){
                $name = $segment->name;
                if (!isset($params[$name])){
                    if ($segment->isCompulsory()) throw new RouteDoesNotMatchParametersException();
                    if (!$omit_defaults && !$segment->isOmitable()) throw new RouteDoesNotMatchParametersException();
                }else{
                    $desired_value = $params[$name];
                    if ($omit_defaults && $segment->default == $desired_value) continue;
                    
                    if (!$segment->meetsRequirement($desired_value)) throw new RouteDoesNotMatchParametersException();
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
    
    protected function getAdditionalKeyValueListForUrl($params)
    {
        if (empty($params)) return '';
        
        $key_value_pairs = array();
        foreach ($params as $name=>$value){
            if (isset($this->defaults[$name])){
                // don't override defaults that don't correspond to dynamic segments, but break
                if ($this->defaults[$name] != $value) throw new RouteDoesNotMatchParametersException();
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
    
    protected function buildSegments($url_pattern,$defaults,$requirements)
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
    
    protected function segmentType($name)
    {
        if ($name) return $name{0};
        return false;
    }
    
    /**
    * Url decode a string or an array of strings
    */
    private function urlDecode($input)
    {
        array_walk_recursive($input,array($this,'_urldecode'));
        return $input;
    }
    
    private function _urldecode(&$input)
    {
        $input = urldecode($input);
    }

    /**
    * Url encodes a string or an array of strings
    */
    private function urlEncode($input)
    {
        array_walk_recursive($input,array($this,'_urlencode'));
        return $input;
    }
    
    private function _urlencode(&$input)
    {
        $input = urlencode($input);
    }
    
}

?>