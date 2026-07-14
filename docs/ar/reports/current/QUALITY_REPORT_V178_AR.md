# تقرير جودة Warqna v178

الإصدار: **0.2.2+178**

## نطاق الإصلاح

- إزالة سببَي التحذير المرتبطين بالحقل `_openingRoomRouteV174`.
- إعادة بناء استخدام سياق التنقل بعد `await` بطريقة لا تعتمد على `BuildContext` قديم.
- تصحيح interpolation غير الضروري في V175.
- إضافة عقد V178 مستقل للحزمة المتحركة والمخزون المؤقت وانتهاء الصلاحية.
- إضافة اختبارات Laravel للحزمة المؤقتة ومكافآت الرصيد الدائمة.

## الفحوص المحلية

تُسجّل النتائج التنفيذية الدقيقة في `docs/ar/validation/current/VALIDATION_RESULTS_V178.txt`. تبقى أوامر `flutter analyze` و`php artisan test` ضمن GitHub Actions، ولا تُعتبر ناجحة محلياً إلا عند توفر Flutter SDK وحزم Composer.
