<?php
# Contains a class method geocode_ip_address which can be used to enable 
# automatic geocoding for request IP addresses.  The geocoded information 
# is stored in a cookie and in the session to minimize web service calls.  
# The point of the helper is to enable location-based websites to have a 
# best-guess for new visitors.
require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');

class IpGeocodeLookup extends AkObserver
{
    var $_ActiveRecordInstance;
    var $options = array();

    function __construct(&$ActiveRecordInstance)
    {
        $this->_ActiveRecordInstance =& $ActiveRecordInstance;
    }

    function init($options = array())
    {
        $success =  $this->_ensureIsActiveRecordInstance(
            $this->_ActiveRecordInstance);
        $singularized_model_name = AkInflector::underscore(
            AkInflector::singularize(
                $this->_ActiveRecordInstance->getTableName()
            )
        );
        $default_options = array(
        'class_name' => $this->_ActiveRecordInstance->
            getModelName().'IpGeocodeLookup'
        );
        $this->options = array_merge($default_options, $options);
        return $success;
    }


    function _ensureIsActiveRecordInstance(&$ActiveRecordInstance)
    {
        if(is_object($ActiveRecordInstance) && 
           method_exists($ActiveRecordInstance,'actsLike')) {
            $this->_ActiveRecordInstance =& $ActiveRecordInstance;
            $this->observe(&$ActiveRecordInstance);
        }else{
            trigger_error(Ak::t('You are trying to set an object that is not an active record.'), E_USER_ERROR);
            return false;
        }
        return true;
    }

    public static function geocode_ip_address(filter_options = array())
    {
        before_filter :store_ip_location, filter_options
    }


} // class IpGeocodeLookup
?>

