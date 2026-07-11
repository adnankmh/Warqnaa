# ابدأ من هنا — Warqna v153

هذه النسخة تجمع كل ميزات v152 السابقة، بما فيها **الغرف العادية والغرف الصوتية**، وتضيف أساس إطلاق إنتاجي عالمي: حماية API، إدارة إصدارات، استعادة كلمة المرور، تأكيد البريد، حذف الحساب، تصدير البيانات، الجلسات، البلاغات، صفحات قانونية، مراقبة الصحة، Docker، CI/CD، نسخ احتياطي ونشر مرحلي/إنتاجي.

## 1) تجربة الواجهة على GitHub Pages

1. ارفع محتويات الحزمة إلى جذر المستودع.
2. من `Settings → Pages` اختر `GitHub Actions`.
3. شغّل `Build and deploy Flutter Web`.
4. بدون `WARQNA_API_URL` تعمل النسخة بوضع المعاينة المحلي فقط.
5. بعد نشر Laravel أضف متغير المستودع:

```text
WARQNA_API_URL=https://api.example.com/api/mobile/v1
```

عندها يُبنى Flutter بوضع الإنتاج، ويُعطّل الدخول المحلي التجريبي تلقائيًا.

## 2) تشغيل Laravel محليًا

```bash
cd backend-laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8006
```

ثم شغّل Flutter:

```bash
cd flutter_app
flutter pub get
flutter run -d chrome \
  --dart-define=WARQNA_API_URL=http://127.0.0.1:8006/api/mobile/v1 \
  --dart-define=WARQNA_PRODUCTION_MODE=false \
  --dart-define=WARQNA_APP_VERSION=1.53.0 \
  --dart-define=WARQNA_APP_BUILD=153
```

## 3) تشغيل اختبار الجاهزية

```bash
cd backend-laravel
php artisan warqna:production-check
php artisan test
```

عند الاستضافة الحقيقية:

```bash
php artisan warqna:production-check --strict
```

## 4) الغرف العادية والصوتية

عند إنشاء غرفة يختار اللاعب:

- لعبة عادية.
- لعبة صوتية.

الغرف الصوتية الحقيقية بين أجهزة مختلفة تحتاج HTTPS وTURN وإعدادات `VOICE_*` في `.env`. اللعبة نفسها تستمر حتى عند انقطاع الصوت.

## 5) الملفات الأهم

- `PRODUCTION_DEPLOYMENT_V153_AR.md`
- `SECURITY_PRIVACY_V153_AR.md`
- `LAUNCH_CHECKLIST_V153_AR.md`
- `RELEASE_NOTES_V153_AR.md`
- `backend-laravel/.env.production.example`
- `backend-laravel/docker-compose.production.yml`
- `.github/workflows/backend-ci.yml`
- `.github/workflows/deploy-staging.yml`
- `.github/workflows/deploy-production.yml`

## 6) حدود ما لا يمكن تضمينه داخل ملف ZIP

لا يمكن تضمين نطاق حقيقي، خادم مدفوع، شهادات Apple/Google، مفاتيح OAuth، حسابات AdMob، مفتاح توقيع متجر Google Play، أو بيانات TURN الخاصة بك. الحزمة جاهزة لاستقبال هذه الأسرار من GitHub Secrets وملف `.env` دون وضعها داخل الكود.
