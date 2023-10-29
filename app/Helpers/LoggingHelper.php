<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Log;

class LoggingHelper
{
    public static function make($type, $message, $line = null, $file = null)
    {
        Log::$type("===LOG MESSAGE: $message | LOG LINE: $line | LOG FILE: $file===");
    }
}
