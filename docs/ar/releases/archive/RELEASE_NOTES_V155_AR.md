# ملاحظات إصدار Warqna v155

## GitHub Actions

- إصلاح فشل `composer validate --no-check-lock --strict` بإضافة ترخيص `proprietary` للمشروع المغلق المصدر.
- إصلاح فشل Docker Compose الناتج عن غياب `.env` في GitHub Runner.
- إنشاء `.env` مؤقت من قالب الإنتاج، وضبط قيم CI غير حقيقية، ثم حذفه في خطوة تعمل دائمًا.
- إضافة فحوص Regression داخل `tools/validate_release.py` و`Production Release Gate`.

## الإصدار

- Flutter: `1.55.0+155`.
- Laravel: `WARQNA_VERSION=1.55.0` و`WARQNA_BUILD=155`.
- Android وiOS وWeb تبني الإصدار 155.

## الميزات المحفوظة

لم تُحذف أي ميزة من v154. بقيت الحسابات والملفات الشخصية المستقلة وتحويل التوكنز والألعاب العادية والصوتية والثيمات واللغات والمتجر والإيموجي والأغلفة والإدارة والإعلانات والأمان والنشر.
