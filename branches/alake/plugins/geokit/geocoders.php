<?php
include_once 'mappable.php';
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

    function __construct()
    {
        $this->timeout = defined('GEOKIT_GEOCODERS_TIMEOUT') ? 
            $this->timeout = GEOKIT_GEOCODERS_TIMEOUT : 0;
        $this->provider_order = explode(',',GEOKIT_PROVIDER_ORDER);
    }
} // class Geocoders

class GeocodeError
{
    function __construct()
    {
        error_reporting(E_ALL);
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
        $res = $this->do_geocode($address);
        return $res['success'] ? $res : new GeoLoc;
    }
  
# Call the geocoder service using the timeout if configured.
    function call_geocoder_service($url)
    {
        $options = array();
        if($this->geocoders->timeout > 0) {
            $options['timeout'] = $this->geocoders->timeout;
        }
        if(!is_null($geocoders->proxy_addr)) {
            $options['proxyhost'] = $this->geocoders->proxy_addr;
            $options['proxyport'] = $this->geocoders->proxy_port;
        }
        if(!is_null($this->geocoders->proxy_user)) {
            $auth = $this->geocoders->proxy_user;
            if(!is_null($this->geocoders->proxy_pass)) {
                $auth .= ':'.$this->geocoders->proxy_pass;
            }
            $options['proxyauth'] = $auth;
        }
        $result = array();
        $response = http_get($url, $options, $info);
        if(!$response) {
            $result['success'] = false;
        }else{
            $obj = http_parse_message($response);
            $result['code']           = $obj->responseCode;
            $result['content-type']   = $obj->type;
            $result['server']         = $obj->headers['Server'];
            $result['content-length'] = $obj->headers['Content-Length'];
            $result['body']           = $obj->body;
            $result['http-version']   = $obj->httpVersion;
            $result['success']        = true;
        }
        return $result;
    } // function call_geocoder_service

