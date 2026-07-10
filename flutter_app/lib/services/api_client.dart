import 'dart:convert';

import 'package:http/http.dart' as http;

class ApiException implements Exception {
  final String message;
  final int? statusCode;
  final Map<String, dynamic>? payload;

  const ApiException(this.message, {this.statusCode, this.payload});

  @override
  String toString() => message;
}

class WarqnaApiClient {
  WarqnaApiClient({String? baseUrl})
      : baseUrl = (baseUrl ?? const String.fromEnvironment(
          'WARQNA_API_URL',
          defaultValue: 'http://127.0.0.1:8006/api/mobile/v1',
        ))
            .replaceAll(RegExp(r'/+$'), '');

  final String baseUrl;
  String? token;

  Map<String, String> get _headers => {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null && token!.isNotEmpty) 'Authorization': 'Bearer $token',
      };

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
  Future<Map<String, dynamic>> gameCatalog() => get('/games/catalog', authenticated: false);
  Future<Map<String, dynamic>> gameRules(String key) => get('/games/$key/rules', authenticated: false);
  Future<Map<String, dynamic>> createGame({
    required String game,
    int bots = 3,
    String visibility = 'private',
    int turnSeconds = 20,
  }) =>
      post('/games/session', {
        'game': game,
        'bots': bots,
        'visibility': visibility,
        'turn_seconds': turnSeconds,
      });
  Future<Map<String, dynamic>> gameAction(String code, String action, [Map<String, dynamic>? payload]) =>
      post('/games/session/$code/action', {'action': action, 'payload': payload ?? const {}});
  Future<Map<String, dynamic>> gameTimeout(String code) => post('/games/session/$code/timeout', const {});
  Future<Map<String, dynamic>> leaveGame(String code) => post('/games/session/$code/leave', const {});
  Future<Map<String, dynamic>> roomChat(String code) => get('/games/session/$code/chat');
  Future<Map<String, dynamic>> sendRoomChat(String code, String body) => post('/games/session/$code/chat', {'body': body});
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
