<?php

$database_settings = array(
    'production' => array(
        'type' => 'mysql', // mysql, sqlite or pgsql
        'database_file' => '', // you only need this for SQLite
        'host' => 'erie.lakeinfoworks.com',
        'port' => '',
        'database_name' => 'geo_kit',
        'user' => 'alan',
        'password' => 'da12ve',
        'options' => '' // persistent, debug, fetchmode, new
    ),
    
    'development' => array(
        'type' => 'mysql',
        'database_file' => '',
        'host' => 'erie.lakeinfoworks.com',
        'port' => '',
        'database_name' => 'geo_kit_dev',
        'user' => 'alan',
        'password' => 'da12ve',
        'options' => ''
    ),
    
    // Warning: The database defined as 'testing' will be erased and
    // re-generated from your development database when you run './script/test app'.
    // Do not set this db to the same as development or production.
    'testing' => array(
        'type' => 'mysql',
        'database_file' => '',
        'host' => 'erie.lakeinfoworks.com',
        'port' => '',
        'database_name' => 'geo_kit_tests',
        'user' => 'alan',
        'password' => 'da12ve',
        'options' => ''
    )
);

defined('AK_SESSION_HANDLER') ? null : define('AK_SESSION_HANDLER', 1);

// If you want to write/delete/create files or directories using ftp instead of local file
// access, you can set an ftp connection string like:
// $ftp_settings = 'ftp://username:password@example.com/path/to_your/base/dir';
$ftp_settings = ''; 

 // Current environment. Options are: development, testing and production
defined('AK_ENVIRONMENT') ? null : define('AK_ENVIRONMENT', 'testing');


// Locale settings ( you must create a file at /config/locales/ using en.php as departure point)
// Please be aware that your charset needs to be UTF-8 in order to edit the locales files
// auto will enable all the locales at config/locales/ dir
defined('AK_AVAILABLE_LOCALES') ? null : define('AK_AVAILABLE_LOCALES', 'en');

// Use this in order to allow only these locales on web requests
defined('AK_ACTIVE_RECORD_DEFAULT_LOCALES') ? 
    null : define('AK_ACTIVE_RECORD_DEFAULT_LOCALES', 'en');
defined('AK_APP_LOCALES') ? null : define('AK_APP_LOCALES', 'en');
defined('AK_PUBLIC_LOCALES') ? null : define('AK_PUBLIC_LOCALES', 'en');

// The web configuration wizard could not detect if you have mod_rewrite enabled. 
// If that is the case, you should uncomment the next line line for better performance. 
defined('AK_URL_REWRITE_ENABLED') ? null : define('AK_URL_REWRITE_ENABLED', true);

defined('AK_FRAMEWORK_DIR') ? null : define('AK_FRAMEWORK_DIR', '/home/alan/develop/php/akelos');

include_once(dirname(__FILE__).DIRECTORY_SEPARATOR.'boot.php');

# These defaults are used in Ruby GeoKit::Mappable.distance_to and in acts_as_mappable
defined('GEOKIT_DEFAULT_UNITS') ? null : define('GEOKIT_DEFAULT_UNITS', 'miles');
defined('GEOKIT_DEFAULT_FORMULA') ? null : define('GEOKIT_DEFAULT_FORMULA', 'sphere');

# This is the timeout value in seconds to be used for calls to the geocoder web
# services.  For no timeout at all, comment out the setting.  The timeout unit
# is in seconds.
defined('GEOKIT_GEOCODERS_TIMEOUT') ? null : define('GEOKIT_GEOCODERS_TIMEOUT', 3);

# These settings are used if web service calls must be routed through a proxy.
# These setting can be null if not needed, otherwise, addr and port must be
# filled in at a minimum.  If the proxy requires authentication, the username
# and password can be provided as well.
defined('GEOKIT_GEOCODERS_PROXY_ADDR') ? null :define('GEOKIT_GEOCODERS_PROXY_ADDR', null);
defined('GEOKIT_GEOCODERS_PROXY_PORT') ? null :define('GEOKIT_GEOCODERS_PROXY_PORT', null);
defined('GEOKIT_GEOCODERS_PROXY_USER') ? null :define('GEOKIT_GEOCODERS_PROXY_USER', null);
defined('GEOKIT_GEOCODERS_PROXY_PASS') ? null :define('GEOKIT_GEOCODERS_PROXY_PASS', null);

# This is your yahoo application key for the Yahoo Geocoder.
# See http://developer.yahoo.com/faq/index.html#appid
# and http://developer.yahoo.com/maps/rest/V1/geocode.html
defined('GEOKIT_YAHOO_GEOCODER_KEY') ? null : define('GEOKIT_YAHOO_GEOCODER_KEY', 'Q2dTv4PV34E80vLuwTJZKlQ0OhMM06_wuJytMiSNBY5mGo8ZSXetRRBpBfQSTDcv7nyGV2fgpeJy4w--');

# This is your Google Maps geocoder key.
# See http://www.google.com/apis/maps/signup.html
# and http://www.google.com/apis/maps/documentation/#Geocoding_Examples
defined('GEOKIT_GOOGLE_GEOCODER_KEY') ? null : define('GEOKIT_GOOGLE_GEOCODER_KEY', 'ABQIAAAA9T-5Co02vI-wAuH0OnZafhSJUJfdQa_tlOR17_HPwBvEc-EL3BRjWMUu9Cp9k8buRQz5JPqC4CinzA'); # For alan.lakeinfoworks.com
# For www.lakeinfoworks.com: ABQIAAAA9T-5Co02vI-wAuH0OnZafhTpMqP1k1J2NLH4Jz_ft_UBDQ9bbBRUH86Eg4kbzB683ax4MBvSfRTurg

# This is your username and password for geocoder.us.
# To use the free service, the value can be set to nil or false.  For
# usage tied to an account, the value should be set to username:password.
# See http://geocoder.us
# and http://geocoder.us/user/signup
defined('GEOKIT_GEOCODERS_GEOCODER_US') ? null :  define('GEOKIT_GEOCODERS_GEOCODER_US', false);

# This is your authorization key for geocoder.ca.
# To use the free service, the value can be set to nil or false.  For
# usage tied to an account, set the value to the key obtained from
# Geocoder.ca.
# See http://geocoder.ca
# and http://geocoder.ca/?register=1
defined('GEOKIT_GEOCODERS_GEOCODER_CA') ? null : define('GEOKIT_GEOCODERS_GEOCODER_CA', false);

# This is the order in which the geocoders are called in a failover scenario
# If you only want to use a single geocoder, define GEOKIT_PROVIDER_ORDER with one provider.
# Valid strings are 'google', 'yahoo', 'us', and 'ca'.
# Be aware that there are Terms of Use restrictions on how you can use the
# various geocoders.  Make sure you read up on relevant Terms of Use for each
# geocoder you are going to use.
defined('GEOKIT_PROVIDER_ORDER') ? null : define('GEOKIT_PROVIDER_ORDER', 'google,us');
?>


