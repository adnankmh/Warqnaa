# رفع Warqna v168 إلى GitHub

1. احتفظ بمجلد `.git` الموجود في مستودعك.
2. استبدل بقية الملفات بمحتويات هذه الحزمة.
3. شغّل `CHECK_WARQNA_WINDOWS.bat`.
4. استخدم Commit: `Warqna v168 fix GitHub root policy validation`.
5. نفّذ Push وانتظر نجاح Backend وWeb وProduction Release Gate.
6. شغّل Android workflow يدويًا عند الحاجة لتنزيل APK وAAB.

> فحص v168 مصمم عمدًا للعمل بوجود `.git` داخل GitHub Actions، مع إبقاء رفض الملفات العشوائية في جذر المشروع.
