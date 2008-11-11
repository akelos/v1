<?php
  # Contains the class acts_as_mappable as an Observer of ActiveRecord.
  # It augments find services such that they provide distance calculation
  # query services.  The find method accepts additional options:
  #
  # * :origin - can be 
  #   1. a two-element array of latititude/longitude -- :origin=>[37.792,-122.393]
  #   2. a geocodeable string -- :origin=>'100 Spear st, San Francisco, CA'
  #   3. an object which responds to lat and lng methods, 
  #      latitude and longitude methods, or whatever methods you have 
  #      specified for lng_column_name and lat_column_name
  #
  # Other finder methods are provided for specific queries.  These are:
  #
  # * find_within (alias: find_inside)
  # * find_beyond (alias: find_outside)
  # * find_closest (alias: find_nearest)
  # * find_farthest
  #
  # Counter methods are available and work similarly to finders.  
  #
  # If raw SQL is desired, the distance_sql method can be used to 
  # obtain SQL appropriate to use in a find_by_sql call.

require_once(AK_LIB_DIR.DS.'AkActiveRecord'.DS.'AkObserver.php');
require_once('mappable.php');
require_once('array_funcs.php');

    # Bring distance query support into ActiveRecord models.  The calculation
    # is done using 'miles' or 'kms' for the distance units and 'flat' or 
    # 'sphere' (Haversine) for the formula.  The defaults for these are found 
    # in config/config.php.

    # When the object is created, an array of options may also be submitted.  
    # The following syntax shows the defaults:
    #
    #   $acts_as_mappable = new ActsAsMappable(array(
    #       'default_units'        => GEOKIT_DEFAULT_UNITS,
    #       'default_formula'      => GEOKIT_DEFAULT_FORMULA,
    #       'lat_column_name'      => 'lat',
    #       'lng_column_name'      => 'lng',
    #       'distance_column_name' => 'distance'
    #       ));
    # 
    # If you wish to auto geocode a column, include the 'auto_geocode' key => 
    # value pair.  If you use the syntax:
    #
    #       'auto_geocode'         => true
    #
    # 'address' will be the default column.  any_column_name and error_message
    # may be used with this syntax:
    #   
    #       'auto_geocode'         => array('field' => 'any_column_name',
    #                                       'error_message' => 'bad address')
    #   
    # In both cases, it creates a beforeValidationOnCreate callback to 
    # geocode the given column.
    # For anything more customized, we recommend you forgo the auto_geocode option
    # and create your own AR callback to handle geocoding.
class ActsAsMappable extends AkObserver
{
    var $_ActiveRecordInstance; 
    var $options = array();
    var $distance_column_name;
    var $default_units;
    var $default_formula;
    var $lat_column_name;
    var $lng_column_name;
    var $qualified_lat_column_name;
    var $qualified_lng_column_name;
    
    function ActsAsMappable(&$ActiveRecordInstance) {
        $this->_ActiveRecordInstance =& $ActiveRecordInstance;    
    }

    function init($options = array())
    {
        $success =  $this->_ensureIsActiveRecordInstance(
            $this->_ActiveRecordInstance);
        $singularized_model_name = AkInflector::underscore(
            AkInflector::singularize(
                $this->_ActiveRecordInstance->getTableName()
            )
        );
        $default_options = array(
        'class_name' => $this->_ActiveRecordInstance->
            getModelName().'ActsAsMappable'
        );
        $this->distance_column_name = 
            array_key_exists('distance_column_name',$options) ?
            $options['distance_column_name'] : 'distance';
        $this->default_units = array_key_exists('default_units',$options) ?
            $options['default_units'] : GEOKIT_DEFAULT_UNITS;
        $this->default_foumula = array_key_exists('default_formula',$options) ?
            $options['default_formula'] : GEOKIT_DEFAULT_FORMULA;
        $this->lat_column_name = array_key_exists('lat_column_name',$options) ?
            $options['lat_column_name'] : 'lat';
        $this->lng_column_name = array_key_exists('lng_column_name',$options) ?
            $options['lng_column_name'] : 'lng';
        $this->qualified_lat_column_name = $this->table_name.$this->lat_column_name;
        $this->qualified_lng_column_name = $this->table_name.$this->lng_column_name;
        if(array_key_exists('auto_geocode',$options)) {
            if($options['auto_geocode'] == true) {
                $options['auto_geocode'] = array();
            }
            $this->auto_geocode_field = 
                array_key_exists('field',$options['auto_geocode']) ?
                    $options['auto_geocode']['field'] : 'address';
            $this->auto_geocode_error_message = 
                array_key_exists('error_message',$options['auto_geocode']) ?
                    $options['auto_geocode']['error_message'] :
                    'could not locate address';
        }
        $this->options = array_merge($default_options, $options);
        return $success;
    } // function init

