# رفع Warqna v169 إلى GitHub

1. احتفظ بمجلد `.git` الموجود في مستودعك.
2. استبدل بقية الملفات بمحتويات مجلد v169 الداخلي.
3. شغّل `CHECK_WARQNA_WINDOWS.bat`.
4. استخدم Commit: `Warqna v169 fix Flutter analyzer and notifications API contracts`.
5. نفّذ Push وانتظر نجاح Backend وProduction Release Gate وFlutter Web.
6. بعد نجاحها شغّل Android workflow يدويًا لبناء APK وAAB.

يفحص v169 توافق واجهة `flutter_local_notifications 22`، الترجمة، وحدود `ChangeNotifier` قبل تشغيل Flutter Analyzer.
