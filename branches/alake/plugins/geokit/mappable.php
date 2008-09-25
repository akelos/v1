<?php 
# Contains class and instance methods providing distance calcuation services.  This
# module is meant to be mixed into classes containing lat and lng attributes where
# distance calculation is desired.  
# 
# At present, two forms of distance calculations are provided:
# 
# * Pythagorean Theory (flat Earth) - which assumes the world is flat and loses accuracy over long distances.
# * Haversine (sphere) - which is fairly accurate, but at a performance cost.
# 
# Distance units supported are 'miles' and 'kms'.
require_once 'defaults.php';
require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');

class Mappable extends AkObserver
{
    var $_ActiveRecordInstance; 
    var $options = array();
    
    function __construct(&$ActiveRecordInstance) {
        $this->_ActiveRecordInstance =& $ActiveRecordInstance;    
        PI_DIV_RAD = 0.0174;
        KMS_PER_MILE = 1.609;
        EARTH_RADIUS_IN_MILES = 3963.19;
        EARTH_RADIUS_IN_KMS = EARTH_RADIUS_IN_MILES * KMS_PER_MILE;
        MILES_PER_LATITUDE_DEGREE = 69.1;
        KMS_PER_LATITUDE_DEGREE = MILES_PER_LATITUDE_DEGREE * KMS_PER_MILE;
        LATITUDE_DEGREES = EARTH_RADIUS_IN_MILES / MILES_PER_LATITUDE_DEGREE;
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
            getModelName().'Mappable'
        );
        $this->options = array_merge($default_options, $options);
        return $success;
    } // function init

    # Returns the distance between two points.  The from and to parameters are
    # required to have lat and lng attributes.  Valid options are:
    # 'units' - valid values are 'miles' or 'kms'. GEOKIT_DEFAULT_UNITS from 
    # config/config.php is the default.
    # 'formula' - valid values are 'flat' or 'sphere'.  GEOKIT_DEFAULT_FORMULA
    # from config/config.php is the default.

    public static function distance_between($from, $to, $options=array())
    {
        $from = LatLng::normalize($from);
        $to   = LatLng::normalize($to);
        $units = array_key_exists('units',$options) ? 
            $options['units'] : GEOKIT_DEFAULT_UNITS;
        $formula = array_key_exists('formula') ?
            $options['formula'] : GEOKIT_DEFAULT_FORMULA;
        switch ($formula) {
        case 'sphere':
            return self::units_sphere_multiplier($units) * 
                acos(sin(self::deg2rad($from->lat)) * sin(self::deg2rad($to->lat)) + 
                cos(self::deg2rad($from->lat))      * cos(self::deg2rad($to>lat))  * 
                cos(self::deg2rad($to->lng) - self::deg2rad($from->lng)));
        case 'flat':
            return sqrt((self::units_per_latitude_degree($units) * 
                ($from->lat - $to->lat))**2 + 
                (self::units_per_longitude_degree($from->lat, $units) * 
                ($from->lng - $to->lng))**2);
        }
     } // function distance_between

    # Returns heading in degrees (0 is north, 90 is east, 180 is south, etc)
    # from the first point to the second point. Typically, the instance 
    # methods will be used instead of this method.
    public static function heading_between($from,$to)
    {
        $from     = LatLng::normalize($from);
        $to       = LatLng::normalize($to);
        $d_lng    = self::deg2rad($to->lng - $from->lng);
        $from_lat = self::deg2rad($from->lat);
        $to_lat   = self::deg2rad($to->lat);
        $y        = sin($d_lng) * cos($to_lat);
        $x        = cos($from_lat) * sin($to_lat) - 
                    sin($from_lat) * cos($to_lat) * cos($d_lng);
        return self::to_heading(atan2($y,$x));
    } // function heading_between

    # Given a start point, distance, and heading (in degrees), provides
    # an endpoint. Returns a LatLng instance. Typically, the instance method
    # will be used instead of this method.
    public static function endpoint($start, $heading, $distance, $options=array())
    {
        $units    = array_key_exists('units',$options) ? 
            $options['units'] : GEOKIT_DEFAULT_UNITS;
        $radius   = units == 'miles' ? EARTH_RADIUS_IN_MILES : EARTH_RADIUS_IN_KMS;
        $start    = LatLng::normalize($start);
        $lat      = self::deg2rad($start->lat);
        $lng      = self::deg2rad($start->lng);
        $heading  = self::deg2rad($heading);
        $distance = (float)$distance;
        $end_lat  = asin(sin($lat) * cos($distance/$radius) +
                    cos($lat) * sin($distance/$radius) * cos($heading));
        $end_lng  = $lng + atan2(sin($heading) * sin($distance/$radius) * 
                    ($lat), cos($distance/$radius) - sin($lat) * sin($end_lat));
        return new LatLng(self::rad2deg($end_lat),self::rad2deg($end_lng));
    } // function endpoint

    # Returns the midpoint, given two points. Returns a LatLng. 
    # Typically, the instance method will be used instead of this method.
    # Valid option:
    #   'units' - valid values are 'miles' or 'kms' 
    #             (GEOKIT_DEFAULT_UNITS in config/config.php is the default)
    public static function midpoint_between($from,$to,$options=array())
    {
        $from     = LatLng->normalize($from);
        $to       = Latlng->normalize($to);  // added by alake
        $units    = array_key_exists('units',$options) ?
            $options['units'] : GEOKIT_DEFAULT_UNITS;
        $heading  = $from->heading_to($to);
        $distance = $from->distance_to($to,$options);
        return $from->endpoint($heading,$distance/2,$options);
    } // function midpoint_between
  
    # Geocodes a location using the multi geocoder.
    public static function geocode($location)
    {
        $result = Geocoders::MultiGeocoder->geocode($location);
        if($result->success) {
            return $result;
        }
        $msg = "Caught an error during Mappable::geocode call: Unsuccessful call";
        Geocoder::logger('error',$msg);
        return new GeoLoc;
    } // function geocode
   
    protected static function deg2rad($degrees)
    {
        return floatval($degrees) / 180.0 * pi();
    }
      
    protected static function rad2deg($radians) 
    {
        return floatval($radians) * 180.0 / pi();
    }
      
    protected static function to_heading($radians)
    {
        return (self::rad2deg($radians) + 360) % 360;
    }

    # Returns the multiplier used to obtain the correct distance units.
    protected static function units_sphere_multiplier($units)
    {
        return $units == 'miles' ? EARTH_RADIUS_IN_MILES : EARTH_RADIUS_IN_KMS;
    }

    # Returns the number of units per latitude degree.
    protected function units_per_latitude_degree($units)
    {
        return $units == 'miles' ? 
            MILES_PER_LATITUDE_DEGREE : KMS_PER_LATITUDE_DEGREE;
    }
    
    # Returns the number units per longitude degree.
    protected function units_per_longitude_degree($lat, $units)
    {
        $miles_per_longitude_degree =
            abs(LATITUDE_DEGREES * cos($lat * PI_DIV_RAD));
        return $units == 'miles' ? 
            $miles_per_longitude_degree : $miles_per_longitude_degree * KMS_PER_MILE;
    }
    # -----------------------------------------------------------------------------------------------
    # Instance methods below here
    # -----------------------------------------------------------------------------------------------
  
    # Extracts a LatLng instance. Use with models that are acts_as_mappable
    public function to_lat_lng()
    {
        if($his instanceof LatLng || $this instanceof GeoLoc) {
            return $this;
        }
        if($this instanceof ActsAsMappable) {
            return new LatLng(get_class($this)::lat_column_name,
                              get_class($this)::lng_column_name);
        }
        return null;
    }

    # Returns the distance from another point.  The other point parameter is
    # required to have lat and lng attributes.  Valid options are:
    # 'units' - valid values are 'miles' or 'kms'.
    #           The default is GEOKIT_DEFAULT_UNITS in config/config.php.
    # 'formula' - valid values are 'flat' or 'sphere'.
    #           The default is GEOKIT_DEFAULT_FORMULA in config/config.php.
    public function distance_to($other, $options=array())
    {
        return get_class($this)::distance_between($this, $other, $options);
    }

    public function distance_from($other, $options=array())
    {
        return get_class($this)::distance_between($this, $other, $options);
    }

    # Returns heading in degrees (0 is north, 90 is east, 180 is south, etc)
    # TO the given point. The given point can be a LatLng or a string to be Geocoded 
    public function heading_to($other)
    {
        return get_class($this)::heading_between($this,$other);
    }

    # Returns heading in degrees (0 is north, 90 is east, 180 is south, etc)
    # FROM the given point. The given point can be a LatLng or a string 
    # to be Geocoded 
    public function heading_from($other)
    {
        return get_class($this)::heading_between($other,$this);
    }
 
    # Returns the endpoint, given a heading (in degrees) and distance.  
    # Valid option is 'units' - valid values are 'miles' or 'kms'.
    # The default is GEOKIT_DEFAULT_UNITS in config/config.php.
    public function endpoint($heading,$distance,$options=array())
    {
        return get_class($this)::endpoint($this,$heading,$distance,$options);
    }

    # Returns the midpoint, given another point on the map.  
    # Valid option is 'units' - valid values are 'miles' or 'kms'.
    # The default is GEOKIT_DEFAULT_UNITS in config/config.php.
    public function midpoint_to($other, $options=array())
    {
        return get_class($this)::midpoint_between($this,$other,$options);
    }
} // class Mappable

