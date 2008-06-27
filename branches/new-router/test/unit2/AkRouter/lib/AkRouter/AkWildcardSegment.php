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


class AkWildcardSegment extends AkSegment 
{

    function isCompulsory()
    {
        return $this->default === COMPULSORY || $this->expectsExactSize();    
    }
    
    function expectsExactSize()
    {
        return is_int($this->default) ? $this->default : false;
    }
    
    function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        $multiplier = ($size = $this->expectsExactSize()) ? '{'. $size .'}' : '+';
        return "((?:$this->delimiter{$this->getInnerRegEx()})$multiplier)$optional_switch";
    }
    
    function addToParams(&$params,$match)
    {
        $match = substr($match,1); // the first char is the delimiter
        $params[$this->name] = explode('/',$match);
    }
    
    function insertPieceForUrl($value)
    {
        return $this->delimiter.join('/',$value);
    }
    
    function meetsRequirement($values)
    {
        if (!$this->hasRequirement()) return true;
        if (($size = $this->expectsExactSize()) && count($values) != $size) return false;

        $regex = "|^{$this->getInnerRegEx()}$|";
        foreach ($values as $value){
            if (!(bool) preg_match($regex,$value)) return false;
        }
        return true;
    }
    
}

?>