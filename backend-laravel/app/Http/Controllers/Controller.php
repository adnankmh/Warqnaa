<?php

namespace App\Http\Controllers;

/**
 * Shared base controller for all HTTP endpoints.
 *
 * Laravel 11 intentionally keeps this class minimal. Its presence is still
 * required because the mobile, legal, account, safety and administration
 * controllers extend it and Composer must be able to autoload that parent.
 */
abstract class Controller
{
}
