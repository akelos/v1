<?php
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'AkActionViewHelper.php');
require_once(AK_LIB_DIR.DS.'AkActionView'.DS.'helpers'.DS.'date_helper.php');
class AkDate extends AkObject
{
    var $timestamp;
    function __construct($dateString = 'now')
    {
        $this->timestamp = AkDate::parse($dateString);
        
    }
    function set($dateString)
    {
        $this->timestamp = AkDate::parse($dateString);
    }
    
    function parse($string)
    {
        
        $currentTime = time();
        
        $separator = '\.';
        $string = trim($string);
        if (strstr($string,' ')) {
            $separator = '\s+';
        }
        $parts = preg_split("|".$separator."|", $string);
        if (preg_match('/\d+/',$parts[0])) {
            $value = array_shift($parts);
        } else {
            $value = 0;
        }
        $unit = @array_shift($parts);
        $direction1 = @array_shift($parts);
        $direction2 = @array_shift($parts);
        if (!isset($direction1)) {
            $direction1='from';
            $direction2='now';
        } else if (!isset($direction2)) {
            $direction2='now';
        }
        
        switch ($direction2) {
            case 'now':
                $destinationTime = $currentTime;
                break;
            case 'yesterday':
                $destinationTime = $currentTime - (60 * 60 * 24);
                break;
            case 'tomorrow':
                $destinationTime = $currentTime + (60 * 60 * 24);
                break;
        }
        
        $value = AkDate::_parseDatePortion($value.' '.$unit);
        
        if ($direction1 == 'ago') {
            $destinationTime -= $value;
        } else if ($direction1 == 'from') {
            $destinationTime += $value;
        }
        
        return $destinationTime;

    }
    function toString($format = null)
    {
        if ($format == null) {
            $format = Ak::locale('date_time_format');
        }
        return date($format,$this->timestamp);
    }
    
    function add($dateString)
    {
        $addValue = AkDate::_parseDatePortion($dateString);
        $this->timestamp+=$addValue;
    }
    function substract($dateString)
    {
        $addValue = AkDate::_parseDatePortion($dateString);
        $this->timestamp-=$addValue;
    }
    
    function getDistanceInSeconds(&$obj)
    {
        if (is_a($object,'AkDate')) {
            $diff = $this->timestamp - $object->toTimestamp();
        }
        return $diff;
    }
    function getDistanceInWords(&$obj)
    {
        if (is_a($object,'AkDate')) {
            $time = $object->toTimestamp();
        } else {
            $time = $obj;
        }
        return DateHelper::distance_of_time_in_words($this->timestamp,$time);
    }
    function toTimestamp()
    {
        return $this->timestamp;
    }
    function toDistanceFromNowInWords()
    {
        return DateHelper::distance_of_time_in_words_to_now($this->timestamp);
    }
    
    function toWords()
    {
        return $this->toDistanceFromNowInWords();
    }
    function _parseDatePortion($string)
    {
        $separator = '\.';
        if(strstr($string,' ')) {
            $separator = '\s+';
        }
        @list($value,$unit) = preg_split('|'.$separator.'|',$string);
        switch($unit) {
            case 'seconds':
                $value = $value * 1;
                break;
            case 'minutes':
                $value = $value * 60;
                break;
            case 'hours':
                $value = $value * 60 * 60;
                break;
            case 'day':
            case 'days':
                $value = $value * 60 * 60 * 24;
                break;
            case 'week':
            case 'weeks':
                $value = $value * 60 * 60 * 24 * 7;
                break;
            case 'month':
            case 'months':
                $value = $value * 60 * 60 * 24 * 30;
                break;
            case 'year':
            case 'years':
                $value = $value * 60 * 60 * 24 * 365;
                break;
        }
        
        return $value;
    }
    
    function render($string, $format = null)
    {
        $timestamp = AkDate::parse($string);
        if ($format == null) {
            $format = Ak::locale('date_time_format');
        }
        return date($format, $timestamp);
    }

}
?>