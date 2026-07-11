import 'dart:async';
import 'dart:io';

import 'package:flutter/foundation.dart';
import 'package:google_mobile_ads/google_mobile_ads.dart';

RewardedAd? _rewardedAd;
Future<void>? _loadingFuture;
Future<void>? _initializingFuture;
bool _sdkReady = false;

String get _rewardedId {
  if (Platform.isAndroid) {
    return const String.fromEnvironment(
      'ADMOB_REWARDED_ANDROID_ID',
      defaultValue: 'ca-app-pub-3940256099942544/5224354917',
    );
  }
  return const String.fromEnvironment(
    'ADMOB_REWARDED_IOS_ID',
    defaultValue: 'ca-app-pub-3940256099942544/1712485313',
  );
}

/// Optional and deliberately non-fatal. The first application frame is never
/// blocked by AdMob and all platform/configuration failures are swallowed.
Future<void> initializeRewardedAds() {
  if (!Platform.isAndroid && !Platform.isIOS) return Future<void>.value();
  final current = _initializingFuture;
  if (current != null) return current;

  final completer = Completer<void>();
  _initializingFuture = completer.future;
  () async {
    try {
      await MobileAds.instance.initialize().timeout(const Duration(seconds: 7));
      _sdkReady = true;
      unawaited(_loadRewarded());
    } catch (error, stack) {
      _sdkReady = false;
      debugPrint('AdMob disabled for this session: $error\n$stack');
    } finally {
      if (!completer.isCompleted) completer.complete();
    }
  }();
  return completer.future;
}

Future<void> _loadRewarded() {
  if (!_sdkReady || _rewardedAd != null) return Future<void>.value();
  final existing = _loadingFuture;
  if (existing != null) return existing;

  final completer = Completer<void>();
  _loadingFuture = completer.future;
  try {
    RewardedAd.load(
      adUnitId: _rewardedId,
      request: const AdRequest(),
      rewardedAdLoadCallback: RewardedAdLoadCallback(
        onAdLoaded: (ad) {
          _rewardedAd = ad;
          _loadingFuture = null;
          if (!completer.isCompleted) completer.complete();
        },
        onAdFailedToLoad: (error) {
          _rewardedAd = null;
          _loadingFuture = null;
          debugPrint('Rewarded ad unavailable: $error');
          if (!completer.isCompleted) completer.complete();
        },
      ),
    );
  } catch (error) {
    _loadingFuture = null;
    if (!completer.isCompleted) completer.complete();
  }
  return completer.future.timeout(
    const Duration(seconds: 10),
    onTimeout: () {
      _loadingFuture = null;
    },
  );
}

Future<bool> showRewardedAd() async {
  if (!Platform.isAndroid && !Platform.isIOS) return false;
  if (!_sdkReady) await initializeRewardedAds();
  if (!_sdkReady) return false;
  try {
    if (_rewardedAd == null) await _loadRewarded();
    final ad = _rewardedAd;
    if (ad == null) return false;

    _rewardedAd = null;
    var earned = false;
    final completer = Completer<bool>();
    ad.fullScreenContentCallback = FullScreenContentCallback(
      onAdDismissedFullScreenContent: (closedAd) {
        closedAd.dispose();
        if (!completer.isCompleted) completer.complete(earned);
        unawaited(_loadRewarded());
      },
      onAdFailedToShowFullScreenContent: (failedAd, error) {
        failedAd.dispose();
        debugPrint('Rewarded ad failed to show: $error');
        if (!completer.isCompleted) completer.complete(false);
        unawaited(_loadRewarded());
      },
    );
    ad.show(onUserEarnedReward: (_, __) => earned = true);
    return completer.future.timeout(const Duration(minutes: 2), onTimeout: () => false);
  } catch (error, stack) {
    debugPrint('Rewarded ad ignored: $error\n$stack');
    return false;
  }
}
