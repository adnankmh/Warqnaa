# تقرير جودة Warqna v164

- الإصدار الموحد: `1.65.0+165`.
- فحص Syntax لعدد 272 ملف PHP.
- فحص 20 ملف JSON و15 ملف YAML.
- فحص Python وShell وعلامات تعارض Git والأسرار.
- اختبار أداة إنشاء AndroidManifest في وضع APK الآمن.
- اختبار رفض AdMob App ID غير الصحيح واستبداله بالقيمة التجريبية.
- فحص MainActivity وInternet وNetwork State وMicrophone وAudio Settings.
- فحص minSdk 24 وcompileSdk 36.
- فحص عدم وجود تهيئة AdMob داخل مسار بدء Flutter.
- فحص تأجيل SharedPreferences إلى ما بعد أول إطار.
- فحص سلامة بنية Dart والمشروع الكامل.

الاختبار النهائي على جهاز Android فعلي يتم بعد بناء Artifact من GitHub Actions.
