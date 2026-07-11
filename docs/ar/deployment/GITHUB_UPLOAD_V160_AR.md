# رفع Warqna v160 إلى GitHub

1. افتح GitHub Desktop واختر مستودع `Warqnaa`.
2. نفّذ **Fetch origin** ثم **Pull origin**.
3. إن وُجد Merge معلق، اختر **Abort merge**.
4. احتفظ بمجلد `.git` واستبدل بقية ملفات المشروع بمحتويات حزمة v160.
5. شغّل `CHECK_V160_WINDOWS.bat`.
6. استخدم رسالة Commit:

```text
Warqna v160 dynamic release contract and wallet relation hotfix
```

7. اضغط **Commit to main** ثم **Push origin**.
8. راقب بالترتيب:
   - Production Release Gate
   - Backend CI and Security Foundation
   - Build and deploy Flutter Web
   - Build Android APK and AAB عند تشغيله يدويًا

لا تعِد تشغيل v159، لأن فحص الإصدار القديم وعلاقة المحفظة غير الصحيحة موجودان فيها.
