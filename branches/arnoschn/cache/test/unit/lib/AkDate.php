<?php
require_once(AK_LIB_DIR.DS.'AkDate.php');

class Test_AkDate extends AkUnitTest
{
    function test_constructor_default()
    {
        $now = time();
        $date = new AkDate();
        $this->assertTrue($now+1>=$date->toTimestamp());
        $this->assertTrue($now-1<=$date->toTimestamp());
    }
    
    function test_constructor_custom()
    {
        $now = time();
        $date = new AkDate('1 year from now');
        $oneyear = 365*24*60*60;
        $this->assertTrue($now+1+$oneyear>=$date->toTimestamp());
        $this->assertTrue($now-1+$oneyear<=$date->toTimestamp());
    }
    
}
ak_test('Test_AkDate');