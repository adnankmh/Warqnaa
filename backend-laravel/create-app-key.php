<?php
$file = __DIR__ . DIRECTORY_SEPARATOR . '.env';
if (!file_exists($file)) {
    copy(__DIR__ . DIRECTORY_SEPARATOR . '.env.example', $file);
}
$content = file_get_contents($file);
if (!preg_match('/^APP_KEY=base64:.+/m', $content)) {
    $key = 'base64:' . base64_encode(random_bytes(32));
    if (preg_match('/^APP_KEY=.*/m', $content)) {
        $content = preg_replace('/^APP_KEY=.*/m', 'APP_KEY=' . $key, $content);
    } else {
        $content .= PHP_EOL . 'APP_KEY=' . $key . PHP_EOL;
    }
    file_put_contents($file, $content);
    echo "APP_KEY created" . PHP_EOL;
} else {
    echo "APP_KEY already exists" . PHP_EOL;
}
