import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';
import 'package:permission_handler/permission_handler.dart';

import 'api_client.dart';

class VoiceParticipant {
  final int userId;
  final String name;
  final String? avatar;
  final bool muted;
  final bool deafened;
  final bool online;
  final bool self;

  const VoiceParticipant({
    required this.userId,
    required this.name,
    this.avatar,
    required this.muted,
    required this.deafened,
    required this.online,
    required this.self,
  });

  factory VoiceParticipant.fromMap(Map<String, dynamic> map, int? selfId) {
    final id = int.tryParse(map['user_id']?.toString() ?? '') ?? 0;
    return VoiceParticipant(
      userId: id,
      name: map['name']?.toString() ?? 'لاعب',
      avatar: map['avatar']?.toString(),
      muted: map['muted'] == true || map['muted'] == 1,
      deafened: map['deafened'] == true || map['deafened'] == 1,
      online: map['online'] != false && map['online'] != 0,
      self: id != 0 && id == selfId,
    );
  }
}

class VoiceRoomService extends ChangeNotifier {
  VoiceRoomService({required this.api, required this.serverConnected});

  final WarqnaApiClient api;
  final bool serverConnected;

  String? roomCode;
  int? selfId;
  bool joined = false;
  bool joining = false;
  bool micEnabled = true;
  bool deafened = false;
  bool localPreview = false;
  bool permissionGranted = kIsWeb;
  bool speakerEnabled = true;
  int audioInputCount = 0;
  DateTime? lastConnectedAt;
  bool _disposed = false;
  String status = 'غير متصل';
  String? error;
  List<VoiceParticipant> participants = const [];

  MediaStream? _localStream;
  Timer? _pollTimer;
  final Map<int, RTCPeerConnection> _peers = {};
  final Map<int, List<MediaStreamTrack>> _remoteTracks = {};
  final Map<int, List<RTCIceCandidate>> _pendingCandidates = {};
  final Map<int, int> _peerFailures = {};
  final Set<int> _locallyMutedPeers = {};
  List<Map<String, dynamic>> _iceServers = const [
    {'urls': ['stun:stun.l.google.com:19302']}
  ];

  void _notify() {
    if (!_disposed) notifyListeners();
  }