class LatLng 
{
    private $lat;
    private $lng;

    # Accepts latitude and longitude or instantiates an empty instance
    # if lat and lng are not provided. Converted to floats if provided
    function __construct($lat = null, $lng = null)
    {
        if $lat && is_numeric($lat) {
            $lat = floatval($lat);
        }
        if $lng && is_numeric($lng) {
            $lng = floatval($lng);
        }
        $this->lat = $lat;
        $this->lng = $lng;
    }

    public function get($what)
    {
        $result = false;
        $vars = array_keys(get_class_vars(get_class($this)));

        foreach ($vars as $var) {   
            if ($what == $var) {
                eval('$result = $this->'.$var.';');
                return $result;
            }
        }
        return $result;
    }

    # Latitude attribute setter; stored as a float.
    public function set_lat ($lat)
    {
        if($lat) {
            $this->lat = floatval($lat);
        }
    }

    # Longitude attribute setter; stored as a float;
    public function set_lng($lng)
    {
        if($lng) {    
            $this->lng = floatval($lng);
        }
    }

    # Returns the lat and lng attributes as a comma-separated string.
    public function ll()
    {
        return $this->lat.",".$this->lng;
    }

    #returns a string with comma-separated lat,lng values
    public function to_s()
    {
        return ll();
    }

