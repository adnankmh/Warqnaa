# رفع Warqna v162 إلى GitHub

1. افتح GitHub Desktop ونفّذ **Fetch origin** ثم **Pull origin**.
2. إن كانت هناك عملية Merge معلقة اختر **Abort merge**.
3. احتفظ بالمجلد المخفي `.git` فقط داخل مجلد المستودع.
4. استبدل بقية الملفات بمحتويات مجلد v162 الداخلي.
5. شغّل `CHECK_V162_WINDOWS.bat`.
6. استخدم رسالة Commit:

```text
Warqna v162 account cancellation and Flutter analyzer hotfix
```

7. اضغط **Commit to main** ثم **Push origin**.
8. راقب بالترتيب:
   - Production Release Gate
   - Backend CI and Security Foundation
   - Build and deploy Flutter Web
   - Build Android APK and AAB

## تشغيل الحذف المؤجل في الخادم

يجب أن يكون Laravel Scheduler فعالاً في الإنتاج. الأمر الفعلي هو:

```bash
php artisan warqna:purge-cancelled-accounts
```

لا تستخدم أي مهمة خارجية تحذف الحسابات بناءً على `last_seen_at`؛ v162 يحذف فقط الحسابات التي اختار أصحابها إلغاءها وانتهت مهلة 30 يوماً.
