# تقرير جودة Warqna v155

## فحوص نُفذت على الحزمة

- فحص عدم وجود علامات تعارض Git.
- فحص اتساق الإصدار `1.55.0+155`.
- فحص بنية `composer.json` ووجود الترخيص `proprietary` المطلوب لإزالة التحذير الحالي.
- فحص منطق إنشاء وحذف `.env` المؤقت في Backend CI.
- فحص PHP Syntax لـ214 ملفًا.
- فحص 11 ملف JSON و15 ملف YAML.
- فحص Python وShell.
- التحقق من عدم تضمين `.env` حقيقي أو مفاتيح توقيع.
- تشغيل فحص الحزمة `tools/validate_release.py` بنجاح.

## فحوص تعتمد على GitHub Actions

لا يتوفر Composer أو Docker Engine أو Flutter SDK داخل بيئة تجهيز الملف، ولذلك يكون نجاح الخطوات التالية بعد الرفع هو الاختبار التنفيذي النهائي:

- `composer validate --no-check-lock --strict`
- `composer install`
- `php artisan test`
- `docker compose -f docker-compose.production.yml config`
- `docker build`
- `flutter analyze`
- `flutter test`
- بناء Web وAPK وAAB وiOS unsigned
