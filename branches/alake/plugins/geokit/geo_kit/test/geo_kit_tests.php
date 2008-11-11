<?php
error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
define('AK_BASE_DIR',realpath(dirname(__FILE__).str_repeat(DS.'..',5)));
define('GEOKIT_PLUGIN_DIR',
    AK_BASE_DIR.DS.'app'.DS.'vendor'.DS.'plugins'.DS.'geo_kit');

# These are the plugin test scripts
$test_dir = GEOKIT_PLUGIN_DIR.DS.'test'.DS;
#require_once($test_dir.'comments.php');
require_once($test_dir.'array_func_tests.php');
require_once($test_dir.'default_tests.php');
# mappable.php
require_once($test_dir.'latlng_test.php');
require_once($test_dir.'geoloc_test.php');
#require_once($test_dir.'bounds_test.php');

#require_once($test_dir.'base_geocoder_test.php');
#require_once($test_dir.'ca_geocoder_test.php');
#require_once($test_dir.'google_geocoder_test.php');
#require_once($test_dir.'ipgeocoder_test.php');
#require_once($test_dir.'us_geocoder_test.php');
#require_once($test_dir.'yahoo_geocoder_test.php');
#require_once($test_dir.'multi_geocoder_test.php');

#require_once($test_dir.'ip_geocode_lookup_test.php');

#require_once($test_dir.'acts_as_mappable_test.php');
?>

