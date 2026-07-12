# ابدأ من هنا — Warqna v169

هذه النسخة إصلاح مباشر لأخطاء Flutter CI في v168، مع المحافظة على البنية المنظمة وجميع وظائف التطبيق السابقة.

## الإصلاحات الأساسية

- الترجمة داخل إشعارات الغرف تستخدم الآن `L.t` بدل دالة غير موجودة.
- مكتبة الإشعارات المحلية v22 تستخدم `settings:` عند التهيئة و`id:` و`notificationDetails:` عند عرض الإشعار.
- تحديث الدردشة يستخدم دالة عامة داخل `AppController` بدل استدعاء `notifyListeners()` المحمي.
- تمت إضافة فحص ثابت يمنع رجوع هذه الأخطاء قبل تشغيل Flutter Analyzer.

## التشغيل والفحص

- فحص Windows من الجذر: `CHECK_WARQNA_WINDOWS.bat`
- تشغيل Windows من الجذر: `START_WARQNA_WINDOWS.bat`
- الفحص المباشر: `scripts/windows/current/CHECK_V169_WINDOWS.bat`
- فحص Linux/macOS: `scripts/unix/current/check-v169.sh`
- فحص Flutter CI فقط: `python tools/test_flutter_ci_contract.py`
