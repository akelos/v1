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

class MultiGeocoderTestCase extends BaseGeocoderTestCase
{
    function test_setup()
    {
        parent::test_setup();
        $this->failure = new GeoLoc;
        $this->failure->success = false;
        $this->multi_geocoder = new MultiGeocoder;
        $this->multi_geocoder->geocoders->provider_order = array('google','yahoo','ca');
    } // function test_setup
  
    function test_successful_first()
    {
        $result = $this->multi_geocoder->geocode($this->address);
        $this->assertEqual($this->success->city,$result->city);
        $this->assertEqual($this->success->state,$result->state);
        $this->assertEqual($this->success->country_code,$result->country_code);
        $this->assertEqual($this->success->lat,$result->lat);
        $this->assertEqual($this->success->lng,$result->lng);
        $this->assertTrue($result->success);
        $this->assertEqual('google',$result->provider);
    }

    function test_failover()
    {
        $this->multi_geocoder->geocoders->force_failure = array('google');
        $result = $this->multi_geocoder->geocode($this->address);
        $this->assertEqual($this->success->city,$result->city);
        $this->assertEqual($this->success->state,$result->state);
        $this->assertEqual($this->success->country_code,$result->country_code);
        $this->assertEqual('37.77916',$result->lat);
        $this->assertEqual('-122.420049',$result->lng);
        $this->assertTrue($result->success);
        $this->assertEqual('yahoo',$result->provider);
    }
  
    function test_double_failover()
    {
        $this->multi_geocoder->geocoders->force_failure = array('google','yahoo');
        $address = new GeoLoc(array('city' => 'San Francisco','state' => 'CA'));
        $result = $this->multi_geocoder->geocode($address);
        $this->assertEqual('San Francisco',$result->city);
        $this->assertEqual('CA',$result->state);
        $this->assertEqual('',$result->country_code);
        $this->assertEqual('37.774929',$result->lat);
        $this->assertEqual('-122.419415',$result->lng);
        $this->assertTrue($result->success);
        $this->assertEqual('geocoder.ca',$result->provider);
    }
  
    function test_failure()
    {
        $this->multi_geocoder->geocoders->force_failure = array('google','yahoo','ca');
        $address = new GeoLoc(array('city' => 'San Francisco','state' => 'CA'));
        $result = $this->multi_geocoder->geocode($address);
        $this->assertFalse($result->success);
    }

    function test_invalid_provider()
    {
        $this->multi_geocoder->geocoders->provider_order = array('bogus');
        $address = new GeoLoc(array('city' => 'San Francisco','state' => 'CA'));
        $result = $this->multi_geocoder->geocode($address);
        $this->assertFalse($result->success);
    }  

} // class MultiGeocoderTestCase

$use_sessions = true;
ak_test('MultiGeocoderTestCase', $use_sessions);
?>

