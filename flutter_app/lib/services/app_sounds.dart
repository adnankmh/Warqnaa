import 'dart:async';

import 'package:audioplayers/audioplayers.dart';
import 'package:flutter/foundation.dart';

/// Central, non-blocking game sound bus. Every call is fail-safe so audio can
/// never prevent the game from opening or a move from being submitted.
class AppSounds {
  AppSounds._();

  static bool enabled = true;
  static double volume = .72;
  static final Map<String, AudioPlayer> _players = <String, AudioPlayer>{};
  static final Map<String, DateTime> _lastPlayed = <String, DateTime>{};

  static Future<void> play(String cue, {double? volumeOverride, Duration throttle = const Duration(milliseconds: 45)}) async {
    if (!enabled) return;
    final now = DateTime.now();
    final previous = _lastPlayed[cue];
    if (previous != null && now.difference(previous) < throttle) return;
    _lastPlayed[cue] = now;
    try {
      final player = _players.putIfAbsent(cue, AudioPlayer.new);
      await player.stop();
      await player.play(AssetSource('sounds/$cue.wav'), volume: (volumeOverride ?? volume).clamp(0.0, 1.0).toDouble());
    } catch (error) {
      debugPrint('Sound cue $cue skipped: $error');
    }
  }

  static void fire(String cue, {double? volumeOverride}) {
    unawaited(play(cue, volumeOverride: volumeOverride));
  }

  static Future<void> dispose() async {
    for (final player in _players.values) {
      try { await player.dispose(); } catch (_) {}
    }
    _players.clear();
  }
}
