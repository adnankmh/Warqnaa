# البداية السريعة — Warqna v152

## معاينة Flutter Web على GitHub Pages

1. ارفع محتويات هذه الحزمة إلى جذر المستودع.
2. من `Settings → Pages` اختر `GitHub Actions`.
3. أضف `WARQNA_API_URL` إذا كان Laravel منشورًا.
4. شغّل `Build and deploy Flutter Web`.

## تشغيل Laravel محليًا

```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve --host=127.0.0.1 --port=8006
```

ثم شغّل Flutter مع:

```bash
cd flutter_app
flutter pub get
flutter run -d chrome --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1
```

## تجربة نوع الغرفة

- افتح أي لعبة.
- اضغط مباراة ودية أو إنشاء غرفة.
- اختر لعبة عادية أو لعبة صوتية.
- في الغرفة الصوتية وافق على إذن الميكروفون.

للتجربة بين هاتفين، انشر Laravel على HTTPS واتبع `VOICE_ROOMS_SETUP_V152_AR.md`.
