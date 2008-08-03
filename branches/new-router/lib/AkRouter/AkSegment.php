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

class SegmentDoesNotMatchParameterException extends RouteDoesNotMatchParametersException 
{ }


abstract class AkSegment 
{
    public     $name;
    protected  $delimiter;

    static protected $DEFAULT_REQUIREMENT='[^/.]+';  //default requirement matches all but stops on dashes
    
    function __construct($name,$delimiter)
    {
        $this->name        = $name;
        $this->delimiter   = $delimiter;
    }
    
    abstract public function isCompulsory();
    
    public function isOptional()
    {
        return !$this->isCompulsory();
    }
    
    public function isOmitable()
    {
        return false;
    }
    
    function __toString()
    {
        return $this->getRegEx();
    }

    abstract public function getRegEx();
    abstract public function generateUrlFromValue($value,$omit_optional_segments);
    
}

?>