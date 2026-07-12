# تقرير جودة Warqna v170

## نطاق المراجعة

- واجهة Flutter المتجاوبة وبطاقات المتجر والبروفايلات.
- محركات اللعب وعدد اللاعبين واتجاه الدور والجولات التلقائية.
- صوت WebRTC على Android ومسار HTTPS/TURN.
- Laravel APIs للغرف والدعوات والحظر والتحويلات والتحديات.
- العقود الأمنية: action id، revision، throttling، إخفاء الحالة الخاصة.
- GitHub Actions لبناء Web وAPK/AAB وiOS unsigned.

## الحماية من الانحدار

- `tools/test_v170_contract.py`
- `backend-laravel/tests/Feature/V170ResponsiveGameplaySecurityContractTest.php`
- تشغيل عقد v170 قبل Flutter Analyzer في Web وAndroid وiOS.
- تشغيله داخل Production Release Gate.

## ملاحظة تنفيذية

لا يمكن اعتبار الصوت بين شبكات مختلفة مضمونًا من الكود وحده؛ يلزم خادم Laravel عام عبر HTTPS وخادم TURN مضبوط. النسخة تمنع loopback المضلل على الهاتف وتعرض تشخيصًا واضحًا.
