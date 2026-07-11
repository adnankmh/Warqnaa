# دليل بنية مشروع Warqna

```text
warqna/
├── .github/workflows/       GitHub Actions
├── backend-laravel/         Laravel API
├── flutter_app/             Flutter Web/Android/iOS
├── assets/play-store/       صور وأصول المتجر
├── docs/ar/                 الوثائق العربية
├── releases/manifests/      ملفات تعريف الإصدارات
├── scripts/windows/         تشغيل وفحص Windows
├── scripts/unix/            تشغيل وفحص Linux/macOS
├── tools/                   أدوات التحقق والإصدار
├── CHECK_WARQNA_WINDOWS.bat
├── START_WARQNA_WINDOWS.bat
├── README.md
├── START_HERE_AR.md
└── RELEASE_VERSION.json
```

لا توضع وثائق إصدار جديدة في الجذر. توضع داخل `docs/ar/releases/current/`، ثم تُنقل إلى `archive/` في الإصدار التالي.
