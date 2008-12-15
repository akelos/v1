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

class BoundsTestCase extends AkUnitTest
{  
    function test_setup()
    {
    # This is the area in Texas
    $this->sw     = new LatLng(32.91663,-96.982841);
    $this->ne     = new LatLng(32.96302,-96.919495);
    $this->bounds = new Bounds($this->sw,$this->ne);
    $this->loc_a  = new LatLng(32.918593,-96.958444); # inside bounds    
    $this->loc_b  = new LatLng(32.914144,-96.958444); # outside bouds
    
    # this is a cross-meridan area
    $this->cross_meridian = Bounds::normalize(array(30,170),array(40,-170));
    $this->inside_cm      = new LatLng(35,175);
    $this->inside_cm_2    = new LatLng(35,-175);
    $this->east_of_cm     = new LatLng(35,-165);
    $this->west_of_cm     = new LatLng(35,165);
    }  

    function test_equality()
    {
        $this->assertEqual(new Bounds($this->sw,$this->ne), 
                           new Bounds($this->sw,$this->ne));
    }

    function test_normalize()
    {
        $result = Bounds::normalize($this->sw,$this->ne);
        $this->assertEqual($result,new Bounds($this->sw,$this->ne));

        $result = Bounds::normalize(array($this->sw,$this->ne));
        $this->assertEqual($result,new Bounds($this->sw,$this->ne));

        $result = Bounds::normalize(
            array($this->sw->get('lat'),$this->sw->get('lng')),
            array($this->ne->get('lat'),$this->ne->get('lng')));
        $this->assertEqual($result,new Bounds($this->sw,$this->ne));

        $result = Bounds::normalize(array(
            array($this->sw->get('lat'),$this->sw->get('lng')),
            array($this->ne->get('lat'),$this->ne->get('lng'))));
        $this->assertEqual($result,new Bounds($this->sw,$this->ne));
    }

    function test_point_inside_bounds()
    {
        $this->assertTrue($this->bounds->contains($this->loc_a));
    }

    function test_point_outside_bounds()
    {
        $this->assertFalse($this->bounds->contains($this->loc_b));
    }

    function test_point_inside_bounds_cross_meridian()
    {
        $this->assertTrue($this->cross_meridian->contains($this->inside_cm));
        $this->assertTrue($this->cross_meridian->contains($this->inside_cm_2));
    }

    function test_point_outside_bounds_cross_meridian()
    {
        $this->assertFalse($this->cross_meridian->contains($this->east_of_cm));
        $this->assertFalse($this->cross_meridian->contains($this->west_of_cm));
    }

    function test_center()
    {

        $latlng = $this->bounds->center();
        $this->assertWithinMargin(32.939828,$latlng->get('lat'),0.00005);
        $this->assertWithinMargin(-96.9511763,$latlng->get('lng'),0.00005);
    }

    function xtest_center_cross_meridian()
    {
        $latlng = $this->cross_meridian->center();
        $this->assertWithinMargin(35.41160,$latlng->get('lat'),0.00005);
        $this->assertWithinMargin(179.38112,$latlng->get('lng'),0.00005);
    }

    function test_creation_from_circle()
    {
        $bounds  = Bounds::from_point_and_radius(array(32.939829, -96.951176),2.5);
        $inside  = new LatLng(32.9695270000,-96.9901590000);
        $outside = new LatLng(32.8951550000,-96.9584440000);
        $this->assertTrue($bounds->contains($inside));
        $this->assertFalse($bounds->contains($outside));
    }
} // class BoundsTestCase

$use_sessions = true;
ak_test('BoundsTestCase', $use_sessions);
?>

