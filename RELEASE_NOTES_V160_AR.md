# ملاحظات إصدار Warqna v160

- تحديث الإصدار إلى `1.60.0+160`.
- إضافة `RELEASE_VERSION.json` كمصدر مركزي وحيد للإصدار.
- إضافة `tools/release_metadata.py` لتصدير الإصدار إلى GitHub Actions.
- إضافة `tools/verify_release_versions.py` للتحقق من الاتساق دون أوامر grep ثابتة.
- تحويل Android وWeb وiOS إلى قراءة الإصدار والبناء آليًا.
- إزالة فحص build 158 المتبقي من Production Release Gate.
- إصلاح `MobileSocialController` بعد منع استدعاء `fresh()` على علاقة `HasOne`.
- تحديث اختبار تحويل التوكنز ليتحقق من المحفظة الراجعة والأرصدة النهائية.
- جعل اختبارات Content-Type تتعامل مع charset بصورة غير حساسة لحالة الأحرف.
- الحفاظ على إصلاحات v159 وجميع ميزات اللعب والمتجر والصوت والحسابات والإدارة.
