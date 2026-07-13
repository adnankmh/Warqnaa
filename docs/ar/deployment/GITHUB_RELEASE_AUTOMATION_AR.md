# نشر إصدارات Warqna تلقائياً عبر GitHub Releases

## التشغيل اليدوي
1. افتح GitHub ثم Actions.
2. اختر **Publish GitHub Release**.
3. اضغط **Run workflow**.
4. اكتب الوسم، مثل `v0.3.0`.
5. اضغط **Run workflow**.

## التشغيل بواسطة Tag
من Terminal داخل المشروع:

```powershell
git tag v0.3.0
git push origin v0.3.0
```

سينشئ GitHub تلقائياً APK وAAB وWeb ZIP وSource ZIP وPatch ZIP وSHA256SUMS.
