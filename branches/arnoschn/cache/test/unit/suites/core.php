<?php
require_once(AK_LIB_DIR.DS.'AkUnitTestSuite.php');
    
class CoreTestSuite extends AkUnitTestSuite {
    var $partial_tests = array(
        'Ak',
        'AkUnitTest',
        'AkSession',
        'AkRouter',
        'AkLocaleManager',
        'AkInstaller',
        'AkInflector',
        'AkImage',
        'AkHttpClient',
        'AkDbSession',
        
        );
    var $baseDir = '';
    var $title = 'Core Tests';
}
?>