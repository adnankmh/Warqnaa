# إعداد الإعلانات المكافِئة — v147

## آلية التشغيل

- Android وiOS: يستخدم التطبيق `google_mobile_ads` ويمنح المكافأة فقط بعد callback إكمال الإعلان.
- Flutter Web / GitHub Pages: تظهر معاينة إعلانية تجريبية مؤقتة، لأن AdMob Mobile SDK ليس إعلان ويب.
- الحد اليومي: 5 إعلانات.
- المكافأة الافتراضية: 3000 توكن + 35 XP.
- Backend يمنع تكرار `verification_id` ويسجل كل مطالبة.

## متغيرات GitHub Actions

من:

`Settings > Secrets and variables > Actions > Variables`

أضف عند الانتقال للإنتاج:

- `ADMOB_ANDROID_APP_ID`
- `ADMOB_REWARDED_ANDROID_ID`
- `ADMOB_IOS_APP_ID`
- `ADMOB_REWARDED_IOS_ID`
- `WARQNA_API_URL`

عند غياب القيم، تستخدم Workflows معرفات Google الاختبارية الرسمية. لا تستخدم معرفات الاختبار في الإصدار التجاري النهائي.

## حماية الإنتاج

النسخة الحالية تطبق callback داخل التطبيق + معرف مطالبة فريد + حد يومي. للحماية المالية القصوى عند الإطلاق العام، اربط AdMob Server-Side Verification بخادم Laravel قبل منح المكافآت الكبيرة.
