# خطة تحويل Warqna من PWA إلى Android / iOS

## المرحلة 1: PWA جاهز
تم إضافة:
- manifest.webmanifest
- sw.js
- SEO و PWA meta
- start_url إلى /games

## المرحلة 2: تغليف PWA
يمكن استخدام:
- Capacitor
- TWA Android
- أو Flutter WebView كبداية

## المرحلة 3: تطبيق Native كامل
لاحقًا يتم بناء:
- React Native / Expo
- شاشات Login/Register
- غرف الألعاب
- الدردشة
- المتجر
- الإشعارات
- WebSocket

## المرحلة 4: API
نحتاج فصل API واضح:
- /api/auth
- /api/games
- /api/rooms
- /api/chat
- /api/store
- /api/profile
