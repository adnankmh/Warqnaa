# تحديث v120 — بناء APK عبر GitHub Actions

## الهدف
تم تجهيز Warqna Zone ليبني APK تلقائيًا عبر GitHub Actions بدون الحاجة لوجود Android Studio أو Android SDK على جهازك.

## ما تم إضافته
1. Workflow بناء APK:
   - `.github/workflows/build-apk.yml`

2. Workflow فحص PWA:
   - `.github/workflows/pwa-apk-check.yml`

3. تحديث package.json:
   - version: 1.20.0
   - scripts جديدة:
     - `apk:github`
     - `apk:local`
     - `pwa:check`
     - `cap:add:android`
     - `cap:sync`

4. أدلة عربية:
   - `GITHUB_ACTIONS_APK_BUILD_GUIDE_AR.md`
   - `GITHUB_ACTIONS_STEPS_QUICK_AR.md`

5. تحديث إعدادات المشروع:
   - v120
   - github_actions_apk=true
   - cloud_build_ready=true

## كيف تحصل على APK
- ارفع المشروع على GitHub.
- افتح Actions.
- شغل Build Warqna APK.
- حمل Artifact باسم:
  `warqna-zone-debug-apk`

## الفحص
- PHP Syntax: OK
- JavaScript Syntax: OK
- PWA readiness: OK
