# ابدأ من هنا — Warqna v154

هذه الحزمة هي الإصدار الكامل التالي لـv153، وليست ملف تعديل منفصل. جميع ملفات Flutter وLaravel وGitHub Actions موجودة داخل ملف ZIP واحد.

## ما تم إصلاحه في v154

- إزالة تعارضات Git من `flutter_app/lib/main.dart` بالكامل.
- تغيير وسيط تسجيل الدخول من `login` إلى `loginId` لمنع تظليل اسم الدالة.
- استخدام `this.login(...)` صراحةً في مسار الدخول المحلي الاحتياطي.
- إضافة فحص آلي يرفض علامات `<<<<<<<` و`=======` و`>>>>>>>` قبل البناء والنشر.
- توحيد الإصدار إلى `1.54.0+154` في Flutter وLaravel وGitHub Actions.
- إضافة سجل إصدار v154 إلى قاعدة البيانات مع إبقاء ترحيلات v153 التاريخية سليمة.
- الحفاظ على جميع الميزات الموجودة في النسخة السابقة دون حذفها.

## أول خطوة بسبب شاشة التعارض الحالية

لا تضغط **Continue merge**. اضغط **Abort merge** في GitHub Desktop، ثم اتبع ملف:

`GITHUB_MERGE_RECOVERY_V154_AR.md`

## تجربة Flutter Web عبر GitHub Pages

1. ارفع محتويات هذه الحزمة إلى جذر المستودع بعد إنهاء التعارض بالطريقة الآمنة.
2. من `Settings → Pages` اختر `GitHub Actions`.
3. شغّل Workflow باسم `Build and deploy Flutter Web`.
4. في حال عدم ضبط `WARQNA_API_URL` تعمل الواجهة بوضع Preview المحلي.
5. عند ضبط رابط Laravel الحقيقي يتم بناء وضع Production وتعطيل الدخول المحلي التجريبي.

## تشغيل Laravel محليًا

من داخل `backend-laravel`:

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate:fresh --seed
php artisan serve --host=127.0.0.1 --port=8006
```

على Windows يمكن تشغيل:

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
  --dart-define=WARQNA_APP_VERSION=1.54.0 \
  --dart-define=WARQNA_APP_BUILD=154
```

أو على Windows:

```bat
flutter_app\RUN_FLUTTER_WEB.bat
```

## فحص الحزمة قبل رفعها

على Windows:

```bat
CHECK_V154_WINDOWS.bat
```

على Linux/macOS:

```bash
./check-v154.sh
```

بعد الرفع ستعمل الفحوص نفسها تلقائيًا داخل GitHub Actions، ثم يعمل `flutter analyze` و`flutter test` والبناء الفعلي.

## ملاحظة إنتاجية

لا يمكن تضمين نطاق حقيقي، استضافة مدفوعة، حسابات المتاجر، مفاتيح AdMob، مفاتيح OAuth، شهادة iOS، Keystore Android أو بيانات TURN داخل ملف ZIP آمن. أما البنية البرمجية ومواضع إعداد هذه القيم فهي موجودة داخل الحزمة.
