# تثبيت Warqnaa V0.3.1

## من الملف الكامل

فك `Warqnaa-V0.3.1-FULL.zip` في مجلد جديد.

## من الجزأين

فك `Warqnaa-V0.3.1-PART-1.zip` و`Warqnaa-V0.3.1-PART-2.zip` داخل المجلد نفسه. الجزآن متكاملان، ولا يحتوي أحدهما المشروع كاملًا منفردًا.

## فحص المصدر

- Windows: `CHECK_WARQNA_WINDOWS.bat`
- Linux/macOS: `bash scripts/unix/current/check-v182.sh`

## Laravel

1. انسخ `.env.example` إلى `.env`.
2. اضبط قاعدة البيانات والرابط العام.
3. نفذ `composer install` و`php artisan key:generate` و`php artisan migrate --seed`.
4. شغّل `php artisan test` قبل النشر.

## Flutter

من `flutter_app` نفّذ `flutter pub get` ثم `flutter analyze` و`flutter test`، وبعدها ابنِ المنصة المطلوبة. اضبط `WARQNA_API_URL` على خادم Laravel الحقيقي.
