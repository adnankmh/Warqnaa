# إنهاء تعارض GitHub Desktop ورفع Warqna v154 بأمان

الشاشة التي تحتوي على `Resolve conflicts before Merge` تعني أن Git بدأ عملية دمج ولم يستطع توحيد `main.dart` تلقائيًا. الحزمة v154 تحتوي على الملف مصححًا، لذلك لا تحاول تعديل الأسطر الخضراء يدويًا.

## الطريقة الموصى بها

1. من نافذة GitHub Desktop الحالية اضغط **Abort merge**.
2. انتظر حتى تختفي حالة `conflicted file`.
3. اضغط **Fetch origin** ثم **Pull origin** حتى يصبح المستودع المحلي مطابقًا لـGitHub.
4. أغلق التطبيق أو اتركه مفتوحًا، وافتح مجلد المستودع من **Repository → Show in Explorer**.
5. أنشئ نسخة احتياطية من المجلد المحلي قبل الاستبدال.
6. احذف محتويات مجلد المستودع المحلي **باستثناء المجلد المخفي `.git`**.
7. فك ضغط حزمة v154، وانسخ جميع محتوياتها إلى مجلد المستودع.
8. ارجع إلى GitHub Desktop. يجب أن تظهر الملفات كتغييرات عادية، دون `Merge conflict`.
9. اكتب Summary مثل:

```text
Warqna v154 merge-safe production package
```

10. اضغط **Commit to main** ثم **Push origin**.
11. افتح تبويب **Actions** على GitHub وتأكد من نجاح:
   - `Production Release Gate`
   - `Build and deploy Flutter Web`
   - `Backend CI and Security Foundation`

## لا تفعل التالي

- لا تضغط **Continue merge** بينما توجد علامات تعارض.
- لا تحذف مجلد `.git` داخل المستودع الذي تريد الاستمرار باستخدامه.
- لا تستخدم Force Push.
- لا تنسخ ملفات v154 فوق مستودع ما زال في حالة Merge نشطة.

## سبب منع تكرار المشكلة

أصبح في الحزمة ملف `tools/validate_release.py`. أي ظهور مستقبلي للأسطر التالية سيوقف Workflow مباشرة قبل البناء:

```text
<<<<<<<
=======
>>>>>>>
```
