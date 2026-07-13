# ابدأ من هنا — Warqna V0.3 (البناء 179)

هذا إصدار كامل يجمع ميزات ورقنا السابقة مع تحديثات V0.3: التوزيع المتوازن، طريق التحدي، الغياب والعودة، مكافآت المستويات، صور البوتات الجديدة، الإعدادات السريعة الدائمة، والمصمم الشامل الخاص بحساب Adnan.

## التشغيل المحلي

### Laravel
```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8006
```

### Flutter
```bash
cd flutter_app
flutter pub get
flutter analyze
flutter test
flutter run -d chrome --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1
```

## الفحص المسبق
```bash
python3 tools/validate_release.py
```
