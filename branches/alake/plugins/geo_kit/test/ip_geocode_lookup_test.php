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

require_once(GEOKIT_PLUGIN_DIR.DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');

require_once(AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkActionController.php');
require_once(AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkObject.php');
require_once(AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkRequest.php');
require_once(AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkResponse.php');
require_once(AK_FRAMEWORK_DIR.DS.'lib'.DS.'AkUnitTest.php');

# These are the plugin scripts
$lib_dir = GEOKIT_PLUGIN_DIR.DS.'lib'.DS.'geo_kit'.DS;
require_once($lib_dir.'array_funcs.php');
require_once($lib_dir.'defaults.php');
require_once($lib_dir.'mappable.php');
require_once($lib_dir.'geocoders.php');
require_once($lib_dir.'ip_geocode_lookup.php');

class LocationAwareController extends AkActionController
{
#  geocode_ip_address
#  
#  def index
#    render :nothing => true
#  end
}

class TestRequest extends AkRequest
{
    var $remote_ip;
}

class TestResponse extends AkResponse
{

}

class IpGeocodeLookupTestCase extends AkUnitTest
{
    function test_setup()
    {
        $this->success = new GeoLoc;
        $this->success->provider = "hostip";
        $this->success->lat = 41.7696;
        $this->success->lng = -88.4588;
        $this->success->city = "Sugar Grove";
        $this->success->state = "IL";
        $this->success->country_code = "US";
        $this->success->success = true;
        
        $this->failure = new GeoLoc;
        $this->failure->provider = "hostip";
        $this->failure->city = "(Private Address)";
        $this->failure->success = false;
        
        $this->controller = new LocationAwareController;
        $this->request    = new TestRequest;  # ActionController::TestRequest
        $this->response   = new TestResponse; # ActionController::TestResponse
    } // function test_setup

    function test_no_location_in_cookie_or_session()
    {
#        $ip_geocoder = new IpGeocoder;
#        $location = $ip_geocoder->geocode('12.215.42.19');
        $this->request->remote_ip = "good ip";
#        $this->assertEqual($response['body'],$this->success);
#  GeoKit::Geocoders::IpGeocoder.expects(:geocode).with("good ip").returns(@success)
        get :index
        $this->verify();
    }
  
    function test_location_in_cookie()
    {
        @request.remote_ip = "good ip"
        @request.cookies['geo_location'] = CGI::Cookie.new('geo_location', @success.to_yaml)
        get :index
        $this->verify();
    }
  
    function test_location_in_session()
    {
        @request.remote_ip = "good ip"
        @request.session[:geo_location] = @success
        @request.cookies['geo_location'] = CGI::Cookie.new('geo_location', @success.to_yaml)
        get :index
        $this->verify();
    }
  
    function test_ip_not_located()
    {
        GeoKit::Geocoders::IpGeocoder.expects(:geocode).with("bad ip").returns(@failure)
        @request.remote_ip = "bad ip"
        get :index
        assert_nil @request.session[:geo_location]
    }
  
    private function verify()
    {
        assert_response :success    
        assert_equal @success, @request.session[:geo_location]
        assert_not_nil cookies['geo_location']
        assert_equal @success, YAML.load(cookies['geo_location'].join)
    }  
} // class IpGeocodeLookupTestCase

$use_sessions = true;
ak_test('IpGeocodeLookupTestCase', $use_sessions);
?>

