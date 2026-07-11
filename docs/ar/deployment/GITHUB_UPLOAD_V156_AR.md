# رفع Warqna v156 إلى GitHub دون تعارض

1. في GitHub Desktop اضغط **Abort merge** إن كانت عملية دمج معلقة.
2. اضغط **Fetch origin** ثم **Pull origin**.
3. احتفظ بالمجلد المخفي `.git` داخل المستودع المحلي.
4. استبدل بقية محتويات المستودع بمحتويات المجلد الداخلي لهذه الحزمة.
5. شغّل `CHECK_V156_WINDOWS.bat`.
6. استخدم رسالة Commit التالية:

```text
Warqna v156 Flutter tests and store migration hotfix
```

7. اضغط **Commit to main** ثم **Push origin**.
8. افتح **Actions** وراقب:
   - Production Release Gate
   - Backend CI and Security Foundation
   - Build and deploy Flutter Web
   - Build Android APK and AAB
   - Build iOS unsigned

لا ترفع ملف `.env` ولا تستخدم Force Push.
