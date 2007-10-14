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
 * @component DbAdapter
 * @author Bermi Ferrer <bermi a.t akelos c.om> 2004 - 2007
 * @author Kaste 2007
 * @copyright Copyright (c) 2002-2006, Akelos Media, S.L. http://www.akelos.org
 * @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
 */

class AkDbAdapter
{

    var $connection;
    var $settings;
    static $methods = array();
    static $properties = array();
    
    /**
     * @param array $database_settings
     */
    function __construct($database_settings,$auto_connect = false)
    {
        $this->settings = $database_settings;
        if ($auto_connect) $this->establish_connection();
    }
    
    function __destruct()
    {
        var_dump(self::$methods);
        var_dump(self::$properties);
    }
    
    function __call($method,$args)
    {
        if (!in_array($method,self::$methods)) {
            self::$methods[] = $method;
        }
        if (method_exists($this->connection,$method)) {
            $result =& call_user_func_array(array(&$this->connection,$method),$args);
            return $result;
        }
    }
    
    function __get($property)
    {
        if (!in_array($property,self::$properties)) {
            self::$properties[] = $property;
        }
        if (property_exists($this->connection,$property)) {
            return $this->connection->{$property};
        }
    }
    
    function establish_connection()
    {
        $dsn = $this->_constructDsn($this->settings);
        require_once(AK_CONTRIB_DIR.DS.'adodb'.DS.'adodb.inc.php');
        $this->connection = AK_DEBUG ? NewADOConnection($dsn) : @NewADOConnection($dsn);
        if (!$this->connection){
            error_reporting(E_ALL);
            if(defined('AK_DATABASE_CONNECTION_FAILURE_CALLBACK') && function_exists(AK_DATABASE_CONNECTION_FAILURE_CALLBACK)){
                $fn = AK_DATABASE_CONNECTION_FAILURE_CALLBACK;
                $fn();
            }
            if(!AK_PHP5 && $this->type() == 'sqlite'){
                trigger_error(Ak::t("\nWarning, sqlite support is not available by default on PHP4.\n Check your PHP version by running \"env php -v\", and change the first line in your scripts/ so they point to a php5 binary\n\n"),E_USER_WARNING);
            }
            trigger_error(Ak::t("Connection to the database failed. %dsn", 
                    array('%dsn'=> AK_DEBUG ? preg_replace('/\/\/(\w+):(.*)@/i','//$1:******@', urldecode($dsn))."\n" : '')), 
                    E_USER_ERROR);
        } else {
            $this->connection->debug = AK_DEBUG == 2;
            defined('AK_DATABASE_CONNECTION_AVAILABLE') ? null : define('AK_DATABASE_CONNECTION_AVAILABLE', true);
        }
    }
    
    /* static */
    /**
     * @param array $database_settings
     * @return object 
     */
    function &getConnection($database_settings = null,$auto_connect = true)
    {
        static $connections,$defaults;
        if (empty($defaults)) {
            //global $default_database_settings;
            $defaults = $GLOBALS['default_database_settings'];
        }
        if (empty($database_settings)) {
            $database_settings = $defaults;
        }
        $settings_hash = AkDbAdapter::_hash($database_settings);
        if (empty($connections[$settings_hash])){
        var_dump($database_settings);
        var_dump($settings_hash);
            $available_adapters = array('mysql','pgsql','sqlite');
            $class_name = 'AkDbAdapter';
            $designated_database = strtolower($database_settings['type']);
            if (in_array($designated_database,$available_adapters)) {
                $class_name .= '_'.$designated_database;
                require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.$class_name.'.php');
            }
            $connections[$settings_hash] = new $class_name($database_settings,$auto_connect);
        }
        $connection = &$connections[$settings_hash]->connection;
        //return $connection;
        return $connections[$settings_hash];
    }
    
    /**
     * @param array $settings
     * @return string
     */
    function _hash($settings)
    {
        if (isset($settings['password'])) unset($settings['password']);
        return join(':',$settings);
    }
    
    /**
     * @param array $database_settings
     * @return string
     */
    function _constructDsn($database_settings)
    {
        $dsn  = $database_settings['type'].'://';
        $dsn .= $database_settings['user'].':'.$database_settings['password'];
        $dsn .= !empty($database_settings['host']) ? '@'.$database_settings['host'] : '@localhost';
        $dsn .= !empty($database_settings['port']) ? ':'.$database_settings['port'] : '';
        $dsn .= '/'.$database_settings['database_name'];
        $dsn .= !empty($database_settings['options']) ? $database_settings['options'] : '';
        return $dsn;
        
    }
    
    function type()
    {
        return $this->settings['type'];
    }
}

?>