    #returns a two-element array
    public function to_a()
    {
        return array($this->lat,$this->lng);
    }

    # Returns true if the candidate object is logically equal.  Logical equivalence
    # is true if the lat and lng attributes are the same for both objects.
    public function equals ($other)
    {
        return $other instanceof LatLng ? 
            $this->lat == $other->lat && $this->lng == $other->lng : false;
    }

    # A *class* method to take anything which can be inferred as a point and generate
    # a LatLng from it. 
    # You should use this at any time that you're not sure what the input is,
    # and want to deal with it as a LatLng if at all possible. Can take:
    #  1) two arguments (lat,lng)
    #  2) a string in the format "37.1234,-129.1234" or "37.1234 -129.1234"
    #  3) a string which can be geocoded on the fly
    #  4) an array in the format [37.1234,-129.1234]
    #  5) a LatLng or GeoLoc (which is just passed through as-is)
    #  6) anything which acts_as_mappable -- a LatLng will be extracted from it
    public static function normalize($thing,$other=null)
    {
        # if an 'other' thing is supplied, normalize the input 
        # by creating an array of two elements
        if($other) {
            $thing = array($thing,$other);
        }
        if(is_string($thing)) {
            $thing = trim($thing);
            if(preg_match(/(\-?\d+\.?\d*)[, ] ?(\-?\d+\.?\d*)$/,$thing) > 0) {
                return new LatLng($thing[1],$thing[2]);
            }else{
                $result = MultiGeocoder->geocode($thing);
                if($result->success) {
                    return $result;
                }
                $msg = 'String "'.$thing.'" cannot be normalized as a LatLng.';
                Geocoder::logger('warning',$msg);
                return new LatLng;
            }
        }elseif(is_array($thing) && count($thing) == 2) {
            return new LatLng ($thing[0],$thing[1]);
        }elseif($thing instanceof LatLng || $thing instanceof GeoLoc) {
            return $thing;
        }elseif($thing instanceof ActsAsMappable) {
            return $thing->to_lat_lng;
        }
        $msg = 'An object of '.get_class($thing).' cannot be normalized ';
        $msg .= 'to a LatLng. We tried interpreting it as an array, string, ';
        $msg .= 'Mappable, etc., but no dice.';
        Geocoder::logger('warning',$msg);
        return new LatLng;
    } // function normalize
} // class LatLng

