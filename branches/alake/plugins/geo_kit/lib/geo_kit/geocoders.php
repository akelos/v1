<?php
include_once 'mappable.php';
include_once AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkHttpClient.php';

# Contains a set of geocoders which can be used independently if desired.  
# The list contains:
# * Google Geocoder - requires an API key.
# * Yahoo Geocoder - requires an API key.
# * Geocoder.us - for the United States.
#   May require authentication if performing more than the free request limit.
# * Geocoder.ca - for Canada; may require authentication as well.
# * IP Geocoder - geocodes an IP address using hostip.info's web service.
# * Multi Geocoder - provides failover for the physical location geocoders.
# 
# Some configuration is required for these geocoders.
#   They can be located in app/vendor/plugins/geo_kit/config/config.php

# Error which is thrown in the event a geocoding error occurs.
class Geocoders
{
    public $proxy_addr = GEOKIT_GEOCODERS_PROXY_ADDR;
    public $proxy_port = GEOKIT_GEOCODERS_PROXY_PORT;
    public $proxy_user = GEOKIT_GEOCODERS_PROXY_USER;
    public $proxy_pass = GEOKIT_GEOCODERS_PROXY_PASS;

    public $timeout;

    public $yahoo  = GEOKIT_YAHOO_GEOCODER_KEY;
    public $google = GEOKIT_GOOGLE_GEOCODER_KEY;
    public $geocoder_us = GEOKIT_GEOCODERS_GEOCODER_US;
    public $geocoder_ca = GEOKIT_GEOCODERS_GEOCODER_CA;
    public $provider_order;
    public $force_failure = array(); # This is for use only by multi_geocoder_test

    function __construct()
    {
        $this->timeout = defined('GEOKIT_GEOCODERS_TIMEOUT') ? 
            $this->timeout = GEOKIT_GEOCODERS_TIMEOUT : 0;
        $this->provider_order = explode(',',GEOKIT_PROVIDER_ORDER);
    }
} // class Geocoders

class GeocodeError
{
    function __construct($msg='')
    {
        error_reporting(E_ALL);
        Geocode::logger('error',$msg);
        throw new Exception($msg);
    }    
}

# The Geocoder base class which defines the interface to be used by all
# other geocoders.
class Geocoder
{

    function __construct()
    {
        $this->geocoders = new Geocoders;
    }

