<?php

error_reporting(E_ALL);
defined('DS') ? null : define('DS', DIRECTORY_SEPARATOR);
require_once(dirname(__FILE__).str_repeat(DS.'..', 5).DS.'test'.DS.'fixtures'.DS.'config'.DS.'config.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'AkActionViewHelper.php');

require_once(AK_LIB_DIR.DS.'AkInstaller.php');


!defined('AK_AKISMET_API_KEY') && define('AK_AKISMET_API_KEY', (!empty($argv[1])?$argv[1]:AkInstaller::promptUserVar('Akismet API key')));
!defined('AK_AKISMET_SITE_URL') && define('AK_AKISMET_SITE_URL', (!empty($argv[2])?$argv[2]:AkInstaller::promptUserVar('Akismet site URL')));

require_once(dirname(__FILE__).DS.'..'.DS.'lib'.DS.'akismet_helper.php');

class AkismetHelperTestCase extends AkUnitTest
{
    function test_setup()
    {
        $this->akismet_helper =& new AkismetHelper();
    }

    function test_should_verify_key()
    {
        $this->assertTrue($this->akismet_helper->verifyKey());
    }

    function test_should_detect_spam()
    {
        $this->assertTrue($this->akismet_helper->isSpam(
        'this author triggers known spam' , array(
        'author' => 'viagra-test-123',
        'user_agent' => 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8.1) Gecko/20061010 Firefox/2.0',
        )));
    }

    function test_should_approve_harmeless_comment()
    {
        $this->assertFalse($this->akismet_helper->isSpam(
        'this author triggers known spam' , array(
        'author' => 'Hi, I really like your blog.',
        'user_agent' => 'Mozilla/5.0 (Macintosh; U; PPC Mac OS X Mach-O; en-US; rv:1.8.1) Gecko/20061010 Firefox/2.0',
        )));
    }
}

ak_test('AkismetHelperTestCase');

?>