# This class encapsulates the result of a geocoding call
# It's primary purpose is to homogenize the results of multiple
# geocoding providers. It also provides some additional functionality, such as 
# the "full address" method for geocoders that do not provide a 
# full address in their results (for example, Yahoo), and the "is_us" method.
class GeoLoc extends LatLng
{
# Location attributes.  Full address is a concatenation of all values.  
# For example:100 Spear St, San Francisco, CA, 94101, US
# attr_accessor :street_address, :city, :state, :zip, :country_code, :full_address
# Attributes set upon return from geocoding.  Success will be true for successful
# geocode lookups.  The provider will be set to the name of the providing geocoder.
# Finally, precision is an indicator of the accuracy of the geocoding.
# attr_accessor :success, :provider, :precision
# Street number and street name are extracted from the street address attribute.
# attr_reader :street_number, :street_name

    private $street_number;
    private $street_name;
    public $street_address;
    public $city;
    public $state;
    public $zip;
    public $country_code;
    public $provider;
    public $success = false;
    public $precision = 'unknown';
    public $full_address;

    # Constructor expects an associated array of attributes.
    function __construct($h=array())
    {
        if(array_key_exists('street_number',$h)) {
            $this->street_number = $h['street_number'];
        }
        if(array_key_exists('street_name',$h)) {
            $this->street_name = $h['street_name'];
        }
        if(array_key_exists('street_address',$h)) {
            $this->street_address = $h['street_address'];
        }else{
#attr_reader :street_number, :street_name
            if(strlen($this->street_number) > 0) {
                $this->street_address = $this->street_number;
            }else{
                $this->street_address = '';
            }
            if(strlen($this->street_name) > 0) {
                $this->street_address .= $this->street_name;
            }
        }
        if(array_key_exists('city',$h)) {
            $this->city = $h['city'];
        }
        if(array_key_exists('state',$h)) {
            $this->state = $h['state'];
        }
        if(array_key_exists('zip',$h)) {
            $this->zip = $h['zip'];
        }
        if(array_key_exists('country_code',$h)) {
            $this->country_code = $h['country_code'];
        }
        if(array_key_exists('provider',$h)) {
            $this->provider = $h['provider'];
        }
        if(array_key_exists('success',$h)) {
            $this->success = $h['success'];
        }
# attr_accessor :success, :provider, :precision
        if(array_key_exists('precision',$h)) {
            $this->precision = $h['precision'];
        }else{
            if($this->success || strlen($this->provider) > 0) {
                $this->precision = $this->success ? 'true' : 'false';
                if(strlen($this->provider) > 0) {
                    $this->precision .= ', '.$this->provider;
                }
            }
        }
        if(array_key_exists('lat',$h)) {
            $this->lat = $h['lat'];
        }
        if(array_key_exists('lng',$h)) {
            $this->lng = $h['lng'];
        }
        if(array_key_exists('full_address',$h)) {
            $this->full_address = $h['full_address'];
        }else{
# attr_accessor :street_address, :city, :state, :zip, :country_code, :full_address
            if(strlen($this->street_address) > 0) {
                $this->full_address = $street_address;
            }
            if(strlen($this->city) > 0) {
                if(strlen($this->full_address) > 0) {
                    $this->full_address .= ', ';
                }
                $this->full_address .= $this->city;
            }
            if(strlen($this->state) > 0) {
                if(strlen($this->full_address) > 0) {
                    $this->full_address .= ', ';
                }
                $this->full_address .= $this->state;
            }
            if(strlen($this->zip) > 0) {
                if(strlen($this->full_address) > 0) {
                    $this->full_address .= ', ';
                }
                $this->full_address .= $this->zip;
            }
            if(strlen($this->country_code) > 0) {
                if(strlen($this->full_address) > 0) {
                    $this->full_address .= ', ';
                }
                $this->full_address .= $this->country_code;
            }
        }
    }

