# رفع وبناء Warqna V0.5 عبر GitHub Desktop

1. خذ نسخة احتياطية من `C:\xampp\htdocs\Warqnaa`.
2. لا تحذف `.git`.
3. فك جميع أجزاء V0.5 بالتتابع داخل `Warqnaa` مباشرة.
4. تأكد أن `flutter_app` و`backend-laravel` و`.github` و`tools` موجودة في جذر `Warqnaa`.
5. افتح GitHub Desktop واختر المستودع، ثم اكتب `Warqna V0.5 build 500`.
6. اضغط `Commit to main` ثم `Push origin`.
7. افتح Actions وانتظر نجاح بوابة الإصدار وBackend وWeb وAndroid.
8. نزّل APK وAAB من Artifacts، وافتح رابط GitHub Pages بعد تحديث قوي `Ctrl+F5`.

## متغيرات مهمة

- `WARQNA_API_URL`: عنوان Laravel عبر HTTPS.
- `ADMOB_ANDROID_APP_ID` و`ADMOB_REWARDED_ANDROID_ID`: معرفات Android.
- متغيرات Firebase للويب والإشعارات.
- `WARQNA_SEED_DEMO_USERS=false` في الإنتاج.
