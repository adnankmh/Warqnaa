# رفع Warqna v172 إلى GitHub

1. نفّذ Fetch ثم Pull.
2. ألغِ أي Merge معلّق.
3. احتفظ بمجلد `.git` فقط واستبدل بقية الملفات بمحتويات مجلد v172 الداخلي.
4. شغّل `CHECK_WARQNA_WINDOWS.bat`.
5. استخدم رسالة Commit:

```text
Warqna v172 additive brand and 40-table collection release
```

6. نفّذ Commit to main ثم Push origin.
7. تأكد من نجاح Production Release Gate وBackend وFlutter Web.
8. شغّل Android يدويًا وحمّل Artifact باسم `warqna-v172-android`.