    public function get($what)
    {
        $result = false;
        $vars = array_keys(get_class_vars(get_class($this)));

        foreach ($vars as $var) {   
            if ($what == $var) {
                eval('$result = $this->'.$var.';');
                return $result;
            }
        }
        return $result;
    }

    # Returns true if geocoded to the United States.
    public function is_us()
    {
        return $this->country_code == 'US';
    }

    # full_address is provided by google but not by yahoo. 
    # It is intended that the google geocoding method will provide the 
    # full address, whereas for yahoo it will be derived from the parts
    # of the address we do have.
    public function full_address()
    {
        return $this->full_address ? $this->full_address : $this->to_geocodeable_s();
    }

    # Extracts the street number from the street address if the street address
    # has a value.
    public function street_number()
    {
        $nbr = '';
        if(isset($street_address)){
            foreach(explode(' ',$street_address) as $element) {
                if(is_numeric($element[0])) {
                    $limit = strlen($element);
                    for($ix=0;$ix<$limit;$ix++) {
                        if(is_numeric($element[$ix])) {
                            $nbr .= $element[$ix];
                        }else{
                            break;
                        }
                    }
                    break;
                }
            }
        }
        return $nbr;
    } // function street_number

    # Returns the street name portion of the street address.
    public function street_name()
    {
        $street = '';
        if(isset($street_address)){
            foreach(explode(' ',$street_address) as $element) {
                if(!is_numeric($element[0])) {
                    $street = strlen($street) > 0 ? $street.' '.$element : $element;
                }
            }
        }
        return $street;
    } // function street_name

    # gives you all the important fields as key-value pairs
    public function hash()
    {
        $result = array();
        $result['success']        = $this->success;
        $result['lat']            = $this->lat;
        $result['lng']            = $this->lng;
        $result['country_code']   = $this->country_code;
        $result['city']           = $this->city;
        $result['state']          = $this->state;
        $result['zip']            = $this->zip;
        $result['street_address'] = $this->$this->street_address;
        $result['provider']       = $this->provider;
        $result['full_address']   = $this->full_address;
        $result['is_us']          = $this->is_us();
        $result['ll']             = $this->ll();
        $result['precision']      = $this->precision;
        return $result;
    }

    public function to_hash()
    {
        return hash();
    }

    # Sets the city after capitalizing each word within the city name.
    public function set_city($city)
    {
        $result = '';
        if($city) {
            $city_ary = explode(' ',strtolower($city));
            foreach($city_ary as $element) {
                 $result .= ucfirst($element).' ';           
            }
            $result = trim($result);
        }
        $this->city = $result;
    }

    # Sets the street address after capitalizing each word within the street address.
    public function set_street_address($address)
    {
        $result = '';
        if($address) {
            $add_ary = explode(' ',strtolower($address));
            foreach($add_ary as $element) {
                 $result .= ucfirst($element).' ';           
            }
            $result = trim($result);
        }
        $this->street_address = $result;
    }  

