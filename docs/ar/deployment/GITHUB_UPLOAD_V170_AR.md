# رفع Warqna v170 إلى GitHub

1. نفّذ Fetch ثم Pull في GitHub Desktop.
2. ألغِ أي Merge معلّق.
3. احتفظ بمجلد `.git` فقط، واستبدل بقية الملفات بمحتويات مجلد v170 الداخلي.
4. شغّل `CHECK_WARQNA_WINDOWS.bat`.
5. استخدم Commit:

```text
Warqna v170 responsive gameplay voice and security release
```

6. نفّذ Push وانتظر نجاح:
   - Production Release Gate
   - Backend CI and Security Foundation
   - Build and deploy Flutter Web
7. شغّل يدويًا `Build Android APK and AAB`.
8. حمّل Artifact باسم `warqna-v170-android` وثبّت `warqna-v170-safe.apk`.

## متغيرات مطلوبة للصوت الحقيقي والإشعارات

- `WARQNA_API_URL` = رابط Laravel API عام عبر HTTPS، مثل `https://api.example.com/api/mobile/v1`
- إعدادات Firebase الخاصة بالمشروع
- TURN URLs واسم المستخدم وكلمة المرور في خادم Laravel

لا تستخدم `localhost` أو `127.0.0.1` في APK.
