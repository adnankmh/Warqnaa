# رفع v161 إلى GitHub

1. افتح GitHub Desktop ونفّذ Fetch ثم Pull.
2. عند وجود Merge معلّق اختر Abort merge.
3. أبقِ `.git` واستبدل بقية الملفات بمحتويات هذه الحزمة.
4. شغّل `CHECK_V161_WINDOWS.bat`.
5. Commit: `Warqna v161 voice mobile social progression release`.
6. Push origin.
7. انتظر نجاح Release Gate وBackend وFlutter Web.
8. من Actions شغّل `Build Android APK and AAB` يدويًا.
9. نزّل Artifact باسم `warqna-v161-android`.

لا ترفع `.env` أو keystore. ضع مفاتيح التوقيع في GitHub Secrets، وروابط API وAdMob في Variables.
