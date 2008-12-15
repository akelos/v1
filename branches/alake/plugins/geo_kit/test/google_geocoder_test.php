<?php
error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
defined('AK_TEST_DATABASE_ON') ? null : define('AK_TEST_DATABASE_ON', true);
defined('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION') ? null : 
    define('AK_ACTIVE_RECORD_PROTECT_GET_RECURSION', false);

defined('AK_BASE_DIR') ? null :
    define('AK_BASE_DIR',realpath(dirname(__FILE__).str_repeat(DS.'..',5)));
defined('GEOKIT_PLUGIN_DIR') ? null :
    define('GEOKIT_PLUGIN_DIR',
        AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'geo_kit');
defined('AK_APP_DIR') ? null :
    define('AK_APP_DIR', GEOKIT_PLUGIN_DIR.DS.'test'.DS.'fixtures'.DS.'app');

# This file may not exist.  It exists if you had to create one for your 
#   plugin, as I had to for geo_kit
require_once(GEOKIT_PLUGIN_DIR.DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

# These are the plugin scripts
$lib_dir = GEOKIT_PLUGIN_DIR.DS.'lib'.DS.'geo_kit'.DS;
require_once($lib_dir.'array_funcs.php');
require_once($lib_dir.'defaults.php');
require_once($lib_dir.'mappable.php');
require_once($lib_dir.'geocoders.php');
#require_once($lib_dir.'ip_geocode_lookup.php');
#require_once($lib_dir.'acts_as_mappable.php');

require_once('base_geocoder_test.php');

class GoogleGeocoderTestCase extends AkUnitTest
{
    function test_setup()
    {
        $this->google_full_addr = array(
            'street_address' => "100 Spear St", 'city' => "San Francisco",
            'state' => "CA", 'zip' => "94105", 'country_code' => "US");
        $this->google_city_addr = array('city' => "San Francisco", 'state' => "CA");

        $this->google_full_loc = new GeoLoc($this->google_full_addr);
        $this->google_city_loc = new GeoLoc($this->google_city_addr);
    } // function test_setup

    function test_google_full_address()
    {
        $geocoders = new Geocoders;
        $geocoders->google = GEOKIT_GOOGLE_GEOCODER_KEY;
        $address = "100 Spear St, San Francisco, CA 94105, USA";
        $url = "http://maps.google.com/maps/geo?q=".
            urlencode($address)."&output=xml&key=".
            $geocoders->google."&oe=utf-8";
        $google_geocoder = new GoogleGeocoder;
        $response = $google_geocoder->call_geocoder_service($url);
        $body = new SimpleXMLElement($response['body']);
        $address = (string)$body->Response->Placemark->address;
        $result = $google_geocoder->geocode($address);
        $this->assertEqual("CA", $result->state);
        $this->assertEqual("San Francisco", $result->city);
        $ll = $result->ll();
        $this->assertEqual("37.7921500,-122.3940000", $ll);
        $this->assertTrue($result->is_us());
        $this->assertEqual("100 Spear St, San Francisco, CA 94105, USA",
            $result->full_address);
        $this->assertEqual("google",$result->provider);
    }

    function test_google_city()
    {
        $geocoders = new Geocoders;
        $geocoders->google = GEOKIT_GOOGLE_GEOCODER_KEY;
        $address = "San Francisco, CA, USA";
        $url = "http://maps.google.com/maps/geo?q=".
            urlencode($address)."&output=xml&key=".
            $geocoders->google."&oe=utf-8";
        $google_geocoder = new GoogleGeocoder;
        $response = $google_geocoder->call_geocoder_service($url);
        $body = new SimpleXMLElement($response['body']);
        $address = $body->Response->Placemark->address;
        $result = $google_geocoder->geocode($address);
        $this->assertEqual("CA", $result->state);
        $this->assertEqual("San Francisco", $result->city);
        $ll = $result->ll();
        $this->assertEqual("37.7751960,-122.4192040", $ll);
        $this->assertTrue($result->is_us());
        $this->assertEqual("San Francisco, CA, USA", $result->full_address);
        $this->assertEqual('',$result->street_address);
        $this->assertEqual("google",$result->provider);
    }

    function test_google_full_address_with_geo_loc()
    {
        $google_geocoder = new GoogleGeocoder;
        $result = $google_geocoder->geocode($this->google_full_loc);
        $this->assertEqual("CA", $result->state);
        $this->assertEqual("San Francisco", $result->city);
        $ll = $result->ll();
        $this->assertEqual("37.7921500,-122.3940000", $ll);
        $this->assertTrue($result->is_us());
        $this->assertEqual("100 Spear St, San Francisco, CA 94105, USA",
            $result->full_address);
        $this->assertEqual("google",$result->provider);
    }
  
    function test_google_city_with_geo_loc()
    {
        $google_geocoder = new GoogleGeocoder;
        $result = $google_geocoder->geocode($this->google_city_loc);
        $this->assertEqual("CA", $result->state);
        $this->assertEqual("San Francisco", $result->city);
        $ll = $result->ll();
        $this->assertEqual("37.7751960,-122.4192040", $ll);
        $this->assertTrue($result->is_us());
        $this->assertEqual("San Francisco, CA, USA", $result->full_address);
        $this->assertEqual('',$result->street_address);
        $this->assertEqual("google",$result->provider);
    }
  

    function test_lookup_failure() # wrong country
    {
        $this->google_full_addr = array(
            'street_address' => "Kalervonkatu 3",
            'city' => "Jyväskylä", 'country_code' => "US");
        $this->google_full_loc = new GeoLoc($this->google_full_addr);
        $google_geocoder = new GoogleGeocoder;
        $address = $google_geocoder->geocode($this->google_full_loc);
        $this->assertFalse($address->success);
    }
} // class GoogleGeocoderTestCase

$use_sessions = true;
ak_test('GoogleGeocoderTestCase', $use_sessions);
?>