    # Main method which calls the do_geocode template method which subclasses
    # are responsible for implementing.  Returns a populated GeoLoc or an
    # empty one with a failed success code.
    function geocode($address)
    {    
        return $this->do_geocode($address);
    }
  
# Call the geocoder service using the timeout if configured.
    function call_geocoder_service($url)
    {
        $response_msg = array(
            100=>array('100 Continue','information'),
            101=>array('101 Switching Protocols','information'),
            102=>array('102 Processing','information'),
            200=>array('200 OK','success'),
            201=>array('201 Created','success'),
            202=>array('202 Accepted','success'),
            203=>array('203 Non-Authoriative Information','success'),
            204=>array('204 No Content','success'),
            205=>array('205 Reset Content','success'),
            206=>array('206 Partial Content','success'),
            207=>array('207 Multi-Status','success'),
            300=>array('300 Multiple Choices','redirection'),
            301=>array('301 Moved Permanently','redirection'),
            302=>array('302 Found','redirection'),
            303=>array('303 See Other','redirection'),
            304=>array('304 Not Modified','redirection'),
            305=>array('305 Use Proxy','redirection'),
            306=>array('306 (Unused)','redirection'),
            307=>array('307 Temporary Redirect','redirection'),
            400=>array('400 Bad Request','client error'),
            401=>array('401 Unauthorized','client error'),
            402=>array('402 Payment Granted','client error'),
            403=>array('403 Forbidden','client error'),
            404=>array('404 Not found','client error'),
            405=>array('405 Method Not Allowed','client error'),
            406=>array('406 Not Acceptable','client error'),
            407=>array('407 Proxy Authentication Required','client error'),
            408=>array('408 Request Time-out','client error'),
            409=>array('409 Conflict','client error'),
            410=>array('410 Gone','client error'),
            411=>array('411 Length Required','client error'),
            412=>array('412 Precondition Failed','client error'),
            413=>array('413 Request Entity Too Large','client error'),
            414=>array('414 Request-URI Too Large','client error'),
            415=>array('415 Unsupported Media Type','client error'),
            416=>array('416 Requested range not satisfiable','client error'),
            417=>array('417 Expectation Failed','client error'),
            422=>array('422 Unprocessable Entity','client error'),
            423=>array('423 Locked','client error'),
            424=>array('424 Failed Dependency','client error'),
            500=>array('500 Internal Server Error','server error'),
            501=>array('501 Not Implemented','server error'),
            502=>array('502 Bad Gateway','server error'),
            503=>array('503 Service Unavailable','server error'),
            504=>array('504 Gateway Timeout','server error'),
            505=>array('505 HTTP Version Not Supported','server error'),
            507=>array('507 Insufficient Storage','server error'));
        $http = new AkHttpClient;
        $options = array();

        if($this->geocoders->timeout > 0) {
            $options['timeout'] = $this->geocoders->timeout;
        }
        if(!is_null($this->geocoders->proxy_addr)) {
            $options['_proxy_host'] = $this->geocoders->proxy_addr;
            $options['_proxy_port'] = $this->geocoders->proxy_port;
            if(!is_null($this->geocoders->proxy_user)) {
                $options['_proxy_user'] = $this->geocoders->proxy_user;
                if(!is_null($this->geocoders->proxy_pass)) {
                    $options['_proxy_pass'] = $this->geocoders->proxy_pass;
                }
            }
        }

        $result = array();
        $instance = $http->getRequestInstance($url, 'GET', $options);
        $body = $http->get($url, $options);
        $result['code'] = $http->getResponseCode();
        if($response_msg[$result['code']][1] != 'success') {
            $result['body']    = $response_msg[$result['code']][0];
            $result['success'] = false;
        }else{
            $result['content-type']   = $http->getResponseHeader('Content-Type');
            $result['server']         = $http->getResponseHeader('Server');
            $result['content-length'] = $http->getResponseHeader('Content-Length');
            $result['body']           = $body;
            $result['http-version']   = $instance->_http;
            $result['success']        = true;
        }
        return $result;
    } // function call_geocoder_service

    public static function logger($type,$msg)
    { 
#        echo 'Log '.$type.': '.$msg."<br />\n";
#        switch ($type) {
#            case 'debug':    $this->log->debug($msg);    break;
#            case 'info':     $this->log->info($msg);     break;
#            case 'message':  $this->log->message($msg);  break;
#            case 'notice':   $this->log->notice($msg);   break;
#            case 'warning':  $this->log->warning($msg);  break;
#            case 'error':    $this->log->error($msg);    break;
#            case 'critical': $this->log->critical($msg); break;
#            default:         $this->log->critical("An attempt was made to log ".
#                "with a type of ".$type.".  The message was ".$msg);
#        }        
    }

    function yahoo_geocoder($address)
    {
        return YahooGeocoder::do_geocode($address);        
    }    

    function google_geocoder($address)
    {
        return GoogleGeocoder::do_geocode($address);        
    }    

    function ca_geocoder($address)
    {
        return CaGeocoder::do_geocode($address);        
    }    

    function us_geocoder($address)
    {
        return UsGeocoder::do_geocode($address);        
    }    

    function multi_geocoder($address)
    {
        return MultiGeocoder::do_geocode($address);        
    }    

    function ip_geocoder($address)
    {
        return IpGeocoder::do_geocode($address);        
    }    
} // class GeoCoder
    
