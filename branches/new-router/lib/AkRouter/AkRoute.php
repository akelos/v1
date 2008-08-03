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
require_once 'AkDynamicSegment.php';
require_once 'AkStaticSegment.php';
require_once 'AkVariableSegment.php';
require_once 'AkLangSegment.php';
require_once 'AkWildcardSegment.php';
require_once 'AkUrl.php';

class AkRoute extends AkObject 
{

    private $url_pattern;
    private $defaults;
    private $requirements;
    private $conditions;
    private $regex;
    private $segments;
    const   DELIMITER_CHAR_CLASS = '[/.]';   // Note: If you change this, take a look at AkSegment::$DEFAULT_REQUIREMENT too
    
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
        
        if (!preg_match($this->getRegex(),$url,$matches)) throw new RouteDoesNotMatchRequestException("Url doesn't match the regex of the route.");
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
            if ($skipped_optional) throw new RouteDoesNotMatchRequestException("Segment $name is missing.");
            $params[$name] = $this->segments[$name]->extractValueFromUrl($match);
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
        throw new RouteDoesNotMatchRequestException("Method does not match.");
    }

    /**
     * @throws RouteDoesNotMatchParametersException
     */
    function urlize($params)
    {
        $params = $this->urlEncode($params);

        $url = $this->buildUrlFromSegments($params);

        // $params now holds additional values which are not present in the url-pattern as 'dynamic-segments'
        $key_value_list = $this->getAdditionalKeyValueListForUrl($params);

        return new AkUrl($url,$key_value_list);
    }
    
    protected function buildUrlFromSegments(&$params)
    {
        $url_pieces    = array();
        $omit_defaults = true;
        foreach (array_reverse($this->getSegments()) as $name=>$segment){
            if (!$url_piece = $segment->generateUrlFromValue(@$params[$name],$omit_defaults)) continue;

            $url_pieces[] = $url_piece;
            unset ($params[$name]); 
            $omit_defaults = false;
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
                if ($this->defaults[$name] != $value) throw new RouteDoesNotMatchParametersException("Parameter $name is not dynamic.");
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
        
        $subject = $url_pattern;
        $pattern = '@'.self::DELIMITER_CHAR_CLASS.'@';
        $matches = preg_split($pattern,$subject,-1,PREG_SPLIT_NO_EMPTY | PREG_SPLIT_OFFSET_CAPTURE);
        foreach ($matches as $match){
            $url_part  = $match[0];
            $delimiter = $subject{$match[1]-1};
            
            $name = substr($url_part,1);
            switch ($this->segmentType($url_part)) {
            	case ':':
            	    switch ($name){
            	        case 'lang':
                            $segments[$name] = new AkLangSegment($name,$delimiter,@$defaults[$name],@$requirements[$name]);
                            break;
            	        default:
                            $segments[$name] = new AkVariableSegment($name,$delimiter,@$defaults[$name],@$requirements[$name]);
                            break;
            	    }
            	    break;
            	case '*':
                    $segments[$name] = new AkWildcardSegment($name,$delimiter,@$defaults[$name],@$requirements[$name]);
            	    break;
            	default:
                    $segments[] = new AkStaticSegment($url_part,$delimiter);
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
    
    /*
     * Returns an array with the names of the dynamic segments. 
     * It's only used in AkRouterHelper; to avoid building the full segments-graph it uses a regex-match
     */
    function getNamesOfDynamicSegments()
    {
        preg_match_all('@'.self::DELIMITER_CHAR_CLASS.'[:*](\w+)@',$this->url_pattern,$matches);
        return ($matches[1]);
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