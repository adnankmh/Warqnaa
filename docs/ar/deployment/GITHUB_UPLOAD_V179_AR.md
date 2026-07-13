# رفع Warqna V0.3 — البناء 179 إلى GitHub

1. ارفع محتويات المشروع كاملة إلى جذر المستودع.
2. لا تحذف مجلدات `.github` و`tools` و`backend-laravel` و`flutter_app`.
3. شغّل Workflow الفحص الإنتاجي أولاً.
4. نفّذ Migrations على الخادم قبل توجيه تطبيق الويب إلى API الإنتاجي.
5. استخدم معرفات AdMob التجريبية أثناء الاختبار فقط، ثم استبدلها بمعرفات حسابك قبل النشر التجاري.

التحقق المحلي:
```bash
python3 tools/verify_release_versions.py
python3 tools/validate_release.py
```