# Geocoder CA geocoder implementation.  
# Requires that the GEOKIT_GEOCODERS_GEOCODER_CA constant in /config/config.php 
# contains true or false based upon whether authentication is to occur.  
# Conforms to the interface set by the Geocoder class.
#
# Returns a response like:
# <?xml version="1.0" encoding="UTF-8" <questionmark>>
# <geodata>
#   <latt>49.243086</latt>
#   <longt>-123.153684</longt>
# </geodata>
class CaGeocoder extends Geocoder
{
    function __construct()
    {
        parent::__construct();
    }

    # Template method which does the geocode lookup.
    protected function do_geocode($address)
    {
        if(!($address instanceof GeoLoc)) {
            $msg = "Caught an error during Geocoder.ca geocoding call: ";
            $msg .= 'Geocoder.ca requires a GeoLoc argument';
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
        $url = $this->construct_request($address);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            return $geoloc;
        }
        $xml = $result['body'];
        $msg = "Geocoder.ca geocoding. Address: ".$address->get('full_address');
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = new SimpleXMLElement($xml);
        $address->provider = 'geocoder.ca';
        $address->lat = (string)$doc->latt;
        $address->lng = (string)$doc->longt;
        $lat = (float)$address->lat;
        $lng = (float)$address->lng;
        if(abs($lat) == 0 && abs($lng) == 0) {
            $address->success = false;
        }else{
            $address->success = true;
        }
        return $address;
    } // function do_geocode

    # Formats the request in the format acceptable by the CA geocoder.
    private function construct_request($location)
    {
        $url = "";
        if(property_exists($location,'street_address') &&
          !is_null($location->street_address)) {
            $url = $this->add_ampersand($url).
                "stno=".$location->get('street_number');
            $url = $this->add_ampersand($url).
                "addresst=".urlencode($location->get('street_name'));
        }
        if(property_exists($location,'city') && !is_null($location->city)) {
            $url = $this->add_ampersand($url)."city=".urlencode($location->city);
        }
        if(property_exists($location,'state') && !is_null($location->state)) {
            $url = $this->add_ampersand($url)."prov=".$location->state;
        }
        if(property_exists($location,'zip') && !is_null($location->zip)) {
            $url = $this->add_ampersand($url)."postal=".$location->zip;
        }
        if($this->geocoders->geocoder_ca) {
            $url = $this->add_ampersand($url)."auth=".$this->geocoders->geocoder_ca;
        }
        $url = $this->add_ampersand($url)."geoit=xml";
        return 'http://geocoder.ca/?'.$url;
    } // function construct_request

    private function add_ampersand($url)
    {
        $result = $url;
        $result .= strlen($url) > 0 ? "&" : "";
        return $result;
    }
} // class CaGeocoder
    
# Google geocoder implementation.
# Requires that the GEOKIT_GEOCODERS_GOOGLE constant in /config/config.php
# contains a Google API key.  
# Conforms to the interface set by the Geocoder class.
class GoogleGeocoder extends Geocoder
{
    function __construct()
    {
        parent::__construct();
    }

