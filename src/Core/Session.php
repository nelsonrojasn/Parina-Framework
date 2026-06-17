<?php

namespace Parina\Core;

class Session
{
    public static function start():void
    {
        session_start();
        session_regenerate_id(true);
    }

    public static function get(string $key): mixed { return $_SESSION[$key] ?? null; }
    public static function set(string $key, mixed $value):void { $_SESSION[$key] = $value; }
    public static function clear():void { session_destroy(); }
}
