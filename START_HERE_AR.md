# ابدأ من هنا — ورقنا V0.2

1. ارفع المشروع كاملاً إلى المستودع، بما في ذلك مجلد `.github/workflows`.
2. شغّل بالترتيب: **Production Release Check**، ثم **Backend CI**، ثم Flutter Web/Android/iOS.
3. على خادم Laravel نفّذ:
   - `composer install --no-dev --optimize-autoloader`
   - `php artisan migrate --force`
   - `php artisan optimize:clear`
4. اضبط `WARQNA_API_URL` ليشير إلى خادم Laravel الحقيقي. اللعب المحلي يعمل أوفلاين، بينما مزامنة الصناديق والمكافآت الرسمية تحتاج الخادم.
5. صفحة **صندوق الجوائز اليومي** موجودة كبطاقة مستقلة في الصفحة الرئيسية؛ الصناديق ليست داخل المتجر.
6. كل فوز مكتمل يمنح صندوقاً واحداً، بحد أقصى 4 صناديق في اليوم لكل لاعب.
7. الإصدار الظاهر: **V0.2**. الإصدار التقني: **0.2.0+176**.

---

## إدارة المشروع الاحترافية

قبل إضافة أي ميزة كبيرة، راجع الملفات التالية داخل:

```text
docs/project-management/
```

- `ROADMAP.md`: توزيع الميزات على الإصدارات.
- `CHANGELOG.md`: التغييرات المنفذة.
- `KNOWN_ISSUES.md`: المشكلات والقيود المعروفة.
- `TEST_PLAN.md`: الاختبارات المطلوبة.
- `RELEASE_CHECKLIST.md`: قائمة فحص النشر.
- `DEFINITION_OF_DONE.md`: متى تعتبر الميزة مكتملة فعليًا.

لتشغيل بوابة الجودة المحلية على Windows:

```text
CHECK_QUALITY_GATE_WINDOWS.bat
```

ولتشغيل المشروع كاملًا محليًا:

```text
START_WARQNA_WINDOWS.bat
```

ملف التشغيل الآن يستخدم الإصدار الصحيح `V0.2.5 Build 181` ويشغّل Laravel وSocket وFlutter Web من المسارات الحالية.
