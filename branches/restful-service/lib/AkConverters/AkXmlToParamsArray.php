<?php

class AkXmlToParamsArray
{

    function convert()
    {
        return self::convertToArray($this->source);    
    }
    
    function convertToArray($xml_or_string)
    {
        $xml = is_string($xml_or_string) ? new SimpleXMLElement($xml_or_string) : $xml_or_string;
        return self::parseXml($xml);
    }
    
    static public function parseXml(SimpleXMLElement $xml)
    {
        $properties = array();
        $properties[$xml->getName()] = self::addChildren($xml);
        return $properties;
    }
    
    static private function addChildren(SimpleXMLElement $xml)
    {
        $properties = array();
    
        foreach ($xml as $child){
#echo "{$xml->getName()};";
            if (count($child->children())>0){
#echo $child->getName()."::> ";            
                $children = self::addChildren($child);
                if (self::isCollectionOf($child->getName(),$xml->getName())){
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
    
    static private function isCollectionOf($child_name,$parent_name)
    {
        return AkInflector::pluralize($child_name) == $parent_name;
    }
    
}


?>