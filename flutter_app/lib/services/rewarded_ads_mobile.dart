import 'dart:async';
import 'dart:io';

import 'package:google_mobile_ads/google_mobile_ads.dart';

RewardedAd? _rewardedAd;
Future<void>? _loadingFuture;

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

Future<void> initializeRewardedAds() async {
  if (!Platform.isAndroid && !Platform.isIOS) return;
  await MobileAds.instance.initialize();
  await _loadRewarded();
}

Future<void> _loadRewarded() {
  if (_rewardedAd != null) return Future<void>.value();
  final existing = _loadingFuture;
  if (existing != null) return existing;

  final completer = Completer<void>();
  _loadingFuture = completer.future;
  RewardedAd.load(
    adUnitId: _rewardedId,
    request: const AdRequest(),
    rewardedAdLoadCallback: RewardedAdLoadCallback(
      onAdLoaded: (ad) {
        _rewardedAd = ad;
        _loadingFuture = null;
        if (!completer.isCompleted) completer.complete();
      },
      onAdFailedToLoad: (_) {
        _rewardedAd = null;
        _loadingFuture = null;
        if (!completer.isCompleted) completer.complete();
      },
    ),
  );
  return completer.future.timeout(
    const Duration(seconds: 12),
    onTimeout: () {
      _loadingFuture = null;
    },
  );
}

Future<bool> showRewardedAd() async {
  if (!Platform.isAndroid && !Platform.isIOS) return false;
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
    onAdFailedToShowFullScreenContent: (failedAd, _) {
      failedAd.dispose();
      if (!completer.isCompleted) completer.complete(false);
      unawaited(_loadRewarded());
    },
  );
  ad.show(onUserEarnedReward: (_, __) => earned = true);
  return completer.future;
}