    # set the actual callback here
    public static function beforeValidationOnCreate() {
        auto_geocode_address();
    }
    
    # this is the callback for auto_geocoding
    private function auto_geocode_address()
    {
        $auto_geocode_field = $this->auto_geocode_field;
        $address = $$auto_geocode_field;
        $geo = MultiGeocoder::geocode($address);
        if($geo->success) {
            $$lat_column_name = $geo->lat;
            $$lng_column_name = $geo->lng;
        }else{
            $msg = $this->auto_geocode_error_message.' ';
            $msg .= $address.' in '.$auto_geocode_field;
            Geocode::logger('error',$msg);
        }
        return $geo->success;
    }
    
    # Extends the existing find method in potentially two ways:
    # - If a mappable instance exists in the options, adds a distance column.
    # - If a mappable instance exists in the options and 
    #   the distance column exists in the conditions, 
    #   substitutes the distance sql for the distance column 
    # -- this saves having to write the gory SQL.
    function find()
    {
        $args = func_get_args();
        $this->prepare_for_find_or_count('find', $args);
        parent::find($args);
    }
        
    # Extends the existing count method by:
    # - If a mappable instance exists in the options and 
    #   the distance column exists in the conditions, 
    #     substitutes the distance sql for the distance column -- 
    #     this saves having to write the gory SQL.
    function count()
    {
        $args = func_get_args();
        $this->prepare_for_find_or_count('count', $args);
        parent::count($args);
    }
    
    # Finds within a distance radius.
    function find_within($distance, $options=array())
    {
        $options['within'] = $distance;
        $this->find('all', $options);
    }

    # find_inside is an alias of find_within
    function find_inside($distance, $options=array())
    {
        $options['within'] = $distance;
        $this->find('all', $options);
    }
                
    # Finds beyond a distance radius.
    function find_beyond($distance, $options=array())
    {
        $options['beyond'] = $distance;
        $this->find('all', $options);
    }

    # find_outside is an alias of find_beyond
    function find_outside($distance, $options=array())
    {
        $options['beyond'] = $distance;
        $this->find('all', $options);
    }
        
    # Finds according to a range.  Accepts inclusive or exclusive ranges.
    function find_by_range($range, $options=array())
    {
        $options['range'] = $range;
        $this->find('all', $options);
    }

    # Finds the nearest to the origin.
    function find_nearest($options=array())
    {
        $this->find('nearest', $options);
    }
        
    #find_closest is an alias of find_nearest
    function find_closest($options=array())
    {
        $this->find('nearest', $options);
    }
        
    # Finds the farthest from the origin.
    function find_farthest($options=array())
    {
        $this->find('farthest', $options);
    }

    # Finds within rectangular bounds (sw,ne).
    function find_within_bounds($bounds, $options=array())
    {
        $options['bounds'] = $bounds;
        $this->find('all', $options);
    }
        
    # counts within a distance radius.
    function count_within($distance, $options=array())
    {
        $options['within'] = $distance;
        $this->count($options);
    }

