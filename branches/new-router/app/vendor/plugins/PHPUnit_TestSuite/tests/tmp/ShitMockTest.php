<?php

class WeDo
{

    function something()
    {
        
    }
}

class ShitMockTest extends PHPUnit_Framework_TestCase 
{

    var $Mock;
    function testOk()
    {
        $WeDo = $this->getMock('WeDo',array('something'));
        $WeDo->expects($this->once())->method('something')->with('what');
        $WeDo->something('what');
    }

    function testOops()
    {
        $WeDo = $this->getMock('WeDo',array('something'));
        $WeDo->expects($this->once())->method('something')->with(array('text'=>'Hello.'));
        $WeDo->something(array('text'=>'Hello.'));
        $WeDo->something(array('text'=>'Hellso.'));
    }
}

?>