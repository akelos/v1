<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
* @package ActiveRecord
* @subpackage Behaviours
* @author Arno Schneider <arno a.t. bermilabs dot com>
* @copyright Copyright (c) 2002-2007, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
* @ExtensionPoint BaseActiveRecord
*/
class ActsAsSluggableExtensions
{
    function get_slug() {
        if (isset($this->sluggable) && method_exists($this->sluggable,"getSlug")) {
            return $this->sluggable->getSlug();
        }
        return null;
    }
}
?>