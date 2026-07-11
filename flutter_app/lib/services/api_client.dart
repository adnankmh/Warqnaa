import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? payload;

  const ApiException(this.message, {this.statusCode, this.payload});

  @override
  String toString() => message;
}

const bool warqnaProductionMode = bool.fromEnvironment('WARQNA_PRODUCTION_MODE', defaultValue: false);
const String warqnaAppVersion = String.fromEnvironment('WARQNA_APP_VERSION', defaultValue: '1.69.0');
const int warqnaAppBuild = int.fromEnvironment('WARQNA_APP_BUILD', defaultValue: 169);

class WarqnaApiClient {
  WarqnaApiClient({String? baseUrl})
      : baseUrl = (baseUrl ?? const String.fromEnvironment(
          'WARQNA_API_URL',
          defaultValue: 'http://127.0.0.1:8006/api/mobile/v1',
        ))
            .replaceAll(RegExp(r'/+$'), '');

  final String baseUrl;
  String? token;

  String get webBaseUrl => baseUrl.replaceFirst(RegExp(r'/api/mobile/v1$'), '');

  String get platform => kIsWeb
      ? 'web'
      : switch (defaultTargetPlatform) {
          TargetPlatform.iOS => 'ios',
          _ => 'android',
        };

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-Warqna-Platform': platform,
        'X-Warqna-Version': warqnaAppVersion,
        'X-Warqna-Build': '$warqnaAppBuild',
        'X-Request-ID': 'app-${DateTime.now().microsecondsSinceEpoch}',
        if (token != null && token!.isNotEmpty) 'Authorization': 'Bearer $token',
      };

  Future<Map<String, dynamic>> health() => get('/health', authenticated: false);
  Future<Map<String, dynamic>> platformConfig() => get('/app-config?platform=$platform', authenticated: false);
  Future<Map<String, dynamic>> countries() => get('/countries', authenticated: false);
  Future<Map<String, dynamic>> startSocialAuth(String provider) => post('/social-auth/start/$provider', const {}, authenticated: false);
  Future<Map<String, dynamic>> socialAuthStatus(String state) => get('/social-auth/status/$state', authenticated: false);
  Future<Map<String, dynamic>> exportAccount() => get('/account/export');
  Future<Map<String, dynamic>> sessions() => get('/account/sessions');
  Future<Map<String, dynamic>> revokeSession(int tokenId) => delete('/account/sessions/$tokenId');
  Future<Map<String, dynamic>> requestDeletion(String password, {String? reason}) =>
      post('/account/deletion-request', {'password': password, 'confirmation': true, if (reason != null && reason.trim().isNotEmpty) 'reason': reason.trim()});
  Future<Map<String, dynamic>> cancelDeletionRequest() => delete('/account/deletion-request');
  Future<Map<String, dynamic>> submitReport({int? reportedUserId, String? roomCode, int? messageId, required String category, String? details}) =>
      post('/safety/reports', {
        if (reportedUserId != null) 'reported_user_id': reportedUserId,
        if (roomCode != null && roomCode.isNotEmpty) 'room_code': roomCode,
        if (messageId != null) 'message_id': messageId,
        'category': category,
        if (details != null && details.trim().isNotEmpty) 'details': details.trim(),
      });
  Future<Map<String, dynamic>> myReports() => get('/safety/reports');

  Future<Map<String, dynamic>> forgotPassword(String email) =>
      post('/password/forgot', {'email': email}, authenticated: false);
  Future<Map<String, dynamic>> resetPassword({required String email, required String resetToken, required String password}) =>
      post('/password/reset', {
        'email': email,
        'token': resetToken,
        'password': password,
        'password_confirmation': password,
      }, authenticated: false);
  Future<Map<String, dynamic>> sendEmailVerification() =>
      post('/email/verification-notification', const {});

  Future<Map<String, dynamic>> login(String login, String password) =>
      post('/login', {'login': login, 'password': password}, authenticated: false);

  Future<Map<String, dynamic>> register({
    required String username,
    required String email,
    required String password,
  }) =>
      post(
        '/register',
        {
          'username': username,
          'email': email,
          'password': password,
          'password_confirmation': password,
          'country_code': 'PS',
        },
        authenticated: false,
      );

  Future<Map<String, dynamic>> bootstrap() => get('/bootstrap');
  Future<Map<String, dynamic>> wallet() => get('/wallet');
  Future<Map<String, dynamic>> social() => get('/social');
  Future<Map<String, dynamic>> searchPlayers(String query) =>
      get('/social/search?q=${Uri.encodeQueryComponent(query)}');
  Future<Map<String, dynamic>> requestFriend(int userId) => post('/social/friends/$userId/request', const {});
  Future<Map<String, dynamic>> respondFriend(int friendshipId, String status) =>
      post('/social/friendships/$friendshipId/respond', {'status': status});
  Future<Map<String, dynamic>> cancelFriend(int friendshipId) => delete('/social/friendships/$friendshipId');
  Future<Map<String, dynamic>> blockUser(int userId) => post('/social/users/$userId/block', const {});
  Future<Map<String, dynamic>> unblockUser(int userId) => delete('/social/users/$userId/block');
  Future<Map<String, dynamic>> chatThread(int userId) => get('/social/chat/$userId');
  Future<Map<String, dynamic>> sendMessage(int userId, String body) =>
      post('/social/chat/$userId', {'body': body});
  Future<Map<String, dynamic>> transferTokens(String receiver, int amount) =>
      post('/social/transfer', {'receiver': receiver, 'amount': amount});
  Future<Map<String, dynamic>> purchase(String key) =>
      post('/store/purchase', {'key': key, 'confirmed': true});
  Future<Map<String, dynamic>> claimDaily() => post('/rewards/daily', const {});
  Future<Map<String, dynamic>> claimRewardedAd(String verificationId) => post('/rewards/rewarded-ad', {'verification_id': verificationId, 'network': 'admob', 'reward_type': 'standard'});
  Future<Map<String, dynamic>> updateProfile(Map<String, dynamic> payload) => patch('/profile', payload);
  Future<Map<String, dynamic>> registerPushDevice(String token) => post('/push/devices', {'token': token, 'platform': platform, 'app_version': warqnaAppVersion, 'app_build': warqnaAppBuild});
  Future<Map<String, dynamic>> removePushDevice(String token) => deleteWithBody('/push/devices', {'token': token});
  Future<Map<String, dynamic>> gameCatalog() => get('/games/catalog', authenticated: false);
  Future<Map<String, dynamic>> gameRules(String key) => get('/games/$key/rules', authenticated: false);
  Future<Map<String, dynamic>> createGame({
    required String game,
    int bots = 3,
    String visibility = 'public',
    int turnSeconds = 10,
    bool voiceEnabled = false,
    String? roomName,
    String? password,
  }) =>
      post('/games/session', {
        'game': game,
        'bots': bots,
        'visibility': visibility,
        'turn_seconds': turnSeconds,
        'voice_enabled': voiceEnabled,
        if (roomName != null && roomName.trim().isNotEmpty) 'room_name': roomName.trim(),
        if (visibility == 'private' && password != null && password.isNotEmpty) 'password': password,
      });
  Future<Map<String, dynamic>> availableRooms(String game) => get('/games/$game/rooms');
  Future<Map<String, dynamic>> joinGame(String code, {String? password}) =>
      post('/games/session/$code/join', {if (password != null && password.isNotEmpty) 'password': password});
  Future<Map<String, dynamic>> gameAction(String code, String action, [Map<String, dynamic>? payload]) =>
      post('/games/session/$code/action', {'action': action, 'payload': payload ?? const {}});
  Future<Map<String, dynamic>> gameTimeout(String code) => post('/games/session/$code/timeout', const {});
  Future<Map<String, dynamic>> leaveGame(String code) => post('/games/session/$code/leave', const {});
  Future<Map<String, dynamic>> roomChat(String code) => get('/games/session/$code/chat');
  Future<Map<String, dynamic>> sendRoomChat(String code, String body) => post('/games/session/$code/chat', {'body': body});
  Future<Map<String, dynamic>> voiceJoin(String code) => post('/games/session/$code/voice/join', const {});
  Future<Map<String, dynamic>> voicePoll(String code) => get('/games/session/$code/voice/poll');
  Future<Map<String, dynamic>> voiceSignal(String code, int recipientId, String type, Map<String, dynamic> payload) =>
      post('/games/session/$code/voice/signal', {'recipient_id': recipientId, 'type': type, 'payload': payload});
  Future<Map<String, dynamic>> voiceControls(String code, {required bool muted, required bool deafened}) =>
      patch('/games/session/$code/voice/controls', {'muted': muted, 'deafened': deafened});
  Future<Map<String, dynamic>> voiceLeave(String code) => post('/games/session/$code/voice/leave', const {});
  Future<Map<String, dynamic>> adminDashboard() => get('/admin/dashboard');
  Future<Map<String, dynamic>> adminUserAction(int userId, String action, {String? amount}) =>
      post('/admin/users/$userId/action', {
        'action': action,
        if (amount != null) 'amount': amount,
      });

  Future<Map<String, dynamic>> get(String path, {bool authenticated = true}) async {
    if (authenticated) _assertToken();
    final response = await http.get(Uri.parse('$baseUrl$path'), headers: _headers).timeout(const Duration(seconds: 20));
    return _decode(response);
  }

  Future<Map<String, dynamic>> post(String path, Map<String, dynamic> body, {bool authenticated = true}) async {
    if (authenticated) _assertToken();
    final response = await http
        .post(Uri.parse('$baseUrl$path'), headers: _headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 25));
    return _decode(response);
  }

  Future<Map<String, dynamic>> patch(String path, Map<String, dynamic> body) async {
    _assertToken();
    final response = await http
        .patch(Uri.parse('$baseUrl$path'), headers: _headers, body: jsonEncode(body))
        .timeout(const Duration(seconds: 25));
    return _decode(response);
  }

  Future<Map<String, dynamic>> delete(String path) async {
    _assertToken();
    final response = await http.delete(Uri.parse('$baseUrl$path'), headers: _headers).timeout(const Duration(seconds: 20));
    return _decode(response);
  }

  Future<Map<String, dynamic>> deleteWithBody(String path, Map<String, dynamic> body) async {
    _assertToken();
    final request = http.Request('DELETE', Uri.parse('$baseUrl$path'))
      ..headers.addAll(_headers)
      ..body = jsonEncode(body);
    final streamed = await request.send().timeout(const Duration(seconds: 25));
    final response = await http.Response.fromStream(streamed);
    return _decode(response);
  }

  void _assertToken() {
    if (token == null || token!.isEmpty) {
      throw const ApiException('يجب تسجيل الدخول إلى الخادم أولاً.');
    }
  }

  Map<String, dynamic> _decode(http.Response response) {
    Map<String, dynamic> data;
    try {
      final decoded = jsonDecode(utf8.decode(response.bodyBytes));
      data = decoded is Map<String, dynamic> ? decoded : {'data': decoded};
    } catch (_) {
      data = {'message': response.body.isEmpty ? 'تعذر قراءة استجابة الخادم.' : response.body};
    }
    if (response.statusCode < 200 || response.statusCode >= 300 || data['ok'] == false) {
      final errors = data['errors'];
      String? firstError;
      if (errors is Map && errors.isNotEmpty) {
        final value = errors.values.first;
        if (value is List && value.isNotEmpty) firstError = value.first.toString();
      }
      throw ApiException(
        firstError ?? data['message']?.toString() ?? 'حدث خطأ أثناء الاتصال بالخادم.',
        statusCode: response.statusCode,
        payload: data,
      );
    }
    return data;
  }
}
