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


class AkVariableSegment extends AkDynamicSegment  
{

    public function getRegEx()
    {
        $optional_switch = $this->isOptional() ? '?': '';
        return "(?:[$this->delimiter](?P<$this->name>{$this->getInnerRegEx()}))$optional_switch";
    }

    public function extractValueFromUrl($url_part)
    {
        return $url_part;
    }

    protected function generateUrlFor($value)
    {
        return $this->delimiter.$value;
    }
    
    protected function fulfillsRequirement($value)
    {
        if (!$this->hasRequirement()) return true;
        
        $regex = "@^{$this->getInnerRegEx()}$@";
        return (bool) preg_match($regex,$value);
    }

}

?>