# دليل تجهيز APK لورقنا زون v119

هذه النسخة أصبحت جاهزة كـ PWA قابلة للتغليف إلى APK عبر Capacitor.

## المتطلبات
1. تثبيت Node.js.
2. تثبيت PHP و Composer وتشغيل المشروع كالمعتاد.
3. لتوليد APK محليًا تحتاج Android SDK، أما بدون Android Studio فاستخدم GitHub Actions المضافة في v139.

## خطوات التجهيز السريع
1. شغل:
   `setup-windows.bat`
2. شغل:
   `start-windows.bat`
3. افتح:
   `http://127.0.0.1:8000/games`

## تجهيز مشروع Android
شغل:
`apk-build-windows.bat`

هذا السكربت يعمل:
- npm install
- فحص ملفات PWA
- migrate
- npx cap add android
- npx cap sync android

## بناء APK
بعد إنشاء مجلد android محليًا يمكن البناء من CMD إن كان Android SDK متوفرًا:
```bat
cd android
gradlew assembleDebug
```

مسار APK غالبًا:
`android/app/build/outputs/apk/debug/app-debug.apk`

## ملاحظة مهمة
Capacitor يغلف موقع Laravel/PWA. أثناء التطوير المحلي يمكن استخدام السيرفر المحلي، وللنشر الحقيقي يجب رفع Laravel على دومين HTTPS ثم تحديث إعدادات السيرفر داخل Capacitor.

---

## تحديث v139 — بدون Android Studio

تمت إضافة Workflow جديد:

`.github/workflows/build-android-no-studio.yml`

هذا يبني APK من GitHub Actions بدون Android Studio على جهازك. فقط ارفع المشروع إلى GitHub، وضع متغير:

`WARQNA_APP_URL=https://your-domain.com`

ثم شغل Action باسم:

`Build Android App Without Android Studio`

