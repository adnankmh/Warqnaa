# إعداد الصوت وتسجيل الدخول الاجتماعي — v161

## الصوت الحقيقي على Web وAndroid

الكود الصوتي مفعّل، ويستخدم WebRTC مع خادم Laravel لتبادل إشارات الاتصال. يلزم للإنتاج:

1. نشر Laravel عبر HTTPS.
2. ضبط `WARQNA_API_URL` في GitHub Variables إلى رابط `/api/mobile/v1`.
3. تركيب TURN حقيقي مثل coturn وضبط:

```env
VOICE_STUN_URLS=stun:stun.l.google.com:19302
VOICE_TURN_URLS=turn:turn.example.com:3478,turns:turn.example.com:5349
VOICE_TURN_USERNAME=warqna
VOICE_TURN_CREDENTIAL=CHANGE_ME
```

STUN وحده مناسب للاختبارات المحلية، لكنه لا يضمن مرور الصوت بين شبكات الهاتف والراوترات المختلفة. الواجهة تعرض حالة TURN ورسالة تشخيص بدل أن تعلق بصمت.

## Google

```env
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
GOOGLE_REDIRECT_URI=https://api.example.com/auth/social/google/callback
```

## Facebook

```env
FACEBOOK_CLIENT_ID=
FACEBOOK_CLIENT_SECRET=
FACEBOOK_REDIRECT_URI=https://api.example.com/auth/social/facebook/callback
FACEBOOK_GRAPH_VERSION=v22.0
```

## Apple

أنشئ Service ID وReturn URL، وأنشئ Client Secret صالحًا ثم اضبط:

```env
APPLE_SERVICE_ID=
APPLE_CLIENT_SECRET=
APPLE_REDIRECT_URI=https://api.example.com/auth/social/apple/callback
```

Apple يعيد النتيجة عبر `form_post`؛ مسار Callback مستثنى فقط من CSRF ويظل محميًا بحالة OAuth عشوائية، مهلة قصيرة، والتحقق من issuer وaudience وexpiry.

## ملاحظة أمنية

لا تضع أي Client Secret أو TURN password داخل GitHub أو التطبيق. تحفظ الأسرار في `.env` على الخادم فقط، بينما يستخدم التطبيق رابط التفويض الصادر من الخادم.
