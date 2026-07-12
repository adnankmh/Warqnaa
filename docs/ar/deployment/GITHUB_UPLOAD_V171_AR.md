# رفع Warqna v171 إلى GitHub

1. نفّذ Fetch ثم Pull.
2. ألغِ أي Merge معلّق.
3. احتفظ بمجلد `.git` فقط واستبدل بقية الملفات بمحتويات مجلد v171 الداخلي.
4. شغّل `CHECK_WARQNA_WINDOWS.bat`.
5. استخدم رسالة Commit:

```text
Warqna v171 fix controller reference CI contract
```

6. نفّذ Commit to main ثم Push origin.
7. تأكد من نجاح Production Release Gate وBackend وFlutter Web.
8. شغّل Android يدويًا وحمّل Artifact باسم `warqna-v171-android`.
