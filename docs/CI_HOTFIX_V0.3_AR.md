# إصلاحات CI العاجلة — Warqnaa V0.3

تمت معالجة الأخطاء التي ظهرت في GitHub Actions بعد إصدار V0.3، دون حذف أي ميزة سابقة.

## إصلاحات Flutter

1. إحاطة شرط `leaveChallenge` بأقواس كاملة بما يتوافق مع قاعدة `curly_braces_in_flow_control_structures`.
2. إضافة getter للقراءة فقط باسم `teamScores` داخل `TarneebLocalEngine`، مرتبط بنتائج الفريق الحقيقية `scores`، للحفاظ على التوافق مع علامة تحدي الطرنيب.
3. استبدال الخاصية المهجورة `DropdownButtonFormField.value` بالخاصية الحديثة `initialValue` المتوافقة مع Flutter 3.44.

## إصلاحات Laravel وPHPUnit

1. إضافة مساحة الأسماء القياسية التالية إلى `composer.json`:

```json
"Database\\Factories\\": "database/factories/"
```

وبذلك يستطيع Composer تحميل `Database\Factories\UserFactory` عند تشغيل `User::factory()`.

2. تصحيح كتالوج V173 ليحتوي على 78 عنصرًا كما يتطلب الاختبار:
   - 14 نمط طربوش غير أسود.
   - 50 طاولة.
   - 14 تذكرة منافسة.

تمت إضافة الطربوش القرمزي بدل إعادة اللون الأسود المحذوف.

## منع رجوع الأخطاء

أضيف الاختبار:

`tools/test_v181_ci_regression_contract.py`

وتم إدخاله في مسارات GitHub Actions للويب وAndroid وiOS وفحص الإنتاج.
