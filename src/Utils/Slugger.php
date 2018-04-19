<?php
/**
 * Created by PhpStorm.
 * User: evita.sivakova
 * Date: 3/14/2018
 * Time: 3:59 PM
 */

namespace App\Utils;


class Slugger
{
    public static function slugify(string $string): string
    {
        $patterns = ['/\x2C+/', '/\x26+/', '/\x27+/'];
        $string = preg_replace($patterns, '', mb_strtolower(trim(strip_tags($string)), 'UTF-8'));
        return preg_replace('/\s+/', '-', $string);
    }
}