    # count_inside is an alias of count_within
    function count_inside($distance, $options=array())
    {
        $options['within'] = $distance;
        $this->count($options);
    }

    # Counts beyond a distance radius.
    function count_beyond($distance, $options=array())
    {
        $options['beyond'] = $distance;
        $this->count($options);
    }

    # count_outside is an alias of count_beyond
    function count_outside($distance, $options=array())
    {
        $options['beyond'] = $distance;
        $this->count($options);
    }
        
    # Counts according to a range.  Accepts inclusive or exclusive ranges.
    function count_by_range($range, $options=array())
    {
        $options['range'] = $range;
        $this->count($options);
    }

    # Finds within rectangular bounds (sw,ne).
    function count_within_bounds($bounds, $options=array())
    {
        $options['bounds'] = $bounds;
        $this->count($options);
    }
                
    # Returns the distance calculation to be used as a display column or 
    # a condition.  This is provided for anyone wanting access to the raw SQL.
    function distance_sql($origin, $units=null, $formula=null)
    {
        if(is_null($units)) {
            $units = $this->default_units;
        }
        if(is_null($formula)) {
            $formula = $this->default_formula;
        }
        switch($formula) {
            case 'sphere':
                $sql = $this->sphere_distance_sql($origin,$units);
                break;
            case 'flat':
                $sql = $this->flat_distance_sql($origin,$units);
                break;
        }
        return $sql;
    }
       
    # Prepares either a find or a count action by parsing through the options and
    # conditionally adding to the select clause for finders.
    private function prepare_for_find_or_count($action, &$args)
    {
        $options = array_key_exists('extract_options',$args) ?
            $args['extract_options'] : $this->extract_options_from_args($args);

        # Obtain items affecting distance condition.
        $origin  = $this->extract_origin_from_options($options);
        $units   = $this->extract_units_from_options($options);
        $formula = $this->extract_formula_from_options($options);
        $bounds  = $this->extract_bounds_from_options($options);

        # if no explicit bounds were given, 
        #   try formulating them from the point and distance given
        if($bounds == null) {
            $bounds = $this->formulate_bounds_from_distance(
                $options, $origin, $units);
        }

        # Apply select adjustments based upon action.
        if($origin != null && $action == 'find') {
            $this->add_distance_to_select($options, $origin, $units, $formula);
        }

        # Apply the conditions for a bounding rectangle if applicable
        if($bounds != null) {
            $this->apply_bounds_conditions($options,$bounds);
        }

        # Apply distance scoping and perform substitutions.
        $this->apply_distance_scope($options);
        if($origin != null && array_key_exists('conditions',$options)) {
            $this->substitute_distance_in_conditions(
                $options, $origin, $units, $formula);
        }

        # Order by scoping for find action.
        if($action == 'find') {
            $this->apply_find_scope($args, $options);
        }

        # Unfortunatley, we need to do extra work if you use an :include. 
        # See the method for more info.
        if(array_key_exists('include',$options) && 
           array_key_exitst('order',$options)   && 
           $origin != null) {
            $this->handle_order_with_include($options,$origin,$units,$formula);
        }

        # Restore options minus the extra options that we used for the
        # GeoKit API.
        $args[]= $options;
    } // function prepare_for_find_or_count
        
    # If we're here, it means that 
    #    1) an origin argument, 
    #    2) an 'include', 
    #    3) an 'order' clause were supplied.
    # Now we have to sub some SQL into the 'order' clause. 
    # The reason is that when you do an 'include', AkActiveRecord drops 
    #   the psuedo-column (specificically, 'distance') which we supplied 
    #   for 'select'. 
    # So, the 'distance' column isn't available for the 'order' clause to 
    #   reference when we use 'include'.
    private function handle_order_with_include($options,$origin,$units,$formula)
    {
        # replace the distance_column_name with the distance sql in order clause
        $sql = $this->distance_sql($origin,$units,$formula);
        str_replace($this->distance_column_name, $sql, $options['order']);
    }

