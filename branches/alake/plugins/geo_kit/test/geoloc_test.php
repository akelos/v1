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
require_once($lib_dir.'ip_geocode_lookup.php');
require_once($lib_dir.'acts_as_mappable.php');

class GeoLocTestCase extends AkUnitTest
{  
    function test_setup()
    {
        $this->loc = new GeoLoc;
    }
  
    function test_is_us()
    {
        $this->assertFalse($this->loc->is_us());
        $this->loc->country_code = 'US';
        $this->assertTrue($this->loc->is_us());
    }
    function test_street_number()
    {
        $this->loc->street_address = '123 Spear St.';
        $this->assertEqual('123', $this->loc->street_number());
    }

    function test_street_name()
    {
        $this->loc->street_address = '123 Spear St.';
        $this->assertEqual('Spear St.', $this->loc->street_name());
    }

    function test_city()
    {
        $this->loc->set_city("san francisco");
        $this->assertEqual('San Francisco', $this->loc->city);
    }

    function test_full_address()
    {
        $this->loc->state = 'CA';
        $this->loc->zip = '94105';
        $this->assertEqual('123 Spear St., San Francisco, CA, 94105, US', 
            $this->loc->full_address());

        $this->loc->set_full_address('Irving, TX, 75063, US');
        $this->assertEqual('Irving, TX, 75063, US', $this->loc->full_address());
    }

    function test_array()
    {
        $this->loc->set_full_address('123 Spear St., San Francisco, CA, 94105, US');
        $another = new GeoLoc($this->loc->to_array());
        $this->assertEqual($this->loc, $another);
    }
} // class GeoLocTestCase

$use_sessions = true;
ak_test('GeoLocTestCase', $use_sessions);
?>

