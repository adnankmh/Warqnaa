# رفع Warqna v166 إلى GitHub

1. افتح GitHub Desktop واختر المستودع الصحيح.
2. نفّذ `Fetch origin` ثم `Pull origin`.
3. إذا ظهر Merge معلّق اختر `Abort merge`.
4. لا تحذف مجلد `.git`.
5. احذف بقية ملفات النسخة السابقة وانسخ محتويات مجلد v166 الداخلي.
6. شغّل `CHECK_V166_WINDOWS.bat`.
7. استخدم رسالة Commit:

```text
Warqna v166 global voice social polish release
```

8. اضغط `Commit to main` ثم `Push origin`.
9. انتظر نجاح Production Release Gate وBackend CI وFlutter Web.
10. شغّل Android Workflow يدويًا، وحمّل Artifact `warqna-v166-android`.
