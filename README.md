# Warqna v159 — HTTP Foundation & Stable Test Suite

حزمة المصدر الكاملة لتطبيق Warqna بإصدار `1.59.0+159`، مبنية على v158 مع الحفاظ على جميع الميزات الحالية.

أهم إصلاحات هذا الإصدار:

- إعادة ملف Laravel الأساسي `app/Http/Controllers/Controller.php` الذي تعتمد عليه جميع واجهات Mobile API والصفحات القانونية والحساب والسلامة.
- منع أخطاء HTTP 500 الناتجة عن فشل Composer في تحميل الأب `Controller`.
- إزالة اختبارات توزيع الورق العشوائية المتذبذبة واستبدالها بفحوص ثابتة: 52 ورقة فريدة و13 ورقة لكل لاعب دون تقوية مصطنعة.
- إضافة فحص CI مستقل لشجرة Controllers قبل PHPUnit.
- توحيد الإصدار في Flutter وLaravel وبناء Android/Web/iOS.

ابدأ من:

- `START_HERE_V159_AR.md`
- `GITHUB_UPLOAD_V159_AR.md`
- `QUALITY_REPORT_V159_AR.md`

فحص Windows:

```bat
CHECK_V159_WINDOWS.bat
```

فحص Linux/macOS:

```bash
./check-v159.sh
```
