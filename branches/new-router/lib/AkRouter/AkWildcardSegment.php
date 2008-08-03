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


class AkWildcardSegment extends AkDynamicSegment  
{

    public function isCompulsory()
    {
        return $this->default === COMPULSORY || $this->expectsExactSize();    
    }
    
    public function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        $multiplier = ($size = $this->expectsExactSize()) ? '{'. $size .'}' : '+';
        return "(?P<$this->name>(?:$this->delimiter{$this->getInnerRegEx()})$multiplier)$optional_switch";
    }
    
    public function extractValueFromUrl($url_part)
    {
        $url_part = substr($url_part,1); // the first char is the delimiter
        return explode('/',$url_part);
    }
    
    private function expectsExactSize()
    {
        return is_int($this->default) ? $this->default : false;
    }
    
    protected function generateUrlFor($value)
    {
        return $this->delimiter.join('/',$value);
    }
    
    protected function fulfillsRequirement($values)
    {
        if (!$this->hasRequirement()) return true;
        if (($size = $this->expectsExactSize()) && count($values) != $size) return false;

        $regex = "@^{$this->getInnerRegEx()}$@";
        foreach ($values as $value){
            if (!(bool) preg_match($regex,$value)) return false;
        }
        return true;
    }
    
}

?>