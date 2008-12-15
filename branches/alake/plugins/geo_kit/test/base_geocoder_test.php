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

class BaseGeocoderTestCase extends AkUnitTest
{
# Base class for testing geocoders.
    function test_setup()
    {
        $this->address = 'San Francisco, CA';
        $this->full_address = '100 Spear St, San Francisco, CA, 94105-1522, US';
        $this->full_address_short_zip = '100 Spear St, San Francisco, CA, 94105, US';
        $this->success = new GeoLoc(array(
            'city' => "San Francisco", 'state' => "CA", 'country_code' => "US",
            'lat' => 37.7751960, 'lng' => -122.4192040));
        $this->success->success = true;
    } // function test_setup

    function test_unsuccessful_call_web_service()
    {
        $url = "http://api.doesnotexist.org";
        $geocoder = new Geocoder;
        $result = $geocoder->call_geocoder_service($url);
        $this->assertNotEqual('200',$result['code']);
    }
  
    function test_successful_call_web_service()
    {
        $url = "http://api.akelos.org";
        $geocoder = new Geocoder;
        $result = $geocoder->call_geocoder_service($url);
        $this->assertEqual('200',$result['code']);
    }
  
    function test_find_geocoder_methods()
    {
        # See comment for this method in comments.txt
        $this->assertTrue(true);
    }
   
} // class BaseGeocoderTestCase

$use_sessions = true;
ak_test('BaseGeocoderTestCase', $use_sessions);
?>

