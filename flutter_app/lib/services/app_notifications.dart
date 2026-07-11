import 'dart:async';

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

const _firebaseApiKey = String.fromEnvironment('FIREBASE_API_KEY');
const _firebaseAppId = String.fromEnvironment('FIREBASE_APP_ID');
const _firebaseSenderId = String.fromEnvironment('FIREBASE_MESSAGING_SENDER_ID');
const _firebaseProjectId = String.fromEnvironment('FIREBASE_PROJECT_ID');

@pragma('vm:entry-point')
Future<void> warqnaFirebaseBackgroundHandler(RemoteMessage message) async {
  if (!PushNotifications.configured) return;
  try {
    await Firebase.initializeApp(options: PushNotifications.options);
    // Android/iOS already display notification payloads while the app is in
    // the background. Only synthesize a local alert for data-only messages.
    if (message.notification != null) return;
    await PushNotifications.showLocal(
      title: message.notification?.title ?? message.data['title']?.toString() ?? 'Warqna',
      body: message.notification?.body ?? message.data['body']?.toString() ?? 'لديك تحديث جديد.',
      payload: message.data['route']?.toString(),
    );
  } catch (error) {
    debugPrint('Background notification skipped: $error');
  }
}

class PushNotifications {
  PushNotifications._();

  static final FlutterLocalNotificationsPlugin _local = FlutterLocalNotificationsPlugin();
  static bool _localReady = false;
  static bool _firebaseReady = false;
  static StreamSubscription<RemoteMessage>? _foregroundSubscription;
  static StreamSubscription<RemoteMessage>? _openedSubscription;
  static StreamSubscription<String>? _tokenSubscription;
  static String? _currentToken;
  static void Function(String title, String body, Map<String, dynamic> data)? onForeground;
  static Future<void> Function(String token)? onToken;
  static void Function(String? payload)? onTap;

  static String? get currentToken => _currentToken;

  static void registerBackgroundHandler() {
    if (configured && !kIsWeb) {
      FirebaseMessaging.onBackgroundMessage(warqnaFirebaseBackgroundHandler);
    }
  }

  static bool get configured => _firebaseApiKey.isNotEmpty && _firebaseAppId.isNotEmpty && _firebaseSenderId.isNotEmpty && _firebaseProjectId.isNotEmpty;

  static FirebaseOptions get options => FirebaseOptions(
    apiKey: _firebaseApiKey,
    appId: _firebaseAppId,
    messagingSenderId: _firebaseSenderId,
    projectId: _firebaseProjectId,
  );

  static Future<void> _ensureLocal() async {
    if (kIsWeb || _localReady) return;
    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const darwin = DarwinInitializationSettings();
    await _local.initialize(
      settings: const InitializationSettings(android: android, iOS: darwin),
      onDidReceiveNotificationResponse: (response) => onTap?.call(response.payload),
    );
    final androidPlugin = _local.resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>();
    await androidPlugin?.requestNotificationsPermission();
    _localReady = true;
  }

  static Future<String?> initialize() async {
    await _ensureLocal();
    if (!configured) return null;
    try {
      if (!_firebaseReady) {
        await Firebase.initializeApp(options: options);
        registerBackgroundHandler();
        final messaging = FirebaseMessaging.instance;
        await messaging.requestPermission(alert: true, badge: true, sound: true);
        _foregroundSubscription ??= FirebaseMessaging.onMessage.listen((message) {
          final title = message.notification?.title ?? message.data['title']?.toString() ?? 'Warqna';
          final body = message.notification?.body ?? message.data['body']?.toString() ?? 'لديك تحديث جديد.';
          onForeground?.call(title, body, Map<String, dynamic>.from(message.data));
          unawaited(showLocal(title: title, body: body, payload: message.data['route']?.toString()));
        });
        _tokenSubscription ??= messaging.onTokenRefresh.listen((token) {
          _currentToken = token;
          final callback = onToken;
          if (callback != null) unawaited(callback(token));
        });
        _openedSubscription ??= FirebaseMessaging.onMessageOpenedApp.listen((message) {
          onTap?.call(message.data['route']?.toString());
        });
        final initialMessage = await messaging.getInitialMessage();
        if (initialMessage != null) {
          Future<void>.delayed(Duration.zero, () => onTap?.call(initialMessage.data['route']?.toString()));
        }
        _firebaseReady = true;
      }
      _currentToken ??= await FirebaseMessaging.instance.getToken();
      return _currentToken;
    } catch (error) {
      debugPrint('Firebase notifications are not configured yet: $error');
      return null;
    }
  }

  static Future<void> showLocal({required String title, required String body, String? payload}) async {
    if (kIsWeb) return;
    try {
      await _ensureLocal();
      const details = NotificationDetails(
        android: AndroidNotificationDetails(
          'warqna_messages',
          'Warqna messages and game alerts',
          channelDescription: 'Friends, rooms, competitions and rewards',
          importance: Importance.high,
          priority: Priority.high,
          playSound: true,
        ),
        iOS: DarwinNotificationDetails(presentAlert: true, presentBadge: true, presentSound: true),
      );
      await _local.show(
        id: DateTime.now().millisecondsSinceEpoch.remainder(2147483647),
        title: title,
        body: body,
        notificationDetails: details,
        payload: payload,
      );
    } catch (error) {
      debugPrint('Local notification skipped: $error');
    }
  }

  static Future<void> dispose() async {
    await _foregroundSubscription?.cancel();
    await _openedSubscription?.cancel();
    await _tokenSubscription?.cancel();
    _foregroundSubscription = null;
    _openedSubscription = null;
    _tokenSubscription = null;
  }
}
