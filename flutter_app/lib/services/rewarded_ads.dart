import 'rewarded_ads_stub.dart'
    if (dart.library.io) 'rewarded_ads_mobile.dart' as implementation;

class RewardedAds {
  static Future<void> initialize() => implementation.initializeRewardedAds();
  static Future<bool> show() => implementation.showRewardedAd();
}

/// Starts the rewarded-ad SDK only after the first Flutter frame and controller load.
Future<void> initializeWarqnaRewardedAdsAfterFirstFrame() => RewardedAds.initialize();
