# رفع Warqna v173 إلى GitHub

## قبل الرفع

1. احتفظ بمجلد `.git` في المستودع الحالي.
2. ألغِ أي Merge أو Rebase معلّق.
3. استبدل ملفات المشروع بمحتويات مجلد v173 الداخلي، من دون إنشاء مجلد مشروع داخل مجلد المشروع.
4. شغّل `CHECK_WARQNA_WINDOWS.bat` وتأكد من ظهور PASS.

## متغيرات GitHub المطلوبة

اضبط عنوان الخادم وإعدادات الإصدار المعتادة. ولتشغيل AdMob الحقيقي أضف في **Settings → Secrets and variables → Actions → Variables**:

- `ADMOB_ANDROID_APP_ID`
- `ADMOB_IOS_APP_ID`
- `ADMOB_REWARDED_ANDROID_ID`
- `ADMOB_REWARDED_IOS_ID`

عند غيابها تستخدم بنية الاختبار الآمنة ولا يجب نشرها كإعلانات إنتاجية.

## الرفع

رسالة Commit المقترحة:

```text
Warqna v173 online Pasha tickets packs competitions and 50-table expansion
```

ثم نفّذ Commit وPush إلى `main`.

## التحقق داخل GitHub Actions

يجب نجاح:

- Production Release Gate
- Backend CI
- Flutter Web Pages
- Flutter Android
- Flutter iOS

Artifact Android المتوقع يحمل build `173`. بعد نجاح Workflow اختبر تسجيل الدخول الحقيقي، انقطاع الشبكة، شراء تذكرة، فتح الحزمة اليومية، الانضمام لمنافسة، تفعيل طاولة وطربوش، ومكافأة إعلان على جهاز حقيقي.

## نشر الخادم

نفّذ ترحيلات Laravel:

```bash
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
```

وتأكد من أن `ALLOW_LOCAL_DEMO_MODE=false` في الإنتاج.
