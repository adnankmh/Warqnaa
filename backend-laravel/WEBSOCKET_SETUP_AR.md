# إعداد WebSocket الحقيقي لورقنا

النسخة الحالية تعمل بنظام Polling/Fallback حتى تعمل فورًا على XAMPP بدون تعقيد.

## المرحلة الاحترافية التالية
يمكن تفعيل WebSocket حقيقي بإحدى الطريقتين:

## الخيار 1: Laravel Reverb
1. تثبيت Reverb:
   composer require laravel/reverb
2. نشر الإعدادات:
   php artisan reverb:install
3. تشغيل السيرفر:
   php artisan reverb:start
4. في ملف .env:
   WARQNA_REALTIME_MODE=reverb
   BROADCAST_CONNECTION=reverb

## الخيار 2: Soketi
1. تشغيل Soketi على سيرفر Node.
2. ضبط:
   WARQNA_REALTIME_MODE=soketi
   WARQNA_WS_HOST=127.0.0.1
   WARQNA_WS_PORT=6001

## ما تم تجهيزه داخل المشروع
- config/warqna_realtime.php
- RealtimeController
- Heartbeat
- Online Presence
- Room polling fallback
- Admin monitor endpoint

## ملاحظة
حتى لا ينكسر التشغيل المحلي على Windows/XAMPP، تركنا الوضع الافتراضي polling، ويمكن تفعيل WebSocket لاحقًا من .env.