    public static function logger($type,$msg)
    { 
        echo 'Log '.$type.': '.$msg."<br />\n";
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
    
    # Adds subclass' geocode method making it conveniently available through 
    # the base class.
    private function inherited(clazz)
    {
#        class_name = clazz.name.split('::').last
#        src = <<-END_SRC
#          def self.#{class_name.underscore}(address)
#            #{class_name}.geocode(address)
#          end
#        END_SRC
#        class_eval(src)
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
    # Template method which does the geocode lookup.
    private function do_geocode($address)
    {
        if(!($address instanceof GeoLoc)) {
            $msg = "Caught an error during Geocoder.ca geocoding call: ";
            $msg .= 'Geocoder.ca requires a GeoLoc argument';
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }
        $url = $this->construct_request($address);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            return new GeoLoc;
        }
        $xml = $result['body'];
        $msg = "Geocoder.ca geocoding. Address: ".$address->full_address;
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = simplexml_load_string($xml);
        $address->lat = $doc->latt;
        $address->lng = $doc->longt;
        $address->success = true;
        return $address;
    } // function do_geocode

    # Formats the request in the format acceptable by the CA geocoder.
    private function construct_request($location)
    {
        $url = "";
        if(property_exists($location,'street_address') &&
          !is_null($location->street_address)) {
            $url .= $this->add_ampersand($url).
                "stno=".$location->get('street_number');
            $url .= $this->add_ampersand($url).
                "addresst=".urlencode($location->get('street_name'));
        }
        if(property_exists($location,'city') && !is_null($location->city)) {
            $url .= $this->add_ampersand($url)."city=".urlencode($location->city);
        }
        if(property_exists($location,'state') && !is_null($location->state)) {
            $url .= $this->add_ampersand($url)."prov=".$location->state;
        }
        if(property_exists($location,'zip') && !is_null($location->zip)) {
            $url .= $this->add_ampersand($url)."postal=".$location->zip;
        }
        if($this->geocoders->geocoder_ca) {
            $url .= $this->add_ampersand($url)."auth=".$this->geocoders->geocoder_ca;
        }
        $url .= $this->add_ampersand($url)."geoit=xml";
        return 'http://geocoder.ca/?'.$url;
    } // function construct_request

    private function add_ampersand($url)
    {
        $result = $url;
        $result .= strlen($url) > 0 ? "&" : "";
    }
} // class CaGeocoder
    
# Google geocoder implementation.
# Requires that the GEOKIT_GEOCODERS_GOOGLE constant in /config/config.php
# contains a Google API key.  
# Conforms to the interface set by the Geocoder class.
class GoogleGeocoder extends Geocoder
{
  # Template method which does the geocode lookup.
    private function do_geocode($address)
    {
        if($this->geocoders->google == 'REPLACE_WITH_YOUR_GOOGLE_KEY') {
            $msg = "Caught an error during Google geocoding call: ";
            $msg .= 'A Google Key must be supplied';
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodable_s : $address;
        $request_url = "http://maps.google.com/maps/geo?q=".urlencode($address_str);
        $request_url .= "&output=xml&key=".$this->geocoders->google."&oe=utf-8";
        $result = $this->call_geocoder_service($request_url);
        if(!$result['success']) {
            $msg = "Caught an error during Google geocoding call: ";
            $msg .= 'Unsuccessful call with '.$request_url;
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }
        $xml = $result['body'];
        $msg = "Google geocoding. Address: "
        $msg .= $address instanceof GeoLoc ? $address->full_address : $address_str;
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = simplexml_load_string($xml);
        $status = $doc->Response->Status->Code;
        if (strcmp($status, "200") == 0) {
            // Successful geocode
            $result = new GeoLoc;

            $coordinates = $doc->Response->Placemark->Point->coordinates;
            $coordinatesSplit = explode(",", $coordinates);
            // Format: Longitude, Latitude, Altitude
            $result->lat = $coordinatesSplit[1];
            $result->lng = $coordinatesSplit[0];

            $result->country_code = $doc->CountryNameCode;
            $result->provider     = 'google';

            #extended -- false if not not available
            if($doc->LocalityName != false) {
                $result->set_city($doc->LocalityName);
            }
            if($doc->AdministrativeAreaName != false) {
                $result->state = $doc->AdministrativeAreaName;
            }
            if($doc->address != false) {   # google provided it
                $result->full_address = $doc->address;
            }
            if($doc->PostalCodeNumber != false) {
                $result->zip = $doc->PostalCodeNumber;
            }
            if($doc->ThoroughfareName != false) {
                $result->set_street_address($doc->ThoroughfareName);
            }
            # Translate accuracy into Yahoo-style token 
            # address, street, zip, zip+4, city, state, country
            # For Google, 1=low accuracy, 8=high accuracy
            $address_details = $doc->AddressDetails;
#', 'urn:oasis:names:tc:ciq:xsdschema:xAL:2.0'
            $accuracy = $address_details ? (int)$address_details->Accuracy : 0;
            $precision = array('unknown','country','state','state',
                'city','zip','zip+4','street address');
            $result->precision = $precision[$accuracy];
            $result->success = true;
            return $result;
        }else{
            $msg = "Google was unable to geocode address: ".$address_str;
            Geocoder::logger('info',$msg);
            return new GeoLoc;
        }
    } // function do_geocode
} // class GoogleGeocoder

# Provides geocoding based upon an IP address.  The underlying web service
# is a hostip.info which sources their data through a combination of publicly
# available information as well as community contributions.
class IpGeocoder extends Geocoder 
{
    # Given an IP address, returns a GeoLoc instance which contains latitude,
    # longitude, city, and country code.  
    # Sets the success attribute to false if the ip parameter does not 
    # match an ip address.  
    private function do_geocode($ip)
    {
        if(!ereg('/^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})?$/',$ip)) {
            $msg = "Caught an error during HostIp geocoding call: ";
            $msg .= 'Invalid IP address;'.$ip;
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }
        $url = "http://api.hostip.info/get_html.php?ip=".$ip."&position=true";
        $result = $this->call_geocoder_service($url);
        if($result['success']) {
            return $this->parse_body($result['body']);
        }else{
            $msg = "Caught an error during HostIp geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            return new GeoLoc;
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
                $city_state = explode(', ',$fields[1]);
                $result->set_city($city_state[0]);
                $result->state = $city_state[1];
            }elseif($fields[0] == 'Latitude') {
                $result->lat = $fields[1];
            }elseif($fields[0] == 'Longitude') {
                $result->lng = $fields[1];
            }else
                continue;           
        }
        $result->success = $result->city != "(Private Address)";
        return $result;
    } // function parse_body
} // class IpGeocoder

    
# Geocoder US geocoder implementation.  
# Requires that the GEOKIT_GEOCODERS_GEOCODER_US constant in /config/config.php 
# contains true or false based upon whether authentication is to occur.  
# Conforms to the interface set by the Geocoder class.
class UsGeocoder extends Geocoder
{

# For now, the geocoder_method will only geocode full addresses
#  -- not zips or cities in isolation
    private function do_geocode($address)
    {
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodable_s : $address;
        $node = $this->geocoders->geocoder_us ? $this->geocoders->geocoder_us : '';
        $url = "http://".$node."geocoder.us/service/csv/geocode?address=";
        $url .= urlencode($address_str);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            $msg = "Caught an error during geocoder.us geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }

        $data = $result['body'];
        $msg = "Geocoder.us geocoding. Address: ".$address_str.". Result: $data";
        Geocoder::logger('debug', $msg;
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
            $result->success        = true;
            return $result;
        }else{
            $msg = "geocoder.us was unable to geocode address: ".$address_str;
            Geocoder::logger('info',$msg);
            return new GeoLoc;
        }
    } // function geo_geocode
} // class UsGeocoder

# Yahoo geocoder implementation.  
# Requires that the GEOKIT_GEOCODERS_YAHOO constant in /config/config.php 
# contains a Yahoo API key.  Conforms to the interface set by the Geocoder class.
class YahooGeocoder extends Geocoder
{
    # Template method which does the geocode lookup.
    private function do_geocode($address)
    {
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodable_s : $address;
        $url = "http://api.local.yahoo.com/MapsService/V1/geocode?appid=";
        $url .= $this->geocoders->yahoo.'&location='.urlencode($address_str);
        $result = $this->call_geocoder_service($url);
        if(!$result['success']) {
            $msg = "Caught an error during Yahoo geocoding call: ";
            $msg .= 'Unsuccessful call with '.$url;
            Geocoder::logger('error',$msg);
            return new GeoLoc;
        }
        $xml = $result['body'];
        $msg = "Yahoo geocoding. Address: "
        $msg .= $address instanceof GeoLoc ? $address->full_address : $address_str;
        $msg .= ". Result: ".$xml;
        Geocoder::logger('debug',$msg);

        # Parse the document.
        $doc = simplexml_load_string($xml);
        $status = $doc->Response->Status->Code;
        if ($doc->ResultSet) {
            // Successful geocode
            $result = new GeoLoc;

            #basic      
            $result->lat          = $doc->Latitude;
            $result->lng          = $doc->Longitude;
            $result->country_code = $doc->Country;
            $result->provider     = 'yahoo';

            #extended - false if not available
            if($doc->City && !is_null($doc->City)) {
                $result->set_city($doc->City);
            }
            if($doc->State && !is_null($doc->State)) {
                $result->state = $doc->State;
            }
            if($doc->Zip && !is_null($doc->Zip)) {
                $result->zip = $doc->Zip;
            }
            if($doc->Address && !is_null($doc->Address)) {
                $result->set_street_address($doc->Address);
            }
            if($doc->Result) {
                $result->precision = $doc->Result->precision;
            }
            $result->success = true;
            return $result;
        }else{ 
            $msg = "Yahoo was unable to geocode address: ".$address_str;
            Geocoder::logger('info',$msg);
            return new GeoLoc;
        }
    }  // function do_geocode
} // class YahooGeocoder

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
    # This method will call one or more geocoders in the order specified in the 
    # configuration until one of the geocoders work.
    # 
    # The failover approach is crucial for production-grade apps, but is rarely used.
    # 98% of your geocoding calls will be successful with the first call  
    private function do_geocode($address)
    {
        # Valid strings are 'google', 'yahoo', 'us', and 'ca'.
        foreach($this->geocoders->provider_order as $provider) {
            $msg = "MultiGeocoder using ".$provider;
            Geocoder::logger('debug',$msg);
            switch ($provider) {
                case 'google':  $geocoder = new GoogleGeocoder;
                case 'yahoo':   $geocoder = new YahooGeocoder;
                case 'us':      $geocoder = new UsGeocoder;
                case 'ca':      $geocoder = new CaGeocoder;
            }
            $result = $geocoder->geocode($address);
            if($result->success) {
                return $result;
            }
        }
        $address_str = $address instanceof GeoLoc ? 
            $address->to_geocodable_s : $address;
        $msg = "Something has gone very wrong during geocoding this: ";
        $msg .= "Address: ".$address_str;
        Geocoder::logger('error',$msg);
        return new GeoLoc;
    }
} // class MultiGeocoder
?>

