# تشغيل Warqna v149

## نشر نسخة Flutter Web على GitHub Pages

1. انسخ محتويات هذه الحزمة إلى جذر مستودع GitHub بحيث يظهر `.github` و`flutter_app` و`backend-laravel` مباشرة.
2. من `Settings → Pages` اختر `GitHub Actions`.
3. من `Actions` شغّل **Build and deploy Flutter Web**.
4. يفتح التطبيق على `https://USERNAME.github.io/REPOSITORY/` ويمكن تثبيته من زر التثبيت أو «إضافة إلى الشاشة الرئيسية».

## تشغيل Laravel محليًا

داخل `backend-laravel` شغّل `setup-windows.bat` مرة واحدة، ثم `start-windows.bat`. يعمل API افتراضيًا على `http://127.0.0.1:8006/api/mobile/v1`.

## ربط GitHub Pages بالخادم الحقيقي

أضف Repository Variable باسم `WARQNA_API_URL` وقيمته رابط HTTPS الكامل للـAPI، ثم أعد تشغيل Workflow.

## حساب المدير

- المستخدم: `Adnan`
- كلمة السر: `Adnan123`
- المستوى: 90 على الأقل
- الباشا: 1000 يوم
- الرصيد: 1000000000000000000 توكن

## ملاحظات

- Google/Apple/Facebook موجودة كواجهات وتجهيزات، لكن تشغيل الدخول الحقيقي يتطلب مفاتيح OAuth الخاصة بك.
- GitHub Pages يشغّل Flutter Web فقط. اللعب المتزامن بين أجهزة متعددة والحسابات المركزية يحتاج Laravel منشورًا على HTTPS.
- إعداد AdMob موضح في `ADS_SETUP_V149_AR.md`.
