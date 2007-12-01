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

require_once(AK_LIB_DIR.DS.'AkObject.php');

class AkDbAdapter extends AkObject 
{

    var $connection;
    var $settings;
    var $dictionary;
    var $debug=false;
    var $logger;
    
    /**
     * @param array $database_settings
     */
    function __construct($database_settings,$auto_connect = false)
    {
        $this->settings = $database_settings;
        if ($auto_connect) $this->connect();
        if (AK_LOG_EVENTS) $this->logger =& Ak::getLogger();
    }
    
    function __destruct()
    {
    }
    
    function connect()
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
            $this->connection->SetFetchMode(ADODB_FETCH_ASSOC);
            defined('AK_DATABASE_CONNECTION_AVAILABLE') ? null : define('AK_DATABASE_CONNECTION_AVAILABLE', true);
        }
    }
    
    function connected()
    {
        return !empty($this->connection);
    }
    
    /**
     * @param array $database_settings
     */
    /* static */
    function &getInstance($database_specifications = AK_DEFAULT_DATABASE_PROFILE,$auto_connect = true)
    {
        static $connections;
        
        $settings_hash = is_string($database_specifications) ? $database_specifications : AkDbAdapter::_hash($database_specifications);
        
        if (empty($connections[$settings_hash])){
            if (is_string($database_specifications)){
                global $database_settings;
                if (!empty($database_settings[$database_specifications])){
                    $database_specifications = $database_settings[$database_specifications];     
                } else {
                    trigger_error(Ak::t("Could not find the database profile '%profile_name' in config/config.php.",array('%profile_name'=>$database_specifications)),E_USER_ERROR);
                    $return = false; 
                    return $return;
                } 
            }
            //var_dump($settings_hash);
            //var_dump($database_specifications);
            
            $available_adapters = array('mysql','pgsql','sqlite');
            $class_name = 'AkDbAdapter';
            $designated_database = strtolower($database_specifications['type']);
            if (in_array($designated_database,$available_adapters)) {
                $class_name .= '_'.$designated_database;
                require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.$class_name.'.php');
            }
            $connections[$settings_hash] =& new $class_name($database_specifications,$auto_connect);
        }
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

    function &getDictionary()
    {
        if (empty($this->dictionary)){
            if (!$this->connected()) $this->connect();
            require_once(AK_CONTRIB_DIR.DS.'adodb'.DS.'adodb.inc.php');
            $this->dictionary =& NewDataDictionary($this->connection);
        }
        return $this->dictionary;
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
    
    function debug($on='switch')
    {
        if ($on=='switch') $this->debug = !$this->debug; 
                      else $this->debug = $on;
        //$this->connection->debug = $this->debug;
        return $this->debug;
    }
    
    function _log($message)
    {
        if (!AK_LOG_EVENTS) return;
        $this->logger->message($message);
    }
    
    function addLimitAndOffset(&$sql,$options)
    {
        if (isset($options['limit']) && $limit = $options['limit']){
            $sql .= " LIMIT $limit";
            if (isset($options['offset']) && $offset = $options['offset']){
                $sql .= " OFFSET $offset";
            }
        }
        return $sql;
    }
    
    /* DATABASE STATEMENTS - CRUD */
    
    function execute($sql,$message = 'SQL')
    {
        if (is_array($sql)) {
            $sql_string = array_shift($sql);
            $bindings = $sql; 
        } else $sql_string = $sql;
        
        $this->_log($message.': '.$sql_string);
        $result = isset($bindings) ? $this->connection->Execute($sql_string,$bindings) : $this->connection->Execute($sql_string);
        //$result = is_array($sql) ? $this->connection->Execute(array_shift($sql),$sql) : $this->connection->Execute($sql);
        if (!$result){
            $error_message = '['.$this->connection->ErrorNo().'] '.$this->connection->ErrorMsg();
            $this->_log('SQL Error: '.$error_message);
            if ($this->debug || AK_DEBUG) trigger_error("Tried '$sql_string'. Got: $error_message", E_USER_NOTICE);
        }
        return $result;
    }
    
    function incrementsPrimaryKeyAutomatically()
    {
        return true;
    }
    
    function getLastInsertedId($table,$pk)
    {
        return $this->connection->Insert_ID($table,$pk);
    }

    function getAffectedRows()
    {
        return $this->connection->Affected_Rows();
    }
    
    function insert($sql,$id=null,$pk=null,$table=null,$message = '')
    {
        $result = $this->execute($sql,$message);
        if (!$result) return false;
        return is_null($id) ? $this->getLastInsertedId($table,$pk) : $id;
    }
    
    function update($sql,$message = '')
    {
        $result = $this->execute($sql,$message);
        return ($result) ? $this->getAffectedRows() : false;
    }
    
    function delete($sql,$message = '')
    {
        $result = $this->execute($sql,$message);
        return ($result) ? $this->getAffectedRows() : false;
    }
    
    /**
    * Returns a single value, the first column from the first row, from a record
    */
    function selectValue($sql)
    {
        $result = $this->selectOne($sql);
        return !is_null($result) ? array_shift($result) : null;
    }

    /**
     * Returns an array of the values of the first column in a select:
     *   sqlSelectValues("SELECT id FROM companies LIMIT 3") => array(1,2,3)
     */
    function selectValues($sql)
    {
        $values = array();
        if($results = $this->select($sql)){
            foreach ($results as $result){
                $values[] = array_shift($result); 
            }
        }
        return $values;
    }

    /**
     * Returns a record array of the first row with the column names as keys and column values
     * as values.
     */
    function selectOne($sql)
    {
        $result = $this->select($sql);
        return  !is_null($result) ? array_shift($result) : null;
    }

    /**
     * alias for select
     */
    function selectAll($sql)
    {
        return $this->select($sql);
    }

    /**
    * Returns an array of record hashes with the column names as keys and
    * column values as values.
    */
    function select($sql,$message = '')
    {
        //$previous_fetch_mode = $GLOBALS['ADODB_FETCH_MODE'];
        //$GLOBALS['ADODB_FETCH_MODE'] = ADODB_FETCH_ASSOC;
        $result = $this->execute($sql,$message);
        if (!$result) return array();     
        //$GLOBALS['ADODB_FETCH_MODE'] = $previous_fetch_mode;

        $records = array();
        while ($record = $result->FetchRow()) {
            $records[] = $record;
        }
        $result->Close();
        return $records;
    }
      
    /* TRANSACTIONS */
    
    function startTransaction()
    {
        return $this->connection->StartTrans();  
    }

    function stopTransaction()
    {
        return $this->connection->CompleteTrans();    
    }
    
    function failTransaction()
    {
        return $this->connection->FailTrans();
    }
    
    function hasTransactionFailed()
    {
        return $this->connection->HasFailedTrans();
    }
    
    /* SCHEMA */
    
    function renameColumn($table_name,$column_name,$new_name)
    {
        trigger_error(Ak::t('renameColumn is not available for your DbAdapter. Using %db_type.',array('%db_type'=>$this->type())));
    }
    
    /* META */
    
    function availableTables()
    {
        return $this->connection->MetaTables();
    }
    
    function getColumnDetails($table_name)
    {
        return $this->connection->MetaColumns($table_name);
    }
    
    function getIndexes($table_name)
    {
        return $this->connection->MetaIndexes($table_name);
    }
    
    /* QUOTING */ 
    
    function quote_string($value)
    {
        return $this->connection->qstr($value);
    }
    
    function quote_datetime($value)
    {
        return $this->connection->DBTimeStamp($value);
    }
    
    function quote_date($value)
    {
        return $this->connection->DBDate($value);
    }
    
    // will be moved to postgre
    function escape_blob($value)
    {
        return $this->connection->BlobEncode($value);
    }
    
    // will be moved to postgre
    function unescape_blob($value)
    {
        return $this->connection->BlobDecode($value);
    }
    
}

?>