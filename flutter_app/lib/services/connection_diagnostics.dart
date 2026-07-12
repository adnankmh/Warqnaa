import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:permission_handler/permission_handler.dart';

import 'api_client.dart';

class DiagnosticItem {
  final String label;
  final bool ok;
  final String details;
  const DiagnosticItem(this.label, this.ok, this.details);
}

class ConnectionDiagnostics {
  ConnectionDiagnostics._();

  static String _text(String lang, String ar, String en) => lang == 'ar' ? ar : en;

  static Future<List<DiagnosticItem>> run(WarqnaApiClient api, {String lang = 'ar'}) async {
    final results = <DiagnosticItem>[];
    try {
      final stopwatch = Stopwatch()..start();
      await api.health().timeout(const Duration(seconds: 8));
      stopwatch.stop();
      results.add(DiagnosticItem(_text(lang, 'الخادم', 'Server'), true, _text(lang, 'متصل خلال ${stopwatch.elapsedMilliseconds} مللي ثانية', 'Connected in ${stopwatch.elapsedMilliseconds} ms')));
    } catch (error) {
      results.add(DiagnosticItem(_text(lang, 'الخادم', 'Server'), false, _text(lang, 'تعذر الوصول إلى ${api.baseUrl}: $error', 'Could not reach ${api.baseUrl}: $error')));
    }

    final apiUri = Uri.tryParse(api.baseUrl);
    final loopbackApi = const {'localhost', '127.0.0.1', '10.0.2.2'}.contains(apiUri?.host);
    if (!kIsWeb) {
      results.add(DiagnosticItem(
        _text(lang, 'رابط خادم الهاتف', 'Mobile server URL'),
        !loopbackApi && apiUri?.scheme == 'https',
        loopbackApi
            ? _text(lang, 'العنوان ${api.baseUrl} محلي للهاتف ولن يشغّل الصوت بين اللاعبين. أدخل رابط HTTPS حقيقياً.', '${api.baseUrl} is loopback on the phone and cannot carry player-to-player voice. Configure a real HTTPS URL.')
            : apiUri?.scheme == 'https'
                ? _text(lang, 'رابط HTTPS صالح للصوت والإشعارات.', 'HTTPS URL is suitable for voice and push.')
                : _text(lang, 'استخدم HTTPS للخادم البعيد.', 'Use HTTPS for the remote server.'),
      ));
    }

    if (kIsWeb) {
      final secure = Uri.base.scheme == 'https' || Uri.base.host == 'localhost' || Uri.base.host == '127.0.0.1';
      results.add(DiagnosticItem(_text(lang, 'أمان المتصفح', 'Browser security'), secure, secure ? _text(lang, 'HTTPS متاح للميكروفون.', 'HTTPS is available for microphone access.') : _text(lang, 'الصوت يحتاج HTTPS.', 'Voice requires HTTPS.')));
    } else {
      final status = await Permission.microphone.status;
      results.add(DiagnosticItem(_text(lang, 'إذن الميكروفون', 'Microphone permission'), status.isGranted, status.isGranted ? _text(lang, 'مسموح.', 'Allowed.') : status.isPermanentlyDenied ? _text(lang, 'مرفوض نهائياً؛ افتح إعدادات التطبيق.', 'Permanently denied; open app settings.') : _text(lang, 'لم يمنح بعد.', 'Not granted yet.')));
    }

    try {
      final devices = await navigator.mediaDevices.enumerateDevices();
      final microphones = devices.where((device) => device.kind == 'audioinput').length;
      results.add(DiagnosticItem(_text(lang, 'أجهزة الصوت', 'Audio devices'), microphones > 0, microphones > 0 ? _text(lang, 'تم العثور على $microphones مدخل صوت.', '$microphones audio input(s) found.') : _text(lang, 'لم يتم العثور على ميكروفون.', 'No microphone found.')));
    } catch (error) {
      results.add(DiagnosticItem(_text(lang, 'أجهزة الصوت', 'Audio devices'), false, '$error'));
    }
    return results;
  }
}
