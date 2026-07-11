<?php

declare(strict_types=1);

$autoload = dirname(__DIR__).'/vendor/autoload.php';
if (!is_file($autoload)) {
    fwrite(STDERR, "vendor/autoload.php is missing. Run composer install first.\n");
    exit(2);
}
require $autoload;

$base = \App\Http\Controllers\Controller::class;
$controllers = [
    \App\Http\Controllers\MobileAdminController::class,
    \App\Http\Controllers\MobileApiController::class,
    \App\Http\Controllers\MobileAuthRecoveryController::class,
    \App\Http\Controllers\MobileGameController::class,
    \App\Http\Controllers\MobileModerationController::class,
    \App\Http\Controllers\MobilePlatformController::class,
    \App\Http\Controllers\MobileSafetyController::class,
    \App\Http\Controllers\MobileSocialController::class,
    \App\Http\Controllers\MobileVoiceController::class,
    \App\Http\Controllers\MobilePushController::class,
    \App\Http\Controllers\MobileAccountController::class,
    \App\Http\Controllers\LegalPageController::class,
];

if (!class_exists($base)) {
    fwrite(STDERR, "Missing Laravel base controller: {$base}\n");
    exit(1);
}
foreach ($controllers as $controller) {
    if (!class_exists($controller)) {
        fwrite(STDERR, "Controller cannot be autoloaded: {$controller}\n");
        exit(1);
    }
    if (!is_subclass_of($controller, $base)) {
        fwrite(STDERR, "Controller does not extend {$base}: {$controller}\n");
        exit(1);
    }
}

echo "HTTP controller foundation OK (".count($controllers)." controllers checked).\n";