    # Looks for mapping-specific tokens and makes appropriate translations
    # so that the original finder has its expected arguments.  Resets the 
    # the scope argument to 'first' and ensures the limit is set to one.
    private function apply_find_scope($args, &$options)
    {
        $args[0] = 'first';
        $options['limit'] = 1;
        switch($args['first']) {
        case 'nearest':
        case 'closest':
            $options['order'] = $this->distance_column_name . "ASC";
            break;
        case 'farthest':
            $options['order'] = $this->distance_column_name . "DESC";
            break;
        }
    }
        
    # If it's a 'within' query, add a bounding box to improve performance.
    # This only gets called if a 'bounds' argument is not otherwise supplied. 
    private function formulate_bounds_from_distance($options,$origin,$units)
    {
        if(array_key_exists('within',$options)) {
            $distance =  $options['within'];
        }
        if(array_key_exists('range',$options)) {
            $distance = $options['range']->last - 
                       ($options['range']->exclude_end ? 1 : 0);
        }
        if(isset($distance)) {
            $options = array('units' => $units);
            $res = Bounds::from_point_and_radius($origin,$distance,$options);
        }else{
            $res = null;
        }
        return $res;
    }
        
    # Replace 'within', 'beyond' and 'range' distance tokens with the 
    # appropriate distance where clauses.  Removes these tokens from the options 
    # associative array.
    private function apply_distance_scope(&$options)
    {
        if(array_key_exists('within',$options)) {
            $distance_condition = $distance_column_name.' <= '.$options['within'];
        }
        if(array_key_exists('beyond',$options)) {
            $distance_condition = $distance_column_name.' > '.$options['beyond']; 
        }
        if(array_key_exists('range',$options)) {
            $distance_condition = 
                $distance_column_name.' >= '.$options['range']->first.' AND '.
                $distance_column_name.' < = ';
            if(!$options['range']->exclude_end) {
                $distance_condition .= $options['range']->last;
            }
        }
        if(isset($distance_condition)) {
            array_delete($options,array('within','beyond','range'));
            $options['conditions'] = 
                $this->augment_conditions($options['conditions'],$distance_condition);
        }
    }

    # This method lets you transparently add a new condition to a query without
    # worrying about whether it currently has conditions, or 
    # what kind of conditions they are (string or array).
    # 
    # Takes the current conditions (which can be an array or a string, 
    # or can be null/false), and a SQL string. 
    # It inserts the sql into the existing conditions, and returns new conditions
    # (which can be a string or an array)
    private function augment_conditions($current_conditions,$sql)
    {
        if(is_null($current_conditions)) {
            return $sql;
        }
        if(is_bool($current_conditions) && !$current_conditions) {
            return $sql;
        }
        if(is_string($current_conditions)) {
            return $current_conditions.' AND '.$sql;
        }
        if(is_array($current_conditions)) {
            $current_conditions[0] = $current_conditions[0].' AND '.$sql;
            return $current_conditions;
        }
        return $sql;
    }

    # Alters the conditions to include rectangular bounds conditions.
    private function apply_bounds_conditions(&$options,$bounds)
    {
        $sw = $bounds->sw; $ne = $bounds->ne;
        $lng_sql = $bounds->crosses_meridian ?
            '('.$qualified_lng_column_name.' < '.$sw->lng.' OR '.
                $qualified_lng_column_name.' > '.$ne->lng.')' :
            $qualified_lng_column_name.' > '.$sw->lng.' AND '.
                $qualified_lng_column_name.' < '.$ne->lng;
        $bounds_sql = $qualified_lat_column_name.' > '.$sw->lat.' AND '.
                      $qualified_lat_column_name.' < '.$ne->lat.' AND '.$lng_sql;
        $options['conditions'] = 
            $this->augment_conditions($options['conditions'],$bounds_sql);
    }

