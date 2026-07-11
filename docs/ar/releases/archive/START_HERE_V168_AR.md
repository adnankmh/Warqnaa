# ابدأ من هنا — Warqna v168

هذه النسخة إصلاح موثوق لبوابة CI بعد تنظيم v167، مع المحافظة على بنية المشروع وجميع وظائف v166/v167.

## الإصلاح الأساسي

أصبح فحص نظافة جذر المشروع يفرّق بين ملفات المشروع وبين بيانات نظام التحكم بالإصدارات. لذلك يقبل `.git` سواء كان مجلدًا عاديًا في GitHub Actions أو ملفًا في Git worktree، مع استمرار رفض أي ملف عشوائي حقيقي داخل الجذر.

## التشغيل والفحص

- فحص Windows من الجذر: `CHECK_WARQNA_WINDOWS.bat`
- تشغيل Windows من الجذر: `START_WARQNA_WINDOWS.bat`
- الفحص المباشر: `scripts/windows/current/CHECK_V168_WINDOWS.bat`
- فحص Linux/macOS: `scripts/unix/current/check-v168.sh`

## المجلدات الأساسية

- `flutter_app/`: تطبيق Flutter.
- `backend-laravel/`: الخادم وواجهات API.
- `docs/`: الوثائق المرتبة.
- `scripts/`: ملفات التشغيل والفحص.
- `assets/play-store/`: أصول Google Play.
- `releases/manifests/`: بيانات الإصدارات.
- `tools/`: أدوات CI والتحقق.
