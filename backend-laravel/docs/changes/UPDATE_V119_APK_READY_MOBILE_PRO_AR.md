# تحديث v119 — نسخة APK Ready Mobile Pro

## الهدف
تحويل Warqna Zone من PWA عادي إلى مشروع جاهز للتغليف APK عبر Capacitor، مع تشغيل أفضل وواجهة موبايل أقوى.

## ما تم إضافته
1. Capacitor readiness:
   - package.json scripts
   - capacitor.config.json
   - apk-build-windows.bat
   - apk-quick-check-windows.bat

2. PWA أقوى:
   - manifest.webmanifest جديد
   - sw.js v119
   - offline.html
   - icons SVG + PNG 192/512

3. Mobile API:
   - app/Http/Controllers/MobileApiController.php
   - /api/mobile/bootstrap
   - /api/mobile/games
   - /api/mobile/store
   - /api/mobile/economy
   - /api/mobile/health

4. إعدادات موبايل:
   - config/warqna_mobile.php

5. واجهة موبايل:
   - زر تثبيت التطبيق
   - Safe area
   - تحسين touch targets
   - تحسين غرف اللعب على الهاتف
   - منع التداخل والـ overflow

6. اختبارات:
   - tests/Feature/MobileApiAndPwaTest.php

7. أدلة تشغيل:
   - APK_BUILD_GUIDE_AR.md
   - APK_READY_CHECKLIST_AR.md

## ملاحظة
هذه النسخة جاهزة للتحويل إلى APK من ناحية PWA/Capacitor. لإخراج APK فعلي تحتاج Android Studio أو Android SDK على الجهاز الذي سيتم البناء عليه.
