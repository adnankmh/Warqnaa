# Warqna v89 Blade Creation Hotfix

- إصلاح خطأ `room/show.blade.php` الناتج عن شرط Blade غير صحيح داخل ملف مقعد اللاعب.
- إعادة كتابة `resources/views/room/seat.blade.php` بصياغة Blade نظيفة ومنظمة.
- إصلاح خطأ `tournaments/index.blade.php` الناتج عن تركيب `@foreach/@forelse` مضغوط في نفس السطر.
- إعادة كتابة صفحة المسابقات ببنية واضحة وفتح/إغلاق صحيح لكل أوامر Blade.
- فحص توازن أوامر Blade الأساسية في كل ملفات `resources/views`.
- فحص ملفات PHP الأساسية عبر `php -l`.
- فحص ملف JavaScript الأساسي عبر `node --check`.

## ملاحظة تشغيل
بعد فك الضغط، شغّل بالترتيب:

```bat
setup-windows.bat
reset-database-windows.bat
start-windows.bat
```

ثم افتح الموقع على:

```text
http://127.0.0.1:8000
```
