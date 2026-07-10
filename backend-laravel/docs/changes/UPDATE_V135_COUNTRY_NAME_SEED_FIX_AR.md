# v135 — إصلاح خطأ Seeder الخاص بالدول

## المشكلة
ظهر الخطأ أثناء تشغيل seed:

Array to string conversion

والسبب أن:
config/countries.php

صار يرجع بيانات الدولة كمصفوفة:
- ar
- en
- flag

بينما دالة:
country_name()

كانت تعيد المصفوفة كاملة بدل نص، فحاول Laravel حفظ مصفوفة داخل عمود country_name النصي.

## الإصلاح
تم تعديل:
app/helpers.php

دالة country_name الآن ترجع نصًا فقط:
- الاسم العربي افتراضيًا.
- ويمكن تمرير en عند الحاجة.
- لم تعد ترجع array.

أضيفت دالة:
country_label()

للعرض الكامل:
العلم + العربي + الإنجليزي

## حماية إضافية
تم تعديل:
database/seeders/DatabaseSeeder.php

وإضافة حماية:
$countryNameTextV135

حتى لو رجع أي مصدر مصفوفة، يتم تحويلها إلى نص قبل الحفظ في قاعدة البيانات.

## إصلاح صفحة التسجيل
تم تعديل:
resources/views/auth/register.blade.php

لأن config('countries') أصبح يحتوي مصفوفات، فصار عرض الدولة يدعم:
العلم + الاسم العربي + الاسم الإنجليزي.

## الفحص
- PHP lint: بدون أخطاء.
- JS check: بدون أخطاء.
- PWA/APK readiness: ناجح.