  Future<void> join(String code) async {
    if (joining || joined) return;
    joining = true;
    roomCode = code;
    error = null;
    status = 'طلب إذن الميكروفون…';
    _notify();

    try {
      if (kIsWeb && Uri.base.scheme != 'https' && Uri.base.host != 'localhost' && Uri.base.host != '127.0.0.1') {
        throw StateError('الغرف الصوتية على الويب تحتاج HTTPS حتى يسمح المتصفح بالميكروفون.');
      }
      final apiUri = Uri.tryParse(api.baseUrl);
      final mobileLoopback = !kIsWeb && const {'localhost', '127.0.0.1', '10.0.2.2'}.contains(apiUri?.host);
      if (mobileLoopback && !code.startsWith('LOCAL')) {
        throw StateError('الصوت الحقيقي على الهاتف يحتاج رابط Laravel API منشوراً عبر HTTPS. افتح فحص الاتصال وأدخل رابط الخادم؛ عنوان localhost يشير إلى الهاتف نفسه.');
      }
      if (!kIsWeb) {
        final permission = await Permission.microphone.request();
        permissionGranted = permission.isGranted;
        if (!permissionGranted) {
          throw StateError(permission.isPermanentlyDenied
              ? 'إذن الميكروفون مرفوض نهائياً. افتح إعدادات التطبيق ثم اسمح بالميكروفون.'
              : 'لم يتم منح إذن الميكروفون.');
        }
      }
      try {
        final devices = await navigator.mediaDevices.enumerateDevices();
        audioInputCount = devices.where((device) => device.kind == 'audioinput').length;
      } catch (_) {
        audioInputCount = 0;
      }
      _localStream = await navigator.mediaDevices.getUserMedia({
        'audio': {
          'echoCancellation': true,
          'noiseSuppression': true,
          'autoGainControl': true,
          'channelCount': 1,
          'sampleRate': 48000,
        },
        'video': false,
      });
      final localAudioTracks = _localStream?.getAudioTracks() ?? const <MediaStreamTrack>[];
      if (localAudioTracks.isEmpty) throw StateError('لم يعثر الهاتف على مسار ميكروفون صالح.');
      for (final track in localAudioTracks) { track.enabled = true; }
      if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
        try {
          await Helper.setSpeakerphoneOn(true);
          speakerEnabled = true;
        } catch (_) {}
      }

      if (code.startsWith('LOCAL')) {
        localPreview = true;
        joined = true;
        joining = false;
        status = 'الميكروفون جاهز — وضع تجريبي محلي';
        lastConnectedAt = DateTime.now();
        participants = const [];
        _notify();
        return;
      }
      if (code.isEmpty) throw StateError('رمز الغرفة الصوتية غير موجود.');
      if (!serverConnected) throw StateError('الخادم غير متصل. افتح فحص الاتصال واضبط رابط Laravel HTTPS قبل تشغيل الصوت.');
      if (api.token == null || api.token!.isEmpty) throw StateError('سجّل الدخول إلى حسابك أولاً حتى يعمل الصوت بين اللاعبين.');

      final data = await api.voiceJoin(code);
      selfId = int.tryParse(data['self_id']?.toString() ?? '');
      _iceServers = _parseIceServers(data['ice_servers']);
      _applyParticipants(data['participants']);
      joined = true;
      joining = false;
      status = 'الصوت متصل';
      lastConnectedAt = DateTime.now();
      _notify();

      await _poll();
      await _ensureOffers();
      _pollTimer = Timer.periodic(const Duration(milliseconds: 900), (_) => unawaited(_poll()));
    } on ApiException catch (e) {
      await _fallbackToLocal(e.message);
    } catch (e) {
      await _fallbackToLocal('تعذر تشغيل الصوت: $e');
    }
  }

  Future<void> _fallbackToLocal(String message) async {
    final intentionallyLocal = roomCode?.startsWith('LOCAL') ?? false;
    localPreview = intentionallyLocal;
    joined = intentionallyLocal && _localStream != null;
    joining = false;
    error = message;
    status = intentionallyLocal
        ? (joined ? 'الميكروفون جاهز — وضع محلي' : 'تعذر تشغيل الميكروفون')
        : 'فشل الاتصال الصوتي بالخادم — اضغط إعادة المحاولة';
    _notify();
  }

  List<Map<String, dynamic>> _parseIceServers(dynamic raw) {
    if (raw is! List) return _iceServers;
    final parsed = raw.whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList();
    return parsed.isEmpty ? _iceServers : parsed;
  }

  void _applyParticipants(dynamic raw) {
    if (raw is! List) return;
    participants = raw
        .whereType<Map>()
        .map((item) => VoiceParticipant.fromMap(Map<String, dynamic>.from(item), selfId))
        .where((item) => item.userId > 0)
        .toList();
  }

  Future<void> _poll() async {
    if (!joined || localPreview || roomCode == null) return;
    try {
      final data = await api.voicePoll(roomCode!);
      _applyParticipants(data['participants']);
      final signals = data['signals'];
      if (signals is List) {
        for (final raw in signals.whereType<Map>()) {
          await _handleSignal(Map<String, dynamic>.from(raw));
        }
      }
      await _ensureOffers();
      status = 'الصوت متصل';
      error = null;
      _notify();
    } catch (e) {
      status = 'إعادة اتصال الصوت…';
      error = e.toString();
      _notify();
    }
  }

  Future<void> _ensureOffers() async {
    final mine = selfId;
    if (mine == null) return;
    for (final participant in participants) {
      if (participant.self || !participant.online || participant.userId <= 0) continue;
      if (mine < participant.userId && !_peers.containsKey(participant.userId)) {
        final pc = await _peerFor(participant.userId);
        final offer = await pc.createOffer({'offerToReceiveAudio': 1});
        await pc.setLocalDescription(offer);
        await api.voiceSignal(roomCode!, participant.userId, 'offer', {
          'sdp': offer.sdp,
          'type': offer.type,
        });
      }
    }
  }

  Future<RTCPeerConnection> _peerFor(int remoteUserId) async {
    final existing = _peers[remoteUserId];
    if (existing != null) return existing;

    final pc = await createPeerConnection({'iceServers': _iceServers});
    final stream = _localStream;
    if (stream != null) {
      for (final track in stream.getAudioTracks()) {
        await pc.addTrack(track, stream);
      }
    }

    pc.onIceCandidate = (candidate) {
      final value = candidate.candidate;
      if (value == null || value.isEmpty || roomCode == null) return;
      api.voiceSignal(roomCode!, remoteUserId, 'candidate', {
        'candidate': value,
        'sdpMid': candidate.sdpMid,
        'sdpMLineIndex': candidate.sdpMLineIndex,
      }).catchError((_) => <String, dynamic>{});
    };

    pc.onTrack = (event) {
      if (event.track.kind != 'audio') return;
      final tracks = _remoteTracks.putIfAbsent(remoteUserId, () => []);
      if (!tracks.contains(event.track)) tracks.add(event.track);
      event.track.enabled = !deafened && !_locallyMutedPeers.contains(remoteUserId);
      _notify();
    };

    pc.onConnectionState = (state) {
      if (state == RTCPeerConnectionState.RTCPeerConnectionStateConnected) {
        _peerFailures[remoteUserId] = 0;
        status = 'الصوت متصل';
        lastConnectedAt = DateTime.now();
      } else if (state == RTCPeerConnectionState.RTCPeerConnectionStateFailed ||
          state == RTCPeerConnectionState.RTCPeerConnectionStateDisconnected) {
        final failures = (_peerFailures[remoteUserId] ?? 0) + 1;
        _peerFailures[remoteUserId] = failures;
        status = failures > 2
            ? (hasTurnServer ? 'تعذر الوصول للاعب — أعد المحاولة' : 'تعذر الوصول للاعب — أضف خادم TURN')
            : 'إعادة توصيل الصوت…';
        if (failures <= 2) {
          unawaited(Future<void>.delayed(const Duration(milliseconds: 700), () => _reconnectPeer(remoteUserId, pc)));
        }
      }
      _notify();
    };

    _peers[remoteUserId] = pc;
    return pc;
  }

  Future<void> _reconnectPeer(int remoteUserId, RTCPeerConnection failedPeer) async {
    if (_disposed || !joined || localPreview) return;
    if (!identical(_peers[remoteUserId], failedPeer)) return;
    try {
      await failedPeer.close();
    } catch (_) {}
    _peers.remove(remoteUserId);
    _remoteTracks.remove(remoteUserId);
    _pendingCandidates.remove(remoteUserId);
    try {
      await _ensureOffers();
    } catch (_) {
      status = hasTurnServer ? 'إعادة الاتصال بالصوت…' : 'الصوت يحتاج خادم TURN على الشبكات المختلفة';
      _notify();
    }
  }

  Future<void> _handleSignal(Map<String, dynamic> signal) async {
    final senderId = int.tryParse(signal['sender_id']?.toString() ?? '') ?? 0;
    final type = signal['type']?.toString() ?? '';
    final payload = signal['payload'] is Map ? Map<String, dynamic>.from(signal['payload'] as Map) : <String, dynamic>{};
    if (senderId <= 0) return;
    final pc = await _peerFor(senderId);

    if (type == 'offer') {
      await pc.setRemoteDescription(RTCSessionDescription(payload['sdp']?.toString(), 'offer'));
      await _flushCandidates(senderId, pc);
      final answer = await pc.createAnswer({'offerToReceiveAudio': 1});
      await pc.setLocalDescription(answer);
      await api.voiceSignal(roomCode!, senderId, 'answer', {'sdp': answer.sdp, 'type': answer.type});
      return;
    }
    if (type == 'answer') {
      await pc.setRemoteDescription(RTCSessionDescription(payload['sdp']?.toString(), 'answer'));
      await _flushCandidates(senderId, pc);
      return;
    }
    if (type == 'candidate') {
      final candidate = payload['candidate']?.toString();
      if (candidate == null || candidate.isEmpty) return;
      final ice = RTCIceCandidate(
        candidate,
        payload['sdpMid']?.toString(),
        int.tryParse(payload['sdpMLineIndex']?.toString() ?? ''),
      );
      try {
        final remote = await pc.getRemoteDescription();
        if (remote == null) {
          _pendingCandidates.putIfAbsent(senderId, () => <RTCIceCandidate>[]).add(ice);
        } else {
          await pc.addCandidate(ice);
        }
      } catch (_) {
        _pendingCandidates.putIfAbsent(senderId, () => <RTCIceCandidate>[]).add(ice);
      }
    }
  }

  Future<void> _flushCandidates(int remoteUserId, RTCPeerConnection pc) async {
    final queued = _pendingCandidates.remove(remoteUserId) ?? const <RTCIceCandidate>[];
    for (final candidate in queued) {
      try { await pc.addCandidate(candidate); } catch (_) {}
    }
  }

  bool get hasTurnServer => _iceServers.any((server) {
    final urls=server['urls'];
    if (urls is List) return urls.any((u)=>u.toString().startsWith('turn:')||u.toString().startsWith('turns:'));
    return urls.toString().startsWith('turn:')||urls.toString().startsWith('turns:');
  });


  Future<bool> requestMicrophonePermission() async {
    if (kIsWeb) {
      permissionGranted = true;
      _notify();
      return true;
    }
    final status = await Permission.microphone.request();
    permissionGranted = status.isGranted;
    if (!permissionGranted && status.isPermanentlyDenied) {
      error = 'إذن الميكروفون مرفوض نهائياً. افتح إعدادات التطبيق.';
    }
    _notify();
    return permissionGranted;
  }

  Future<void> openPermissionSettings() async {
    if (!kIsWeb) await openAppSettings();
  }

  Future<void> setSpeakerEnabled(bool value) async {
    speakerEnabled = value;
    if (!kIsWeb && defaultTargetPlatform == TargetPlatform.android) {
      try { await Helper.setSpeakerphoneOn(value); } catch (e) { error = 'تعذر تغيير مخرج الصوت: $e'; }
    }
    _notify();
  }

  Future<void> retry() async {
    final code = roomCode;
    await leave();
    if (code != null && code.isNotEmpty) await join(code);
  }

  Map<String, String> get diagnostics => <String, String>{
    'permission': permissionGranted ? 'مسموح' : 'غير مسموح',
    'microphones': '$audioInputCount',
    'speaker': speakerEnabled ? 'مكبر الصوت' : 'سماعة المكالمات',
    'transport': localPreview ? 'محلي' : hasTurnServer ? 'TURN/STUN' : 'STUN فقط',
    'peers': '${_peers.length}',
    'lastConnected': lastConnectedAt?.toIso8601String() ?? '—',
  };

  Future<void> setMicEnabled(bool value) async {
    micEnabled = value;
    for (final track in _localStream?.getAudioTracks() ?? const <MediaStreamTrack>[]) {
      track.enabled = value;
    }
    _notify();
    if (!localPreview && roomCode != null) {
      try {
        await api.voiceControls(roomCode!, muted: !value, deafened: deafened);
      } catch (_) {}
    }
  }

  Future<void> setDeafened(bool value) async {
    deafened = value;
    for (final entry in _remoteTracks.entries) {
      final enabled = !value && !_locallyMutedPeers.contains(entry.key);
      for (final track in entry.value) {
        track.enabled = enabled;
      }
    }
    _notify();
    if (!localPreview && roomCode != null) {
      try {
        await api.voiceControls(roomCode!, muted: !micEnabled, deafened: value);
      } catch (_) {}
    }
  }

  void togglePeerMute(int userId) {
    if (_locallyMutedPeers.contains(userId)) {
      _locallyMutedPeers.remove(userId);
    } else {
      _locallyMutedPeers.add(userId);
    }
    for (final track in _remoteTracks[userId] ?? const <MediaStreamTrack>[]) {
      track.enabled = !deafened && !_locallyMutedPeers.contains(userId);
    }
    _notify();
  }

  bool isPeerMuted(int userId) => _locallyMutedPeers.contains(userId);

  Future<void> leave({bool notify = true}) async {
    _pollTimer?.cancel();
    _pollTimer = null;
    if (!localPreview && roomCode != null) {
      try {
        await api.voiceLeave(roomCode!);
      } catch (_) {}
    }
    for (final pc in _peers.values) {
      try {
        await pc.close();
      } catch (_) {}
    }
    _peers.clear();
    _pendingCandidates.clear();
    _peerFailures.clear();
    for (final track in _localStream?.getTracks() ?? const <MediaStreamTrack>[]) {
      try {
        track.stop();
      } catch (_) {}
    }
    try {
      await _localStream?.dispose();
    } catch (_) {}
    _localStream = null;
    joined = false;
    joining = false;
    participants = const [];
    status = 'غير متصل';
    if (notify) _notify();
  }

  @override
  void dispose() {
    _disposed = true;
    unawaited(leave(notify: false));
    super.dispose();
  }
}
