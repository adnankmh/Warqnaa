# رفع Warqna V0.2.5 — البناء 181 إلى GitHub

هذه نسخة مصدر كاملة وليست Patch Only.

```bash
git checkout -b release/v0.2.5
git add .
python3 tools/validate_release.py
git commit -m "release: Warqna V0.2.5 build 181"
git push -u origin release/v0.2.5
```

بعد نجاح إجراءات GitHub Actions، أنشئ وسمًا:

```bash
git tag -a v0.2.5 -m "Warqna V0.2.5 build 181"
git push origin v0.2.5
```

متغيرات الإنتاج المطلوبة تشمل عنوان API، قاعدة البيانات، مفاتيح Sanctum/التخزين، إعدادات Firebase، ومعرّفات AdMob الإنتاجية. الحزمة تستخدم معرّفات Google التجريبية افتراضيًا لتجنب نقرات غير صالحة أثناء الاختبار.
