# Warqna v154 — Merge-Safe Global Production Package

حزمة موحدة لتطبيق **Warqna** مبنية بـFlutter وLaravel. تحافظ على مزايا v153 السابقة كاملة، ومنها الحسابات، الملفات الشخصية المستقلة، تحويل التوكنز، الألعاب العادية والصوتية، المتجر والثيمات واللغات، الإعلانات، الإدارة، الخصوصية، الأمان وCI/CD.

يعالج الإصدار v154 تعارض `flutter_app/lib/main.dart` الذي ظهر في GitHub Desktop، ويضيف فحصًا آليًا يمنع رفع أي ملف يحتوي على علامات تعارض Git.

ابدأ من:

- `START_HERE_V154_AR.md`
- `GITHUB_MERGE_RECOVERY_V154_AR.md`
- `PRODUCTION_DEPLOYMENT_V154_AR.md`
- `SECURITY_PRIVACY_V154_AR.md`
- `LAUNCH_CHECKLIST_V154_AR.md`
- `QUALITY_REPORT_V154_AR.md`

فحص الحزمة على Windows:

```bat
CHECK_V154_WINDOWS.bat
```

وعلى Linux/macOS:

```bash
./check-v154.sh
```
