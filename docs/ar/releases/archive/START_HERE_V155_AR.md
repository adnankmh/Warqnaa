# ابدأ من هنا — Warqna v155

هذه حزمة كاملة وليست ملفات ترقيع منفصلة. تحتوي على Flutter وLaravel وGitHub Actions مع الاحتفاظ بجميع ميزات v154.

## الإصلاحات الأساسية

- إضافة `"license": "proprietary"` إلى `backend-laravel/composer.json` حتى لا يحوّل Composer تحذير الترخيص إلى فشل عند استخدام `--strict`.
- إنشاء ملف `.env` مؤقت داخل GitHub Actions من `.env.production.example` قبل `docker compose config`.
- استبدال القيم الحساسة الوهمية بقيم CI مؤقتة ثم حذف `.env` دائمًا بعد الفحص، حتى عند فشل خطوة البناء.
- إضافة فحوص تمنع رجوع الخطأين في الإصدارات التالية.
- توحيد الإصدار الحالي إلى `1.55.0+155`.
- إضافة Migration لتسجيل v155 مع الحفاظ على Migration v154 التاريخي.

## الرفع الصحيح إلى GitHub

اتبع الملف:

`GITHUB_UPLOAD_V155_AR.md`

## تشغيل Laravel محليًا

من داخل `backend-laravel`:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8006
```

على Windows:

```bat
backend-laravel\setup-windows.bat
backend-laravel\start-windows.bat
```

## تشغيل Flutter محليًا

```bash
cd flutter_app
flutter pub get
flutter run -d chrome \
  --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1 \
  --dart-define=WARQNA_PRODUCTION_MODE=false \
  --dart-define=WARQNA_APP_VERSION=1.55.0 \
  --dart-define=WARQNA_APP_BUILD=155
```

أو على Windows:

```bat
flutter_app\RUN_FLUTTER_WEB.bat
```

## فحص الحزمة

```bat
CHECK_V155_WINDOWS.bat
```

أو:

```bash
./check-v155.sh
```

لا تضع ملف `.env` الحقيقي أو مفاتيح Android/iOS أو أسرار AdMob وTURN داخل GitHub.