    # Returns a comma-delimited string consisting of the street address, city, state,
    # zip, and country code.  Only includes those attributes that are non-blank.
    public function to_geocodeable_s()
    {
        $result = '';
        if(!is_null($this->street_address) && strlen($this->street_address) > 0) {
            $result = $this->street_address;
        }
        if(!is_null($this->city) && strlen($this->city) > 0) {
            if(strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $this->city;
        }
        if(!is_null($this->state) && strlen($this->state) > 0) {
            if(strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $this->state;
        }
        if(!is_null($this->zip) && strlen($this->zip) > 0) {
            if(strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $this->zip;
        }
        if(!is_null($this->country_code) && strlen($this->country_code) > 0) {
            if(strlen($result) > 0) {
                $result .= ', ';
            }
            $result .= $this->country_code;
        }
        return $result;
    } // function to_geocodeable_s

    # Returns a string representation of the instance.
    public function to_s()
    {
        $result =  "Provider: ".$this->provider."\n";
        $result .= "Street: ".$this->street_address."\n";
        $result .= "City: ".$this->city."\n";
        $result .= "State: ".$this->state."\n";
        $result .= "Zip: ".$this->zip."\n";
        $result .= "Latitude: ".$this->lat."\n";
        $result .= "Longitude: ".$this->lng."\n";
        $result .= "Country: ".$this->country_code."\n";
        $result .= "Success: ".$this->success;
        return $result;
    }
} // class GeoLoc
  
# Bounds represents a rectangular bounds, defined by the SW and NE corners
class Bounds
{
# sw and ne are LatLng objects
    public $sw, $ne;

    # provide sw and ne to instantiate a new Bounds instance
    function __construct($sw,$ne)
    {
        if(!($sw instanceof LatLng && $ne instanceof LatLng)) (
            $msg = 'The parameters to a Bounds class object must be LatLng. ';
            $msg .= 'The first parameter is a '.get_class($sw).'.  The second ';
            $msg .= 'is a '.get_class($ne).'.';
            Geocoder::logger('error',$msg);
            return null;
        }
        $this->sw = $sw;
        $this->ne = $ne;
    }

    #returns the a single point which is the center of the rectangular bounds
    public function center()
    {
        return $this->sw->midpoint_to($this->ne);
    }

    # a simple string representation:sw,ne
    public function to_s()
    {
        return $this->sw->to_s().','.$this->ne->to_s();
    }

    # a two-element array of two-element arrays: sw,ne
    public function to_a()
    {
        $sw = $this->sw->to_a();
        $ne = $this->ne->to_a();
        return array($sw,$ne);
    }

    # Returns true if the bounds contain the passed point.
    # allows for bounds which cross the meridian
    public function contains($point)
    {
        $point = LatLng::normalize($point);
        $result = $point->lat > $this->sw->lat && $point->lat < $this->ne->lat;
        if crosses_meridian() {
          $result &= $point->lng < $this->ne->lng || $point->lng > $this->sw->lng;
        }else{
          $result &= $point->lng < $this->ne->lng && $point->lng > $this->sw->lng;
        }
        return $result;
    }

    # returns true if the bounds crosses the international dateline
    public functon crosses_meridian()
    {
        return $this->sw->lng > $this->ne->lng;
    }

    # Returns true if the candidate object is logically equal.  Logical equivalence
    # is true if the lat and lng attributes are the same for both objects.
    public function equals($other)
    {
        return $other instanceof Bounds ? 
            $this->sw == $other->sw && $this->ne == $other->ne : false;
    }

    # returns an instance of bounds which completely encompases the given circle
    public function from_point_and_radius($point,$radius,$options=array())
    {
        $point = LatLng::normalize($point);
        $p0    = $point->endpoint(0,$radius,$options);
        $p90   = $point->endpoint(90,$radius,$options);
        $p180  = $point->endpoint(180,$radius,$options);
        $p270  = $point->endpoint(270,$radius,$options);
        $sw    = new LatLng($p180->lat,$p270->lng);
        $ne    = new LatLng($p0->lat,$p90->lng);
        return new Bounds($sw,$ne);
    }

    # Takes two main combinations of arguments to create a bounds:
    # point,point   (this is the only one which takes two arguments, both points.
    # A point is anything LatLng::normalize can handle (which is quite a lot)
    #
    # NOTE: Every $thing combination is assumed to pass points in the order $sw, $ne
    public function normalize($thing,$other=null)
    {   
        # maybe this will be simple -- 
        #  If an actual bounds object is passed, we can all go home
        if($thing instanceof Bounds) {
            return $thing;
        }
        # If there's no $other, $thing had better be a two-element array        
        if(!$other && is_array($thing) && count($thing) == 2) {
#            thing,other=thing 
            return $thing;
        }
        # Now that we're set with a thing and another thing, 
        # let LatLng do the heavy lifting.
        # Exceptions may be thrown
        return new Bounds(LatLng::normalize($thing),LatLng::normalize($other));
    }
}
?>

