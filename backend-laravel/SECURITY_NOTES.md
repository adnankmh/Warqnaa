# Security Notes - Warqna

- كلمة مرور الغرف الخاصة تحفظ كـ Hash وليس نصًا صريحًا.
- كل عمليات إنشاء الغرف، الشراء، التحويل، النوادي، المسابقات محمية بـ Laravel CSRF + Auth Middleware.
- Anti-Cheat يسجل الحركات المشبوهة في `anti_cheat_events` و `game_actions`.
- إعداد التشغيل المحلي يستخدم SQLite و `APP_DEBUG=true`. عند النشر الحقيقي غيّرها إلى `APP_DEBUG=false` واستخدم MySQL/PostgreSQL و HTTPS.
- غيّر كلمة مرور الإدارة فور أول دخول عند تحويل المشروع إلى إنتاج.
- لا تجعل مجلد المشروع كله هو Web Root؛ اجعل Web Root هو مجلد `public` فقط.
