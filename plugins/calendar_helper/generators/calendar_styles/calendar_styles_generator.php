<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2007, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveSupport
 * @subpackage Generators
 * @author Bermi Ferrer <bermi a.t akelos c.om>
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

class CalendarStylesGenerator extends  AkelosGenerator
{
    function manifest()
    {
        Ak::copy($this->_generator_base_path.DS.'templates', AK_PUBLIC_DIR.DS.'stylesheets'.DS.'calendar');
    }

    function hasCollisions()
    {
        $this->collisions = array();
        if(is_dir(AK_PUBLIC_DIR.DS.'stylesheets'.DS.'calendar')){
            $this->collisions[] = Ak::t('%path directory already exists',array('%path'=>AK_PUBLIC_DIR.DS.'stylesheets'.DS.'calendar'));
        }
        return !empty($this->collisions);
    }

    function printLog()
    {
        echo Ak::t("Added calendar stylesheets at %path", array('%path'=>AK_PUBLIC_DIR.DS.'stylesheets'.DS.'calendar'))."\n";
    }
}

?>
