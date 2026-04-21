<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// libphonenumber: fallback when Composer autoload omits giggsey packages
require_once __DIR__.'/../app/Helpers/LibPhoneAutoload.php';

// Ensure Sanctum HasApiTokens is loaded (fixes "Trait not found" when autoload order varies)
$sanctumTrait = __DIR__.'/../vendor/laravel/sanctum/src/HasApiTokens.php';
if (file_exists($sanctumTrait)) {
    require_once $sanctumTrait;
}

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

$app->handleRequest(Request::capture());
