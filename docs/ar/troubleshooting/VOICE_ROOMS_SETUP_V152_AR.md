# إعداد الغرف الصوتية — Warqna v152

## ما تم تنفيذه

أصبح إنشاء أي لعبة يتيح اختيار أحد النمطين:

- **لعبة عادية:** لعب كامل بدون تشغيل الميكروفون.
- **لعبة صوتية:** لعب كامل مع محادثة صوتية بين اللاعبين الحقيقيين داخل الغرفة.

تتضمن الغرفة الصوتية:

- طلب إذن الميكروفون بعد دخول الغرفة الصوتية فقط.
- كتم أو تشغيل ميكروفون اللاعب.
- كتم سماع جميع اللاعبين.
- كتم لاعب محدد محليًا دون التأثير على بقية اللاعبين.
- إظهار حالة الاتصال الصوتي وحالة كل مشارك.
- استمرار اللعب حتى لو تعذر الاتصال الصوتي.
- عدم خصم توكنز مقابل اللعب العادي أو الصوتي.

## التشغيل المحلي وGitHub Pages

على GitHub Pages يستطيع التطبيق اختبار الميكروفون محليًا، لكن المحادثة بين أجهزة مختلفة تحتاج Backend منشورًا عبر HTTPS. عند غياب الخادم يظهر وضع المعاينة المحلية بدل تعطيل اللعبة.

## متغيرات Laravel المطلوبة

أضف إلى ملف `.env` في خادم Laravel:

```env
VOICE_STUN_URLS=stun:stun.l.google.com:19302
VOICE_TURN_URL=turn:turn.example.com:3478,turns:turn.example.com:5349
VOICE_TURN_USERNAME=warqna
VOICE_TURN_CREDENTIAL=CHANGE_THIS_TO_A_LONG_SECRET
```

- STUN يكفي غالبًا داخل الشبكات البسيطة.
- TURN ضروري للإنتاج لأن بعض شبكات الهاتف والجدران النارية لا تسمح باتصال مباشر بين اللاعبين.
- لا تضع بيانات TURN داخل مستودع GitHub العام.

## تشغيل Migration والمهام المجدولة

بعد رفع Laravel:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
```

وشغّل Laravel Scheduler كل دقيقة:

```cron
* * * * * cd /path/to/backend-laravel && php artisan schedule:run >> /dev/null 2>&1
```

توجد مهمة تلقائية باسم:

```bash
php artisan warqna:cleanup-voice
```

وهي تنظف إشارات WebRTC القديمة وحالات الحضور الصوتي المنتهية.

## GitHub Actions

أضف Repository Variable:

```text
WARQNA_API_URL=https://api.your-domain.com/api/mobile/v1
```

ثم شغّل:

```text
Build and deploy Flutter Web
Build Android APK and AAB
Build iOS unsigned
```

Workflow الأندرويد يضيف صلاحيات الميكروفون تلقائيًا، وWorkflow iOS يضيف وصف استخدام الميكروفون إلى `Info.plist`.

## ملاحظات الإنتاج

- يجب أن يكون الموقع والـAPI وTURN عبر نطاقات صحيحة وشهادات TLS.
- لا يعمل عنوان `127.0.0.1` بين الهواتف.
- لا تبدأ تسجيل الصوت أو رفعه إلى الخادم؛ الصوت في هذا الإصدار مباشر بين اللاعبين عبر WebRTC ولا يتم حفظه.
- للاختبارات الجماعية، استخدم هاتفين على شبكتين مختلفتين وليس الجهاز نفسه فقط.
