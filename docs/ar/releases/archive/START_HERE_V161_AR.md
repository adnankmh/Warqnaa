# ابدأ من هنا — Warqna v161

هذه الحزمة هي مشروع كامل للإصدار `1.61.0+161` وليست ملف APK جاهزًا داخل ZIP. البناء النهائي يتم من GitHub Actions.

## التشغيل المحلي على Windows

1. فك الضغط داخل مسار قصير مثل `C:\Warqna-v161`.
2. شغّل `CHECK_V161_WINDOWS.bat`.
3. شغّل `START_WARQNA_V161_WINDOWS.bat`.
4. الواجهة المحلية تعمل حتى لو لم يكن Laravel منشورًا، أما اللعب الحقيقي بين الأجهزة والصوت وتسجيل الدخول الاجتماعي فتحتاج خادم Laravel منشورًا عبر HTTPS.

## رفع النسخة إلى GitHub

انسخ محتويات هذه الحزمة إلى جذر المستودع مع إبقاء مجلد `.git`، ثم نفّذ Commit بعنوان:

`Warqna v161 voice mobile social progression release`

بعد Push راقب:

- Production Release Gate
- Backend CI and Security Foundation
- Build and deploy Flutter Web
- Build Android APK and AAB

## إعدادات الإنتاج الأساسية

اضبط GitHub Variables:

- `WARQNA_API_URL=https://api.your-domain.com/api/mobile/v1`
- `ADMOB_ANDROID_APP_ID`
- `ADMOB_REWARDED_ANDROID_ID`

واضبط Laravel `.env` لمفاتيح TURN وGoogle وFacebook وApple حسب الملف `VOICE_AND_SOCIAL_SETUP_V161_AR.md`.
