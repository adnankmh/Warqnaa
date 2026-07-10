# تقرير فحص Warqna v147

## فحوص ناجحة داخل بيئة التجهيز

- فحص توازن الأقواس والسلاسل لجميع ملفات Dart الأساسية: ناجح.
- فحص PHP syntax: 217 ملفاً ناجحاً.
- فحص YAML لجميع GitHub Workflows: ناجح.
- فحص JSON للـPWA وRelease Manifest: ناجح.
- التحقق من 12 لعبة ظاهرة فقط.
- التحقق من وجود محرك محلي احتياطي لجميع الألعاب الظاهرة.
- التحقق من 24 لون لاعب + 24 لون دردشة مولدة بمدد 3/7/30 يوماً.
- التحقق من إعداد Rewarded Ads والحد اليومي وسجل المطالبات.
- التحقق من حساب Adnan: مستوى 90، 1000 يوم باشا، ورصيد 10^18 توكن.
- التحقق من حماية حذف المدير وحذف غير النشطين بعد 30 يوماً.
- التحقق من سلامة GitHub Workflows للويب وAndroid وiOS.

## فحص يتولى GitHub Actions تنفيذه

لم يكن Flutter SDK مثبتاً في بيئة إعداد الحزمة، لذلك يتولى Workflow تنفيذ:

```text
flutter analyze --no-fatal-infos --no-fatal-warnings
flutter test
flutter build web --release
```

كما تتولى Workflows المنفصلة بناء APK وAAB ونسخة iOS غير موقعة.
