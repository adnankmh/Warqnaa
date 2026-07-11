# رفع Warqna v157 إلى GitHub دون تعارض

1. في GitHub Desktop اضغط **Abort merge** فقط إن كانت عملية دمج معلقة.
2. اضغط **Fetch origin** ثم **Pull origin**.
3. احتفظ بالمجلد المخفي `.git` داخل المستودع المحلي.
4. استبدل بقية ملفات المستودع بمحتويات المجلد الداخلي لهذه الحزمة.
5. شغّل `CHECK_V157_WINDOWS.bat`.
6. استخدم رسالة Commit:

```text
Warqna v157 Android CI and PHPUnit suite hotfix
```

7. اضغط **Commit to main** ثم **Push origin**.
8. راقب Backend CI، ثم شغّل Android Workflow يدويًا.

لا ترفع `.env` ولا تستخدم Force Push.
