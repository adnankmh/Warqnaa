# إعداد الصوت والإشعارات — Warqna v166

## أولاً: الصوت على Android

يتضمن v166 طلب إذن الميكروفون داخل APK، تفعيل مكبر الصوت، منع الصدى والضوضاء، حفظ ICE candidates إلى حين اكتمال الاتصال، وإعادة إنشاء اتصال WebRTC عند الانقطاع. كما توجد شاشة تشخيص تعرض حالة الخادم والميكروفون وأجهزة الإدخال وTURN/STUN.

للاتصال الصوتي بين لاعبين على شبكات مختلفة يجب أن يكون Laravel منشورًا عبر HTTPS وأن يعيد إعداد ICE يتضمن TURN صالحًا. STUN وحده قد ينجح داخل بعض الشبكات ولا يضمن المرور عبر جميع أنواع NAT.

## ثانيًا: متغيرات Firebase داخل بناء Flutter

أضف القيم التالية في GitHub Repository Variables:

```text
FIREBASE_API_KEY
FIREBASE_APP_ID
FIREBASE_MESSAGING_SENDER_ID
FIREBASE_PROJECT_ID
```

يبني GitHub Actions ملف APK/AAB بهذه القيم. بعد تسجيل الدخول يسجل التطبيق FCM token تلقائيًا في:

```text
POST /api/mobile/v1/push/devices
```

ويجدد التسجيل تلقائيًا عند تغيير FCM token، ويحذف الجهاز من حساب المستخدم عند تسجيل الخروج.

## ثالثًا: إرسال الإشعارات من Laravel والتطبيق مغلق

يتضمن v166 مرسل Firebase HTTP v1 كاملًا في:

```text
backend-laravel/app/Services/Notifications/FirebasePushService.php
```

ويتم استخدامه لإشعارات:

- الرسائل الخاصة بين الأصدقاء.
- رسائل غرفة اللعبة.
- طلبات الصداقة والرد عليها.

أنشئ Service Account من مشروع Firebase، ثم حوّل ملف JSON إلى Base64 وضعه في بيئة الخادم فقط:

```text
PUSH_NOTIFICATIONS_ENABLED=true
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_SERVICE_ACCOUNT_B64=BASE64_OF_COMPLETE_SERVICE_ACCOUNT_JSON
FIREBASE_ANDROID_CHANNEL_ID=warqna_messages
```

بدائل أقل تفضيلًا:

```text
FIREBASE_SERVICE_ACCOUNT_JSON=
FIREBASE_SERVICE_ACCOUNT_PATH=
```

لا تضع Service Account داخل GitHub Variables العامة أو داخل كود Flutter. بيانات خادم Firebase تبقى داخل Secrets/Environment في خادم Laravel فقط.

## رابعًا: بعد تعديل متغيرات الخادم

نفّذ داخل مجلد Laravel:

```bash
php artisan optimize:clear
php artisan config:cache
php artisan migrate --force
```

ثم اختبر برسالتين بين حسابين: أغلق التطبيق على الهاتف الأول، وأرسل رسالة من الهاتف الثاني. يجب أن يظهر الإشعار، وعند الضغط عليه يسجل التطبيق مسار غرفة اللعبة أو دردشة الصديق ويعرض خيار العودة المناسب.
