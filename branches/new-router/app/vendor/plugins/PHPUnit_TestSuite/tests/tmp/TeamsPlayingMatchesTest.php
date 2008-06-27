<?php

class _Team extends ActiveRecord 
{
    
}

class Match extends ActiveRecord
{
    var $belongs_to = array(
        'home_team' => array('class_name'=>'Team','primary_key_name'=>'home_team_id'),
        'away_team' => array('class_name'=>'Team','primary_key_name'=>'away_team_id')
    );    
}

class TeamsPlayingMatchesTest extends PHPUnit_Model_TestCase
{

    function setUp()
    {
        list($Team)  = $this->useModel('Team=>id,name');
        list($Match) = $this->useModel('Match=>id,home_team_id,away_team_id');
        
        $Real  = $this->createTeam('name: Real Madrid');
        $Barca = $this->createTeam('name: Barcelona');
        #$Barca = $this->as('Barca')->createTeam('name: Barcelona');
        $this->createMatch("home_team_id: {$Real->id}, away_team_id: {$Barca->id}");
    }
    
    function testAssocitaionSetup()
    {
        
    }
    
    function test()
    {
        $Match = $this->Match->find('first',array('include'=>array('home_team','away_team')));
        
        $this->assertType('Team',$Match->home_team);
        $this->assertType('Team',$Match->away_team);
        $this->assertEquals('Real Madrid',$Match->home_team->name);
        $this->assertEquals('Barcelona',$Match->away_team->name);
        
    }
}

?>