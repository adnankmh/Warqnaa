# رفع Warqna v159 إلى GitHub

1. افتح GitHub Desktop واختر مستودع Warqnaa.
2. نفّذ Fetch origin ثم Pull origin.
3. إن وُجد Merge معلق، اختر Abort merge.
4. استبدل ملفات المشروع بمحتويات حزمة v159 مع إبقاء مجلد `.git`.
5. شغّل `CHECK_V159_WINDOWS.bat`.
6. استخدم رسالة Commit:

```text
Warqna v159 HTTP foundation and stable tests hotfix
```

7. اضغط Commit to main ثم Push origin.
8. راقب Backend CI وProduction Release Gate وFlutter Web ثم شغّل Android عند الحاجة.
