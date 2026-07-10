# Backend Laravel — Warqna v142

## تشغيل أول مرة

```text
setup-windows.bat
```

ثم:

```text
start-windows.bat
```

الرابط:

```text
http://127.0.0.1:8006
```

## حساب المدير

```text
Adnan / Adnan123
```

الرصيد بعد Seeder:

```text
1000000000000000000
```

## API

Prefix:

```text
/api/mobile/v1
```

أهم المسارات:

- `POST /register`
- `POST /login`
- `GET /bootstrap`
- `GET /games/catalog`
- `POST /games/session`
- `POST /games/session/{code}/action`
- `GET|POST /games/session/{code}/chat`
- `GET /social`
- `POST /social/friends/{user}/request`
- `GET|POST /social/chat/{user}`
- `POST /social/transfer`
- `POST /store/purchase`
- `GET /admin/dashboard`

المسارات الخاصة محمية بـ Sanctum، والمسارات الإدارية تتحقق من صلاحية المدير داخل Controller.

## اختبار المحركات بدون Vendor

```bat
php tools\test-engine-adapters.php
php tools\test-v142-rule-cores.php
```

بعد تثبيت Composer dependencies:

```bat
php artisan test
```

## الإنتاج

- استخدم HTTPS.
- استخدم MySQL/PostgreSQL بدلاً من SQLite عند الحمل الكبير.
- غيّر `ADMIN_PASSWORD`.
- قيد `CORS_ALLOWED_ORIGINS` بدومينات التطبيق.
- شغّل Queue وWebSocket تحت Process Manager.
- فعّل النسخ الاحتياطي ومراقبة الأخطاء ومحددات المعدل.
