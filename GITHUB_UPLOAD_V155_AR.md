# رفع Warqna v155 إلى GitHub دون تعارض

## أولًا: إذا بقي GitHub Desktop في حالة Merge

1. اضغط **Abort merge**.
2. انتظر اختفاء `conflicted file`.
3. اضغط **Fetch origin** ثم **Pull origin**.
4. أنشئ نسخة احتياطية من مجلد المستودع المحلي.
5. احذف محتويات مجلد المستودع باستثناء المجلد المخفي `.git`.
6. فك ضغط حزمة v155 وانسخ محتويات المجلد الداخلي إلى جذر المستودع.
7. افتح GitHub Desktop وتأكد أن الملفات تظهر كتغييرات عادية وليست تعارضات.

## الرفع

اكتب Summary:

```text
Warqna v155 CI validation hotfix
```

ثم:

1. **Commit to main**
2. **Push origin**
3. افتح **Actions**
4. راقب `Production Release Gate` و`Backend CI and Security Foundation`

## النتيجة المتوقعة

في Backend CI يجب أن تنجح الخطوات التالية:

- `Validate Composer definition`
- `Prepare temporary Compose environment`
- `Validate production Compose`
- `Build production image`
- `Remove temporary Compose environment`

لا تستخدم Force Push ولا ترفع ملف `backend-laravel/.env`.
