# Warqna Laravel Platform v58 Pro

نسخة Laravel جاهزة للتشغيل محليًا، مبنية على v40 ومتكاملة مع تفاصيل v38 والتحديثات اللاحقة حتى v58.

## بيانات الدخول الافتراضية

- Email: `adnanasd63@gmail.com`
- Password: `Adnan123`

## التشغيل السريع على Windows / XAMPP

1. فك الضغط داخل:
   `C:\xampp\htdocs\warqna-laravel-platform-v58-pro`
2. افتح XAMPP وشغّل Apache.
3. افتح CMD داخل مجلد المشروع.
4. شغّل:
   ```bat
   setup-windows.bat
   ```
5. بعد انتهاء التجهيز شغّل:
   ```bat
   start-windows.bat
   ```
6. افتح المتصفح على:
   `http://127.0.0.1:8000`

## التشغيل اليدوي عند الحاجة

```bash
composer install
npm install
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve
```

وفي نافذة أخرى:

```bash
npm run socket
```

Socket server يعمل على `http://localhost:4000`.

## أهم ما في v58

- إلغاء زر إضافة بوت؛ عند بدء اللعبة يتم ملء المقاعد الفارغة ببوتات تلقائيًا.
- طاولة مربعة احترافية، زر معاينة الطاولة، وأيقونة الطاولة مستطيلة وليست دائرية.
- ترتيب الشركاء في الألعاب الجماعية بحيث يكون الشركاء مقابل بعض.
- ورق أكبر وأجمل بتأثير 3D ودعم ظهر الورق المفعل من المتجر.
- أزرار الطلب في الطرنيب من 7 إلى 13 مع زر تمرير واحد فقط بالعربية / Pass للغات الأخرى.
- الهاند والبناكل والكونكان: اختيار أوراق، تبديل ترتيب بالسحب، وتجميع مجموعة محددة.
- نظام تأكيد احترافي بدل Popups أعلى الصفحة.
- صفحة بروفايل مستقلة في القائمة العليا تشمل الإحصائيات، المشتريات، المعاينة، والأصدقاء.
- مودال البروفايل داخل الغرفة يعرض تفاصيل اللاعب فقط، ولا يظهر قوائم إضافية مزعجة.
- الدردشة لها زر X للإغلاق وزر عائم لإعادتها، مع لون كتابة اللاعب المفعل.
- المتجر مقسم إلى تبويبات منفصلة: باشا، طاولات، ألوان، ظهر الورق، إطارات، مؤثرات، إيموجي، ومشترياتي.
- عناصر متجر جديدة وأسعار أعلى: طاولات بمستويات، ألوان فاخرة، ظهر ورق، إطارات، وإيموجي متحرك.
- تحسينات SEO أساسية في `<head>`.

## ملاحظات مهمة

- المشروع يستخدم SQLite افتراضيًا لتسهيل التشغيل المحلي.
- إذا أردت MySQL غيّر إعدادات `.env` ثم شغّل `php artisan migrate:fresh --seed`.
- عند فتح اللعبة يجب تشغيل Laravel وSocket معًا للحصول على الدردشة اللحظية.
- القواعد مكتوبة بصياغة أصلية مستوحاة من قواعد ألعاب الورق الشائعة؛ لم يتم نسخ أصول أو نصوص مملوكة لمنصات أخرى.


## v68 Composer note
This package disables Composer audit blocking for local development setup because Composer may stop installation when no composer.lock exists. Run `composer audit` and update packages before production deployment.


## v79 Stabilized Pro

إذا ظهر خطأ Composer بسبب `--no-audit`، استخدم هذه النسخة لأن ملف `setup-windows.bat` تم تعديله لاستخدام `--no-security-blocking`.

## v84 Pro
- لوحة الإدارة تحتوي الآن على تحكم عام بالموقع والثيمات والمتجر بدون تعديل أكواد.
- لإدارة المقتنيات: افتح الإدارة > إدارة المتجر.
- لإدارة الثيم العام: افتح الإدارة > تحكم الموقع.


## v86 Docs Applied Pro
This version applies the uploaded instruction documents for remaining store, tournament, club, translation, theme, profile, chat, and admin-control requirements. See `docs/changes/V86_DOCS_APPLIED_CHANGES.md`.

## ملاحظة v96 للصوت الحي
اللعبة الصوتية تخصم 100 توكنز من كل لاعب ينضم. لاستخدام الصوت بين أجهزة مختلفة شغّل خادم Socket.IO بجانب Laravel:

```bash
npm install
npm run socket
```

Laravel يعمل على البورت 8000، وخادم الصوت/الإشارات يعمل على البورت 4000.
