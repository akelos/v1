<?php
    # Deletes one or more key => value pairs from an array.  
    # $key_to_be_deleted may be a string or an array.
    function array_delete(&$ary,$key_to_be_deleted) 
    {
        $new = array();
        if(is_string($key_to_be_deleted)) {
            if(!array_key_exists($key_to_be_deleted,$ary)) {
                return;
            }
            foreach($ary as $key => $value) {
                if($key != $key_to_be_deleted) {
                    $new[$key] = $value;
                }
            }
            $ary = $new;
        }
        if(is_array($key_to_be_deleted)) {
            foreach($key_to_be_deleted as $del) {
                array_delete(&$ary,$del);
            }
        }
    }

    # Each element of $ary is an object.
    # This method creates a "distance" property on each object, then
    # calculates the distance from $origin.  If a distance property name 
    # other than 'distance' exists, the inclusion of it among the $options
    # will cause the value to be stored there as well as in 'distance'.
    # Finally, the $ary elements are sorted by the calculated distance.
    function array_sort_by_distance_from($origin, &$ary, &$options=array())
    {
        if(!function_exists('compare_distance')) {
            function compare_distance($a,$b)
            {
                if($a->distance > $b->distance) return 1;
                if($a->distance < $b->distance) return -1;
                return 0;
            }
        }
        if(array_key_exists('distance_property_name',$options)) {
            $distance_property_name = $options['distance_property_name'];
            array_delete($options,'distance_property_name');
        }else{
            $distance_property_name = 'distance';
        }
        foreach($ary as $object) {
            $object->distance = $origin->distance_to($object,$options);
            if(property_exists($object,$distance_property_name)) {
                $object->$distance_property_name = $object->distance;
            }
        }
        usort($ary,'compare_distance');
    }
?>
