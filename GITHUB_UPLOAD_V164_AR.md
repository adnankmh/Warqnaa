# رفع Warqna v164 إلى GitHub

1. في GitHub Desktop نفّذ `Fetch origin` ثم `Pull origin`.
2. إذا وجدت Merge معلّقاً اختر `Abort merge`.
3. استبدل ملفات المشروع بمحتويات حزمة v164 مع إبقاء `.git`.
4. شغّل `CHECK_V164_WINDOWS.bat`.
5. استخدم رسالة Commit:

```text
Warqna v164 Android first-frame and AdMob startup safety fix
```

6. اضغط `Commit to main` ثم `Push origin`.
7. شغّل `Build Android APK and AAB` يدوياً.
8. ثبّت `warqna-v164-safe.apk` بعد حذف الإصدار القديم من الهاتف.
