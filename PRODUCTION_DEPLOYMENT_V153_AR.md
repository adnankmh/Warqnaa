# نشر Warqna v153 على خادم إنتاج

## البنية المقترحة

```text
app.example.com     Flutter Web / PWA
api.example.com     Laravel API + Nginx
PostgreSQL          قاعدة البيانات
Redis               Cache / Queue / Sessions
TURN                 الصوت عبر WebRTC
```

## الإعداد الأول على الخادم

1. انسخ `backend-laravel/.env.production.example` إلى `.env`.
2. استبدل كل قيم `CHANGE_ME_*`.
3. أنشئ مفتاح Laravel:

```bash
docker compose -f docker-compose.production.yml run --rm app php artisan key:generate --show
```

ضع الناتج في `APP_KEY` داخل `.env`.

4. شغّل:

```bash
docker compose -f docker-compose.production.yml build --pull
docker compose -f docker-compose.production.yml up -d
```

5. نفّذ:

```bash
docker compose -f docker-compose.production.yml exec app php artisan migrate --force
docker compose -f docker-compose.production.yml exec app php artisan warqna:production-check --strict
```

## الخدمات التي تُشغّل تلقائيًا

- PHP-FPM/Laravel.
- Nginx.
- PostgreSQL.
- Redis.
- Queue worker.
- Laravel Scheduler.

## HTTPS

ضع Reverse Proxy أمام منفذ Nginx الداخلي `8080`، مثل Cloudflare أو Caddy أو Nginx على الخادم، واربطه بـ`api.example.com` مع شهادة TLS.

لا تستخدم HTTP في تطبيق الإنتاج. Workflow الأندرويد يعطّل cleartext تلقائيًا عندما يبدأ `WARQNA_API_URL` بـ`https://`.

## إعداد GitHub Secrets للنشر

داخل المستودع:

`Settings → Secrets and variables → Actions → Secrets`

أضف:

```text
PRODUCTION_SSH_HOST
PRODUCTION_SSH_USER
PRODUCTION_SSH_KEY
PRODUCTION_APP_PATH
PRODUCTION_HEALTH_URL
```

ثم شغّل:

```text
Actions → Deploy Laravel Production → Run workflow
```

استخدم GitHub Environment باسم `production` مع Required Reviewers لمنع النشر العرضي.

## نشر Staging

استخدم Workflow:

```text
Deploy Laravel Staging
```

واجعل Staging منفصلًا في الدومين وقاعدة البيانات والأسرار.

## إعداد Flutter

أضف متغير Repository Variable:

```text
WARQNA_API_URL=https://api.example.com/api/mobile/v1
```

ثم أعد تشغيل:

- `Build and deploy Flutter Web`
- `Build Android APK and AAB`
- `Build iOS unsigned`

## توقيع Android للإنتاج

أضف GitHub Secrets التالية:

```text
ANDROID_KEYSTORE_BASE64
ANDROID_KEYSTORE_PASSWORD
ANDROID_KEY_ALIAS
ANDROID_KEY_PASSWORD
```

عند غيابها ينتج Workflow ملفًا للاختبار، وليس ملفًا صالحًا للنشر التجاري النهائي.

## AdMob

Repository Variables:

```text
ADMOB_ANDROID_APP_ID
ADMOB_REWARDED_ANDROID_ID
```

استخدم معرفات الاختبار خلال الاختبارات المغلقة ثم استبدلها بالمعرفات الحقيقية قبل الإنتاج.

## الصوت

داخل `.env`:

```text
VOICE_STUN_URLS=stun:...
VOICE_TURN_URLS=turn:...udp,turn:...tcp
VOICE_TURN_USERNAME=...
VOICE_TURN_CREDENTIAL=...
```

بدون TURN قد يعمل الصوت على بعض الشبكات ويفشل على شبكات أخرى.

## النسخ الاحتياطي

```bash
cd backend-laravel
./scripts/backup-production.sh
```

اختبر الاستعادة دوريًا:

```bash
./scripts/restore-production.sh /path/to/backup
```

النسخة الاحتياطية التي لم تُختبر استعادتها لا تعتبر خطة حماية مكتملة.
