# رفع وبناء Warqna v174 عبر GitHub

## الرفع

ارفع محتويات مجلد المشروع إلى فرع `main` أو `master` مع الحفاظ على المجلدات المخفية، خصوصاً `.github`.

## مسارات الفحص والبناء

- `Production Release Gate`: يتحقق من عقود المصدر والإصدار وعدم وجود أسرار أو تعارضات.
- `Backend CI`: يثبت Composer ويشغل `php artisan test`.
- `Flutter Android`: يشغل عقود المصدر و`flutter analyze` و`flutter test` ثم يبني APK/AAB.
- `Flutter Web Pages`: يشغل التحليل والاختبارات ثم يبني نسخة الويب.
- `Flutter iOS`: يشغل الفحوص ثم يبني تطبيق iOS غير موقع.

## قبل الرفع

شغل:

```bash
python3 tools/test_v174_offline_progression_navigation_contract.py
python3 tools/verify_release_versions.py
python3 tools/validate_release.py
```

أو على Windows:

```bat
CHECK_WARQNA_WINDOWS.bat
```

## الإعلانات والخادم

أضف متغيرات GitHub المناسبة عند البناء الإنتاجي:

- `WARQNA_API_URL`
- `ADMOB_ANDROID_APP_ID`
- `ADMOB_IOS_APP_ID`
- `ADMOB_REWARDED_ANDROID_ID`
- `ADMOB_REWARDED_IOS_ID`

يمكن تشغيل التطبيق أوفلاين، لكن المعاملات الاقتصادية والمنافسات تحتاج خادماً متصلاً.
