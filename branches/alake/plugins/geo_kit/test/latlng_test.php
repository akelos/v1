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

class LatLngTestCase extends AkUnitTest
{
    function test_setup()
    {
        $this->loc_a = new LatLng(32.918593,-96.958444);
        $this->loc_e = new LatLng(32.969527,-96.990159);
        $this->point = new LatLng($this->loc_a->get('lat'), $this->loc_a->get('lng'));
    }

    function test_distance_between_same_using_defaults()
    {
       $this->assertEqual(0,LatLng::distance_between($this->loc_a, $this->loc_a));
       $this->assertEqual(0,$this->loc_a->distance_to($this->loc_a));
    }

    function test_distance_between_same_with_miles_and_flat()
    {
        $this->assertEqual(0,LatLng::distance_between(
            $this->loc_a, $this->loc_a, array('units'=>'miles','formula'=>'flat')));
        $this->assertEqual(0,$this->loc_a->distance_to(
            $this->loc_a, array('units'=>'miles','formula'=>'flat')));
    }

    function test_distance_between_same_with_kms_and_flat()
    {
        $this->assertEqual(0, LatLng::distance_between(
            $this->loc_a, $this->loc_a, array('units'=>'kms','formula'=>'flat')));
        $this->assertEqual(0, $this->loc_a->distance_to($this->loc_a,
            array('units'=>'kms','formula'=>'flat')));
    }

    function test_distance_between_same_with_miles_and_sphere()
    {
        $this->assertEqual(0, LatLng::distance_between(
            $this->loc_a, $this->loc_a, array('units'=>'miles','formula'=>'sphere')));
        $this->assertEqual(0, $this->loc_a->distance_to($this->loc_a, 
            array('units'=>'miles','formula'=>'sphere')));
    }

    function test_distance_between_same_with_kms_and_sphere()
    {
        $this->assertEqual(0, LatLng::distance_between(
            $this->loc_a, $this->loc_a, array('units'=>'kms','formula'=>'sphere')));
        $this->assertEqual(0, $this->loc_a->distance_to($this->loc_a, 
            array('units'=>'kms','formula'=>'sphere')));
    }

    function test_distance_between_diff_using_defaults()
    {
        $this->assertWithinMargin(3.97,LatLng::distance_between(
            $this->loc_a, $this->loc_e),0.01);
        $this->assertWithinMargin(3.97,$this->loc_a->distance_to($this->loc_e), 0.01);
    }

    function test_distance_between_diff_with_miles_and_flat()
    {
        $this->assertWithinMargin(3.97,LatLng::distance_between($this->loc_a, 
            $this->loc_e, array('units'=>'miles','formula'=>'flat')),0.2);
        $this->assertWithinMargin(3.97,$this->loc_a->distance_to($this->loc_e,
            array('units'=>'miles','formula'=>'flat')),0.2);
    }

    function test_distance_between_diff_with_kms_and_flat()
    {
        $this->assertWithinMargin(6.39,LatLng::distance_between($this->loc_a,
            $this->loc_e, array('units'=>'kms','formula'=>'flat')),0.4);
        $this->assertWithinMargin(6.39,$this->loc_a->distance_to($this->loc_e, 
            array('units'=>'kms','formula'=>'flat')), 0.4);
    }

    function test_distance_between_diff_with_miles_and_sphere()
    {
        $this->assertWithinMargin(3.97,LatLng::distance_between($this->loc_a, 
            $this->loc_e,array('units'=>'miles','formula'=>'sphere')),0.01);
        $this->assertWithinMargin(3.97,$this->loc_a->distance_to($this->loc_e, 
            array('units'=>'miles','formula'=>'sphere')),0.01);
    }

    function test_distance_between_diff_with_kms_and_sphere()
    {
        $this->assertWithinMargin(6.39,LatLng::distance_between($this->loc_a, 
            $this->loc_e, array('units'=>'kms','formula'=>'sphere')), 0.01);
        $this->assertWithinMargin(6.39,$this->loc_a->distance_to($this->loc_e, 
            array('units'=>'kms','formula'=>'sphere')), 0.01);
    }

    function test_manually_mixed_in()
    {
        $this->assertEqual(0,LatLng::distance_between($this->point, $this->point));
        $this->assertEqual(0,$this->point->distance_to($this->point));
        $this->assertEqual(0,$this->point->distance_to($this->loc_a));
        $this->assertWithinMargin(3.97,$this->point->distance_to($this->loc_e, 
            array('units'=>'miles','formula'=>'flat')),0.2);
        $this->assertWithinMargin(6.39,$this->point->distance_to($this->loc_e,
            array('units'=>'kms','formula'=>'flat')),0.4);
    }

    function test_heading_between()
    {
        $this->assertWithinMargin(332,LatLng::heading_between($this->loc_a,
            $this->loc_e),0.5);
    }

    function test_heading_to()
    {
        $this->assertWithinMargin(332,$this->loc_a->heading_to($this->loc_e), 0.5);
    }

    function test_class_endpoint()
    {
        $endpoint = LatLng::end_point($this->loc_a, 332, 3.97);
        $this->assertWithinMargin(
            $this->loc_e->get('lat'),$endpoint->get('lat'), 0.0005);
        $this->assertWithinMargin(
            $this->loc_e->get('lng'),$endpoint->get('lng'), 0.0005);
    }

    function test_instance_endpoint()
    {
        $endpoint = $this->loc_a->endpoint(332, 3.97);
        $this->assertWithinMargin(
            $this->loc_e->get('lat'),$endpoint->get('lat'), 0.0005);
        $this->assertWithinMargin(
            $this->loc_e->get('lng'),$endpoint->get('lng'), 0.0005);
    }

    function test_midpoint()
    {
        $midpoint = $this->loc_a->midpoint_to($this->loc_e);
        $this->assertWithinMargin(32.944061,  $midpoint->get('lat'), 0.0005);
        $this->assertWithinMargin(-96.974296, $midpoint->get('lng'), 0.0005);
    }

    function test_normalize()
    {
        $lat = 37.7690;
        $lng = -122.443;
        $res = LatLng::normalize($lat,$lng);
        $this->assertEqual($res,new LatLng($lat,$lng));
        $res = LatLng::normalize($lat.','.$lng);
        $this->assertEqual($res,new LatLng($lat,$lng));
        $res = LatLng::normalize($lat.' '.$lng);
        $this->assertEqual($res,new LatLng($lat,$lng));
        $res = LatLng::normalize((integer)$lat.' '.(integer)$lng);
        $this->assertEqual($res,new LatLng((integer)$lat,(integer)$lng));
        $res = LatLng::normalize(array($lat,$lng));
        $this->assertEqual($res,new LatLng($lat,$lng));
    }
} // class LatLngTestCase

$use_sessions = true;
ak_test('LatLngTestCase', $use_sessions);
?>