    # Extracts the origin instance out of the options if it exists and returns
    # it.  If there is no origin, looks for latitude and longitude values to 
    # create an origin.  
    # The side-effect of the method is to remove these keys from $options.
    #
    # Comments by Ruby to PHP translator: 
    #   If there is no origin, a null value is returned.  The statement 
    #       "If there is no origin, looks for latitude and longitude values 
    #       to create an origin." was not coded in Ruby.
    #   The first side effect above mentions "these keys" in the comment from
    #       the Ruby coders.  In fact, this function removes only the 'origin' 
    #       instance.  It is possible that a function called by
    #       normalize_point_to_lat_lng removes other keys from $origin, 
    #       but I don't think this is the case.
    private function extract_origin_from_options(&$options)
    {
        if(array_key_exists('origin',$options)) {
            $origin = $options['origin'];
            array_delete($options,'origin');
            return $this->normalize_point_to_lat_lng($origin);
        }else{
            return null;
        }
# Ruby code:
#      origin = options.delete(:origin)
#      res = normalize_point_to_lat_lng(origin) if origin
#      res
    }
        
    # Extract the units out of the options if it exists and returns it.  If
    # there is no 'units' key, it uses the default.  The side effect of the 
    # method is to remove the 'units' key from the $options.
    private function extract_units_from_options(&$options)
    {
        if(array_key_exists('units',$options)) {
            $units = $options['units'];
            array_delete($options,'units');
        }else{
            $units = $this->default_units;
        }
        return $units;
    }
        
    # Extract the formula out of the options if it exists and returns it.  If
    # there is no 'formula' key, it uses the default.  The side effect of the 
    # method is to remove the 'formula' key from the options hash.
    private function extract_formula_from_options(&$options)
    {
        if(array_key_exists('formula',$options)) {
            $formula = $options['formula'];
            $this->array_delete($options,'formula');
        }else{
            $formula = $this->default_formula;
        }
        return $formula;
    }
        
    private function extract_bounds_from_options(&$options)
    {
        if(array_key_exists('bounds',$options)) {
            $bounds = $options['bounds'];
            array_delete($options,'bounds');
            return Bounds::normalize($bounds);
        }
        return null;    
    }
       
    # Geocode IP address.
    private function geocode_ip_address($origin)
    {
        $geo_location = IpGeocoder::geocode($origin);
        if($geo_location->success) {
            return $geo_location;
        }
        $msg = "Couldn't geocode ip address: ".$origin;
        new GeocodeError($msg);
    }

    # Given a point in a variety of formats (an address to geocode,
    # an array of [lat,lng], or an object with appropriate lat/lng methods, or
    # an IP address), this method will normalize it into a LatLng instance. 
    # The only thing this method adds on top of LatLng->normalize is handling 
    # of IP addresses.
    private function normalize_point_to_lat_lng($point)
    {
        if(is_string($point) && 
           ereg("^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3})?$",$point)) {
            return $this->geocode_ip_address($point);
        }else{
            return LatLng::normalize($point);
        }
    }

    # Augments the select with the distance SQL.
    private function add_distance_to_select(
        &$options,$origin,$units=null,$formula=null)
    {
        if(is_null($units)) {
            $units = $this->default_units;
        }
        if(is_null($formula)) {
            $formula = $this->default_formula;
        }
        $distance_selector = $this->distance_sql($origin,$units,$formula).' AS '.
            $this->distance_column_name;
        $selector = array_key_exists('select',$options) && 
                    strlen($options['select']) > 0 ? 
            $options['select'] : '*';

        $options['select'] = $selector.', '.$distance_selector;  
    }

