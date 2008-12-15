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

require_once(AK_BASE_DIR.DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

# This file may not exist.  It exists if you had to create one for your 
#   plugin, as I had to for geo_kit
require_once(GEOKIT_PLUGIN_DIR.DS.'config'.DS.'config.php');

# These are the plugin scripts
$lib_dir = GEOKIT_PLUGIN_DIR.DS.'lib'.DS.'geo_kit'.DS;
require_once($lib_dir.'array_funcs.php');
require_once($lib_dir.'defaults.php');
require_once($lib_dir.'mappable.php');
require_once($lib_dir.'geocoders.php');
#require_once($lib_dir.'ip_geocode_lookup.php');
#require_once($lib_dir.'acts_as_mappable.php');

require_once('base_geocoder_test.php');
class UsGeocoderTestCase extends BaseGeocoderTestCase
{
    var $geocoder_us_full = 
        "37.792528,-122.393981,100 Spear St,San Francisco,CA,94105\n";

    function test_setup()
    {
        parent::test_setup();
        $this->us_full_addr = array('street_address' => '100 Spear St', 
            'city' => "San Francisco", 'state' => "CA");
        $this->us_full_loc = new GeoLoc($this->us_full_addr);
    } // function test_setup
  
    function test_geocoder_us()
    {
        $address = '100 Spear St,San Francisco,CA';
        $geocoders = new Geocoders;
        $url = "http://geocoder.us/service/csv/geocode?address=".
            urlencode($address);
        $us_geocoder = new UsGeocoder;
        $response = $us_geocoder->call_geocoder_service($url);
        $this->assertEqual($response['body'],$this->geocoder_us_full);
        $this->verify($us_geocoder->geocode($address));
    }

    function test_geocoder_with_geo_loc()
    {
        $us_geocoder = new UsGeocoder;
        $result = $us_geocoder->geocode($this->us_full_loc);
        $this->verify($result);
    }

    function test_lookup_failure()
    {
        $address = '100 Spear St,San Francisco,IA';
        $url = "http://geocoder.us/service/csv/geocode?address=".
            urlencode($address);
        $us_geocoder = new UsGeocoder;
        $response = $us_geocoder->call_geocoder_service($url);
        $this->assertEqual($response['body'],
            "2: couldn't find this address! sorry");
    }   
  
    private function verify($location)
    {
        $this->assertEqual("CA", $location->state);
        $this->assertEqual("San Francisco", $location->city);
        $this->assertEqual("37.792528,-122.393981", $location->ll());
        $this->assertTrue($location->is_us());
        $this->assertEqual("100 Spear St, San Francisco, CA, 94105, US",
            $location->full_address);
        $this->assertTrue($location->success);
    }
} // class UsGeocoderTestCase

$use_sessions = true;
ak_test('UsGeocoderTestCase', $use_sessions);
?>

