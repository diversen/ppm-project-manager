<?php

namespace Pebble;

class Substring
{
    public static function get($str, $length, $minword = 3, $use_dots = true){
        $sub = '';
        $len = 0;
        foreach (explode(' ', $str) as $word) {
            $part = (($sub != '') ? ' ' : '') . $word;
            $sub .= $part;
            $len += self::strlen($part);
            if (self::strlen($word) > $minword && self::strlen($sub) >= $length) {
                break;
            }
        }
        if ($use_dots) {
            return $sub . (($len < self::strlen($str)) ? '...' : '');
        }
        return $sub;
    }

    private static function strlen ($str) {
        return mb_strlen($str, 'UTF-8');
    }
}