    # Looks for the distance column and replaces it with the distance sql. 
    # If an origin was not passed in and the distance column exists, we 
    # leave it to be flagged as bad SQL by the database.
    # Conditions are either a string or an array.  
    #     In the case of an array, the first entry contains the condition.
    private function substitute_distance_in_conditions(
        &$options, $origin, $units=null,$formula=null)
    {
        if(is_null($units)) {
            $units = $this->default_units;
        }
        if(is_null($formula)) {
            $formula = $this->default_formula;
        }
        $original_conditions = $options['conditions'];
        $condition = is_string($original_conditions) ? 
            $original_conditions : $original_conditions[0];
        $pattern = '\s*'.$this->distance_column_name.'(\s<>=)*';
        $sql = $this->distance_sql($origin,$units,$formula);
        $condition = ereg_replace($pattern, $sql ,$condition);
        if(is_string($original_conditions)) {
            $original_conditions = $condition;
        }
        if(is_array($original_conditions)) {
            $original_conditions[0] = $condition;
        }
        $options['conditions'] = $original_conditions;
    }
        
    # Returns the distance SQL using the spherical world formula (Haversine).  
    # The SQL is tuned to the database in use.
    private function sphere_distance_sql($origin, $units)
    {
        $lat = deg2rad($origin->lat);
        $lng = deg2rad($origin->lng);
        $multiplier = Mappable::units_sphere_multiplier($units);
        $database_type = strtolower($database_settings[AK_ENVIRONMENT]['type']);
        switch($database_type) {
            case 'mysql':
                $sql = '(ACOS(COS('.$lat.')*COS('.$lng.')*COS(RADIANS('.
                    $this->qualified_lat_column_name.'))*COS(RADIANS('.
                    $this->qualified_lng_column_name.'))+COS('.
                    $lat.')*SIN('.$lng.')*COS(RADIANS('.
                    $this->qualified_lat_column_name.'))*SIN(RADIANS('.
                    $this->qualified_lng_column_name.'))+SIN('.
                    $lat.')*SIN(RADIANS('.$this->qualified_lat_column_name.
                    ')))*'.$multiplier.')';
                break;
            case 'pgsql':
                $sql = '(ACOS(COS('.$lat.')*COS('.$lng.')*COS(RADIANS('.
                    $this->qualified_lat_column_name.'))*COS(RADIANS('.
                    $this->qualified_lng_column_name.'))+COS('.$lat.
                    ')*SIN('.$lng.')*COS(RADIANS('.
                    $this->qualified_lat_column_name.'))*SIN(RADIANS('.
                    $this->qualified_lng_column_name.'))+SIN('.$lat.
                    ')*SIN(RADIANS('.$this->qualified_lat_column_name.
                    ')))*'.$multiplier.')';
                break;
            default: 
                $sql = 'unhandled '.$database_type.' adapter in ';
                $sql .= 'ActsAsMappable->sphere_distance_sql.';
                break;
        }
        return $sql;
    } // function sphere_distance_sql
        
    # Returns the distance SQL using the flat-world formula (Phythagorean Theory).  
    # The SQL is tuned to the database in use.
    private function flat_distance_sql($origin, $units)
    {
        $lat_degree_units = Mappable::units_per_latitude_degree($units);
        $lng_degree_units = 
            Mappable::units_per_longitude_degree($origin->lat, $units);
        $database_type = strtolower($database_settings[AK_ENVIRONMENT]['type']);
        switch($database_type) {
            case 'mysql':
                $sql = 'SQRT(POW('.$lat_degree_units.'*('.$origin->lat.'-'.
                    $this->qualified_lat_column_name.'),2)+POW('.
                    $lng_degree_units.'*('.$origin->lng.'-'.
                    $this->qualified_lng_column_name.'),2))';
                break;
            case 'pgsql':
                $sql = 'SQRT(POW('.$lat_degree_units.'*('.$origin->lat.'-'.
                    $this->qualified_lat_column_name.'),2)+POW('.
                    $lng_degree_units.'*('.$origin->lng.'-'.
                    $this->qualified_lng_column_name.'),2))';
                break;
            default: 
                $sql = 'unhandled '.$database_type.' adapter in ';
                $sql .= 'ActsAsMappable->flat_distance_sql.';
                break;
        }
        return $sql;
    } // function flat_distance_sql
} // class ActsAsMappable
?>

