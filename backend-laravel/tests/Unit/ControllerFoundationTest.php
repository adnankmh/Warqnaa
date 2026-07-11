<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use PHPUnit\Framework\TestCase;

final class ControllerFoundationTest extends TestCase
{
    public function test_every_controller_that_uses_the_shared_parent_is_autoloadable(): void
    {
        self::assertTrue(class_exists(Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileAdminController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileApiController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileAuthRecoveryController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileGameController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileModerationController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobilePlatformController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileSafetyController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileSocialController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileVoiceController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobilePushController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\MobileAccountController::class, Controller::class));
        self::assertTrue(is_subclass_of(\App\Http\Controllers\LegalPageController::class, Controller::class));
    }
}
