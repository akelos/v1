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
class CaGeocoderTestCase extends AkUnitTest
{
    function test_geocoder_with_geoloc()
    {
        $ca_full_addr = array(
            'street_address' => "2100 32nd AVE W",
            'city' => "Vancouver", 'state' => "BC");
        $ca_geoloc = new GeoLoc($ca_full_addr);
        $ca_geocoder = new CaGeocoder;
        $address = $ca_geocoder->geocode($ca_geoloc);
        $url = "http://geocoder.ca/?latt=".$address->lat."&longt=".$address->lng."&reverse=1&geoit=xml";
        $response = $ca_geocoder->call_geocoder_service($url);
        $xml = new SimpleXMLElement($response['body']);
        $this->assertTrue($address->success);
        $this->assertEqual($address->get('street_number'),$xml->stnumber);
        $this->assertEqual($address->get('street_name'),$xml->staddress);
        $this->assertEqual($address->city,$xml->city);
        $this->assertEqual($address->state,$xml->prov);
        $this->assertEqual($address->lat,$xml->inlatt);
        $this->assertEqual($address->lng,$xml->inlongt);
    }

    function test_lookup_failure()
    {
        $this->ca_full_addr = array(
            'street_address' => "Kalervonkatu 3",
            'city' => "Jyväskylä", 'country_code' => "FI");
        $this->ca_geoloc = new GeoLoc($this->ca_full_addr);
        $ca_geocoder = new CaGeocoder;
        $address = $ca_geocoder->geocode($this->ca_geoloc);
        $this->assertFalse($address->success);
    }
} // class CaGeocoderTestCase

$use_sessions = true;
ak_test('CaGeocoderTestCase', $use_sessions);
?>