  # Template method which does the geocode lookup.
    protected function do_geocode($address)
    {
        if($this->geocoders->google == 'REPLACE_WITH_YOUR_GOOGLE_KEY') {
            $msg = "Caught an error during Google geocoding call: ";
            $msg .= 'A Google Key must be supplied';
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodeable_s() : $address;
        $request_url = "http://maps.google.com/maps/geo?q=".urlencode($address_str);
        $request_url .= "&output=xml&key=".$this->geocoders->google."&oe=utf-8";
        $result = $this->call_geocoder_service($request_url);
        if(!$result['success']) {
            $msg = "Caught an error during Google geocoding call: ";
            $msg .= 'Unsuccessful call with "'.$request_url.'"';
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
        $xml = $result['body'];
        $msg = "Google geocoding. Address: ";
        $msg .= $address instanceof GeoLoc ? $address->full_address : $address_str;
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = simplexml_load_string($xml);

        # Put the data elements into a single dimensioned array.  Unique and
        # consistent element names are provided by Google even though there is
        # variation (by country) in the structure, so we can get away with this.
        $data = array();
        $element = $doc->Response;
        $result = $this->parse_doc($element);
        $data = array_merge($data,$result['data']);
        while(count($result['arrays']) > 0) {
            foreach($result['arrays'] as $key => $val) {
                $element = $element->$key;
                $result = $this->parse_doc($element);
                $data = array_merge($data,$result['data']);
            }
        }

        if (strcmp($data['code'], "200") == 0) { // Successful geocode
            $result = new GeoLoc;

            # Translate accuracy into Yahoo-style token 
            # address, street, zip, zip+4, city, state, country
            # For Google, 1=low accuracy, 8=high accuracy
            $attrib = $doc->Response->Placemark->AddressDetails->attributes();
            $accuracy = $attrib->Accuracy ? (int)$attrib->Accuracy-1 : 0;
            $precision = array('unknown','country','state','state',
                'city','zip','zip+4','street address');
            $result->precision = $precision[$accuracy];

            $coordinates = $data['coordinates'];
            $coordinatesSplit = explode(",", $coordinates);
            // Format: Longitude, Latitude, Altitude
            $result->lat = $coordinatesSplit[1];
            $result->lng = $coordinatesSplit[0];

            if(array_key_exists('address',$data)) {
                $result->full_address = $data['address'];
            }
            $result->country_code = $data['CountryNameCode'];
            $result->provider     = 'google';

            #extended -- false if not not available
            if(array_key_exists('AdministrativeAreaName',$data)) {
                $result->state = $data['AdministrativeAreaName'];
            }
            if(array_key_exists('LocalityName',$data)) {
                $result->set_city($data['LocalityName']);
            }
            if(array_key_exists('PostalCodeNumber',$data)) {
                $result->zip = $data['PostalCodeNumber'];
            }
            if(array_key_exists('ThoroughfareName',$data)) {
                $result->set_street_address($data['ThoroughfareName']);
            }
            if(abs($result->lat) == 0 && abs($result->lng) == 0) {
                $result->success = false;
            }else{
                $result->success = true;
            }
            return $result;
        }else{
            $msg = 'Google was unable to geocode address: "'.$address_str.'"';
            Geocoder::logger('info',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;

        }
    } // function do_geocode

    private function parse_doc($element)
    {
        $data = $this->parser($element);
        foreach($data as $key => $val) {
            if($val == '<attribute />') {
                $data[$key] = $this->parser($element->$key);
            }
        }
        foreach($data as $key => $val) {
            if(is_array($val)) {
                $cnt = 0;
                foreach($val as $k => $v) if($v == '<attribute />') $cnt++;
                if($cnt == 0) {
                    foreach($val as $k => $v) {
                        $data[$k] = $v;
                    }
                    $data[$key] = null;
                }
            }
        }
        $data_tmp = array();
        foreach($data as $key => $val) {
            if(!is_null($val)) {
                $data_tmp[$key] = $val;
            }
        }
        $data = array();
        $arrays = array();
        foreach($data_tmp as $key => $val) {
            if(is_array($val)) {
                $arrays[$key] = $val;
            }else{
                $data[$key] = $val;
            }
        }    
        return array('data' => $data,'arrays' => $arrays);
    }

    private function parser($element)
    {
        $data = array();
        foreach($element->children() as $key => $val) {
            $value = trim((string)$val);
            if(strlen($value) == 0) {
                $data[$key] = '<attribute />';
            }else{
                $data[$key] = $value;
            }
        }
        return $data;
    }
} // class GoogleGeocoder

# Provides geocoding based upon an IP address.  The underlying web service
# is a hostip.info which sources their data through a combination of publicly
# available information as well as community contributions.
class IpGeocoder extends Geocoder 
{
    function __construct()
    {
        parent::__construct();
    }

    # Given an IP address, returns a GeoLoc instance which contains latitude,
    # longitude, city, and country code.  
    # Sets the success attribute to false if the ip parameter does not 
    # match an ip address.  
    protected function do_geocode($ip)
    {
        if(!ereg('^([0-9]{1,3}\.){3}[0-9]{1,3}$',$ip)) {
            $msg = "Caught an error during HostIp geocoding call: ";
            $msg .= 'Invalid IP address: '.$ip;
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
        $url = "http://api.hostip.info/get_html.php?ip=".$ip."&position=true";
        $result = $this->call_geocoder_service($url);
        if($result['success']) {
            return $this->parse_body($result['body']);
        }else{
            $msg = "Caught an error during HostIp geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
    } // function do_geocode

    # Converts the body to YAML since its in the form of:
    #
    # Country: UNITED STATES (US)
    # City: Sugar Grove, IL
    # Latitude: 41.7696
    # Longitude: -88.4588
    #
    # then instantiates a GeoLoc instance to populate with location data.
    private function parse_body($body)
    {
        $result = new GeoLoc;
        $result->provider = 'hostip';
        $lines = explode("\n",$body);
        foreach($lines as $line) {
            $fields = explode(':',$line);
            if($fields[0] == 'Country') {
                $country = explode('(',$fields[1]);
                $result->country_code = substr($country[1],0,2);
            }elseif($fields[0] == 'City') {
                $city = trim($fields[1]);
                $city_state = explode(', ',$city);
                $result->set_city($city_state[0]);
                $result->state = count($city_state) == 1 ? '' : $city_state[1];
            }elseif($fields[0] == 'Latitude') {
                $result->lat = $fields[1];
            }elseif($fields[0] == 'Longitude') {
                $result->lng = $fields[1];
            }else
                continue;           
        }
        if(abs($result->lat) == 0 && abs($result->lng) == 0) {
            $result->success = false;
        }else{
            $result->success = true;
        }
        return $result;
    } // function parse_body
} // class IpGeocoder

    
# Geocoder US geocoder implementation.  
# Requires that the GEOKIT_GEOCODERS_GEOCODER_US constant in /config/config.php 
# contains true or false based upon whether authentication is to occur.  
# Conforms to the interface set by the Geocoder class.
class UsGeocoder extends Geocoder
{
    function __construct()
    {
        parent::__construct();
    }

# For now, the geocoder_method will only geocode full addresses
#  -- not zips or cities in isolation
    protected function do_geocode($address)
    {
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodeable_s() : $address;
        $node = $this->geocoders->geocoder_us ? $this->geocoders->geocoder_us : '';
        $url = "http://".$node."geocoder.us/service/csv/geocode?address=";
        $url .= urlencode($address_str);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            $msg = "Caught an error during geocoder.us geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }

        $data = $result['body'];
        $msg = 'Geocoder.us geocoding. Address: '.$address_str.'. Result: '.$data;
        Geocoder::logger('debug', $msg);
        $data = trim($data);
        $parts = explode(',',$data);
        if(count($parts) == 6) {
            $result = new GeoLoc;
            $result->lat            = $parts[0];
            $result->lng            = $parts[1];
            $result->set_street_address($parts[2]);
            $result->set_city($parts[3]);
            $result->state          = $parts[4];
            $result->zip            = $parts[5];
            $result->country_code   = 'US';
            $result->full_address   = $parts[2].', '.$parts[3].', '.$parts[4].
                ', '.$parts[5].', US';
            $result->provider       = 'geocoder.us';
            if(abs($result->lat) == 0 && abs($result->lng) == 0) {
                $result->success = false;
            }else{
                $result->success = true;
            }
            return $result;
        }else{
            $msg = "geocoder.us was unable to geocode address: ".$address_str;
            Geocoder::logger('info',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
    } // function geo_geocode
} // class UsGeocoder

# Yahoo geocoder implementation.  
# Requires that the GEOKIT_GEOCODERS_YAHOO constant in /config/config.php 
# contains a Yahoo API key.  Conforms to the interface set by the Geocoder class.
class YahooGeocoder extends Geocoder
{
    function __construct()
    {
        parent::__construct();
    }

    # Template method which does the geocode lookup.
    protected function do_geocode($address)
    {
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodeable_s() : $address;
        $url = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=";
        $url .= $this->geocoders->yahoo.'&location='.urlencode($address_str);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            $msg = "Caught an error during Yahoo geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;

        }
        $xml = $result['body'];
        $msg = "Yahoo geocoding. Address: ";
        $msg .= $address instanceof GeoLoc ? $address->full_address : $address_str;
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = simplexml_load_string($xml);
        $doc = $doc->Result;

        if(!(abs((float)$doc->Latitude) == 0 && abs((float)$doc->Longitude) == 0)) {
            // Successful geocode
            $result = new GeoLoc;

            #basic      
            $result->lat          = (float)$doc->Latitude;
            $result->lng          = (float)$doc->Longitude;
            $result->country_code = (string)$doc->Country;
            $result->provider     = 'yahoo';

            #extended - false if not available
            if(strlen((string)$doc->City) > 0) {
                $result->set_city((string)$doc->City);
            }
            if(strlen((string)$doc->State)) {
                $result->state = (string)$doc->State;
            }
            if(strlen((string)$doc->Zip)) {
                $result->zip = (string)$doc->Zip;
            }
            if(strlen((string)$doc->Address)) {
                $result->set_street_address((string)$doc->Address);
                $result->full_address = $result->street_address.', ';
            }
            $result->full_address .= $result->city.', '.$result->state.', '.
                $result->country_code;
            $result->precision = $doc->attributes()->precision;
            $result->success = strlen((string)$doc->attributes()->warning) == 0;
            return $result;
        }else{ 
            $msg = "Yahoo was unable to geocode address: ".$address_str;
            Geocoder::logger('info',$msg);
            $geoloc = new GeoLoc;
            $geoloc->success = false;
            $geoloc->street_address = $msg;
            return $geoloc;
        }
    }  // function do_geocode
}

# Provides methods to geocode with a variety of geocoding service providers, 
# plus failover among providers in the order you configure.
# 
# Goal:
# - homogenize the results of multiple geocoders
# 
# Limitations:
# - currently only provides the first result. Sometimes geocoders will
#   return multiple results.
# - currently discards the "accuracy" component of the geocoding calls
class MultiGeocoder extends Geocoder 
{
    function __construct()
    {
        parent::__construct();
    }

    # This method will call one or more geocoders in the order specified in the 
    # configuration until one of the geocoders work.
    # 
    # The failover approach is crucial for production-grade apps, but is rarely used.
    # 98% of your geocoding calls will be successful with the first call  
    protected function do_geocode($address)
    {
        foreach($this->geocoders->provider_order as $provider) {
            $msg = "MultiGeocoder using ".$provider;
            Geocoder::logger('debug',$msg);

            switch ($provider) {
                case 'google':  $geocoder = new GoogleGeocoder; break;
                case 'yahoo':   $geocoder = new YahooGeocoder; break;
                case 'us':      $geocoder = new UsGeocoder; break;
                case 'ca':      $geocoder = new CaGeocoder; break;
                default:        $msg = '"'.$provider.'" is an invalid Geocode provider.';
                                Geocoder::logger('error',$msg);
                                $geoloc = new GeoLoc;
                                $geoloc->success = false;
                                $geoloc->street_address = $msg;
                                return $geoloc;
            }
            $result = $geocoder->geocode($address);
            # This statement is for use by multi_geocoder_test only
            if(isset($this->geocoders->force_failure)) {
                foreach($this->geocoders->force_failure as $prov) {
                    if($provider == $prov) $result->success = false;
                }
            }
            if($result->success) {
                return $result;
            }
        }
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodeable_s() : $address;
        $msg = "Something has gone very wrong during the geocoding of this address: ".
            $address_str;
        Geocoder::logger('error',$msg);
        $geoloc = new GeoLoc;
        $geoloc->success = false;
        $geoloc->street_address = $msg;
        return $geoloc;
    }
} // class MultiGeocoder
?>

