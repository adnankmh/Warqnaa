# رفع Warqna v158 إلى GitHub دون تعارض

1. في GitHub Desktop اضغط **Abort merge** فقط إن كانت عملية دمج معلقة.
2. اضغط **Fetch origin** ثم **Pull origin**.
3. احتفظ بالمجلد المخفي `.git` داخل مجلد المستودع المحلي.
4. استبدل بقية الملفات بمحتويات المجلد الداخلي لهذه الحزمة.
5. شغّل `CHECK_V158_WINDOWS.bat`.
6. استخدم رسالة Commit:

```text
Warqna v158 CI lock parser and Laravel quality hotfix
```

7. اضغط **Commit to main** ثم **Push origin**.
8. راقب `Production Release Gate` و`Backend CI and Security Foundation` و`Build and deploy Flutter Web`.
9. شغّل `Build Android APK and AAB` يدويًا بعد نجاح الفحوص.

لا ترفع `.env` أو مفاتيح التوقيع، ولا تستخدم Force Push.
