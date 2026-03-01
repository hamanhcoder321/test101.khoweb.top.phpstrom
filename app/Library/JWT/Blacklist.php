<?php
namespace App\Library\JWT;

class Blacklist
{
    protected static $tokens = [];

    public static function add($token)
    {
        self::$tokens[$token] = true;
    }

    public static function has($token)
    {
        return isset(self::$tokens[$token]);
    }
}
