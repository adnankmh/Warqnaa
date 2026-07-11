# Warqna v155 — Global Production CI Hotfix Package

حزمة كاملة موحدة لتطبيق **Warqna** مبنية بـFlutter وLaravel، وتحافظ على جميع مزايا v154 السابقة: الحسابات المستقلة، الصور الشخصية لكل لاعب، تحويل التوكنز، الألعاب العادية والصوتية، الغرف، المتجر، الثيمات، اللغات، الإيموجي، الأغلفة، الإدارة، الإعلانات، الخصوصية، الأمان وCI/CD.

يعالج الإصدار v155 خطأَي GitHub Actions التاليين:

- تحذير Composer الخاص بعدم تحديد الترخيص مع `--strict`.
- غياب `backend-laravel/.env` أثناء فحص `docker compose config`.

ابدأ من:

- `START_HERE_V155_AR.md`
- `GITHUB_UPLOAD_V155_AR.md`
- `PRODUCTION_DEPLOYMENT_V155_AR.md`
- `SECURITY_PRIVACY_V155_AR.md`
- `LAUNCH_CHECKLIST_V155_AR.md`
- `QUALITY_REPORT_V155_AR.md`

فحص الحزمة على Windows:

```bat
CHECK_V155_WINDOWS.bat
```

وعلى Linux/macOS:

```bash
./check-v155.sh
```
