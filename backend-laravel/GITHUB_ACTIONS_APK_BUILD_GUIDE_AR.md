# بناء APK تلقائيًا عبر GitHub Actions — Warqna Zone v120

هذه النسخة تتيح لك بناء APK بدون Android Studio على جهازك.

## الفكرة
بدل ما تبني APK على جهازك، GitHub يبنيه على سيرفراته، ثم يعطيك ملف APK جاهز للتنزيل من Artifacts.

## الملفات المضافة
- `.github/workflows/build-apk.yml`
- `.github/workflows/pwa-apk-check.yml`

## الخطوات

### 1. ارفع المشروع على GitHub
أنشئ Repository جديد، ثم ارفع ملفات المشروع.

### 2. افتح تبويب Actions
من GitHub:
- افتح المشروع.
- اضغط Actions.
- اختر workflow باسم:
  **Build Warqna APK**

### 3. اضغط Run workflow
سيبدأ البناء تلقائيًا:
- تثبيت Node.js.
- تثبيت Java 17.
- تثبيت PHP.
- فحص PWA.
- إنشاء منصة Android عبر Capacitor.
- بناء APK debug.

### 4. تنزيل APK
بعد انتهاء workflow:
- افتح آخر Run.
- انزل إلى Artifacts.
- حمل:
  `warqna-zone-debug-apk`

داخل الملف ستجد APK مثل:
`app-debug.apk`

## ملاحظات مهمة
- هذا APK Debug مناسب للتجربة على الهاتف.
- للنشر على Google Play تحتاج Release APK/AAB وتوقيع رسمي.
- بعد رفع الموقع على دومين HTTPS، يمكن ضبط Capacitor لفتح الدومين بدل الملفات المحلية.
- إذا ظهر خطأ بسبب Composer أو Laravel، تأكد من وجود `.env.example` و `composer.json`.

## اختصار
لا تحتاج Android Studio على جهازك. فقط تحتاج:
- حساب GitHub.
- رفع المشروع.
- تشغيل Workflow.
