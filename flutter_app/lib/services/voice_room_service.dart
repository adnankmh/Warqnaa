import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_webrtc/flutter_webrtc.dart';

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
  bool _disposed = false;
  String status = 'غير متصل';
  String? error;
  List<VoiceParticipant> participants = const [];

  MediaStream? _localStream;
  Timer? _pollTimer;
  final Map<int, RTCPeerConnection> _peers = {};
  final Map<int, List<MediaStreamTrack>> _remoteTracks = {};
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
      _localStream = await navigator.mediaDevices.getUserMedia({
        'audio': {
          'echoCancellation': true,
          'noiseSuppression': true,
          'autoGainControl': true,
        },
        'video': false,
      });

      if (!serverConnected || api.token == null || api.token!.isEmpty || code.isEmpty || code.startsWith('LOCAL')) {
        localPreview = true;
        joined = true;
        joining = false;
        status = 'الميكروفون جاهز — وضع تجريبي محلي';
        participants = const [];
        _notify();
        return;
      }

      final data = await api.voiceJoin(code);
      selfId = int.tryParse(data['self_id']?.toString() ?? '');
      _iceServers = _parseIceServers(data['ice_servers']);
      _applyParticipants(data['participants']);
      joined = true;
      joining = false;
      status = 'الصوت متصل';
      _notify();

      await _ensureOffers();
      _pollTimer = Timer.periodic(const Duration(milliseconds: 1300), (_) => _poll());
    } on ApiException catch (e) {
      await _fallbackToLocal(e.message);
    } catch (e) {
      await _fallbackToLocal('تعذر تشغيل الصوت: $e');
    }
  }

  Future<void> _fallbackToLocal(String message) async {
    localPreview = true;
    joined = _localStream != null;
    joining = false;
    error = message;
    status = joined ? 'الميكروفون جاهز محليًا — الخادم الصوتي غير متصل' : 'تعذر تشغيل الميكروفون';
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
      status = state == RTCPeerConnectionState.RTCPeerConnectionStateConnected
          ? 'الصوت متصل'
          : state == RTCPeerConnectionState.RTCPeerConnectionStateFailed
              ? 'فشل اتصال صوت أحد اللاعبين'
              : status;
      _notify();
    };

    _peers[remoteUserId] = pc;
    return pc;
  }

  Future<void> _handleSignal(Map<String, dynamic> signal) async {
    final senderId = int.tryParse(signal['sender_id']?.toString() ?? '') ?? 0;
    final type = signal['type']?.toString() ?? '';
    final payload = signal['payload'] is Map ? Map<String, dynamic>.from(signal['payload'] as Map) : <String, dynamic>{};
    if (senderId <= 0) return;
    final pc = await _peerFor(senderId);

    if (type == 'offer') {
      await pc.setRemoteDescription(RTCSessionDescription(payload['sdp']?.toString(), 'offer'));
      final answer = await pc.createAnswer({'offerToReceiveAudio': 1});
      await pc.setLocalDescription(answer);
      await api.voiceSignal(roomCode!, senderId, 'answer', {'sdp': answer.sdp, 'type': answer.type});
      return;
    }
    if (type == 'answer') {
      await pc.setRemoteDescription(RTCSessionDescription(payload['sdp']?.toString(), 'answer'));
      return;
    }
    if (type == 'candidate') {
      final candidate = payload['candidate']?.toString();
      if (candidate == null || candidate.isEmpty) return;
      await pc.addCandidate(RTCIceCandidate(
        candidate,
        payload['sdpMid']?.toString(),
        int.tryParse(payload['sdpMLineIndex']?.toString() ?? ''),
      ));
    }
  }

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
