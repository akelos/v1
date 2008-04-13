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
* @author Bermi Ferrer <bermi a.t akelos c.om>
* @copyright Copyright (c) 2002-2008, Akelos Media, S.L. http://www.akelos.org
* @license GNU Lesser General Public License <http://www.gnu.org/copyleft/lesser.html>
*/

require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');


class ActsAsLucene extends AkObserver
{
    private $_ActiveRecordInstance;
    public $options = array();

    function __construct(&$ActiveRecordInstance)
    {
        @ini_set("include_path",(ini_get("include_path").PATH_SEPARATOR.dirname(__FILE__).DS.'vendor'.DS.'Zend'));
        include_once("Zend/Search/Lucene.php");

        $this->_ActiveRecordInstance = $ActiveRecordInstance;
    }

    public function init($options = array())
    {
        $success =  $this->_ensureIsActiveRecordInstance($this->_ActiveRecordInstance);
        $singularized_model_name = AkInflector::underscore(AkInflector::singularize($this->_ActiveRecordInstance->getTableName()));
        $default_options = array(
            'base_path' => AK_BASE_DIR.DS.'index'.AK_ENVIRONMENT.DS,
        );

        $this->options = array_merge($default_options, $options);

        return $success;
    }


    private function _ensureIsActiveRecordInstance(&$ActiveRecordInstance)
    {
        if(is_object($ActiveRecordInstance) && method_exists($ActiveRecordInstance,'actsLike')){
            $this->_ActiveRecordInstance = $ActiveRecordInstance;
            $this->observe($ActiveRecordInstance);
        }else{
            trigger_error(Ak::t('You are trying to set an object that is not an active record.'), E_USER_ERROR);
            return false;
        }
        return true;
    }
    
    public function getIndex()
    {
        
    }
    
    # count hits for a query with multiple models
    public function totalHits($query, $models, $options = array())
    {
        
    }
    
    public function searchIds($query, $models, $options = array())
    {
        
    }
        
    public function search($query, $options = array(), $active_record_options = array())
    {
        
    }
    
    public function rebuildIndex($index_name)
    {
        
    }
    
    public function getIndexInstance($index_name)
    {
        
    }
    
    public function getMultiIndexInstance($index_names)
    {
        
    }
    
    public function ensureIndexBaseDirExists()
    {
        
    }
    
    public function indexModel()
    {
        
    }
        
    public function indexModels()
    {
        
    }
    
    public function addIndexableFields()
    {
        
    }
    
    public function indexRecordsById($ids)
    {
        
    }
    
    protected function afterSaveOnCreate(&$Object)
    {
        return true;
    }

    protected function afterSaveOnUpdate(&$Object)
    {
        return true;    
    }

    protected function afterDestroy(&$Object)
    {
        return true;
    }
}



?>