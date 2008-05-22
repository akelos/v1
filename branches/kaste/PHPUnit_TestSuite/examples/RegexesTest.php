<?php

class RegexesTest extends PHPUnit_Regexes_TestCase
{

    function testMatchAgainstStaticText()
    {
        $this->given('/Hello/')->against('Hello')->matches();
    }
    
    function testMatchAgainstGroupedStaticText()
    {
        $this->given('/Hel(lo)/')->against('Hello')->matches('lo');
    }
    
    function testGivenTextShouldNotMatch()
    {
        $this->given('/Hel(lo)/')->against('World')->doesntMatch();
    }
}
?>