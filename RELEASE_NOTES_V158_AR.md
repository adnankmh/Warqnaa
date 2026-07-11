# ملاحظات إصدار Warqna v158

- Flutter CI: إزالة فحص `grep -A2` الذي أعطى نتيجة خاطئة رغم حل الحزم بالإصدارات المطلوبة.
- Flutter CI: إضافة محلل Python مستقل لـ`pubspec.lock` مع اختبار ذاتي وتحقق دقيق من الحزم المثبتة.
- Laravel/PHPUnit: إصلاح دعم SQLite `:memory:` وSQLite URI جذريًا.
- Laravel/PWA: إضافة مسارات صريحة للـmanifest وservice worker وoffline page، وجعل sitemap مقاومًا لعدم جاهزية قاعدة البيانات.
- Mobile API: إبقاء `/api/mobile/v1` كمسار رسمي مع aliases عامة آمنة للإصدارات القديمة.
- Gameplay: إصلاح PlayActionNormalizer ومحرك الطرنيب للحالات الحديثة والقديمة وصيغ الورق المحلية.
- Test suite: إزالة افتراضات تاريخية متعارضة وتثبيت عقد الإصدار الحالي: 12 لعبة، 50 طاولة، 40 ظهر ورق.
- Versioning: توحيد التطبيق والخادم ومسارات البناء على `1.58.0+158`.
