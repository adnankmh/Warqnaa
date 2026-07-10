# Warqna Zone v139 — تحويل الموقع إلى تطبيق Android بدون Android Studio

هذه النسخة تضيف طبقة تطبيق احترافية للموقع:

- PWA قابل للتثبيت من المتصفح.
- Android wrapper عبر Capacitor.
- بناء APK من GitHub Actions بدون تثبيت Android Studio على جهازك.
- واجهة موبايل محسّنة للألعاب، المتجر، الدردشة، الثيمات، واللغات.
- زر تثبيت التطبيق داخل الموقع.
- Splash / Icon / Manifest / Service Worker / Offline page.
- إصلاح عملي لأزرار الثيمات واللغات حتى تعمل داخل الموقع والتطبيق.

---

## الفكرة الصحيحة

مشروع Laravel لا يعمل كاملًا داخل الهاتف كملفات PHP محلية. التطبيق الصحيح يكون كالتالي:

1. ترفع مشروع Laravel على سيرفر بدومين HTTPS.
2. التطبيق Android يفتح هذا الدومين داخل WebView/Capacitor بطريقة فخمة.
3. المستخدم يراه كتطبيق كامل بأيقونة وSplash وواجهة موبايل.
4. APK/AAB يتم بناؤه في GitHub Actions بدون Android Studio محليًا.

إذا كان الموقع يعمل فقط على XAMPP أو `127.0.0.1` فلن يعمل كتطبيق حقيقي على هواتف الآخرين. لازم رابط عام مثل:

`https://warqna.com`

---

## ملفات مهمة تمت إضافتها

- `public/assets/css/mobile-app.css`  
  تحسين واجهة الموبايل والتطبيق.

- `public/assets/js/mobile-app.js`  
  زر التثبيت، إصلاح الثيمات واللغات، Bottom navigation للموبايل، تحسين الدردشة والتفاعلات.

- `tools/prepare-capacitor-config.js`  
  يجهز `capacitor.config.json` تلقائيًا بناءً على رابط الموقع.

- `tools/mobile-app-check.js`  
  يفحص جاهزية ملفات التطبيق.

- `.github/workflows/build-android-no-studio.yml`  
  يبني APK في السحابة بدون Android Studio.

---

## طريقة بناء APK بدون Android Studio

### 1) ارفع المشروع على GitHub

ارفع هذه النسخة على Repository خاص أو عام.

### 2) أضف رابط الموقع

من GitHub:

`Settings → Secrets and variables → Actions → Variables → New repository variable`

أضف:

Name:

`WARQNA_APP_URL`

Value:

`https://your-domain.com`

ضع رابط موقع Laravel الحقيقي بدل `your-domain.com`.

### 3) شغل بناء التطبيق

اذهب إلى:

`Actions → Build Android App Without Android Studio → Run workflow`

يمكنك أيضًا كتابة الرابط يدويًا في خانة `app_url` عند تشغيل workflow.

### 4) حمّل APK

بعد انتهاء البناء:

`Artifacts → warqna-zone-debug-apk-no-android-studio`

ستجد ملف APK للتجربة.

---

## ملاحظات مهمة للنشر الرسمي

- APK الناتج Debug مناسب للتجربة على الهاتف.
- للنشر على Google Play تحتاج AAB موقّع Keystore.
- يمكن إضافة توقيع تلقائي لاحقًا عبر GitHub Secrets بدون Android Studio.
- التطبيق يعتمد على الدومين، لذلك أي تعديل تعمله في Laravel يظهر داخل التطبيق مباشرة.

---

## ما الذي صار أفضل في v139؟

- أزرار الثيمات واللغات لم تعد مخفية.
- الثيم يتغير فعليًا في الموقع والتطبيق.
- اللغة تتغير من زر 🌐 داخل التطبيق.
- واجهة الألعاب صارت تتكيف مع شاشة الهاتف.
- الدردشة تظهر بشكل أفضل داخل التطبيق.
- شريط تنقل سفلي للموبايل: ألعاب، متجر، دردشة، بطولات، توكنز.
- زر تثبيت التطبيق يظهر عندما يدعم المتصفح التثبيت.
- إعدادات Capacitor صارت تقرأ رابط السيرفر تلقائيًا من `WARQNA_APP_URL`.

