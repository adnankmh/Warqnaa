# Warqna v160 — Release Contract & Wallet Hotfix

حزمة المصدر الكاملة لتطبيق Warqna بإصدار `1.60.0+160`، مبنية على v159 مع الحفاظ على جميع ميزات التطبيق الحالية.

أهم إصلاحات الإصدار:

- إلغاء فحص الإصدار القديم المعتمد على أوامر `grep` ثابتة، والذي بقي يبحث عن build 158 داخل إصدار v159.
- إضافة مصدر واحد للإصدار: `RELEASE_VERSION.json`.
- جعل Android وWeb وiOS يقرؤون الإصدار والبناء آليًا من المصدر نفسه.
- إضافة `tools/verify_release_versions.py` لمنع أي تعارض بين Flutter وLaravel وGitHub Actions.
- إصلاح تحويل التوكنز بعد إزالة الاستدعاء غير الصحيح `fresh()` من علاقة `HasOne`.
- تحديث اختبار تحويل التوكنز ليتحقق من الخصم 1100، عمولة 100، والأرصدة النهائية الصحيحة.
- جعل اختبارات `Content-Type` تتعامل مع `UTF-8` و`utf-8` بصورة صحيحة وفق عدم حساسية أسماء charset لحالة الأحرف.

ابدأ من:

- `START_HERE_V160_AR.md`
- `GITHUB_UPLOAD_V160_AR.md`
- `QUALITY_REPORT_V160_AR.md`

فحص Windows:

```bat
CHECK_V160_WINDOWS.bat
```

فحص Linux/macOS:

```bash
./check-v160.sh
```
