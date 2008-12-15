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

    # A helper to geocode the user's location based on IP address,
    # and retain the location in a cookie.
    # The original function was called "geocode_ip_address".  To fit the
    # AkObserver instructions, the function is now named afterCreate.
    # This eliminates the need for "before_filter" in the original code 
    # that won't work in the PHP versin anyhow.
    public static function afterCreate()
    {
        $this->store_ip_location();
    }

    # Places the IP address' geocode location into the session if it 
    # can be found.  Otherwise, looks for a geo location cookie and
    # uses that value.  The last resort is to call the web service to
    # get the value.
    private function store_ip_location()
    {
        $geo_location = retrieve_location_from_cookie_or_service();
        if(!is_null($geo_location)) {
            $_SESSION['geo_location'] = $geo_location;
            $expire = time()+60*60*24*30; # 30 days from now
            $array_to_yaml = new AkArrayToYaml;
            $array_to_yaml->source = $geo_location->to_array();
            $yaml = $array_to_yaml->convert();
            setcookie('geo_location', $yaml, $expire);
        }
    }
    
    # Uses the stored location value from the cookie if it exists.  If
    # no cookie exists, calls out to the web service to get the location. 
    private function retrieve_location_from_cookie_or_service()
    {
        if(isset($_COOKIE['geo_location'])) {
            $yaml_to_array = new AkYamlToArray;
            $yaml_to_array->source = $_COOKIE['geo_location'];
            return new GeoLoc($yaml_to_array->convert());
        }
        $location = IpGeocoder::geocode(get_ip_address());
        if($location->success) {
            return $location;
        }
        return null;
    }
    
    # Returns the real ip address, though this could be the localhost ip
    # address.  No special handling here.
    private function get_ip_address()
    {
        return $_SERVER['REMOTE_ADDR'];
    }
} // class IpGeocodeLookup
?>

