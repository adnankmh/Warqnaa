# Warqna v151 — ابدأ من هنا

## التشغيل على GitHub Pages

1. انسخ محتويات هذه الحزمة إلى جذر مستودع GitHub.
2. نفّذ Commit ثم Push إلى فرع `main`.
3. من `Settings → Pages` اختر `GitHub Actions`.
4. من `Actions` شغّل **Build and deploy Flutter Web**.
5. افتح رابط Pages الخاص بالمستودع.

عندما يكون `WARQNA_API_URL` غير مضبوط أو يشير إلى `127.0.0.1`، تعمل نسخة الويب تلقائياً في الوضع المحلي ولا تعرض خطأ Laravel لحسابات التجربة.

## بناء APK وAAB

شغّل من GitHub Actions:

`Build Android APK and AAB`

ثم حمّل Artifact باسم:

`warqna-v151-android`

## ربط Laravel الحقيقي

أضف Repository Variable باسم:

`WARQNA_API_URL`

مثال:

`https://api.example.com/api/mobile/v1`

GitHub Pages لا يشغّل PHP أو قاعدة البيانات؛ لذلك يحتاج اللعب المتزامن الحقيقي، الدردشة بين أجهزة مختلفة، المجموعات والمنافسات الدائمة إلى نشر مجلد `backend-laravel` على خادم HTTPS.

## حساب المدير

- المستخدم: `Adnan`
- كلمة المرور: `Adnan123`
- المستوى: 90+
- أيام الباشا: 1000
- الرصيد: `1000000000000000000`

راجع `DEMO_ACCOUNTS_V151_AR.md` لبقية الحسابات.
