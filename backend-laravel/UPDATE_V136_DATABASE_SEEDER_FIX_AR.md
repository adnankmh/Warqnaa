# v136 — إصلاح خطأ Seeder المتبقي

## الخطأ
ظهر الخطأ:

Undefined variable $countryNameTextV135

داخل:
database/seeders/DatabaseSeeder.php

## السبب
في v135 تم استبدال بعض country_name() بمتغير مساعد داخل Seeder، لكن هذا المتغير لم يكن معرّفًا قبل أول استخدام في نسخة الملف المضغوط.

## الإصلاح
تم حذف الاعتماد على:
$countryNameTextV135

واستبداله مباشرة بـ:
country_name($country)

لأن دالة country_name() نفسها أصبحت آمنة وترجع نصًا فقط، وليس Array.

## الملفات المعدلة
- database/seeders/DatabaseSeeder.php
- app/helpers.php
- config/warqna_pro_features.php

## الفحص
- PHP lint: بدون أخطاء.
- JS check: بدون أخطاء.
- PWA/APK readiness: ناجح.
