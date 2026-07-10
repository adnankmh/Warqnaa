# ابدأ من هنا — Warqna v147

هذه النسخة تجمع تطبيق Flutter للويب وAndroid وiOS مع Backend Laravel.

## تجربة GitHub Pages

1. ارفع محتويات الحزمة إلى جذر مستودع GitHub.
2. من `Settings > Pages` اختر `GitHub Actions`.
3. شغّل Workflow: `Build and deploy Flutter Web`.
4. افتح الرابط: `https://USERNAME.github.io/REPOSITORY/`.
5. على Android اختر **تثبيت التطبيق**، وعلى iPhone افتح Safari ثم **إضافة إلى الشاشة الرئيسية**.

نسخة GitHub Pages تعمل بمحركات محلية للتجربة. الميزات المتزامنة بين أجهزة مختلفة، قاعدة البيانات، حذف الحسابات، المحفظة المركزية، والدردشة الحقيقية تحتاج نشر مجلد `backend-laravel` على خادم HTTPS.

## تشغيل Laravel محلياً

```bash
cd backend-laravel
composer install
copy .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8006
```

ثم شغّل Flutter Web مع:

```bash
cd flutter_app
flutter pub get
flutter run -d chrome --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1
```

## حساب المدير

- المستخدم: `Adnan`
- كلمة المرور: `Adnan123`
- المستوى: 90 على الأقل
- الباشا: 1000 يوم
- الرصيد: 1000000000000000000 توكن

## حسابات التجربة المحلية

- `Kareem / Kareem123`
- `Rami / Rami12345`
- `Lina / Lina12345`
- `Samar / Samar12345`
- `Layla / Layla12345`
- `Jameel / Jameel12345`
- `Nour / Nour12345`

## حذف الحسابات غير النشطة

Laravel يحتوي أمر:

```bash
php artisan warqna:purge-inactive-accounts --days=30
```

وهو مجدول يومياً في `routes/console.php`. على الاستضافة يجب تشغيل Laravel Scheduler عبر Cron كل دقيقة:

```bash
php artisan schedule:run
```

## بناء Android وiOS

- Android: شغّل `Build Android APK and AAB`.
- iOS: شغّل `Build iOS unsigned`، والنشر النهائي يحتاج توقيع Apple.
- متغيرات GitHub الاختيارية موضحة في `ADS_SETUP_V147_AR.md`.
