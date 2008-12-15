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

require_once('base_geocoder_test.php');
class IpGeocoderTestCase extends BaseGeocoderTestCase
{
    function test_setup()
    {
        parent::test_setup();
        $this->success->provider = "hostip";
        $this->ip_failure = "Country: (Private Address) (XX)\n".
            "City: (Private Address)\nLatitude: \nLongitude: ";
        $this->ip_success = "Country: UNITED STATES (US)\n".
            "City: Sugar Grove, IL\nLatitude: 41.7696\nLongitude: -88.4588"; 
        $this->ip_unicoded = "Country: FINLAND (FI)\n".
            "City: Malmgård\nLatitude: 60.55\nLongitude: 25.95";
    } // function test_setup
  
    function test_successful_lookup()
    {
        $url = 
            'http://api.hostip.info/get_html.php?ip=12.215.42.19&position=true';
        $ip_geocoder = new IpGeocoder;
        $response = $ip_geocoder->call_geocoder_service($url);
        $this->assertEqual($response['body'],$this->ip_success);
        $location = $ip_geocoder->geocode('12.215.42.19');
        $this->assertEqual(41.7696, $location->lat);
        $this->assertEqual(-88.4588, $location->lng);
        $this->assertEqual("Sugar Grove", $location->city);
        $this->assertEqual("IL", $location->state);
        $this->assertEqual("US", $location->country_code);
        $this->assertEqual("hostip", $location->provider);
        $this->assertTrue($location->success);
    }

    function test_unicoded_lookup()
    {
        $url = 'http://api.hostip.info/get_html.php?ip=217.30.180.55&position=true';
        $ip_geocoder = new IpGeocoder;
        $response = $ip_geocoder->call_geocoder_service($url);
        $body = $response['body'];
        $this->assertEqual($body,$this->ip_unicoded);
        $location = $ip_geocoder->geocode('217.30.180.55');
        $this->assertEqual(60.55, (float)$location->lat);
        $this->assertEqual(25.95, (float)$location->lng);
        $this->assertEqual("Malmgård", $location->city);
        $this->assertEqual('',$location->state);
        $this->assertEqual("FI", $location->country_code);
        $this->assertEqual("hostip", $location->provider);
        $this->assertTrue($location->success);
    }

    function test_failed_lookup()
    {
        $url = 'http://api.hostip.info/get_html.php?ip=0.0.0.0&position=true';
        $ip_geocoder = new IpGeocoder;
        $response = $ip_geocoder->call_geocoder_service($url);
        $this->assertEqual($response['body'],$this->ip_failure);
        $location = $ip_geocoder->geocode('0.0.0.0');
        $this->assertFalse($location->success);
    }
  
    function test_invalid_ip()
    {
        $ip_geocoder = new IpGeocoder;
        $location = $ip_geocoder->geocode('blah');
        $this->assertFalse($location->success);
    }
  
    function test_service_unavailable()
    {
        $url = 'http://api.hostip.info/get_html.php?ip=0.0.0.0&position=true';
        $ip_geocoder = new IpGeocoder;
        $response = $ip_geocoder->call_geocoder_service($url);
        $this->assertEqual($response['body'],$this->ip_failure);
        $location = $ip_geocoder->geocode('0.0.0.0');
        $this->assertFalse($location->success);
    }  
} // class IpGeocoderTestCase

$use_sessions = true;
ak_test('IpGeocoderTestCase', $use_sessions);
?>

