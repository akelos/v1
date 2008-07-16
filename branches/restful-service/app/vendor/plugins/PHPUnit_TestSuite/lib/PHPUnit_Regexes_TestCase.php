<?php

abstract class PHPUnit_Regexes_TestCase extends PHPUnit_Framework_TestCase 
{
    
    private $given_pattern;
    private $preg_match_result;
    private $resulting_matches;

    /**
     * @param string $pattern
     * @return PHPUnit_Regexes_TestCase
     */
    protected function given($pattern)
    {
        $this->given_pattern = $pattern;
        return $this;
    }
    
    /**
     * @param string $url
     * @return PHPUnit_Regexes_TestCase
     */
    protected function against($url)
    {
        $this->preg_match_result = preg_match($this->given_pattern,$url,$matches);
        $this->resulting_matches = $matches;
        return $this;
    }
    
    /**
     * @return PHPUnit_Regexes_TestCase
     */
    protected function matches()
    {
        if (!$this->preg_match_result) return $this->fail("Expected match, actual no match.");
        
        $expected_matches = func_get_args();
        if (empty($expected_matches)) return $this;
        
        array_shift($this->resulting_matches);
        $this->assertEquals($expected_matches,$this->resulting_matches);
        return $this;
    }
    
    /**
     * @return PHPUnit_Regexes_TestCase
     */
    protected function doesntMatch()
    {
        if ($this->preg_match_result) return $this->fail("Expected no match, actual matched");
        return $this;
    }
    
    protected function andDump()
    {
        var_dump($this->resulting_matches);
    }
    
    
    /**
     * @param string $pattern
     * @param string $text
     * @return array matches
     */
    protected function patternMatches($pattern,$text)
    {
        $result = preg_match($pattern,$text,$matches);
        $this->assertEquals(1,$result,"Expected match, actual no match.");
        return $matches;
    }
    
    /**
     * @param string $pattern
     * @param string $text
     */
    protected function patternDoesNotMatch($pattern,$text)
    {
        $result = preg_match($pattern,$text,$matches);
        $this->assertEquals(0,$result,"Expected no match, actual matched.");
    }
     
    
}

?>