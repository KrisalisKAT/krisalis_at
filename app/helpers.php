<?php

use Illuminate\Support\Carbon;

if (! function_exists('dateOrNull')) {
    function dateOrNull($date): ?Carbon {
        return $date ? Carbon::parse($date) : null;
    }
}
