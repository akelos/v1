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
#require_once($lib_dir.'defaults.php');
#require_once($lib_dir.'mappable.php');
#require_once($lib_dir.'acts_as_mappable.php');
require_once($lib_dir.'array_funcs.php');
#require_once($lib_dir.'ip_geocode_lookup.php');
#require_once($lib_dir.'geocoders.php');

class Origin
{
    function distance_to($object,$options=array())
    {
        $distances = array('vantaa' => 20, 'hanko' => 100, 'hyvinkää' => 54, 
            'oulu' => 600, 'jyväskylä' => 400, 'tampere' => 160, 'turku' => 170);
        $city = strtolower($object->city);
        return $distances[$city];
    }
}

class City
{
    function __construct($da,$dist=0,$from=false,$expected=false)
    {
        $this->city = $da;
        if($from) {
            $this->from_Helsinki = $dist;
        }
        if($expected) {
            $this->distance = $dist;
        }
    }
}

class ArrayFuncTestCase extends AkUnitTest
{
/*
    function test_setup()
    {
        # Create tables
        $this->installAndIncludeModels('Company');
        $this->installAndIncludeModels('CustomLocation');
        $this->installAndIncludeModels('Location');
        $this->installAndIncludeModels('Store');
        $this->populateTables(array('companies','custom_locations','locations'));
        $Installer =& new AkInstaller();   
    } // function test_setup

    # This function is a modification of a function of the same name in
    # AK_UNIT_TEST.  The changes accomplish two things:
    #       This function looks for the YAML files within the
    #           GEOKIT_PLUGIN_DIR instead of app_home/test/fixtures/data, 
    #           where non-plugin testing is done.
    #       It looks for files with the extension "yml" as well as "yaml".
    #           The reason for this is that Rails uses the extension "yml".
    function populateTables()
    {
        $args = func_get_args();
        $tables = !empty($args) ? (is_array($args[0]) ? $args[0] : 
            (count($args) > 1 ? $args : Ak::toArray($args))) : array();
        foreach ($tables as $table){
            $data_dir = GEOKIT_PLUGIN_DIR.DS.'test'.DS.'fixtures'.DS.'data';
            $file = $data_dir.DS.$table.'.yaml';
            if(!file_exists($file)){
                $file = $data_dir.DS.$table.'.yml';
                if(!file_exists($file)) {
                    continue;
                }
            }
            $class_name = AkInflector::classify($table);
            if($this->instantiateModel($class_name)) {
                $items = Ak::convert('yaml','array',file_get_contents($file));
                foreach ($items as $item) {
                    $this->{$class_name}->create($item);
                }
            }
        }
    } // function populateTables()
*/

    function test_array_sort_by_distance_from()
    {
        $origin = new Origin;
        $expected_result = array();
        $expected_result[] = new City('Vantaa',20,true,true);
        $expected_result[] = new City('Hyvinkää',54,false,true);
        $expected_result[] = new City('Hanko',100,true,true);
        $expected_result[] = new City('Tampere',160,false,true);
        $expected_result[] = new City('Turku',170,true,true);
        $expected_result[] = new City('Jyväskylä',400,false,true);
        $expected_result[] = new City('Oulu',600,true,true);

        $cities = array();
        $cities[] = new City('Vantaa',0,true);
        $cities[] = new City('Hanko',0,true);
        $cities[] = new City('Hyvinkää');
        $cities[] = new City('Oulu',0,true);
        $cities[] = new City('Jyväskylä');
        $cities[] = new City('Tampere');
        $cities[] = new City('Turku',0,true);
        $opt = array(
            'distance_property_name' => 'from_Helsinki','dummy' => 'options');
        array_sort_by_distance_from($origin,$cities,$opt);
        $this->assertEqual($expected_result,$cities);
    }
    
} // class ArrayFuncTestCase

$use_sessions = true;
ak_test('ArrayFuncTestCase', $use_sessions);
?>

