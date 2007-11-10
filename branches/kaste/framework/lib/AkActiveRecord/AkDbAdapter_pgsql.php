<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

// +----------------------------------------------------------------------+
// | Akelos Framework - http://www.akelos.org                             |
// +----------------------------------------------------------------------+
// | Copyright (c) 2002-2006, Akelos Media, S.L.  & Bermi Ferrer Martinez |
// | Released under the GNU Lesser General Public License, see LICENSE.txt|
// +----------------------------------------------------------------------+

/**
 * @package ActiveRecord
 * @subpackage Base
 * @component DbAdapter PostgreSQL
 * @author Bermi Ferrer <bermi a.t akelos c.om> 2004 - 2007
 * @author Kaste 2007
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

/* nothing in here, its more a demo */

class AkDbAdapter_pgsql extends AkDbAdapter
{
    
    
    function type()
    {
        return 'postgre';
    }
    
    function renameColumn($table_name,$column_name,$new_name)
    {
        return $this->sqlexecute("ALTER TABLE $table_name RENAME COLUMN $column_name TO $new_name");
    }
    
}
?>