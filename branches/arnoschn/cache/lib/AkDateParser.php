<?php

class AkDateParser extends AkObject
{
    /** static */ function parse($expression, $format = 'Y-m-d H:i:s', $timestamp = false)
    {
        @list($value,$unit,$pot1,$pot2) = split('\.',$expression);
        /**
         * parses:
         * 
         * 5.days.ago
         * 5.days.from.now
         */
        $currentTime = time();
        $destinationTime = $currentTime;
        if (!isset($pot1)) {
            $pot1='from';
            $pot2='now';
        }
        
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
        
        if ($pot1 == 'ago') {
            $destinationTime -= $value;
        } else if ($pot1 == 'from') {
            $destinationTime += $value;
        }
        
        if ($timestamp) {
            return $destinationTime;
        } else {
            $destinationDate = date($format, $destinationTime);
            return $destinationDate;
        }
        
    }
}