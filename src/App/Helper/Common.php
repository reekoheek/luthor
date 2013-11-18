<?php

namespace App\Helper;

class Common {
    public static function secsToV($secs) {
        $units = array(
            "weeks"   => 7*24*3600,
            "days"    =>   24*3600,
            "hours"   =>      3600,
            "minutes" =>        60,
            "seconds" =>         1,
        );

        foreach ( $units as &$unit ) {
            $quot  = intval($secs / $unit);
            $secs -= $quot * $unit;
            $unit  = $quot;
        }

        return $units;
    }
}