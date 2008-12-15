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

class YahooGeocoderTestCase extends BaseGeocoderTestCase
{
    var $yahoo_full_addr = '<?xml version="1.0"?>
<ResultSet xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:yahoo:maps" xsi:schemaLocation="urn:yahoo:maps http://api.local.yahoo.com/MapsService/V1/GeocodeResponse.xsd"><Result precision="address"><Latitude>37.792332</Latitude><Longitude>-122.394027</Longitude><Address>100 Spear St</Address><City>San Francisco</City><State>CA</State><Zip>94105-1578</Zip><Country>US</Country></Result></ResultSet>';

    var $yahoo_city = '<?xml version="1.0"?>
<ResultSet xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="urn:yahoo:maps" xsi:schemaLocation="urn:yahoo:maps http://api.local.yahoo.com/MapsService/V1/GeocodeResponse.xsd"><Result precision="zip"><Latitude>37.779160</Latitude><Longitude>-122.420049</Longitude><Address></Address><City>San Francisco</City><State>CA</State><Zip></Zip><Country>US</Country></Result></ResultSet>';

    function test_setup()
    {
        parent::test_setup();
        $this->yahoo_full_addr = trim($this->yahoo_full_addr);
        $this->yahoo_full_hash = array('street_address' => "100 Spear St",
            'city' => "San Francisco",'state' => "CA",'zip'=>"94105-1578",
            'country_code' => "US");
        $this->yahoo_city_hash = array('city' => "San Francisco", 'state' => "CA");
        $this->yahoo_full_loc = new GeoLoc($this->yahoo_full_hash);
        $this->yahoo_city_loc = new GeoLoc($this->yahoo_city_hash);
    } // function test_setup

    function test_yahoo_full_address()
    {
        $address = "100 Spear St, San Francisco, CA,94105-1578,US";
        $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=Yahoo&location='.
            urlencode($address);
        $yahoo_geocoder = new YahooGeocoder;
        $response = $yahoo_geocoder->call_geocoder_service($url);
        $body = substr($response['body'],0,strlen($this->yahoo_full_addr));
        $this->assertEqual($body,$this->yahoo_full_addr);
        $this->do_full_address_assertions($yahoo_geocoder->geocode($address));
    }
  
    function test_yahoo_full_address_with_geo_loc()
    {
        $address = "100 Spear St, San Francisco, CA,94105-1578,US";
        $yahoo_geocoder = new YahooGeocoder;
        $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=Yahoo&location='.
            urlencode($address);
        $response = $yahoo_geocoder->call_geocoder_service($url);
        $body = substr($response['body'],0,strlen($this->yahoo_full_addr));
        $this->assertEqual($body,$this->yahoo_full_addr);
        $result = $yahoo_geocoder->geocode($this->yahoo_full_loc);
        $this->do_full_address_assertions(
            $yahoo_geocoder->geocode($this->yahoo_full_loc));        
    }

    function test_yahoo_city()
    {
        $address = "San Francisco, CA,US";
        $url = 'http://api.local.yahoo.com/MapsService/V1/geocode?appid=Yahoo&location='.
            urlencode($address);
        $yahoo_geocoder = new YahooGeocoder;
        $response = $yahoo_geocoder->call_geocoder_service($url);
        $body = substr($response['body'],0,strlen($this->yahoo_city));
        $this->assertEqual($body,$this->yahoo_city);
        $this->do_city_assertions($yahoo_geocoder->geocode($address));
    }

    function test_yahoo_city_with_geo_loc()
    {
        $yahoo_geocoder = new YahooGeocoder;
        $result = $yahoo_geocoder->geocode($this->yahoo_city_loc);
        $this->do_city_assertions($yahoo_geocoder->geocode($this->yahoo_city_loc));
    }

    function test_lookup_failure() # wrong country
    {
        $this->yahoo_full_addr = array(
            'street_address' => "Kalervonkatu 3",
            'city' => "Jyväskylä", 'country_code' => "US");
        $this->yahoo_full_loc = new GeoLoc($this->yahoo_full_addr);
        $yahoo_geocoder = new YahooGeocoder;
        $address = $yahoo_geocoder->geocode($this->yahoo_full_loc);
        $this->assertFalse($address->success);
    }  
  
    private function do_full_address_assertions($location)
    {
        $this->assertEqual("CA", $location->state);
        $this->assertEqual("San Francisco", $location->city);
        $this->assertEqual("37.792332,-122.394027", $location->ll());
        $this->assertTrue($location->is_us());
        $this->assertEqual("100 Spear St, San Francisco, CA, US",
            $location->full_address);
        $this->assertEqual("yahoo", $location->provider);
    }
  
    private function do_city_assertions($location)
    {
        $this->assertEqual("CA", $location->state);
        $this->assertEqual("San Francisco", $location->city);
        $this->assertEqual("37.77916,-122.420049", $location->ll());
        $this->assertTrue($location->is_us());
        $this->assertEqual("San Francisco, CA, US", $location->full_address);
        $this->assertEqual('', $location->street_address);
        $this->assertEqual("yahoo", $location->provider);
    }  
} // class YahooGeocoderTestCase

$use_sessions = true;
ak_test('YahooGeocoderTestCase', $use_sessions);
?>

