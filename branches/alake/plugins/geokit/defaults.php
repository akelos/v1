<?php

class GeoKitDefaults
{
    private $default_units = GEOKIT_DEFAULT_UNITS;
    private $default_formula = GEOKIT_DEFAULT_FORMULA;

    # These defaults are used in GeoKit::Mappable.distance_to and in acts_as_mappable
    function get($key)
    {
        switch ($key) {
            case 'units':   return $this->default_units;
            case 'formula': return $this->default_formula;
            default:        return false;
        }
    }
    
    function set($default) 
    {
        if(!is_array($default)) {
            return false;
        }
        $keys = array_keys($default);
        foreach($keys as $key) {
            if($key == 'units') {
                if(in_array($default['units'], array('miles','kms'))) {
                    $this->default_units = $default['units'];
                }else{
                    return false;
                }
            }elseif($key == 'formula') {
                if(in_array($default['formula'], array('sphere','flat'))) {
                    $this->default_formula = $default['formula'];
                }else{
                    return false;
                }
            }else{
                return false;
            }
        }
        return true;
    }
}
?>
