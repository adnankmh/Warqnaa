# v102 Cache Folder Fix

تم إصلاح مشكلة:

`bootstrap/cache directory must be present and writable`

## ما تم تعديله
- إضافة مجلد `bootstrap/cache` داخل الحزمة مع `.gitkeep`.
- إضافة مجلدات Laravel المطلوبة داخل `storage`.
- تعديل `setup-windows.bat` لإنشاء المجلدات تلقائيًا قبل تشغيل أوامر Laravel.
- تعديل `start-windows.bat` وملفات التشغيل الأخرى.
- إضافة ملف جديد:
  `fix-cache-folders-windows.bat`

## سبب الخطأ
ملفات ZIP لا تحفظ المجلدات الفارغة غالبًا، لذلك كان مجلد `bootstrap/cache` غير موجود بعد فك الضغط.

## الحل السريع لمن فك v101 بالفعل
شغّل:
`fix-cache-folders-windows.bat`

ثم شغّل:
`setup-windows.bat`
