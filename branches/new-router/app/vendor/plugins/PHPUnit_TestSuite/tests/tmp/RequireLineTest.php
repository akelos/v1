<?php

class RequireLineTest extends PHPUnit_Framework_TestCase
{

    function testWinDS()
    {
        $given_path = 'd:\Dev\Akelos\vendor\PHPUnit_TestSuite\tests\example.php';
        $this->DS = $DS = '\\';
        $this->pattern = "|(?<=PHPUnit_TestSuite\\$DS".")tests\\$DS.*$|";
        $this->replace = "lib$DS"."PHPUnit_Akelos.php";
        
        $this->assertReplace($given_path,"d:\Dev\Akelos\vendor\PHPUnit_TestSuite\lib$DS"."PHPUnit_Akelos.php");
    }
    
    function testMacDS()
    {
        $given_path = '/Dev/Akelos/vendor/PHPUnit_TestSuite/tests/example.php';
        $this->DS = $DS = '/';
        
        $this->pattern = "|tests\\$DS.*$|";
        $this->replace = "lib$DS"."PHPUnit_Akelos.php";
        
        $this->assertReplace($given_path,"/Dev/Akelos/vendor/PHPUnit_TestSuite/lib$DS"."PHPUnit_Akelos.php");
    }
    
    function assertReplace($given,$result)
    {
        $pattern = $this->pattern;
        $replace = $this->replace;
        $this->assertEquals($result,preg_replace($pattern,$replace,$given));
    }
    
}

?>