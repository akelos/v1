<?php

class SimpleFixturesForModelsTest extends PHPUnit_Model_TestCase 
{

    function testCreateDefaultArtist()
    {
        list($Artist,) = $this->useModel('Artist=>id,name');
        
        $Supertramp = $this->createArtist('name: Supertramp');
        $this->assertEquals('Supertramp',$Artist->find('first')->name);
        
        $Default = $this->createArtist();
        $this->assertEquals('Dominique',$Artist->find($Default->id)->name);
    }
    
    /**
     * will be merged with the other data from a createXX-call
     */
    function defaultArtist()
    {
        return array('name'=>'Dominique');
    }
    
    function testSplitStringIntoArray()
    {
        $this->assertParsed('a:amen,b:bmen',    array('a'=>'amen','b'=>'bmen'));
        $this->assertParsed('a: amen, b: bmen', array('a'=>'amen','b'=>'bmen'));
        $this->assertParsed('a: amen,b: b\,men',array('a'=>'amen','b'=>'b,men'));
        $this->assertParsed('a:am:en,b:b\,men', array('a'=>'am:en','b'=>'b,men'));
        $this->assertParsed('a: amen,b: 5',     array('a'=>'amen','b'=>5));
        $this->assertParsed('a: amen',          array('a'=>'amen'));
        $this->assertParsed('',                 array());
    }
    
    function assertParsed($string_to_parse,$parsed_array)
    {
        return $this->assertEquals($parsed_array,$this->splitIntoDataArray($string_to_parse));
    }
}

?>