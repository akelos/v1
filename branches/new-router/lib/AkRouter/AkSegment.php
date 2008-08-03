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

class SegmentDoesNotMatchParametersException extends RouteDoesNotMatchParametersException 
{ }


class AkSegment 
{
    public     $name;
    protected  $delimiter;
    public     $default;
    protected  $requirement;  //default requirement matches all but stops on dashes
    
    static  $DEFAULT_REQUIREMENT='[^/.]+';  //default requirement matches all but stops on dashes
    
    function __construct($name,$delimiter,$default=null,$requirement=null)
    {
        $this->name        = $name;
        $this->delimiter   = $delimiter;
        $this->default     = $default;
        $this->requirement = $requirement;
    }
    
    function hasRequirement()
    {
        return $this->requirement ? true : false;
    }
    
    function getInnerRegEx()
    {
        if ($this->hasRequirement()) return $this->requirement;
        return self::$DEFAULT_REQUIREMENT;
    }
    
    function isOptional()
    {
        return !$this->isCompulsory();
    }
    
    function isCompulsory()
    {
        return $this->default === COMPULSORY;
    }
    
    function isOmitable()
    {
        return false;
    }
    
    function __toString()
    {
        return $this->getRegEx();
    }
    
    function getUrlPartFor($value=null,$omit_defaults)
    {
        if (is_null($value)){
            if ($this->isCompulsory()) throw new SegmentDoesNotMatchParametersException();
            if (!$omit_defaults && !$this->isOmitable()) throw new SegmentDoesNotMatchParametersException();
            return false;
        }else{
            if ($omit_defaults && $this->default == $value) return false;
            
            if (!$this->meetsRequirement($value)) throw new SegmentDoesNotMatchParametersException();
            return $this->insertPieceForUrl($value);
        }
    }
    
}

?>