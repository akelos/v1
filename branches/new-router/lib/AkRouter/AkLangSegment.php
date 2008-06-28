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


class AkLangSegment extends AkVariableSegment 
{

    function isOmmitable()
    {
        return true;
    }

    function getInnerRegEx()
    {
        if ($this->hasRequirement()) return $this->requirement;
        return '('.join('|',Ak::langs()).')';
    }
}

?>