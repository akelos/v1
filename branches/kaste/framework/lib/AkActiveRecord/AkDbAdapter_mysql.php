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
 * @component DbAdapter MySQL
 * @author Bermi Ferrer <bermi a.t akelos c.om> 2004 - 2007
 * @author Kaste 2007
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

/* nothing in here, its more a demo */

class AkDbAdapter_mysql extends AkDbAdapter
{
    
    /**
     * @param array $database_settings
     * @return string
     */
    function _constructDsn($database_settings)
    {
        $dsn  = 'mysqlt://';
        $dsn .= $database_settings['user'].':'.$database_settings['password'];
        $dsn .= !empty($database_settings['host']) ? '@'.$database_settings['host'] : '@localhost';
        $dsn .= !empty($database_settings['port']) ? ':'.$database_settings['port'] : '';
        $dsn .= '/'.$database_settings['database_name'];
        $dsn .= !empty($database_settings['options']) ? '?'.$database_settings['options'] : '';
        return $dsn;
    }
    
    function type()
    {
        return 'mysql';
    }

    function addLimitAndOffset(&$sql,$options)
    {
        if (isset($options['limit']) && $limit = $options['limit']){
            if (isset($options['offset']) && $offset = $options['offset'])
                $sql .= " LIMIT $offset, $limit";
            else
                $sql .= " LIMIT $limit";
        }
        return $sql;
    }
    
    /* SCHEMA */
    
    function renameColumn($table_name,$column_name,$new_name)
    {
        $column_details = $this->selectOne("SHOW COLUMNS FROM $table_name = '$column_name'");
        $column_type_definition = $column_details['type'];
        $this->sqlexecute("ALTER TABLE $table_name CHANGE COLUMN $column_name $new_name $column_type_definition");
    }
    
}
?>