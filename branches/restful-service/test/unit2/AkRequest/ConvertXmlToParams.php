<?php

class ConvertXmlToParams extends PHPUnit_Framework_TestCase
{
    
    function testXmlToArray()
    {
        $data = '<person><name>Steve</name><age>21</age></person>';
        
        $expected = array(
            'person'=>array('name'=>'Steve','age'=>'21')
        );
        $this->assertEquals($expected,$this->parseXml($data));
        #var_dump($this->parseXml($data));
    }

    function testXmlToArray2()
    {
        $data = '
        <people>
            <person><name>Steve</name><age>21</age></person>
            <person><name>Mart</name><age>21</age></person>
        </people>';
        
        $expected = array(
            'people'=>array(
                0=>array(
                    'name'=>'Steve',
                    'age'=>'21'),
                1=>array(
                    'name'=>'Mart',
                    'age'=>'21')
            )
        );
        #var_dump($this->parseXml($data));
        $this->assertEquals($expected,$this->parseXml($data));
    }

    function testXmlToArray3()
    {
        $data = '
        <people>
            <person>
                <name>Steve</name>
                <comments>
                    <comment>
                        <title>No1</title>
                    </comment>
                    <comment>
                        <title>No2</title>
                    </comment>
                </comments>
            </person>
            <person>
                <name>Mart</name>
                <comments>
                    <comment>
                        <title>No3</title>
                    </comment>
                    <comment>
                        <title>No4</title>
                    </comment>
                </comments>
            </person>
        </people>';
        
        $expected = array(
            'people'=>array(
                0=>array(
                    'name'=>'Steve',
                    'comments'=>array(
                        0=>array('title'=>'No1'),
                        1=>array('title'=>'No2'),
                    )
                ),
                1=>array(
                    'name'=>'Mart',
                    'comments'=>array(
                        0=>array('title'=>'No3'),
                        1=>array('title'=>'No4'),
                    )
                ),
            )
        );
        #var_dump($this->parseXml($data));
        $as_array = $this->parseXml($data);
        $this->assertEquals($expected,$as_array);
        $this->assertEquals('No2',$as_array['people'][0]['comments'][1]['title']);
    }
    
    function testXmlToArray4()
    {
        $data ='
        <person>
            <name>Steve</name>
            <details>
                <age>21</age>
            </details>
        </person>
        ';
        
        $expected = array(
            'person'=>array(
                'name'=>'Steve',
                'details'=>array(
                    'age'=>21))
        );
        $as_array = $this->parseXml($data);
        #var_dump($as_array);
        $this->assertEquals($expected,$as_array);
    }
    
    function testXmlToArray5()
    {
        $data ='
        <person>
            <name>Steve</name>
            <photos>
                <photo>
                    <title>One</title>
                </photo>
                <photo>
                    <title>Two</title>
                </photo>
            </photos>
            <age>21</age>
        </person>
        ';
        
        $expected = array(
            'person'=>array(
                'name'=>'Steve',
                'photos'=>array(
                    0=>array('title'=>'One'),
                    1=>array('title'=>'Two')),
                'age'=>'21')
            );
        $as_array = $this->parseXml($data);
        #var_dump($as_array);
        $this->assertEquals($expected,$as_array);
        
    }
    
    function parseXml($xml_string)
    {
        $xml = new SimpleXMLElement($xml_string);
        
        $properties = array();
        $properties[$xml->getName()] = $this->addChildren($xml);
        return $properties;
    }
    
    private function addChildren(SimpleXMLElement $xml)
    {
        $properties = array();
    
        foreach ($xml as $child){
#echo "{$xml->getName()};";
            if (count($child->children())>0){
#echo $child->getName()."::> ";            
                $children = $this->addChildren($child);
                if ($this->isCollectionOf($child->getName(),$xml->getName())){
#echo "[[[{$xml->getName()};{$child->getName()}]]].";
                    $properties[]= $children;
                }else{
                    $properties[$child->getName()] = $children;
                }
            }else{
#echo " {$child->getName()}<-->$child";
                $properties[$child->getName()] = (string)$child;
            }
#echo "\n\r";            
        }
        return $properties;
    }
    
    private function isCollectionOf($child_name,$parent_name)
    {
        return AkInflector::pluralize($child_name) == $parent_name;
    }
    
}

?>