# ابدأ من هنا — Warqna v164

الإصدار: **1.65.0+165**

## ما المختلف في Android

- ملف APK المخصص للتجربة يُبنى دائماً باستخدام AdMob App ID تجريبي صحيح من Google.
- لا يتم تشغيل AdMob أو WebRTC أو SharedPreferences قبل ظهور أول إطار من التطبيق.
- أي بيانات محلية قديمة وغير متوافقة لن تمنع شاشة الدخول من الظهور.
- ملف AAB يقبل معرفات AdMob الإنتاجية فقط بعد التحقق من صيغتها، وإلا يستخدم القيم التجريبية الآمنة.

## الرفع

1. نفّذ `Fetch origin` ثم `Pull origin` في GitHub Desktop.
2. عند وجود Merge معلّق اختر `Abort merge`.
3. احتفظ بمجلد `.git` واستبدل بقية الملفات بمحتويات هذا المجلد.
4. شغّل `CHECK_V164_WINDOWS.bat`.
5. نفّذ Commit ثم Push.
6. انتظر نجاح فحوص Backend وWeb.
7. شغّل Workflow: `Build Android APK and AAB`.
8. من Artifact نزّل الملف الذي ينتهي بـ:

```text
warqna-v164-safe.apk
```

## مهم قبل تثبيت APK

احذف إصدار Warqna القديم من الهاتف أولاً، ثم ثبّت `warqna-v164-safe.apk`. هذا يزيل بيانات محلية قديمة ويمنع تعارض توقيع APK التجريبي بين تشغيل وآخر على GitHub Actions.

رسالة Commit المقترحة:

```text
Warqna v164 Android first-frame and AdMob startup safety fix
```
