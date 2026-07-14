# دليل تثبيت وتشغيل Warqnaa V0.3

## 1. دمج الحزمة

فك الجزأين داخل مجلد واحد. بعد الدمج يجب أن تظهر المجلدات التالية في الجذر:

- `flutter_app`
- `backend-laravel`
- `.github`
- `tools`

يمكن التحقق من الملفات بواسطة `releases/manifests/current/SOURCE_MANIFEST_V0.3.sha256` بعد إنشاء الحزمة النهائية.

## 2. تشغيل Flutter Web محليًا

يتطلب Flutter 3.44.0 أو إصدارًا متوافقًا مع القيود في `pubspec.yaml`.

```bash
cd flutter_app
flutter pub get
flutter analyze
flutter test
flutter run -d chrome --dart-define=WARQNA_PRODUCTION_MODE=false
```

للربط بالخادم:

```bash
flutter run -d chrome \
  --dart-define=WARQNA_API_URL=https://your-api.example \
  --dart-define=WARQNA_PRODUCTION_MODE=true
```

## 3. تشغيل Laravel محليًا

يتطلب PHP حديثًا، Composer، وإضافات PHP المعتادة لـ Laravel.

```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan optimize:clear
php artisan serve --host=127.0.0.1 --port=8006
```

تأكد من ضبط قاعدة البيانات، `APP_URL`، `FRONTEND_URL`، أصول CORS، وإعدادات البريد والكاش والجلسات حسب بيئة النشر.

## 4. بناء Android

الطريقة الموصى بها هي GitHub Actions لأن سير العمل ينشئ منصة Android النظيفة، يضبط SDK 36، يطبق الهوية، يشغّل التحليل والاختبارات، ثم يبني APK وAAB.

للبناء المحلي بعد تثبيت Flutter وAndroid SDK:

```bash
cd flutter_app
flutter create . --platforms=android --project-name warqna_mobile --org com.warqna --no-pub
python3 ../tools/apply_brand_assets.py
flutter pub get
flutter analyze
flutter test
flutter build apk --release
```

ملف AAB الموقّع للإنتاج يحتاج أسرار التوقيع في GitHub، كما هو موضح داخل `.github/workflows/flutter-android.yml`.

## 5. بناء iOS

يتطلب macOS وXcode وحساب Apple Developer للتوقيع والنشر. سير عمل iOS الموجود يفحص المصدر ويبني النسخة غير الموقعة عند توفر البيئة المناسبة.

## 6. الإعلانات

- الويب يملك وضع معاينة مكافأة محليًا للاختبار.
- Android/iOS يستخدمان معرّفات Google الاختبارية افتراضيًا.
- قبل النشر التجاري، أضف معرّفات AdMob الحقيقية في متغيرات GitHub المشار إليها في ملفات سير العمل.

## 7. الحسابات التجريبية

راجع `docs/DEMO_ACCOUNTS_V0.3_AR.md`. الحسابات مخصصة للاختبار فقط، ويجب تغيير كلمات المرور وإزالة أو تعطيل الحسابات التجريبية قبل إطلاق الإنتاج العام.
