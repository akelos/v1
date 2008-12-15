<?php

class GeoKitPlugin extends AkPlugin
{
    function load()
    {
        # Executed when project is started
        require_once($this->getPath().DS.'lib'.DS.'geo_kit/defaults.php');
#        require_once($this->getPath().DS.'lib'.DS.'geo_kit/mappable.php');
#        require_once($this->getPath().DS.'lib'.DS.'geo_kit/acts_as_mappable.php');
#        require_once($this->getPath().DS.'lib'.DS.'geo_kit/ip_geocode_lookup.php');
#        require_once($this->getPath().DS.'lib'.DS.'geo_kit/geocoders.php');

#        $acts_as_mappable =& new ActsAsMappable(); 
#        $ActiveRecord->addObserver(&$acts_as_mappable);

#        $ip_geocode_lookup =& new IpGeocodeLookup();
#        $ActionController->addObserver(&$ip_geocode_lookup);
    }
} // class GeoKitPlugin

?>
