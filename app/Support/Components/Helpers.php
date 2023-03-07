<?php

if (!function_exists('sentry_log')) {
    function sentry_log($message)
    {
        if (app()->bound('sentry')) {
            app('sentry')->captureMessage($message);
        }
    }
}