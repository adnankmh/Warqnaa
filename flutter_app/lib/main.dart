import 'dart:async';
import 'dart:convert';
import 'dart:math' as math;
import 'dart:typed_data';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:image_picker/image_picker.dart';

import 'engines/tarneeb_engine.dart';
import 'engines/local_game_engine.dart';
import 'services/api_client.dart';
import 'services/rewarded_ads.dart';
import 'services/voice_room_service.dart';
import 'models/room_launch_options.dart';
import 'premium_v149.dart';

part 'premium_v151.dart';
part 'production_v153.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();
  await RewardedAds.initialize();
  runApp(const WarqnaApp());
}

class WarqnaApp extends StatefulWidget {
  const WarqnaApp({super.key});

  @override
  State<WarqnaApp> createState() => _WarqnaAppState();
}

class _WarqnaAppState extends State<WarqnaApp> {
  late final AppController controller;

  @override
  void initState() {
    super.initState();
    controller = AppController()..load();
  }

  @override
  Widget build(BuildContext context) {
    return AnimatedBuilder(
      animation: controller,
      builder: (context, _) {
        final palette = AppPalette.fromCode(controller.themeCode);
        return MaterialApp(
          debugShowCheckedModeBanner: false,
          locale: Locale(controller.localeCode),
          supportedLocales: const [
            Locale('ar'),
            Locale('en'),
            Locale('de'),
            Locale('tr'),
            Locale('fr'),
            Locale('es'),
          ],
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          builder: (context, child) {
            final current = MediaQuery.textScalerOf(context).scale(1.0);
            final safeScale = (current * controller.uiFontScale).clamp(.85, 1.28).toDouble();
            return MediaQuery(
              data: MediaQuery.of(context).copyWith(textScaler: TextScaler.linear(safeScale)),
              child: ClipRect(child: child ?? const SizedBox.shrink()),
            );
          },
          theme: ThemeData(
            useMaterial3: true,
            brightness: Brightness.dark,
            scaffoldBackgroundColor: palette.bg,
            colorScheme: ColorScheme.dark(
              primary: colorFromHex(controller.uiAccentHex),
              secondary: palette.green,
              surface: palette.panel,
              error: const Color(0xffd94f4f),
            ),
            navigationBarTheme: NavigationBarThemeData(
              backgroundColor: palette.panel.withOpacity(.96),
              indicatorColor: palette.gold.withOpacity(.16),
              labelTextStyle: WidgetStateProperty.resolveWith(
                (states) => TextStyle(
                  fontWeight: FontWeight.w800,
                  fontSize: 10,
                  color: states.contains(WidgetState.selected)
                      ? palette.gold
                      : palette.muted,
                ),
              ),
            ),
            inputDecorationTheme: InputDecorationTheme(
              filled: true,
              fillColor: palette.panel2,
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()),
                borderSide: BorderSide(color: Colors.white.withOpacity(.08)),
              ),
              enabledBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()),
                borderSide: BorderSide(color: Colors.white.withOpacity(.08)),
              ),
            ),
            filledButtonTheme: FilledButtonThemeData(
              style: FilledButton.styleFrom(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()),
                ),
                minimumSize: Size(48, controller.uiButtonHeight),
                textStyle: const TextStyle(fontWeight: FontWeight.w900),
              ),
            ),
            fontFamily: 'Arial',
          ),
          home: !controller.ready
              ? const AppLoadingScreen()
              : controller.isAuthenticated
                  ? HomeShell(controller: controller)
                  : LoginScreen(controller: controller),
        );
      },
    );
  }
}

class AppController extends ChangeNotifier {
  final WarqnaApiClient api = WarqnaApiClient();

  bool ready = false;
  bool isAuthenticated = false;
  bool isAdmin = false;
  bool serverConnected = false;
  bool soundEnabled = true;
  bool landscapeMode = false;
  String selectedTable = 'table_premium_01';
  String selectedCardBack = 'cardback_01';
  String selectedNameColor = '#facc15';
  String selectedChatColor = '#ffffff';
  DateTime? nameColorExpiresAt;
  DateTime? chatColorExpiresAt;
  String avatarEmoji = '🦁';
  String? avatarData;
  String selectedBadge = 'badge_pro';
  String selectedEmojiPack = 'emoji_free_basic';
  String selectedEffect = 'effect_gold_entry';
  String selectedCover = 'cover_royal_gold';
  String botDifficultyCode = 'pro';
  double uiButtonHeight = 48;
  double uiRadius = 18;
  double uiFontScale = 1.0;
  double uiChatScale = 1.0;
  String uiAccentHex = '#ffcf67';
  bool tableAmbientEffects = true;
  bool awayMode = false;
  final Map<String, int> gameExitCounts = <String, int>{};
  int gamesPlayed = 842;
  int wins = 514;
  double activeXpMultiplier = 1.0;
  int giftRoadProgress = 5;
  final Set<int> claimedGiftSteps = <int>{};
  String? activeCompetition;
  String? activeChallenge;
  String? activeGame;
  final Set<String> rewardedMatches = <String>{};
  String localeCode = 'ar';
  String themeCode = 'dark';
  String username = 'Adnan';
  String displayName = 'Adnan';
  String email = 'adnan@warqna.local';
  String? authToken;
  BigInt coins = BigInt.from(125680);
  int level = 28;
  int xp = 18560;
  int xpNext = 25000;
  int vipDays = 12;
  int consecutiveLoginDays = 0;
  String? lastLoginDate;
  String? lastDailyClaimDate;
  int rewardedAdClaimsToday = 0;
  String? rewardedAdClaimDate;
  String? activeClub;
  final Set<String> owned = {'emoji_fun'};
  final Map<String, int> storePriceOverrides = <String, int>{};
  final Map<String, String> storeNameOverrides = <String, String>{};
  final Map<String, String> storeDescriptionOverrides = <String, String>{};
  final Map<String, int> storeDurationOverrides = <String, int>{};
  final Map<String, String> storeColor1Overrides = <String, String>{};
  final Map<String, String> storeColor2Overrides = <String, String>{};
  final Set<String> hiddenStoreProducts = <String>{};
  final List<AppNotice> notices = [
    AppNotice('🏆', 'بدأ التسجيل في بطولة الأبطال', 'المقاعد محدودة والتسجيل متاح الآن.'),
    AppNotice('🎁', 'مكافأتك اليومية جاهزة', 'استلم 100 توكن و20 نقطة خبرة.'),
    AppNotice('👤', 'أرسل سامر طلب صداقة', 'يمكنك القبول من مركز الأصدقاء.'),
  ];
  final List<TokenTransaction> transactions = [
    const TokenTransaction('مكافأة يومية', 100, 'اليوم 09:12'),
    const TokenTransaction('شراء حزمة المرح', -4200, 'أمس 18:27'),
  ];
  final List<LocalFriend> friends = [
    LocalFriend(2, 'سامر', 'Samer', online: true, activity: 'يلعب طرنيب'),
    LocalFriend(3, 'ليلى', 'Layla', online: true, activity: 'في المتجر'),
    LocalFriend(4, 'جميل', 'Jameel', online: false, activity: 'آخر ظهور قبل ساعة'),
  ];
  final List<LocalFriend> incomingRequests = [
    LocalFriend(5, 'نور', 'Noor', online: true, activity: 'طلب صداقة جديد'),
  ];
  final List<LocalFriend> outgoingRequests = [];
  final List<LocalFriend> blocked = [];
  final Map<int, List<ChatMessage>> privateChats = {
    2: [
      ChatMessage('سامر', 'أهلاً بك، هل نبدأ مباراة طرنيب؟', false, '09:10'),
      ChatMessage('Adnan', 'نعم، أرسل الدعوة.', true, '09:11'),
    ],
  };

  String get _accountPrefix => 'warqna.account.${username.trim().toLowerCase()}.';
  String _accountKey(String key) => '$_accountPrefix$key';

  Future<void> _loadAccountState(
    SharedPreferences prefs, {
    String? defaultCoins,
    int? defaultLevel,
    int? defaultVipDays,
    String? defaultAvatar,
  }) async {
    final initializedKey = _accountKey('initialized');
    final initialized = prefs.getBool(initializedKey) ?? false;
    if (!initialized) {
      coins = BigInt.tryParse(defaultCoins ?? '') ?? BigInt.from(1500);
      level = defaultLevel ?? 1;
      xp = level <= 1 ? 0 : level * 620;
      xpNext = xpNeededForLevel(level);
      vipDays = defaultVipDays ?? 0;
      avatarEmoji = defaultAvatar ?? demoAvatarFor(username);
      avatarData = null;
      selectedTable = 'table_premium_01';
      selectedCardBack = 'cardback_01';
      selectedNameColor = '#facc15';
      selectedChatColor = '#ffffff';
      selectedBadge = 'badge_pro';
      selectedEmojiPack = 'emoji_free_basic';
      selectedEffect = 'effect_gold_entry';
      selectedCover = 'cover_royal_gold';
      activeClub = null;
      activeCompetition = null;
      activeChallenge = null;
      activeGame = null;
      owned
        ..clear()
        ..add('emoji_fun');
      gameExitCounts.clear();
      await prefs.setBool(initializedKey, true);
      await _saveAccountState(prefs);
      return;
    }

    displayName = prefs.getString(_accountKey('displayName')) ?? displayName;
    email = prefs.getString(_accountKey('email')) ?? email;
    coins = BigInt.tryParse(prefs.getString(_accountKey('coins')) ?? '') ?? BigInt.from(1500);
    level = prefs.getInt(_accountKey('level')) ?? defaultLevel ?? 1;
    xp = prefs.getInt(_accountKey('xp')) ?? (level <= 1 ? 0 : level * 620);
    xpNext = prefs.getInt(_accountKey('xpNext')) ?? xpNeededForLevel(level);
    vipDays = prefs.getInt(_accountKey('vipDays')) ?? defaultVipDays ?? 0;
    avatarEmoji = prefs.getString(_accountKey('avatarEmoji')) ?? defaultAvatar ?? demoAvatarFor(username);
    avatarData = prefs.getString(_accountKey('avatarData'));
    selectedTable = prefs.getString(_accountKey('selectedTable')) ?? 'table_premium_01';
    selectedCardBack = prefs.getString(_accountKey('selectedCardBack')) ?? 'cardback_01';
    selectedNameColor = prefs.getString(_accountKey('selectedNameColor')) ?? '#facc15';
    selectedChatColor = prefs.getString(_accountKey('selectedChatColor')) ?? '#ffffff';
    nameColorExpiresAt = DateTime.tryParse(prefs.getString(_accountKey('nameColorExpiresAt')) ?? '');
    chatColorExpiresAt = DateTime.tryParse(prefs.getString(_accountKey('chatColorExpiresAt')) ?? '');
    selectedBadge = prefs.getString(_accountKey('selectedBadge')) ?? 'badge_pro';
    selectedEmojiPack = prefs.getString(_accountKey('selectedEmojiPack')) ?? 'emoji_free_basic';
    selectedEffect = prefs.getString(_accountKey('selectedEffect')) ?? 'effect_gold_entry';
    selectedCover = prefs.getString(_accountKey('selectedCover')) ?? 'cover_royal_gold';
    activeXpMultiplier = prefs.getDouble(_accountKey('activeXpMultiplier')) ?? 1.0;
    gamesPlayed = prefs.getInt(_accountKey('gamesPlayed')) ?? 0;
    wins = prefs.getInt(_accountKey('wins')) ?? 0;
    giftRoadProgress = prefs.getInt(_accountKey('giftRoadProgress')) ?? 0;
    consecutiveLoginDays = prefs.getInt(_accountKey('consecutiveLoginDays')) ?? 0;
    lastLoginDate = prefs.getString(_accountKey('lastLoginDate'));
    lastDailyClaimDate = prefs.getString(_accountKey('lastDailyClaimDate'));
    activeClub = prefs.getString(_accountKey('activeClub'));
    activeCompetition = prefs.getString(_accountKey('activeCompetition'));
    activeChallenge = prefs.getString(_accountKey('activeChallenge'));
    activeGame = prefs.getString(_accountKey('activeGame'));
    owned
      ..clear()
      ..addAll(prefs.getStringList(_accountKey('owned')) ?? const <String>['emoji_fun']);
    gameExitCounts
      ..clear()
      ..addAll(decodeIntMap(prefs.getString(_accountKey('gameExitCounts'))));
    _normalizeTimedCosmetics();
  }

  Future<void> _saveAccountState(SharedPreferences prefs) async {
    if (username.trim().isEmpty) return;
    await prefs.setBool(_accountKey('initialized'), true);
    await prefs.setString(_accountKey('coins'), coins.toString());
    await prefs.setInt(_accountKey('level'), level);
    await prefs.setInt(_accountKey('xp'), xp);
    await prefs.setInt(_accountKey('xpNext'), xpNext);
    await prefs.setInt(_accountKey('vipDays'), vipDays);
    await prefs.setString(_accountKey('displayName'), displayName);
    await prefs.setString(_accountKey('email'), email);
    await prefs.setString(_accountKey('avatarEmoji'), avatarEmoji);
    if (avatarData == null) { await prefs.remove(_accountKey('avatarData')); } else { await prefs.setString(_accountKey('avatarData'), avatarData!); }
    await prefs.setString(_accountKey('selectedTable'), selectedTable);
    await prefs.setString(_accountKey('selectedCardBack'), selectedCardBack);
    await prefs.setString(_accountKey('selectedNameColor'), selectedNameColor);
    await prefs.setString(_accountKey('selectedChatColor'), selectedChatColor);
    if (nameColorExpiresAt == null) { await prefs.remove(_accountKey('nameColorExpiresAt')); } else { await prefs.setString(_accountKey('nameColorExpiresAt'), nameColorExpiresAt!.toIso8601String()); }
    if (chatColorExpiresAt == null) { await prefs.remove(_accountKey('chatColorExpiresAt')); } else { await prefs.setString(_accountKey('chatColorExpiresAt'), chatColorExpiresAt!.toIso8601String()); }
    await prefs.setString(_accountKey('selectedBadge'), selectedBadge);
    await prefs.setString(_accountKey('selectedEmojiPack'), selectedEmojiPack);
    await prefs.setString(_accountKey('selectedEffect'), selectedEffect);
    await prefs.setString(_accountKey('selectedCover'), selectedCover);
    await prefs.setDouble(_accountKey('activeXpMultiplier'), activeXpMultiplier);
    await prefs.setInt(_accountKey('gamesPlayed'), gamesPlayed);
    await prefs.setInt(_accountKey('wins'), wins);
    await prefs.setInt(_accountKey('giftRoadProgress'), giftRoadProgress);
    await prefs.setInt(_accountKey('consecutiveLoginDays'), consecutiveLoginDays);
    if (lastLoginDate == null) { await prefs.remove(_accountKey('lastLoginDate')); } else { await prefs.setString(_accountKey('lastLoginDate'), lastLoginDate!); }
    if (lastDailyClaimDate == null) { await prefs.remove(_accountKey('lastDailyClaimDate')); } else { await prefs.setString(_accountKey('lastDailyClaimDate'), lastDailyClaimDate!); }
    if (activeClub == null) { await prefs.remove(_accountKey('activeClub')); } else { await prefs.setString(_accountKey('activeClub'), activeClub!); }
    if (activeCompetition == null) { await prefs.remove(_accountKey('activeCompetition')); } else { await prefs.setString(_accountKey('activeCompetition'), activeCompetition!); }
    if (activeChallenge == null) { await prefs.remove(_accountKey('activeChallenge')); } else { await prefs.setString(_accountKey('activeChallenge'), activeChallenge!); }
    if (activeGame == null) { await prefs.remove(_accountKey('activeGame')); } else { await prefs.setString(_accountKey('activeGame'), activeGame!); }
    await prefs.setStringList(_accountKey('owned'), owned.toList());
    await prefs.setString(_accountKey('gameExitCounts'), jsonEncode(gameExitCounts));
  }

  Future<String?> _registerLocal(String user, String mail, String password) async {
    final normalized = user.trim().toLowerCase();
    if (normalized.length < 3) return 'اسم المستخدم يجب أن يتكون من 3 أحرف على الأقل.';
    if (password.length < 6) return 'كلمة المرور يجب أن تتكون من 6 أحرف على الأقل.';
    final prefs = await SharedPreferences.getInstance();
    final prefix = 'warqna.account.$normalized.';
    if ((prefs.getBool('${prefix}initialized') ?? false) || demoAccounts.containsKey(normalized)) {
      return 'اسم المستخدم مستخدم مسبقاً.';
    }
    await prefs.setString('${prefix}localPassword', base64Encode(utf8.encode(password)));
    username = user.trim();
    displayName = user.trim();
    email = mail.trim().isEmpty ? '$normalized@warqna.local' : mail.trim();
    isAdmin = false;
    isAuthenticated = true;
    serverConnected = false;
    authToken = null;
    await _loadAccountState(prefs, defaultCoins: '1500', defaultLevel: 1, defaultVipDays: 0);
    _applyLocalLoginStreak();
    await _save();
    notifyListeners();
    return null;
  }

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();
    localeCode = prefs.getString('locale') ?? localeCode;
    themeCode = prefs.getString('theme') ?? themeCode;
    final storedCoins = prefs.getString('coins');
    if (storedCoins != null) coins = BigInt.tryParse(storedCoins) ?? coins;
    vipDays = prefs.getInt('vipDays') ?? vipDays;
    activeClub = prefs.getString('activeClub');
    soundEnabled = prefs.getBool('soundEnabled') ?? soundEnabled;
    landscapeMode = prefs.getBool('landscapeMode') ?? landscapeMode;
    selectedTable = prefs.getString('selectedTable') ?? selectedTable;
    selectedCardBack = prefs.getString('selectedCardBack') ?? selectedCardBack;
    selectedNameColor = prefs.getString('selectedNameColor') ?? selectedNameColor;
    selectedChatColor = prefs.getString('selectedChatColor') ?? selectedChatColor;
    nameColorExpiresAt = DateTime.tryParse(prefs.getString('nameColorExpiresAt') ?? '');
    chatColorExpiresAt = DateTime.tryParse(prefs.getString('chatColorExpiresAt') ?? '');
    avatarEmoji = prefs.getString('avatarEmoji') ?? avatarEmoji;
    avatarData = prefs.getString('avatarData');
    selectedBadge = prefs.getString('selectedBadge') ?? selectedBadge;
    selectedEmojiPack = prefs.getString('selectedEmojiPack') ?? selectedEmojiPack;
    selectedEffect = prefs.getString('selectedEffect') ?? selectedEffect;
    selectedCover = prefs.getString('selectedCover') ?? selectedCover;
    botDifficultyCode = prefs.getString('botDifficultyCode') ?? botDifficultyCode;
    uiButtonHeight = prefs.getDouble('uiButtonHeight') ?? uiButtonHeight;
    uiRadius = prefs.getDouble('uiRadius') ?? uiRadius;
    uiFontScale = prefs.getDouble('uiFontScale') ?? uiFontScale;
    uiChatScale = prefs.getDouble('uiChatScale') ?? uiChatScale;
    uiAccentHex = prefs.getString('uiAccentHex') ?? uiAccentHex;
    tableAmbientEffects = prefs.getBool('tableAmbientEffects') ?? tableAmbientEffects;
    gamesPlayed = prefs.getInt('gamesPlayed') ?? gamesPlayed;
    wins = prefs.getInt('wins') ?? wins;
    activeXpMultiplier = prefs.getDouble('activeXpMultiplier') ?? activeXpMultiplier;
    giftRoadProgress = prefs.getInt('giftRoadProgress') ?? giftRoadProgress;
    consecutiveLoginDays = prefs.getInt('consecutiveLoginDays') ?? consecutiveLoginDays;
    lastLoginDate = prefs.getString('lastLoginDate');
    lastDailyClaimDate = prefs.getString('lastDailyClaimDate');
    rewardedAdClaimsToday = prefs.getInt('rewardedAdClaimsToday') ?? 0;
    rewardedAdClaimDate = prefs.getString('rewardedAdClaimDate');
    _normalizeTimedCosmetics();
    _resetAdCounterIfNeeded();
    claimedGiftSteps
      ..clear()
      ..addAll((prefs.getStringList('claimedGiftSteps') ?? const <String>[]).map(int.parse));
    activeCompetition = prefs.getString('activeCompetition');
    activeChallenge = prefs.getString('activeChallenge');
    activeGame = prefs.getString('activeGame');
    owned
      ..clear()
      ..addAll(prefs.getStringList('owned') ?? const ['emoji_fun']);
    storePriceOverrides
      ..clear()
      ..addAll(decodeIntMap(prefs.getString('storePriceOverrides')));
    storeNameOverrides
      ..clear()
      ..addAll(decodeStringMapV151(prefs.getString('storeNameOverrides')));
    storeDescriptionOverrides
      ..clear()
      ..addAll(decodeStringMapV151(prefs.getString('storeDescriptionOverrides')));
    storeDurationOverrides
      ..clear()
      ..addAll(decodeIntMap(prefs.getString('storeDurationOverrides')));
    storeColor1Overrides
      ..clear()
      ..addAll(decodeStringMapV151(prefs.getString('storeColor1Overrides')));
    storeColor2Overrides
      ..clear()
      ..addAll(decodeStringMapV151(prefs.getString('storeColor2Overrides')));
    hiddenStoreProducts
      ..clear()
      ..addAll(prefs.getStringList('hiddenStoreProducts') ?? const <String>[]);
    authToken = prefs.getString('authToken');
    final offlineLoggedIn = prefs.getBool('offlineLoggedIn') ?? false;
    if (authToken != null && authToken!.isNotEmpty) {
      api.token = authToken;
      try {
        final data = await api.bootstrap();
        _applySession(data);
        isAuthenticated = true;
        serverConnected = true;
      } catch (_) {
        authToken = null;
        api.token = null;
      }
    }
    if (!isAuthenticated && offlineLoggedIn && !warqnaProductionMode) {
      username = prefs.getString('username') ?? username;
      displayName = prefs.getString('displayName') ?? username;
      email = prefs.getString('email') ?? email;
      isAdmin = prefs.getBool('isAdmin') ?? username.toLowerCase() == 'adnan';
      final seed = demoAccounts[username.toLowerCase()];
      await _loadAccountState(
        prefs,
        defaultCoins: seed?['coins']?.toString(),
        defaultLevel: int.tryParse(seed?['level']?.toString() ?? ''),
        defaultVipDays: isAdmin ? 1000 : 0,
        defaultAvatar: demoAvatarFor(username),
      );
      if (isAdmin) {
        coins = BigInt.parse('1000000000000000000');
        level = math.max(level, 90);
        vipDays = math.max(vipDays, 1000);
      }
      isAuthenticated = true;
      serverConnected = false;
      _applyLocalLoginStreak();
    }
    ready = true;
    notifyListeners();
  }

  Future<void> _save() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('locale', localeCode);
    await prefs.setString('theme', themeCode);
    await prefs.setString('coins', coins.toString());
    await prefs.setInt('vipDays', vipDays);
    await prefs.setBool('soundEnabled', soundEnabled);
    await prefs.setBool('landscapeMode', landscapeMode);
    await prefs.setString('selectedTable', selectedTable);
    await prefs.setString('selectedCardBack', selectedCardBack);
    await prefs.setString('selectedNameColor', selectedNameColor);
    await prefs.setString('selectedChatColor', selectedChatColor);
    if (nameColorExpiresAt == null) { await prefs.remove('nameColorExpiresAt'); } else { await prefs.setString('nameColorExpiresAt', nameColorExpiresAt!.toIso8601String()); }
    if (chatColorExpiresAt == null) { await prefs.remove('chatColorExpiresAt'); } else { await prefs.setString('chatColorExpiresAt', chatColorExpiresAt!.toIso8601String()); }
    await prefs.setString('avatarEmoji', avatarEmoji);
    if (avatarData == null) { await prefs.remove('avatarData'); } else { await prefs.setString('avatarData', avatarData!); }
    await prefs.setString('selectedBadge', selectedBadge);
    await prefs.setString('selectedEmojiPack', selectedEmojiPack);
    await prefs.setString('selectedEffect', selectedEffect);
    await prefs.setString('selectedCover', selectedCover);
    await prefs.setString('botDifficultyCode', botDifficultyCode);
    await prefs.setDouble('uiButtonHeight', uiButtonHeight);
    await prefs.setDouble('uiRadius', uiRadius);
    await prefs.setDouble('uiFontScale', uiFontScale);
    await prefs.setDouble('uiChatScale', uiChatScale);
    await prefs.setString('uiAccentHex', uiAccentHex);
    await prefs.setBool('tableAmbientEffects', tableAmbientEffects);
    await prefs.setInt('gamesPlayed', gamesPlayed);
    await prefs.setInt('wins', wins);
    await prefs.setDouble('activeXpMultiplier', activeXpMultiplier);
    await prefs.setInt('giftRoadProgress', giftRoadProgress);
    await prefs.setInt('consecutiveLoginDays', consecutiveLoginDays);
    if (lastLoginDate == null) { await prefs.remove('lastLoginDate'); } else { await prefs.setString('lastLoginDate', lastLoginDate!); }
    if (lastDailyClaimDate == null) { await prefs.remove('lastDailyClaimDate'); } else { await prefs.setString('lastDailyClaimDate', lastDailyClaimDate!); }
    await prefs.setInt('rewardedAdClaimsToday', rewardedAdClaimsToday);
    if (rewardedAdClaimDate == null) { await prefs.remove('rewardedAdClaimDate'); } else { await prefs.setString('rewardedAdClaimDate', rewardedAdClaimDate!); }
    await prefs.setStringList('claimedGiftSteps', claimedGiftSteps.map((e) => '$e').toList());
    if (activeCompetition == null) { await prefs.remove('activeCompetition'); } else { await prefs.setString('activeCompetition', activeCompetition!); }
    if (activeChallenge == null) { await prefs.remove('activeChallenge'); } else { await prefs.setString('activeChallenge', activeChallenge!); }
    if (activeGame == null) { await prefs.remove('activeGame'); } else { await prefs.setString('activeGame', activeGame!); }
    await prefs.setStringList('owned', owned.toList());
    await prefs.setString('storePriceOverrides', jsonEncode(storePriceOverrides));
    await prefs.setString('storeNameOverrides', jsonEncode(storeNameOverrides));
    await prefs.setString('storeDescriptionOverrides', jsonEncode(storeDescriptionOverrides));
    await prefs.setString('storeDurationOverrides', jsonEncode(storeDurationOverrides));
    await prefs.setString('storeColor1Overrides', jsonEncode(storeColor1Overrides));
    await prefs.setString('storeColor2Overrides', jsonEncode(storeColor2Overrides));
    await prefs.setStringList('hiddenStoreProducts', hiddenStoreProducts.toList());
    await prefs.setBool('offlineLoggedIn', isAuthenticated && !serverConnected);
    await prefs.setString('username', username);
    await prefs.setString('displayName', displayName);
    await prefs.setString('email', email);
    await prefs.setBool('isAdmin', isAdmin);
    if (authToken == null) {
      await prefs.remove('authToken');
    } else {
      await prefs.setString('authToken', authToken!);
    }
    if (activeClub == null) {
      await prefs.remove('activeClub');
    } else {
      await prefs.setString('activeClub', activeClub!);
    }
    await _saveAccountState(prefs);
  }

  Future<String?> login(String login, String password, {bool offline = false}) async {
    if (login.trim().isEmpty || password.isEmpty) return 'أدخل اسم المستخدم وكلمة المرور.';
    final loopbackApi = api.baseUrl.contains('127.0.0.1') || api.baseUrl.contains('localhost');
    if (!offline && kIsWeb && loopbackApi) {
      return this.login(login, password, offline: true);
    }
    if (offline && warqnaProductionMode) return 'وضع الدخول المحلي معطل في نسخة الإنتاج.';
    if (offline) {
      final normalized = login.trim().toLowerCase();
      var user = demoAccounts[normalized];
      final prefs = await SharedPreferences.getInstance();
      if (user == null && (prefs.getBool('warqna.account.$normalized.initialized') ?? false)) {
        final stored = prefs.getString('warqna.account.$normalized.localPassword');
        if (stored == base64Encode(utf8.encode(password))) {
          user = <String, Object>{
            'password': password,
            'name': prefs.getString('warqna.account.$normalized.displayName') ?? login.trim(),
            'coins': prefs.getString('warqna.account.$normalized.coins') ?? '1500',
            'admin': false,
            'level': prefs.getInt('warqna.account.$normalized.level') ?? 1,
          };
        }
      }
      if (user == null || user['password'] != password) {
        return 'بيانات الدخول المحلي غير صحيحة. استخدم أحد حسابات التجربة المرفقة.';
      }
      username = login.trim();
      displayName = user['name']!.toString();
      email = '$normalized@warqna.local';
      isAdmin = user['admin'] == true;
      coins = BigInt.parse(user['coins']!.toString());
      level = int.tryParse(user['level']!.toString()) ?? 1;
      if (isAdmin) level = math.max(level, 90);
      xp = isAdmin ? 0 : level * 620;
      xpNext = xpNeededForLevel(level);
      await _loadAccountState(
        prefs,
        defaultCoins: user['coins']!.toString(),
        defaultLevel: int.tryParse(user['level']!.toString()) ?? 1,
        defaultVipDays: isAdmin ? 1000 : 0,
        defaultAvatar: demoAvatarFor(username),
      );
      if (isAdmin) {
        coins = BigInt.parse('1000000000000000000');
        level = math.max(level, 90);
        vipDays = math.max(vipDays, 1000);
      }
      isAuthenticated = true;
      serverConnected = false;
      authToken = null;
      _applyLocalLoginStreak();
      await _save();
      notifyListeners();
      return null;
    }
    try {
      final data = await api.login(login.trim(), password);
      authToken = data['token']?.toString();
      api.token = authToken;
      _applySession(data);
      final streak = data['streak_reward'];
      if (streak is Map) {
        consecutiveLoginDays = int.tryParse(streak['streak']?.toString() ?? '') ?? consecutiveLoginDays;
        final awarded = int.tryParse(streak['pasha_awarded']?.toString() ?? '') ?? 0;
        if (awarded > 0) notices.insert(0, AppNotice('🎩', 'مكافأة الاستمرارية', 'حصلت على يوم باشا مجاني بعد 3 أيام دخول متواصلة.'));
      }
      isAuthenticated = true;
      serverConnected = true;
      await _save();
      notifyListeners();
      return null;
    } on ApiException catch (e) {
      final fallback = await this.login(login, password, offline: true);
      if (fallback == null) {
        notices.insert(0, AppNotice('📱', 'دخول محلي', 'تم فتح الحساب محلياً لأن خادم Laravel غير متاح.'));
        return null;
      }
      return e.message;
    } catch (_) {
      final fallback = await this.login(login, password, offline: true);
      if (fallback == null) {
        notices.insert(0, AppNotice('📱', 'دخول محلي', 'تم فتح الحساب محلياً لأن خادم Laravel غير متاح.'));
        return null;
      }
      return 'تعذر الاتصال بالخادم، ولم يتم العثور على حساب محلي بهذه البيانات.';
    }
  }

  Future<String?> register(String user, String mail, String password) async {
    final loopbackApi = api.baseUrl.contains('127.0.0.1') || api.baseUrl.contains('localhost');
    if (kIsWeb && loopbackApi && !warqnaProductionMode) return _registerLocal(user, mail, password);
    if (warqnaProductionMode && loopbackApi) return 'يجب ضبط رابط الخادم الحقيقي قبل تشغيل نسخة الإنتاج.';
    try {
      final data = await api.register(username: user.trim(), email: mail.trim(), password: password);
      authToken = data['token']?.toString();
      api.token = authToken;
      _applySession(data);
      isAuthenticated = true;
      serverConnected = true;
      await _save();
      notifyListeners();
      return null;
    } on ApiException catch (e) {
      if (warqnaProductionMode) return e.message;
      return _registerLocal(user, mail, password);
    } catch (_) {
      if (warqnaProductionMode) return 'تعذر إنشاء الحساب بسبب عدم توفر الخادم.';
      return _registerLocal(user, mail, password);
    }
  }

  Future<void> loginAsGuest() async {
    final stamp = DateTime.now().millisecondsSinceEpoch.toString();
    username = 'Guest${stamp.substring(stamp.length - 5)}';
    displayName = localeCode == 'ar' ? 'ضيف جديد' : 'New Guest';
    email = '$username@guest.warqna.local';
    isAdmin = false;
    isAuthenticated = true;
    serverConnected = false;
    authToken = null;
    coins = BigInt.from(500);
    level = 1;
    xp = 0;
    xpNext = xpNeededForLevel(level);
    vipDays = 0;
    _applyLocalLoginStreak();
    await _save();
    notifyListeners();
  }

  Future<void> logout() async {
    if (serverConnected) {
      try {
        await api.post('/logout', const {});
      } catch (_) {}
    }
    isAuthenticated = false;
    serverConnected = false;
    isAdmin = false;
    authToken = null;
    api.token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('authToken');
    await prefs.setBool('offlineLoggedIn', false);
    notifyListeners();
  }

  void _applySession(Map<String, dynamic> data) {
    final user = data['user'];
    final wallet = data['wallet'];
    if (user is Map) {
      username = user['username']?.toString() ?? username;
      displayName = user['display_name']?.toString() ?? user['name']?.toString() ?? username;
      email = user['email']?.toString() ?? email;
      avatarEmoji = user['avatar']?.toString() ?? avatarEmoji;
      avatarData = user['avatar_data']?.toString().isNotEmpty == true ? user['avatar_data']?.toString() : avatarData;
      selectedNameColor = user['name_color']?.toString() ?? selectedNameColor;
      selectedChatColor = user['chat_color']?.toString() ?? selectedChatColor;
      nameColorExpiresAt = DateTime.tryParse(user['name_color_expires_at']?.toString() ?? '');
      chatColorExpiresAt = DateTime.tryParse(user['chat_color_expires_at']?.toString() ?? '');
      consecutiveLoginDays = int.tryParse(user['login_streak']?.toString() ?? '') ?? consecutiveLoginDays;
      isAdmin = user['is_admin'] == true || user['is_admin'] == 1;
      level = int.tryParse(user['level']?.toString() ?? '') ?? level;
      xp = int.tryParse(user['xp']?.toString() ?? '') ?? xp;
      vipDays = int.tryParse(user['pasha_days']?.toString() ?? '') ?? vipDays;
      selectedCover = user['active_cover']?.toString() ?? selectedCover;
      botDifficultyCode = user['bot_difficulty']?.toString() ?? botDifficultyCode;
      final uiPreferences = user['ui_preferences'];
      if (uiPreferences is Map) {
        uiButtonHeight = double.tryParse(uiPreferences['button_height']?.toString() ?? '') ?? uiButtonHeight;
        uiRadius = double.tryParse(uiPreferences['radius']?.toString() ?? '') ?? uiRadius;
        uiFontScale = double.tryParse(uiPreferences['font_scale']?.toString() ?? '') ?? uiFontScale;
        tableAmbientEffects = uiPreferences['ambient_effects'] == true || uiPreferences['ambient_effects'] == 1;
      }
      gamesPlayed = int.tryParse(user['games_played']?.toString() ?? '') ?? gamesPlayed;
      wins = int.tryParse(user['wins']?.toString() ?? '') ?? wins;
      xpNext = xpNeededForLevel(level);
    }
    if (wallet is Map) {
      coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
    }
    if (isAdmin && username.toLowerCase() == 'adnan') {
      if (coins < BigInt.parse('1000000000000000000')) coins = BigInt.parse('1000000000000000000');
      level = math.max(level, 90);
      vipDays = math.max(vipDays, 1000);
    }
    _normalizeTimedCosmetics();
  }

  int xpNeededForLevel(int currentLevel) {
    final safe = currentLevel.clamp(1, 200).toInt();
    return 900 + (safe * 260) + (safe * safe * 18);
  }

  void _recalculateLevel() {
    if (isAdmin) {
      level = math.max(level, 90);
      xpNext = xpNeededForLevel(level);
      return;
    }
    xpNext = xpNeededForLevel(level);
    while (xp >= xpNext && level < 200) {
      xp -= xpNext;
      level += 1;
      xpNext = xpNeededForLevel(level);
      notices.insert(0, AppNotice('⭐', 'ترقية مستوى', 'وصلت إلى المستوى $level وحصلت على شارة تقدم جديدة.'));
    }
  }

  void _normalizeTimedCosmetics() {
    final now = DateTime.now();
    if (nameColorExpiresAt != null && now.isAfter(nameColorExpiresAt!)) {
      selectedNameColor = '#facc15';
      nameColorExpiresAt = null;
    }
    if (chatColorExpiresAt != null && now.isAfter(chatColorExpiresAt!)) {
      selectedChatColor = '#ffffff';
      chatColorExpiresAt = null;
    }
  }

  void _resetAdCounterIfNeeded() {
    final today = DateTime.now().toIso8601String().substring(0, 10);
    if (rewardedAdClaimDate != today) {
      rewardedAdClaimDate = today;
      rewardedAdClaimsToday = 0;
    }
  }

  int get rewardedAdsRemaining {
    _resetAdCounterIfNeeded();
    return math.max(0, 5 - rewardedAdClaimsToday);
  }

  void _applyLocalLoginStreak() {
    final now = DateTime.now();
    final today = DateTime(now.year, now.month, now.day);
    final todayText = today.toIso8601String().substring(0, 10);
    if (lastLoginDate == todayText) return;
    final previous = DateTime.tryParse(lastLoginDate ?? '');
    final yesterday = today.subtract(const Duration(days: 1));
    consecutiveLoginDays = previous != null && DateTime(previous.year, previous.month, previous.day) == yesterday
        ? consecutiveLoginDays + 1
        : 1;
    lastLoginDate = todayText;
    if (consecutiveLoginDays % 3 == 0) {
      vipDays += 1;
      notices.insert(0, AppNotice('🎩', 'يوم باشا مجاني', 'أكملت 3 أيام دخول متواصلة، وتمت إضافة يوم باشا.'));
    }
  }

  Future<String?> updateAvatarFromGallery(BuildContext context) async {
    try {
      final file = await ImagePicker().pickImage(source: ImageSource.gallery, maxWidth: 1400, imageQuality: 92);
      if (file == null) return null;
      final bytes = await file.readAsBytes();
      if (bytes.length > 5000000) return 'الصورة كبيرة جداً. اختر صورة أصغر من 5MB.';
      if (!context.mounted) return null;
      final cropped = await showDialog<Uint8List>(
        context: context,
        barrierDismissible: false,
        builder: (_) => AvatarCropDialog(bytes: bytes),
      );
      if (cropped == null) return null;
      if (cropped.length > 1500000) return 'تعذر ضغط المعاينة. جرّب صورة أصغر.';
      avatarData = 'data:image/png;base64,${base64Encode(cropped)}';
      if (serverConnected) {
        await api.updateProfile({'avatar_data': avatarData, 'avatar': avatarEmoji});
      }
      await _save();
      notifyListeners();
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return 'تعذر قراءة الصورة المختارة أو قصها.';
    }
  }

  Future<String?> chooseAvatarEmoji(String emoji) async {
    avatarEmoji = emoji;
    avatarData = null;
    if (serverConnected) {
      try {
        await api.updateProfile({'avatar': emoji, 'avatar_data': null});
      } on ApiException catch (e) {
        return e.message;
      }
    }
    await _save();
    notifyListeners();
    return null;
  }

  Future<String?> deleteAccount(String password) async {
    if (isAdmin) return 'لا يمكن حذف حساب المدير الرئيسي.';
    if (password.trim().isEmpty) return 'أدخل كلمة المرور لتأكيد حذف الحساب.';
    if (serverConnected) {
      try {
        await api.deleteAccount(password);
      } on ApiException catch (e) {
        return e.message;
      }
    }
    final prefs = await SharedPreferences.getInstance();
    await prefs.clear();
    isAuthenticated = false;
    serverConnected = false;
    authToken = null;
    api.token = null;
    notifyListeners();
    return null;
  }

  Future<String?> grantRewardedAd(String verificationId) async {
    _resetAdCounterIfNeeded();
    if (rewardedAdClaimsToday >= 5) return 'وصلت إلى الحد اليومي: 5 إعلانات مكافِئة.';
    var tokens = 50;
    var earnedXp = 15;
    if (serverConnected) {
      try {
        final data = await api.claimRewardedAd(verificationId);
        tokens = int.tryParse(data['tokens']?.toString() ?? '') ?? tokens;
        earnedXp = int.tryParse(data['xp']?.toString() ?? '') ?? earnedXp;
        final wallet = data['wallet'];
        if (wallet is Map) coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
      } on ApiException catch (e) {
        return e.message;
      }
    } else {
      coins += BigInt.from(tokens);
    }
    rewardedAdClaimsToday += 1;
    xp += earnedXp;
    _recalculateLevel();
    transactions.insert(0, TokenTransaction('مكافأة مشاهدة إعلان', tokens, 'الآن'));
    notices.insert(0, AppNotice('📺', 'مكافأة إعلان', 'حصلت على $tokens توكن و$earnedXp XP.'));
    await _save();
    notifyListeners();
    return null;
  }

  void changeLocale(String value) {
    localeCode = value;
    _save();
    notifyListeners();
  }

  void changeTheme(String value) {
    themeCode = value;
    _save();
    notifyListeners();
  }

  void toggleSound(bool value) {
    soundEnabled = value;
    _save();
    notifyListeners();
  }

  Future<void> playReactionFeedback({bool strong = false}) async {
    if (soundEnabled) {
      try { await SystemSound.play(strong ? SystemSoundType.alert : SystemSoundType.click); } catch (_) {}
    }
    try {
      if (strong) { await HapticFeedback.mediumImpact(); } else { await HapticFeedback.selectionClick(); }
    } catch (_) {}
  }

  Future<bool> buy(StoreProduct product) async {
    final reusable = product.category == 'pasha' || product.category == 'boost';
    if (!reusable && owned.contains(product.id)) {
      activateProduct(product);
      return true;
    }
    if (coins < BigInt.from(priceFor(product))) return false;
    if (serverConnected) {
      try {
        final data = await api.purchase(product.id);
        final wallet = data['wallet'];
        if (wallet is Map) coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
      } on ApiException {
        return false;
      }
    } else {
      coins -= BigInt.from(priceFor(product));
    }
    if (!reusable) owned.add(product.id);
    transactions.insert(0, TokenTransaction('شراء ${nameFor(product, 'ar')}', -priceFor(product), 'الآن'));
    activateProduct(product);
    await _save();
    notifyListeners();
    return true;
  }

  int priceFor(StoreProduct product) => storePriceOverrides[product.id] ?? product.price;
  String nameFor(StoreProduct product, [String? lang]) => storeNameOverrides[product.id]?.trim().isNotEmpty == true
      ? storeNameOverrides[product.id]!
      : product.name(lang ?? localeCode);
  String descriptionFor(StoreProduct product, [String? lang]) => storeDescriptionOverrides[product.id]?.trim().isNotEmpty == true
      ? storeDescriptionOverrides[product.id]!
      : product.description(lang ?? localeCode);
  int? durationFor(StoreProduct product) => storeDurationOverrides.containsKey(product.id)
      ? storeDurationOverrides[product.id]
      : product.durationDays;
  Color color1For(StoreProduct product) => colorFromHex(storeColor1Overrides[product.id] ?? colorToHex(product.previewColor1 ?? const Color(0xff0b4731)));
  Color color2For(StoreProduct product) => colorFromHex(storeColor2Overrides[product.id] ?? colorToHex(product.previewColor2 ?? const Color(0xffd6aa59)));
  bool isStoreProductVisible(StoreProduct product) => !hiddenStoreProducts.contains(product.id);

  Future<void> updateStoreProductAdmin(
    StoreProduct product, {
    int? price,
    bool? visible,
    String? name,
    String? description,
    int? durationDays,
    String? color1,
    String? color2,
  }) async {
    if (price != null) storePriceOverrides[product.id] = price.clamp(0, 999999999).toInt();
    if (name != null) {
      if (name.trim().isEmpty) { storeNameOverrides.remove(product.id); } else { storeNameOverrides[product.id] = name.trim(); }
    }
    if (description != null) {
      if (description.trim().isEmpty) { storeDescriptionOverrides.remove(product.id); } else { storeDescriptionOverrides[product.id] = description.trim(); }
    }
    if (durationDays != null) storeDurationOverrides[product.id] = durationDays.clamp(0, 3650).toInt();
    if (color1 != null && color1.trim().isNotEmpty) storeColor1Overrides[product.id] = color1.trim();
    if (color2 != null && color2.trim().isNotEmpty) storeColor2Overrides[product.id] = color2.trim();
    if (visible != null) {
      if (visible) { hiddenStoreProducts.remove(product.id); } else { hiddenStoreProducts.add(product.id); }
    }
    await _save();
    notifyListeners();
  }

  void activateProduct(StoreProduct product) {
    switch (product.category) {
      case 'pasha':
        vipDays += durationFor(product) ?? 0;
        selectedBadge = 'badge_pasha';
        break;
      case 'themes':
        if (product.value != null) themeCode = product.value!;
        break;
      case 'tables':
        selectedTable = product.id;
        break;
      case 'cards':
        selectedCardBack = product.id;
        break;
      case 'names':
        selectedNameColor = product.value ?? selectedNameColor;
        final nameDays = durationFor(product);
        nameColorExpiresAt = nameDays == null || nameDays <= 0 ? null : DateTime.now().add(Duration(days: nameDays));
        break;
      case 'chat_colors':
        selectedChatColor = product.value ?? selectedChatColor;
        final chatDays = durationFor(product);
        chatColorExpiresAt = chatDays == null || chatDays <= 0 ? null : DateTime.now().add(Duration(days: chatDays));
        break;
      case 'badges':
        selectedBadge = product.id;
        break;
      case 'emoji':
        selectedEmojiPack = product.id;
        break;
      case 'effects':
        selectedEffect = product.id;
        break;
      case 'covers':
        selectedCover = product.id;
        if (serverConnected) api.updateProfile({'active_cover': selectedCover}).catchError((_) => <String, dynamic>{});
        break;
      case 'boost':
        activeXpMultiplier = product.multiplier ?? 1.0;
        break;
    }
    _save();
    notifyListeners();
  }

  void updateNoCodeDesign({double? buttonHeight, double? radius, double? fontScale, double? chatScale, String? accentHex, bool? ambientEffects}) {
    if (buttonHeight != null) uiButtonHeight = buttonHeight.clamp(38, 64).toDouble();
    if (radius != null) uiRadius = radius.clamp(8, 32).toDouble();
    if (fontScale != null) uiFontScale = fontScale.clamp(.85, 1.35).toDouble();
    if (chatScale != null) uiChatScale = chatScale.clamp(.8, 1.35).toDouble();
    if (accentHex != null && accentHex.startsWith('#')) uiAccentHex = accentHex;
    if (ambientEffects != null) tableAmbientEffects = ambientEffects;
    _save();
    if (serverConnected) {
      api.updateProfile({'ui_preferences': {'button_height': uiButtonHeight, 'radius': uiRadius, 'font_scale': uiFontScale, 'chat_scale': uiChatScale, 'accent_hex': uiAccentHex, 'ambient_effects': tableAmbientEffects}}).catchError((_) => <String, dynamic>{});
    }
    notifyListeners();
  }

  void changeBotDifficulty(String value) {
    if (!const {'easy', 'normal', 'pro', 'master'}.contains(value)) return;
    botDifficultyCode = value;
    _save();
    if (serverConnected) api.updateProfile({'bot_difficulty': value}).catchError((_) => <String, dynamic>{});
    notifyListeners();
  }

  double get winRate => gamesPlayed <= 0 ? 0 : (wins / gamesPlayed) * 100;
  int get losses => math.max(0, gamesPlayed - wins);

  Future<void> toggleOrientationMode() async {
    landscapeMode = !landscapeMode;
    try {
      await SystemChrome.setPreferredOrientations(
        landscapeMode
            ? <DeviceOrientation>[DeviceOrientation.landscapeLeft, DeviceOrientation.landscapeRight]
            : <DeviceOrientation>[DeviceOrientation.portraitUp, DeviceOrientation.portraitDown],
      );
    } catch (_) {}
    await _save();
    notifyListeners();
  }

  Future<void> setLandscapeMode(bool value) async {
    landscapeMode = value;
    try {
      await SystemChrome.setPreferredOrientations(
        value
            ? <DeviceOrientation>[DeviceOrientation.landscapeLeft, DeviceOrientation.landscapeRight]
            : <DeviceOrientation>[DeviceOrientation.portraitUp, DeviceOrientation.portraitDown],
      );
    } catch (_) {}
    await _save();
    notifyListeners();
  }

  int exitsForGame(String gameId) => gameExitCounts[gameId] ?? 0;
  bool canEnterGame(String gameId) => exitsForGame(gameId) < 3;

  Future<int> recordGameExit(String gameId) async {
    final next = (gameExitCounts[gameId] ?? 0) + 1;
    gameExitCounts[gameId] = next.clamp(0, 3).toInt();
    activeGame = null;
    awayMode = false;
    await _save();
    notifyListeners();
    return gameExitCounts[gameId]!;
  }

  void setAwayMode(bool value) {
    if (value && vipDays <= 0) return;
    awayMode = value;
    notifyListeners();
  }

  bool joinCompetition(String id) {
    if (activeCompetition != null && activeCompetition != id) return false;
    activeCompetition = id;
    _save();
    notifyListeners();
    return true;
  }

  void leaveCompetition() {
    activeCompetition = null;
    _save();
    notifyListeners();
  }

  bool joinChallenge(String id) {
    if (activeChallenge != null && activeChallenge != id) return false;
    activeChallenge = id;
    _save();
    notifyListeners();
    return true;
  }

  void leaveChallenge() {
    activeChallenge = null;
    _save();
    notifyListeners();
  }

  bool enterGame(String id) {
    if (!canEnterGame(id)) return false;
    if (activeGame != null && activeGame != id) return false;
    activeGame = id;
    _save();
    notifyListeners();
    return true;
  }

  void leaveGame([String? id]) {
    if (id == null || activeGame == id) activeGame = null;
    _save();
    notifyListeners();
  }

  void rewardGameWin(String gameId) {
    final marker = '${activeChallenge ?? 'friendly'}:$gameId:${DateTime.now().millisecondsSinceEpoch ~/ 30000}';
    if (rewardedMatches.contains(marker)) return;
    rewardedMatches.add(marker);
    final baseReward = activeChallenge == null ? 50 : 200;
    final xpReward = (10 * (vipDays > 0 ? 2 : 1) * activeXpMultiplier).round();
    coins += BigInt.from(baseReward);
    xp += xpReward;
    _recalculateLevel();
    gamesPlayed += 1;
    wins += 1;
    giftRoadProgress = (giftRoadProgress + 1).clamp(0, 30).toInt();
    transactions.insert(0, TokenTransaction('مكافأة فوز', baseReward, 'الآن'));
    notices.insert(0, AppNotice(storeProductById(selectedEffect)?.icon ?? '🏆', 'فوز جديد', 'حصلت على $baseReward توكن و$xpReward XP مع تفعيل مؤثر الفوز.'));
    activeChallenge = null;
    _save();
    notifyListeners();
  }

  int giftRewardFor(int step) => switch (step) {
        5 => 50,
        10 => 100,
        20 => 100,
        30 => 200,
        _ => 0,
      };

  bool claimGiftRoad(int step) {
    if (giftRoadProgress < step || claimedGiftSteps.contains(step)) return false;
    final reward = giftRewardFor(step);
    claimedGiftSteps.add(step);
    coins += BigInt.from(reward);
    transactions.insert(0, TokenTransaction('طريق الهدايا — المرحلة $step', reward, 'الآن'));
    _save();
    notifyListeners();
    return true;
  }

  void addCoins(int value, String label) {
    coins += BigInt.from(value);
    transactions.insert(0, TokenTransaction(label, value, 'الآن'));
    _save();
    notifyListeners();
  }

  Future<void> claimDaily() async {
    final now = DateTime.now();
    final today = '${now.year.toString().padLeft(4, '0')}-${now.month.toString().padLeft(2, '0')}-${now.day.toString().padLeft(2, '0')}';
    if (lastDailyClaimDate == today) {
      notices.insert(0, AppNotice('⏳', 'المكافأة اليومية', 'استلمت مكافأة اليوم بالفعل. عد غداً.'));
      notifyListeners();
      return;
    }
    var granted = false;
    if (serverConnected) {
      try {
        final data = await api.claimDaily();
        final wallet = data['wallet'];
        if (wallet is Map) coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
        granted = true;
      } catch (_) {
        granted = false;
      }
    } else {
      coins += BigInt.from(100);
      transactions.insert(0, const TokenTransaction('مكافأة يومية', 100, 'الآن'));
      granted = true;
    }
    if (!granted) return;
    lastDailyClaimDate = today;
    xp += 20;
    _recalculateLevel();
    notices.insert(0, AppNotice('🎁', 'مكافأة يومية', 'تمت إضافة 100 توكن و20 XP.'));
    await _save();
    notifyListeners();
  }

  void markAllRead() {
    for (final notice in notices) notice.read = true;
    notifyListeners();
  }

  void removeNotice(AppNotice notice) {
    notices.remove(notice);
    notifyListeners();
  }

  bool joinClub(String id) {
    if (activeClub != null && activeClub != id) return false;
    activeClub = id;
    _save();
    notifyListeners();
    return true;
  }

  void leaveClub() {
    activeClub = null;
    _save();
    notifyListeners();
  }

  void acceptFriend(LocalFriend friend) {
    incomingRequests.remove(friend);
    if (!friends.any((e) => e.id == friend.id)) friends.add(friend);
    notifyListeners();
  }

  void rejectFriend(LocalFriend friend) {
    incomingRequests.remove(friend);
    notifyListeners();
  }

  void sendFriendRequest(LocalFriend friend) {
    if (!outgoingRequests.any((e) => e.id == friend.id)) outgoingRequests.add(friend);
    notifyListeners();
  }

  void cancelFriendRequest(LocalFriend friend) {
    outgoingRequests.remove(friend);
    notifyListeners();
  }

  void blockFriend(LocalFriend friend) {
    friends.removeWhere((e) => e.id == friend.id);
    incomingRequests.removeWhere((e) => e.id == friend.id);
    outgoingRequests.removeWhere((e) => e.id == friend.id);
    if (!blocked.any((e) => e.id == friend.id)) blocked.add(friend);
    notifyListeners();
  }

  void unblockFriend(LocalFriend friend) {
    blocked.remove(friend);
    notifyListeners();
  }

  void sendLocalMessage(LocalFriend friend, String body) {
    privateChats.putIfAbsent(friend.id, () => []);
    privateChats[friend.id]!.add(ChatMessage(displayName, body, true, '${DateTime.now().hour.toString().padLeft(2, '0')}:${DateTime.now().minute.toString().padLeft(2, '0')}'));
    notifyListeners();
  }

  Future<String?> transferLocal(String receiver, int amount) async {
    if (amount <= 0) return 'أدخل قيمة صحيحة.';
    if (receiver.trim().toLowerCase() == username.trim().toLowerCase()) return 'لا يمكنك التحويل إلى حسابك نفسه.';
    final fee = (amount * .10).ceil();
    final total = BigInt.from(amount + fee);
    if (coins < total) return 'الرصيد غير كافٍ لتغطية المبلغ وعمولة الإدارة 10%.';
    final prefs = await SharedPreferences.getInstance();
    final receiverKey = receiver.trim().toLowerCase();
    final receiverPrefix = 'warqna.account.$receiverKey.';
    final demo = demoAccounts[receiverKey];
    if (!(prefs.getBool('${receiverPrefix}initialized') ?? false) && demo == null) return 'الحساب المستلم غير موجود محلياً.';
    final receiverBalance = BigInt.tryParse(prefs.getString('${receiverPrefix}coins') ?? demo?['coins']?.toString() ?? '0') ?? BigInt.zero;
    const adminKey = 'adnan';
    const adminPrefix = 'warqna.account.adnan.';
    final senderKey = username.trim().toLowerCase();
    coins -= total;

    var receiverCredit = BigInt.from(amount);
    if (receiverKey == adminKey && senderKey != adminKey) receiverCredit += BigInt.from(fee);
    await prefs.setBool('${receiverPrefix}initialized', true);
    await prefs.setString('${receiverPrefix}coins', (receiverBalance + receiverCredit).toString());

    if (senderKey == adminKey) {
      coins += BigInt.from(fee);
    } else if (receiverKey != adminKey) {
      final adminDefault = demoAccounts[adminKey]?['coins']?.toString() ?? '1000000000000000000';
      final adminBalance = BigInt.tryParse(prefs.getString('${adminPrefix}coins') ?? adminDefault) ?? BigInt.parse(adminDefault);
      await prefs.setBool('${adminPrefix}initialized', true);
      await prefs.setString('${adminPrefix}coins', (adminBalance + BigInt.from(fee)).toString());
    }

    transactions.insert(0, TokenTransaction('تحويل إلى $receiver', -amount, 'الآن'));
    transactions.insert(1, TokenTransaction('عمولة تحويل 10%', -fee, 'الآن'));
    await _save();
    notifyListeners();
    return null;
  }
}

class AppNotice {
  final String icon;
  final String title;
  final String body;
  bool read;

  AppNotice(this.icon, this.title, this.body, {this.read = false});
}

class TokenTransaction {
  final String label;
  final int amount;
  final String date;

  const TokenTransaction(this.label, this.amount, this.date);
}


class LocalFriend {
  final int id;
  final String name;
  final String username;
  final bool online;
  final String activity;

  const LocalFriend(this.id, this.name, this.username, {required this.online, required this.activity});
}

class ChatMessage {
  final String sender;
  final String body;
  final bool mine;
  final String time;

  const ChatMessage(this.sender, this.body, this.mine, this.time);
}

class AppPalette {
  final Color bg;
  final Color panel;
  final Color panel2;
  final Color gold;
  final Color green;
  final Color accent;
  final Color text;
  final Color muted;

  const AppPalette(
    this.bg,
    this.panel,
    this.panel2,
    this.gold,
    this.green,
    this.accent,
    this.text,
    this.muted,
  );

  static AppPalette fromCode(String code) {
    switch (code) {
      case 'royal':
        return const AppPalette(
          Color(0xff07142d),
          Color(0xff10254a),
          Color(0xff173461),
          Color(0xffffcf6a),
          Color(0xff1ea979),
          Color(0xff4f7cff),
          Colors.white,
          Color(0xff9fb0cb),
        );
      case 'emerald':
        return const AppPalette(
          Color(0xff061a14),
          Color(0xff0d2c22),
          Color(0xff123b2d),
          Color(0xffffd36e),
          Color(0xff23b47e),
          Color(0xff00c98d),
          Colors.white,
          Color(0xff9fc7b8),
        );
      case 'purple':
        return const AppPalette(
          Color(0xff160b23),
          Color(0xff27113d),
          Color(0xff351a52),
          Color(0xffffd06a),
          Color(0xff3db58b),
          Color(0xff9b5de5),
          Colors.white,
          Color(0xffc0a9d1),
        );
      case 'classic':
        return const AppPalette(
          Color(0xff21170f),
          Color(0xff372619),
          Color(0xff493322),
          Color(0xffffd178),
          Color(0xff2fa36f),
          Color(0xffb77a42),
          Colors.white,
          Color(0xffc8b59f),
        );
      case 'crimson':
        return const AppPalette(Color(0xff21070d), Color(0xff3b1019), Color(0xff521725), Color(0xffffd078), Color(0xff20a777), Color(0xffef334f), Colors.white, Color(0xffd2a5ad));
      case 'midnight':
        return const AppPalette(Color(0xff020617), Color(0xff0b1730), Color(0xff112448), Color(0xffe7c873), Color(0xff21b58a), Color(0xff4f86ff), Colors.white, Color(0xff91a4c4));
      case 'aurora':
        return const AppPalette(Color(0xff04171b), Color(0xff0a2b31), Color(0xff10414a), Color(0xffffd772), Color(0xff35d09a), Color(0xff67e8f9), Colors.white, Color(0xffa4ced2));
      case 'obsidian':
        return const AppPalette(Color(0xff020307), Color(0xff0b0d12), Color(0xff171a21), Color(0xffe5c07b), Color(0xff36c692), Color(0xff6b7280), Colors.white, Color(0xffa3a3a3));
      case 'rose_gold':
        return const AppPalette(Color(0xff1b0710), Color(0xff351322), Color(0xff512038), Color(0xffffd0c7), Color(0xff41b883), Color(0xfffb7185), Colors.white, Color(0xffe7b3c1));
      case 'desert':
        return const AppPalette(Color(0xff241307), Color(0xff3b2410), Color(0xff5a3514), Color(0xffffd38a), Color(0xff3cab76), Color(0xffd97706), Colors.white, Color(0xffd4b58f));
      case 'forest':
        return const AppPalette(Color(0xff03140d), Color(0xff0a291d), Color(0xff10432e), Color(0xffffd166), Color(0xff22c55e), Color(0xff16a34a), Colors.white, Color(0xffa7cdb9));
      case 'ice':
        return const AppPalette(Color(0xff06131d), Color(0xff0d2534), Color(0xff12394d), Color(0xffe0f2fe), Color(0xff2dd4bf), Color(0xff38bdf8), Colors.white, Color(0xffb8d9e8));
      default:
        return const AppPalette(
          Color(0xff07111c),
          Color(0xff0d1a28),
          Color(0xff132336),
          Color(0xffffcf67),
          Color(0xff20a777),
          Color(0xff815ac0),
          Colors.white,
          Color(0xff93a3b7),
        );
    }
  }
}

class L {
  static const Map<String, Map<String, String>> data = {
    'ar': {
      'home': 'الرئيسية',
      'games': 'الألعاب',
      'store': 'المتجر',
      'clubs': 'المجموعات',
      'events': 'المنافسات',
      'welcome': 'مرحباً بعودتك',
      'level': 'المستوى',
      'coins': 'العملات',
      'vip': 'الباشا',
      'days': 'يوم',
      'champions': 'بطولة الأبطال',
      'hero': 'نافس أقوى اللاعبين واربح جوائز ذهبية ضخمة',
      'join': 'انضم الآن',
      'giftRoad': 'طريق الهدايا',
      'featured': 'ألعاب مميزة',
      'friendly': 'مباراة ودية',
      'competitions': 'المنافسات',
      'challenges': 'التحديات',
      'tournaments': 'المنافسات',
      'friends': 'الأصدقاء',
      'settings': 'الإعدادات',
      'language': 'اللغة',
      'theme': 'الثيم',
      'claim': 'استلام',
      'buy': 'شراء',
      'owned': 'مملوك',
      'rules': 'القوانين',
      'leaderboard': 'لوحة الصدارة',
      'chat': 'الدردشة',
      'quick': 'التفاعلات السريعة',
      'yourTurn': 'دورك',
      'play': 'طرح',
      'pass': 'سكون',
      'bid': 'أخذ',
      'notifications': 'الإشعارات',
      'rewards': 'المكافآت',
      'transactions': 'سجل التوكنز',
      'inventory': 'مقتنياتي',
      'createRoom': 'إنشاء غرفة',
      'chooseGameMode': 'اختر نوع اللعبة',
      'chooseGameModeHint': 'ابدأ لعبة عادية أو أنشئ غرفة صوتية مع تحكم كامل بالميكروفون.',
      'normalGame': 'لعبة عادية',
      'normalGameHint': 'لعب سريع بدون محادثة صوتية.',
      'voiceGame': 'لعبة صوتية',
      'voiceGameHint': 'تحدث مع اللاعبين أثناء اللعب مع كتم فردي.',
      'roomModeDescription': 'حدد نوع الغرفة وخصوصيتها وسرعة الدور قبل بدء اللعب.',
      'roomName': 'اسم الغرفة',
      'roomVisibility': 'خصوصية الغرفة',
      'publicRoom': 'غرفة عامة',
      'friendsRoom': 'للأصدقاء فقط',
      'privateRoom': 'غرفة خاصة',
      'turnSpeed': 'سرعة الدور',
      'voicePrivacyHint': 'يُطلب إذن الميكروفون فقط عند دخول غرفة صوتية، ويمكنك كتم صوتك أو أي لاعب في أي وقت.',
      'enterRoomName': 'أدخل اسمًا للغرفة.',
      'enterRoomPassword': 'أدخل كلمة سر من 3 أحرف على الأقل.',
      'createVoiceRoom': 'إنشاء غرفة صوتية',
      'createNormalRoom': 'إنشاء غرفة عادية',
      'openRooms': 'الغرف المفتوحة',
      'openRoomsHint': 'انضم إلى غرفة عادية أو صوتية أنشأها لاعب آخر.',
      'noOpenRooms': 'لا توجد غرف مفتوحة الآن.',
      'serverRoomsNeedBackend': 'الغرف بين اللاعبين تحتاج ربط التطبيق بخادم Laravel المنشور.',
      'roomsLoadFailed': 'تعذر تحميل الغرف المفتوحة.',
      'joinByCode': 'دخول برمز',
      'joinByCodeHint': 'أدخل رمز الغرفة الذي أرسله لك صديقك.',
      'roomCode': 'رمز الغرفة',
      'optional': 'اختياري',
      'enterRoomCode': 'أدخل رمز غرفة صحيحًا.',
      'joinRoom': 'دخول الغرفة',
      'search': 'ابحث عن لعبة',
      'members': 'عضو',
      'treasury': 'الخزينة',
      'leaveClub': 'مغادرة المجموعة',
      'joinClub': 'انضمام',
      'domino': 'دومينو',
      'tarneeb': 'طرنيب',
      'trix': 'تركس',
      'hand': 'هاند',
      'banakil': 'بناكل',
      'baloot': 'بلوت',
      'basra': 'باصرة',
      'jackaroo': 'جاكارو',
      'chess': 'شطرنج',
      'backgammon': 'طاولة الزهر',
      'solitaire_multiplayer': 'سوليتير تنافسي',
      'tarneeb_400': 'طرنيب 400',
      'syrian_tarneeb': 'طرنيب سوري',
      'trix_complex': 'تركس كومبلكس',
      'saudi_hand': 'هاند سعودي',
      'hand_partner': 'هاند شراكة',
      'trix_partner': 'تركس شراكة',
      'pinochle': 'بناكل كلاسيك',
    },
    'en': {
      'home': 'Home',
      'games': 'Games',
      'store': 'Store',
      'clubs': 'Groups',
      'events': 'Competitions',
      'welcome': 'Welcome back',
      'level': 'Level',
      'coins': 'Coins',
      'vip': 'VIP',
      'days': 'Days',
      'champions': 'Champions Cup',
      'hero': 'Compete with top players and win major golden prizes',
      'join': 'Join now',
      'giftRoad': 'Gifts Road',
      'featured': 'Featured Games',
      'friendly': 'Friendly Match',
      'competitions': 'Competitions',
      'challenges': 'Challenges',
      'tournaments': 'Tournaments',
      'friends': 'Friends',
      'settings': 'Settings',
      'language': 'Language',
      'theme': 'Theme',
      'claim': 'Claim',
      'buy': 'Buy',
      'owned': 'Owned',
      'rules': 'Rules',
      'leaderboard': 'Leaderboard',
      'chat': 'Chat',
      'quick': 'Quick Reactions',
      'yourTurn': 'Your Turn',
      'play': 'Play',
      'pass': 'Pass',
      'bid': 'Bid',
      'notifications': 'Notifications',
      'rewards': 'Rewards',
      'transactions': 'Token History',
      'inventory': 'Inventory',
      'createRoom': 'Create Room',
      'chooseGameMode': 'Choose game mode',
      'chooseGameModeHint': 'Start a normal match or create a voice room with full microphone controls.',
      'normalGame': 'Normal game',
      'normalGameHint': 'Fast play without voice chat.',
      'voiceGame': 'Voice game',
      'voiceGameHint': 'Talk to players while playing with individual mute controls.',
      'roomModeDescription': 'Choose room type, privacy, and turn speed before playing.',
      'roomName': 'Room name',
      'roomVisibility': 'Room privacy',
      'publicRoom': 'Public room',
      'friendsRoom': 'Friends only',
      'privateRoom': 'Private room',
      'turnSpeed': 'Turn speed',
      'voicePrivacyHint': 'Microphone permission is requested only for voice rooms. You can mute yourself or any player at any time.',
      'enterRoomName': 'Enter a room name.',
      'enterRoomPassword': 'Enter a password of at least 3 characters.',
      'createVoiceRoom': 'Create voice room',
      'createNormalRoom': 'Create normal room',
      'openRooms': 'Open rooms',
      'openRoomsHint': 'Join a normal or voice room created by another player.',
      'noOpenRooms': 'No open rooms right now.',
      'serverRoomsNeedBackend': 'Multiplayer rooms require the published Laravel server.',
      'roomsLoadFailed': 'Could not load open rooms.',
      'joinByCode': 'Join by code',
      'joinByCodeHint': 'Enter the room code shared by your friend.',
      'roomCode': 'Room code',
      'optional': 'optional',
      'enterRoomCode': 'Enter a valid room code.',
      'joinRoom': 'Join room',
      'search': 'Search games',
      'members': 'members',
      'treasury': 'Treasury',
      'leaveClub': 'Leave Group',
      'joinClub': 'Join',
      'domino': 'Domino',
      'tarneeb': 'Tarneeb',
      'trix': 'Trix',
      'hand': 'Hand',
      'banakil': 'Banakil',
      'baloot': 'Baloot',
      'basra': 'Basra',
      'jackaroo': 'Jackaroo',
      'chess': 'Chess',
      'backgammon': 'Backgammon',
      'solitaire_multiplayer': 'Competitive Solitaire',
      'tarneeb_400': 'Tarneeb 400',
      'syrian_tarneeb': 'Syrian Tarneeb',
      'trix_complex': 'Trix Complex',
      'saudi_hand': 'Saudi Hand',
      'hand_partner': 'Partnership Hand',
      'trix_partner': 'Partnership Trix',
      'pinochle': 'Classic Pinochle',
    },
    'de': {
      'home': 'Startseite',
      'games': 'Spiele',
      'store': 'Shop',
      'clubs': 'Gruppen',
      'events': 'Wettbewerbe',
      'welcome': 'Willkommen zurück',
      'level': 'Stufe',
      'coins': 'Token',
      'vip': 'VIP',
      'days': 'Tage',
      'champions': 'Champions-Pokal',
      'hero': 'Tritt gegen starke Spieler an und gewinne große goldene Preise',
      'join': 'Jetzt teilnehmen',
      'giftRoad': 'Geschenkpfad',
      'featured': 'Empfohlene Spiele',
      'friendly': 'Freundschaftsspiel',
      'competitions': 'Wettbewerbe',
      'challenges': 'Herausforderungen',
      'tournaments': 'Turniere',
      'friends': 'Freunde',
      'settings': 'Einstellungen',
      'language': 'Sprache',
      'theme': 'Design',
      'claim': 'Abholen',
      'buy': 'Kaufen',
      'owned': 'Im Besitz',
      'rules': 'Regeln',
      'leaderboard': 'Bestenliste',
      'chat': 'Chat',
      'quick': 'Schnellreaktionen',
      'yourTurn': 'Du bist dran',
      'play': 'Spielen',
      'pass': 'Passen',
      'bid': 'Reizen',
      'notifications': 'Benachrichtigungen',
      'rewards': 'Belohnungen',
      'transactions': 'Token-Verlauf',
      'inventory': 'Inventar',
      'createRoom': 'Raum erstellen',
      'chooseGameMode': 'Spielmodus wählen',
      'chooseGameModeHint': 'Starte ein normales Spiel oder erstelle einen Sprachraum mit Mikrofonsteuerung.',
      'normalGame': 'Normales Spiel',
      'normalGameHint': 'Schnelles Spiel ohne Sprachchat.',
      'voiceGame': 'Sprachspiel',
      'voiceGameHint': 'Sprich während des Spiels mit den Spielern und schalte einzelne stumm.',
      'roomModeDescription': 'Wähle Raumtyp, Privatsphäre und Zugzeit.',
      'roomName': 'Raumname',
      'roomVisibility': 'Raumsichtbarkeit',
      'publicRoom': 'Öffentlicher Raum',
      'friendsRoom': 'Nur Freunde',
      'privateRoom': 'Privater Raum',
      'turnSpeed': 'Zugzeit',
      'voicePrivacyHint': 'Die Mikrofonberechtigung wird nur in Sprachräumen angefordert.',
      'enterRoomName': 'Bitte einen Raumnamen eingeben.',
      'enterRoomPassword': 'Passwort mit mindestens 3 Zeichen eingeben.',
      'createVoiceRoom': 'Sprachraum erstellen',
      'createNormalRoom': 'Normalen Raum erstellen',
      'openRooms': 'Offene Räume',
      'openRoomsHint': 'Tritt einem normalen oder Sprachraum bei.',
      'noOpenRooms': 'Zurzeit keine offenen Räume.',
      'serverRoomsNeedBackend': 'Mehrspielerräume benötigen den veröffentlichten Laravel-Server.',
      'roomsLoadFailed': 'Offene Räume konnten nicht geladen werden.',
      'joinByCode': 'Mit Code beitreten',
      'joinByCodeHint': 'Gib den Raumcode deines Freundes ein.',
      'roomCode': 'Raumcode',
      'optional': 'optional',
      'enterRoomCode': 'Gültigen Raumcode eingeben.',
      'joinRoom': 'Raum beitreten',
      'search': 'Spiel suchen',
      'members': 'Mitglieder',
      'treasury': 'Kasse',
      'leaveClub': 'Gruppe verlassen',
      'joinClub': 'Beitreten',
      'domino': 'Domino',
      'tarneeb': 'Tarneeb',
      'trix': 'Trix',
      'hand': 'Hand',
      'banakil': 'Banakil',
      'baloot': 'Baloot',
      'basra': 'Basra',
      'jackaroo': 'Jackaroo',
      'chess': 'Schach',
      'backgammon': 'Backgammon',
      'solitaire_multiplayer': 'Wettkampf-Solitär',
      'tarneeb_400': 'Tarneeb 400',
      'syrian_tarneeb': 'Syrisches Tarneeb',
      'trix_complex': 'Trix Complex',
      'saudi_hand': 'Saudi Hand',
      'hand_partner': 'Partner-Hand',
      'trix_partner': 'Partner-Trix',
      'pinochle': 'Klassisches Pinochle',
    },
    'tr': {
      'home': 'Ana Sayfa',
      'games': 'Oyunlar',
      'store': 'Mağaza',
      'clubs': 'Gruplar',
      'events': 'Yarışmalar',
      'welcome': 'Tekrar hoş geldin',
      'level': 'Seviye',
      'coins': 'Jeton',
      'vip': 'VIP',
      'days': 'Gün',
      'champions': 'Şampiyonlar Kupası',
      'hero': 'Güçlü oyuncularla yarış ve büyük altın ödüller kazan',
      'join': 'Şimdi katıl',
      'giftRoad': 'Hediye Yolu',
      'featured': 'Öne Çıkan Oyunlar',
      'friendly': 'Dostluk Maçı',
      'competitions': 'Yarışmalar',
      'challenges': 'Meydan Okumalar',
      'tournaments': 'Turnuvalar',
      'friends': 'Arkadaşlar',
      'settings': 'Ayarlar',
      'language': 'Dil',
      'theme': 'Tema',
      'claim': 'Al',
      'buy': 'Satın Al',
      'owned': 'Sahip Olunan',
      'rules': 'Kurallar',
      'leaderboard': 'Liderlik Tablosu',
      'chat': 'Sohbet',
      'quick': 'Hızlı Tepkiler',
      'yourTurn': 'Sıra Sende',
      'play': 'Oyna',
      'pass': 'Pas',
      'bid': 'Teklif',
      'notifications': 'Bildirimler',
      'rewards': 'Ödüller',
      'transactions': 'Jeton Geçmişi',
      'inventory': 'Envanter',
      'createRoom': 'Oda Oluştur',
      'chooseGameMode': 'Oyun modunu seç',
      'chooseGameModeHint': 'Normal oyun başlat veya mikrofon kontrollü sesli oda oluştur.',
      'normalGame': 'Normal oyun',
      'normalGameHint': 'Sesli sohbet olmadan hızlı oyun.',
      'voiceGame': 'Sesli oyun',
      'voiceGameHint': 'Oynarken konuş ve oyuncuları ayrı ayrı sustur.',
      'roomModeDescription': 'Oda türünü, gizliliği ve tur süresini seç.',
      'roomName': 'Oda adı',
      'roomVisibility': 'Oda gizliliği',
      'publicRoom': 'Herkese açık',
      'friendsRoom': 'Sadece arkadaşlar',
      'privateRoom': 'Özel oda',
      'turnSpeed': 'Tur süresi',
      'voicePrivacyHint': 'Mikrofon izni yalnızca sesli odalarda istenir.',
      'enterRoomName': 'Oda adı girin.',
      'enterRoomPassword': 'En az 3 karakterlik şifre girin.',
      'createVoiceRoom': 'Sesli oda oluştur',
      'createNormalRoom': 'Normal oda oluştur',
      'openRooms': 'Açık odalar',
      'openRoomsHint': 'Başka bir oyuncunun normal veya sesli odasına katıl.',
      'noOpenRooms': 'Şu anda açık oda yok.',
      'serverRoomsNeedBackend': 'Çok oyunculu odalar yayınlanmış Laravel sunucusu gerektirir.',
      'roomsLoadFailed': 'Açık odalar yüklenemedi.',
      'joinByCode': 'Kodla katıl',
      'joinByCodeHint': 'Arkadaşının paylaştığı oda kodunu gir.',
      'roomCode': 'Oda kodu',
      'optional': 'isteğe bağlı',
      'enterRoomCode': 'Geçerli oda kodu girin.',
      'joinRoom': 'Odaya katıl',
      'search': 'Oyun ara',
      'members': 'üye',
      'treasury': 'Kasa',
      'leaveClub': 'Gruptan Ayrıl',
      'joinClub': 'Katıl',
      'domino': 'Domino',
      'tarneeb': 'Tarneeb',
      'trix': 'Trix',
      'hand': 'Hand',
      'banakil': 'Banakil',
      'baloot': 'Baloot',
      'basra': 'Basra',
      'jackaroo': 'Jackaroo',
      'chess': 'Satranç',
      'backgammon': 'Tavla',
      'solitaire_multiplayer': 'Rekabetçi Solitaire',
      'tarneeb_400': 'Tarneeb 400',
      'syrian_tarneeb': 'Suriye Tarneeb',
      'trix_complex': 'Trix Complex',
      'saudi_hand': 'Suudi Hand',
      'hand_partner': 'Eşli Hand',
      'trix_partner': 'Eşli Trix',
      'pinochle': 'Klasik Pinochle',
    },
    'fr': {
      'home': 'Accueil',
      'games': 'Jeux',
      'store': 'Boutique',
      'clubs': 'Groupes',
      'events': 'Compétitions',
      'welcome': 'Bon retour',
      'level': 'Niveau',
      'coins': 'Jetons',
      'vip': 'VIP',
      'days': 'Jours',
      'champions': 'Coupe des champions',
      'hero': 'Affrontez les meilleurs joueurs et gagnez de grandes récompenses dorées',
      'join': 'Rejoindre',
      'giftRoad': 'Route des cadeaux',
      'featured': 'Jeux vedettes',
      'friendly': 'Partie amicale',
      'competitions': 'Compétitions',
      'challenges': 'Défis',
      'tournaments': 'Tournois',
      'friends': 'Amis',
      'settings': 'Paramètres',
      'language': 'Langue',
      'theme': 'Thème',
      'claim': 'Réclamer',
      'buy': 'Acheter',
      'owned': 'Possédé',
      'rules': 'Règles',
      'leaderboard': 'Classement',
      'chat': 'Discussion',
      'quick': 'Réactions rapides',
      'yourTurn': 'À vous de jouer',
      'play': 'Jouer',
      'pass': 'Passer',
      'bid': 'Annoncer',
      'notifications': 'Notifications',
      'rewards': 'Récompenses',
      'transactions': 'Historique des jetons',
      'inventory': 'Inventaire',
      'createRoom': 'Créer une salle',
      'chooseGameMode': 'Choisir le mode',
      'chooseGameModeHint': 'Lancez une partie normale ou créez une salle vocale avec contrôle du micro.',
      'normalGame': 'Partie normale',
      'normalGameHint': 'Jeu rapide sans discussion vocale.',
      'voiceGame': 'Partie vocale',
      'voiceGameHint': 'Parlez pendant la partie et coupez chaque joueur individuellement.',
      'roomModeDescription': 'Choisissez le type, la confidentialité et la durée du tour.',
      'roomName': 'Nom de la salle',
      'roomVisibility': 'Confidentialité',
      'publicRoom': 'Salle publique',
      'friendsRoom': 'Amis uniquement',
      'privateRoom': 'Salle privée',
      'turnSpeed': 'Durée du tour',
      'voicePrivacyHint': 'Le micro est demandé uniquement dans les salles vocales.',
      'enterRoomName': 'Saisissez un nom de salle.',
      'enterRoomPassword': 'Saisissez un mot de passe de 3 caractères minimum.',
      'createVoiceRoom': 'Créer une salle vocale',
      'createNormalRoom': 'Créer une salle normale',
      'openRooms': 'Salles ouvertes',
      'openRoomsHint': 'Rejoignez une salle normale ou vocale créée par un autre joueur.',
      'noOpenRooms': 'Aucune salle ouverte actuellement.',
      'serverRoomsNeedBackend': 'Les salles multijoueurs nécessitent le serveur Laravel publié.',
      'roomsLoadFailed': 'Impossible de charger les salles.',
      'joinByCode': 'Rejoindre par code',
      'joinByCodeHint': 'Saisissez le code partagé par votre ami.',
      'roomCode': 'Code de salle',
      'optional': 'facultatif',
      'enterRoomCode': 'Saisissez un code valide.',
      'joinRoom': 'Rejoindre la salle',
      'search': 'Rechercher un jeu',
      'members': 'membres',
      'treasury': 'Trésorerie',
      'leaveClub': 'Quitter le groupe',
      'joinClub': 'Rejoindre',
      'domino': 'Domino',
      'tarneeb': 'Tarneeb',
      'trix': 'Trix',
      'hand': 'Hand',
      'banakil': 'Banakil',
      'baloot': 'Baloot',
      'basra': 'Basra',
      'jackaroo': 'Jackaroo',
      'chess': 'Échecs',
      'backgammon': 'Backgammon',
      'solitaire_multiplayer': 'Solitaire compétitif',
      'tarneeb_400': 'Tarneeb 400',
      'syrian_tarneeb': 'Tarneeb syrien',
      'trix_complex': 'Trix Complex',
      'saudi_hand': 'Hand saoudien',
      'hand_partner': 'Hand en équipe',
      'trix_partner': 'Trix en équipe',
      'pinochle': 'Pinochle classique',
    },
    'es': {
      'home': 'Inicio',
      'games': 'Juegos',
      'store': 'Tienda',
      'clubs': 'Grupos',
      'events': 'Competiciones',
      'welcome': 'Bienvenido de nuevo',
      'level': 'Nivel',
      'coins': 'Fichas',
      'vip': 'VIP',
      'days': 'Días',
      'champions': 'Copa de Campeones',
      'hero': 'Compite con grandes jugadores y gana enormes premios dorados',
      'join': 'Únete ahora',
      'giftRoad': 'Camino de regalos',
      'featured': 'Juegos destacados',
      'friendly': 'Partida amistosa',
      'competitions': 'Competiciones',
      'challenges': 'Desafíos',
      'tournaments': 'Torneos',
      'friends': 'Amigos',
      'settings': 'Ajustes',
      'language': 'Idioma',
      'theme': 'Tema',
      'claim': 'Reclamar',
      'buy': 'Comprar',
      'owned': 'Comprado',
      'rules': 'Reglas',
      'leaderboard': 'Clasificación',
      'chat': 'Chat',
      'quick': 'Reacciones rápidas',
      'yourTurn': 'Tu turno',
      'play': 'Jugar',
      'pass': 'Pasar',
      'bid': 'Pujar',
      'notifications': 'Notificaciones',
      'rewards': 'Recompensas',
      'transactions': 'Historial de fichas',
      'inventory': 'Inventario',
      'createRoom': 'Crear sala',
      'chooseGameMode': 'Elegir modo de juego',
      'chooseGameModeHint': 'Inicia una partida normal o crea una sala de voz con controles de micrófono.',
      'normalGame': 'Partida normal',
      'normalGameHint': 'Juego rápido sin chat de voz.',
      'voiceGame': 'Partida de voz',
      'voiceGameHint': 'Habla durante la partida y silencia jugadores individualmente.',
      'roomModeDescription': 'Elige tipo de sala, privacidad y tiempo de turno.',
      'roomName': 'Nombre de la sala',
      'roomVisibility': 'Privacidad',
      'publicRoom': 'Sala pública',
      'friendsRoom': 'Solo amigos',
      'privateRoom': 'Sala privada',
      'turnSpeed': 'Tiempo de turno',
      'voicePrivacyHint': 'El permiso del micrófono se solicita solo en salas de voz.',
      'enterRoomName': 'Escribe un nombre de sala.',
      'enterRoomPassword': 'Escribe una contraseña de al menos 3 caracteres.',
      'createVoiceRoom': 'Crear sala de voz',
      'createNormalRoom': 'Crear sala normal',
      'openRooms': 'Salas abiertas',
      'openRoomsHint': 'Únete a una sala normal o de voz creada por otro jugador.',
      'noOpenRooms': 'No hay salas abiertas ahora.',
      'serverRoomsNeedBackend': 'Las salas multijugador requieren el servidor Laravel publicado.',
      'roomsLoadFailed': 'No se pudieron cargar las salas.',
      'joinByCode': 'Entrar con código',
      'joinByCodeHint': 'Introduce el código compartido por tu amigo.',
      'roomCode': 'Código de sala',
      'optional': 'opcional',
      'enterRoomCode': 'Introduce un código válido.',
      'joinRoom': 'Entrar a la sala',
      'search': 'Buscar juego',
      'members': 'miembros',
      'treasury': 'Tesorería',
      'leaveClub': 'Salir del grupo',
      'joinClub': 'Unirse',
      'domino': 'Dominó',
      'tarneeb': 'Tarneeb',
      'trix': 'Trix',
      'hand': 'Hand',
      'banakil': 'Banakil',
      'baloot': 'Baloot',
      'basra': 'Basra',
      'jackaroo': 'Jackaroo',
      'chess': 'Ajedrez',
      'backgammon': 'Backgammon',
      'solitaire_multiplayer': 'Solitario competitivo',
      'tarneeb_400': 'Tarneeb 400',
      'syrian_tarneeb': 'Tarneeb sirio',
      'trix_complex': 'Trix Complex',
      'saudi_hand': 'Hand saudí',
      'hand_partner': 'Hand por parejas',
      'trix_partner': 'Trix por parejas',
      'pinochle': 'Pinochle clásico',
    },
  };

  static const Map<String, Map<String, String>> extra = {
    'ar': {
      'login': 'تسجيل الدخول', 'register': 'إنشاء حساب', 'username': 'اسم المستخدم', 'email': 'البريد الإلكتروني', 'password': 'كلمة المرور',
      'guest': 'الدخول كضيف', 'profile': 'الملف الشخصي', 'logout': 'تسجيل الخروج', 'deleteAccount': 'حذف الحساب', 'appearance': 'المظهر',
      'beginner': 'مبتدئ', 'professional': 'محترف', 'legendary': 'أسطوري', 'covers': 'أغلفة البروفايل', 'bots': 'اللاعبون الآليون',
      'difficulty': 'مستوى الذكاء', 'easy': 'سهل', 'normal': 'متوسط', 'pro': 'محترف', 'master': 'خبير', 'noCode': 'المصمم بدون كود',
      'statistics': 'الإحصاءات', 'winRate': 'نسبة الفوز', 'matches': 'المباريات', 'wins': 'الانتصارات', 'losses': 'الخسائر',
      'pashaBenefits': 'مزايا الباشا', 'giftReward': 'مكافأة', 'watchAd': 'شاهد إعلاناً', 'groups': 'المجموعات', 'competitionsTitle': 'المنافسات',
      'socialLoginNote': 'يتطلب مفاتيح مزود الخدمة', 'cropAvatar': 'معاينة وقص الصورة', 'save': 'حفظ', 'cancel': 'إلغاء', 'activate': 'تفعيل',
    },
    'en': {
      'login': 'Sign in', 'register': 'Create account', 'username': 'Username', 'email': 'Email', 'password': 'Password',
      'guest': 'Continue as guest', 'profile': 'Profile', 'logout': 'Sign out', 'deleteAccount': 'Delete account', 'appearance': 'Appearance',
      'beginner': 'Beginner', 'professional': 'Professional', 'legendary': 'Legendary', 'covers': 'Profile Covers', 'bots': 'AI Players',
      'difficulty': 'AI Difficulty', 'easy': 'Easy', 'normal': 'Normal', 'pro': 'Pro', 'master': 'Master', 'noCode': 'No-code Designer',
      'statistics': 'Statistics', 'winRate': 'Win rate', 'matches': 'Matches', 'wins': 'Wins', 'losses': 'Losses',
      'pashaBenefits': 'Pasha Benefits', 'giftReward': 'Reward', 'watchAd': 'Watch Ad', 'groups': 'Groups', 'competitionsTitle': 'Competitions',
      'socialLoginNote': 'Provider credentials required', 'cropAvatar': 'Preview and crop image', 'save': 'Save', 'cancel': 'Cancel', 'activate': 'Activate',
    },
    'de': {
      'login': 'Anmelden', 'register': 'Konto erstellen', 'username': 'Benutzername', 'email': 'E-Mail', 'password': 'Passwort',
      'guest': 'Als Gast fortfahren', 'profile': 'Profil', 'logout': 'Abmelden', 'deleteAccount': 'Konto löschen', 'appearance': 'Darstellung',
      'beginner': 'Anfänger', 'professional': 'Profi', 'legendary': 'Legendär', 'covers': 'Profil-Cover', 'bots': 'KI-Spieler',
      'difficulty': 'KI-Schwierigkeit', 'easy': 'Einfach', 'normal': 'Normal', 'pro': 'Profi', 'master': 'Meister', 'noCode': 'No-Code-Designer',
      'statistics': 'Statistiken', 'winRate': 'Siegquote', 'matches': 'Spiele', 'wins': 'Siege', 'losses': 'Niederlagen',
      'pashaBenefits': 'Pasha-Vorteile', 'giftReward': 'Belohnung', 'watchAd': 'Werbung ansehen', 'groups': 'Gruppen', 'competitionsTitle': 'Wettbewerbe',
      'socialLoginNote': 'Anbieter-Zugangsdaten erforderlich', 'cropAvatar': 'Bild ansehen und zuschneiden', 'save': 'Speichern', 'cancel': 'Abbrechen', 'activate': 'Aktivieren',
    },
    'tr': {
      'login': 'Giriş yap', 'register': 'Hesap oluştur', 'username': 'Kullanıcı adı', 'email': 'E-posta', 'password': 'Şifre',
      'guest': 'Misafir olarak devam et', 'profile': 'Profil', 'logout': 'Çıkış yap', 'deleteAccount': 'Hesabı sil', 'appearance': 'Görünüm',
      'beginner': 'Başlangıç', 'professional': 'Profesyonel', 'legendary': 'Efsanevi', 'covers': 'Profil Kapakları', 'bots': 'Yapay Zekâ Oyuncuları',
      'difficulty': 'YZ Zorluğu', 'easy': 'Kolay', 'normal': 'Normal', 'pro': 'Profesyonel', 'master': 'Usta', 'noCode': 'Kodsuz Tasarımcı',
      'statistics': 'İstatistikler', 'winRate': 'Kazanma oranı', 'matches': 'Maçlar', 'wins': 'Galibiyet', 'losses': 'Mağlubiyet',
      'pashaBenefits': 'Paşa Avantajları', 'giftReward': 'Ödül', 'watchAd': 'Reklam izle', 'groups': 'Gruplar', 'competitionsTitle': 'Yarışmalar',
      'socialLoginNote': 'Sağlayıcı bilgileri gerekli', 'cropAvatar': 'Resmi önizle ve kırp', 'save': 'Kaydet', 'cancel': 'İptal', 'activate': 'Etkinleştir',
    },
    'fr': {
      'login': 'Connexion', 'register': 'Créer un compte', 'username': "Nom d'utilisateur", 'email': 'E-mail', 'password': 'Mot de passe',
      'guest': 'Continuer comme invité', 'profile': 'Profil', 'logout': 'Déconnexion', 'deleteAccount': 'Supprimer le compte', 'appearance': 'Apparence',
      'beginner': 'Débutant', 'professional': 'Professionnel', 'legendary': 'Légendaire', 'covers': 'Couvertures de profil', 'bots': 'Joueurs IA',
      'difficulty': "Difficulté de l'IA", 'easy': 'Facile', 'normal': 'Normal', 'pro': 'Pro', 'master': 'Maître', 'noCode': 'Designer sans code',
      'statistics': 'Statistiques', 'winRate': 'Taux de victoire', 'matches': 'Parties', 'wins': 'Victoires', 'losses': 'Défaites',
      'pashaBenefits': 'Avantages Pasha', 'giftReward': 'Récompense', 'watchAd': 'Regarder une pub', 'groups': 'Groupes', 'competitionsTitle': 'Compétitions',
      'socialLoginNote': 'Identifiants du fournisseur requis', 'cropAvatar': "Prévisualiser et recadrer l'image", 'save': 'Enregistrer', 'cancel': 'Annuler', 'activate': 'Activer',
    },
    'es': {
      'login': 'Iniciar sesión', 'register': 'Crear cuenta', 'username': 'Usuario', 'email': 'Correo', 'password': 'Contraseña',
      'guest': 'Continuar como invitado', 'profile': 'Perfil', 'logout': 'Cerrar sesión', 'deleteAccount': 'Eliminar cuenta', 'appearance': 'Apariencia',
      'beginner': 'Principiante', 'professional': 'Profesional', 'legendary': 'Legendario', 'covers': 'Portadas de perfil', 'bots': 'Jugadores IA',
      'difficulty': 'Dificultad de IA', 'easy': 'Fácil', 'normal': 'Normal', 'pro': 'Pro', 'master': 'Maestro', 'noCode': 'Diseñador sin código',
      'statistics': 'Estadísticas', 'winRate': 'Tasa de victoria', 'matches': 'Partidas', 'wins': 'Victorias', 'losses': 'Derrotas',
      'pashaBenefits': 'Beneficios Pasha', 'giftReward': 'Recompensa', 'watchAd': 'Ver anuncio', 'groups': 'Grupos', 'competitionsTitle': 'Competiciones',
      'socialLoginNote': 'Se requieren credenciales del proveedor', 'cropAvatar': 'Previsualizar y recortar imagen', 'save': 'Guardar', 'cancel': 'Cancelar', 'activate': 'Activar',
    },
  };

  static String t(String lang, String key) =>
      extra[lang]?[key] ?? data[lang]?[key] ?? extra['en']?[key] ?? data['en']?[key] ?? data['ar']?[key] ?? key;
}

class GameInfo {
  final String id;
  final String icon;
  final int players;
  final Color color;

  const GameInfo(this.id, this.icon, this.players, this.color);
}

const gamesCatalog = [
  GameInfo('tarneeb', '🂡', 18872, Color(0xff194c83)),
  GameInfo('trix', '🃏', 9456, Color(0xff7c3158)),
  GameInfo('hand', '🂮', 8154, Color(0xff845a20)),
  GameInfo('banakil', '🎴', 6420, Color(0xff4c3a82)),
  GameInfo('baloot', '♠️', 15220, Color(0xff17604c)),
  GameInfo('basra', '♦️', 5219, Color(0xff7a3037)),
  GameInfo('tarneeb_400', '4️⃣', 7315, Color(0xff6a2e52)),
  GameInfo('syrian_tarneeb', '🇸🇾', 3520, Color(0xff33573b)),
  GameInfo('trix_complex', '👑', 6189, Color(0xff69417b)),
  GameInfo('saudi_hand', '🇸🇦', 5791, Color(0xff1c654c)),
  GameInfo('hand_partner', '🤝', 4882, Color(0xff5f4327)),
  GameInfo('trix_partner', '👥', 5140, Color(0xff583a70)),
];

class StoreProduct {
  final String id;
  final String category;
  final String icon;
  final String nameAr;
  final String nameEn;
  final String descriptionAr;
  final String descriptionEn;
  final int price;
  final int? durationDays;
  final String? value;
  final double? multiplier;
  final Color? previewColor1;
  final Color? previewColor2;

  const StoreProduct({
    required this.id,
    required this.category,
    required this.icon,
    required this.nameAr,
    required this.nameEn,
    required this.descriptionAr,
    required this.descriptionEn,
    required this.price,
    this.durationDays,
    this.value,
    this.multiplier,
    this.previewColor1,
    this.previewColor2,
  });

  String name(String lang) => localizeStoreProductNameV151(this, lang);
  String description(String lang) => localizeStoreProductDescriptionV151(this, lang);
  bool get reusable => category == 'pasha' || category == 'boost';
  String get tier {
    final days = durationDays ?? 0;
    if (days >= 30 || price >= 25000 || id.contains('legend')) return 'legendary';
    if (days >= 7 || price >= 7000 || id.contains('pro')) return 'pro';
    return 'beginner';
  }

  String tierLabel(String lang) {
    if (lang == 'ar') return tier == 'legendary' ? 'أسطوري' : tier == 'pro' ? 'محترف' : 'مبتدئ';
    return tier == 'legendary' ? 'Legendary' : tier == 'pro' ? 'Professional' : 'Beginner';
  }
}

List<StoreProduct> buildTimedColorProducts() {
  const palette = <(String, String, String, Color)>[
    ('ruby', 'ياقوت متوهج', 'Ruby Glow', Color(0xffff355e)),
    ('orange', 'برتقالي شمسي', 'Solar Orange', Color(0xffff8a00)),
    ('amber', 'كهرماني فاخر', 'Luxury Amber', Color(0xffffc107)),
    ('gold', 'ذهبي ملكي', 'Royal Gold', Color(0xfff5c542)),
    ('lime', 'ليموني نيون', 'Neon Lime', Color(0xffa3e635)),
    ('emerald', 'زمرد عميق', 'Deep Emerald', Color(0xff10b981)),
    ('mint', 'نعناع نيون', 'Neon Mint', Color(0xff5eead4)),
    ('cyan', 'سماوي ليزر', 'Laser Cyan', Color(0xff22d3ee)),
    ('sky', 'أزرق سماوي', 'Sky Blue', Color(0xff38bdf8)),
    ('royal', 'أزرق ملكي', 'Royal Blue', Color(0xff3b82f6)),
    ('indigo', 'نيلي فاخر', 'Luxury Indigo', Color(0xff6366f1)),
    ('violet', 'بنفسجي مخملي', 'Velvet Violet', Color(0xff8b5cf6)),
    ('amethyst', 'جمشت لامع', 'Shiny Amethyst', Color(0xffc084fc)),
    ('pink', 'وردي لؤلؤي', 'Pearl Pink', Color(0xffec4899)),
    ('rose', 'وردي غروب', 'Sunset Rose', Color(0xfffb7185)),
    ('coral', 'مرجاني فاخر', 'Luxury Coral', Color(0xffff7f6e)),
    ('silver', 'فضي معدني', 'Metallic Silver', Color(0xffcbd5e1)),
    ('diamond', 'ألماسي جليدي', 'Ice Diamond', Color(0xffe0f2fe)),
    ('white', 'أبيض ناصع', 'Pure White', Color(0xffffffff)),
    ('bronze', 'برونزي قديم', 'Antique Bronze', Color(0xffc08457)),
    ('teal', 'تركوازي محترف', 'Pro Teal', Color(0xff14b8a6)),
    ('magenta', 'ماجنتا أسطوري', 'Legendary Magenta', Color(0xffd946ef)),
    ('crimson', 'قرمزي ناري', 'Fire Crimson', Color(0xffdc2626)),
    ('aurora', 'شفق قطبي', 'Aurora', Color(0xff67e8f9)),
  ];
  const durations = <int>[3, 7, 30];
  final result = <StoreProduct>[];
  for (var index = 0; index < palette.length; index++) {
    final item = palette[index];
    final days = durations[index % durations.length];
    final base = days == 3 ? 2400 : days == 7 ? 6200 : 16800;
    final price = base + (index * 175);
    final hex = '#${item.$4.value.toRadixString(16).padLeft(8, '0').substring(2)}';
    result.add(StoreProduct(
      id: 'player_color_${item.$1}_${days}d', category: 'names', icon: '✨',
      nameAr: 'لون لاعب ${item.$2} — $days أيام', nameEn: '${item.$3} player color — $days days',
      descriptionAr: 'لون متوهج حول صورة اللاعب واسمه داخل البروفايل والطاولة مع معاينة مباشرة.',
      descriptionEn: 'Glowing player color around the avatar and name with a live preview.',
      price: price, durationDays: days, value: hex, previewColor1: item.$4, previewColor2: const Color(0xff07111c),
    ));
    result.add(StoreProduct(
      id: 'chat_color_${item.$1}_${days}d', category: 'chat_colors', icon: '💬',
      nameAr: 'لون دردشة ${item.$2} — $days أيام', nameEn: '${item.$3} chat color — $days days',
      descriptionAr: 'لون فاخر لرسائلك داخل الدردشة العامة والخاصة ودردشة الغرفة.',
      descriptionEn: 'Premium color for public, private and in-room chat messages.',
      price: price + 900, durationDays: days, value: hex, previewColor1: item.$4, previewColor2: const Color(0xff111827),
    ));
  }
  return result;
}


final List<StoreProduct> products = <StoreProduct>[
  StoreProduct(id: "pasha_1_day_v132", category: "pasha", icon: "🎩", nameAr: "باشا يوم واحد", nameEn: "Pasha 1 Day", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 1700, durationDays: 1, value: "pasha"),
  StoreProduct(id: "pasha_3_days_v132", category: "pasha", icon: "🎩", nameAr: "باشا 3 أيام", nameEn: "Pasha 3 Days", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 5000, durationDays: 3, value: "pasha"),
  StoreProduct(id: "pasha_7_days_v128", category: "pasha", icon: "🎩", nameAr: "باشا 7 أيام", nameEn: "Pasha 7 Days", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 10000, durationDays: 7, value: "pasha"),
  StoreProduct(id: "pasha_30_days_v128", category: "pasha", icon: "🎩", nameAr: "باشا 30 يوم", nameEn: "Pasha 30 Days", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 38000, durationDays: 30, value: "pasha"),
  StoreProduct(id: "pasha_90_days_v132", category: "pasha", icon: "🎩", nameAr: "باشا 90 يوم", nameEn: "Pasha 90 Days", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 105000, durationDays: 90, value: "pasha"),
  StoreProduct(id: "pasha_365_days_v128", category: "pasha", icon: "🎩", nameAr: "باشا سنة كاملة", nameEn: "Pasha 365 Days", descriptionAr: "طرد من الغرفة، شارة باشا، إنشاء مجموعات ومنافسات، ومضاعفة خبرة حسب الخطة.", descriptionEn: "Pasha badge, room controls, club and tournament privileges, and bonus XP.", price: 300000, durationDays: 365, value: "pasha"),
  StoreProduct(id: "theme_dark_premium", category: "themes", icon: "🎨", nameAr: "داكن فاخر", nameEn: "Premium Dark", descriptionAr: "ثيم كامل يغيّر الخلفيات والبطاقات والأزرار والطاولات.", descriptionEn: "A complete theme for backgrounds, panels, buttons and tables.", price: 0, value: "dark", previewColor1: Color(0xff07111f), previewColor2: Color(0xffd6aa59)),
  StoreProduct(id: "theme_crimson_legend", category: "themes", icon: "🌹", nameAr: "قرمزي أسطوري", nameEn: "Legendary Crimson", descriptionAr: "ثيم قرمزي داكن مع وهج ذهبي وبطاقات احترافية.", descriptionEn: "Deep crimson theme with gold glow and premium panels.", price: 34000, value: "crimson", previewColor1: Color(0xff21070d), previewColor2: Color(0xffef334f)),
  StoreProduct(id: "theme_midnight_elite", category: "themes", icon: "🌌", nameAr: "منتصف الليل النخبوي", nameEn: "Elite Midnight", descriptionAr: "ثيم أزرق ليلي احترافي للغرف والمتجر والملف الشخصي.", descriptionEn: "Premium midnight-blue theme for rooms, store and profile.", price: 42000, value: "midnight", previewColor1: Color(0xff020617), previewColor2: Color(0xff4f86ff)),
  StoreProduct(id: "theme_aurora_supreme", category: "themes", icon: "🌈", nameAr: "الشفق الفاخر", nameEn: "Supreme Aurora", descriptionAr: "ثيم تركوازي متوهج بمظهر عالمي وهادئ.", descriptionEn: "Glowing aurora theme with a refined global look.", price: 52000, value: "aurora", previewColor1: Color(0xff04171b), previewColor2: Color(0xff67e8f9)),
  StoreProduct(id: "theme_emerald", category: "themes", icon: "🎨", nameAr: "زمردي ملكي", nameEn: "Royal Emerald", descriptionAr: "ثيم كامل يغيّر الخلفيات والبطاقات والأزرار والطاولات.", descriptionEn: "A complete theme for backgrounds, panels, buttons and tables.", price: 11800, value: "emerald", previewColor1: Color(0xff052e26), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "theme_royal", category: "themes", icon: "🎨", nameAr: "أزرق ملكي", nameEn: "Royal Blue", descriptionAr: "ثيم كامل يغيّر الخلفيات والبطاقات والأزرار والطاولات.", descriptionEn: "A complete theme for backgrounds, panels, buttons and tables.", price: 12500, value: "royal", previewColor1: Color(0xff071b3d), previewColor2: Color(0xff3b82f6)),
  StoreProduct(id: "theme_purple", category: "themes", icon: "🎨", nameAr: "بنفسجي أسطوري", nameEn: "Legendary Purple", descriptionAr: "ثيم كامل يغيّر الخلفيات والبطاقات والأزرار والطاولات.", descriptionEn: "A complete theme for backgrounds, panels, buttons and tables.", price: 14500, value: "purple", previewColor1: Color(0xff2e1065), previewColor2: Color(0xffa855f7)),
  StoreProduct(id: "theme_classic", category: "themes", icon: "🎨", nameAr: "كلاسيكي فاخر", nameEn: "Luxury Classic", descriptionAr: "ثيم كامل يغيّر الخلفيات والبطاقات والأزرار والطاولات.", descriptionEn: "A complete theme for backgrounds, panels, buttons and tables.", price: 9800, value: "classic", previewColor1: Color(0xff2b2118), previewColor2: Color(0xffd2a85f)),
  StoreProduct(id: "theme_obsidian", category: "themes", icon: "🖤", nameAr: "أوبسيديان فاخر", nameEn: "Luxury Obsidian", descriptionAr: "ثيم أسود معدني هادئ للواجهات الاحترافية.", descriptionEn: "A refined metallic-black professional theme.", price: 24000, value: "obsidian", previewColor1: Color(0xff020307), previewColor2: Color(0xff6b7280)),
  StoreProduct(id: "theme_rose_gold", category: "themes", icon: "🌸", nameAr: "روز غولد", nameEn: "Rose Gold", descriptionAr: "ثيم وردي ذهبي أنيق للبروفايل والمتجر والغرف.", descriptionEn: "Elegant rose-gold theme for profiles, store and rooms.", price: 28000, value: "rose_gold", previewColor1: Color(0xff351322), previewColor2: Color(0xfffb7185)),
  StoreProduct(id: "theme_desert", category: "themes", icon: "🏜️", nameAr: "الصحراء الملكية", nameEn: "Royal Desert", descriptionAr: "ثيم دافئ بدرجات الرمل والذهب.", descriptionEn: "Warm sand-and-gold premium theme.", price: 22000, value: "desert", previewColor1: Color(0xff3b2410), previewColor2: Color(0xffd97706)),
  StoreProduct(id: "theme_forest", category: "themes", icon: "🌲", nameAr: "الغابة العميقة", nameEn: "Deep Forest", descriptionAr: "ثيم أخضر عميق بإضاءة زمردية.", descriptionEn: "Deep green theme with emerald highlights.", price: 26000, value: "forest", previewColor1: Color(0xff03140d), previewColor2: Color(0xff22c55e)),
  StoreProduct(id: "theme_ice", category: "themes", icon: "❄️", nameAr: "الجليد الأزرق", nameEn: "Blue Ice", descriptionAr: "ثيم جليدي نظيف بدرجات الأزرق والسماوي.", descriptionEn: "Clean icy theme in blue and cyan tones.", price: 30000, value: "ice", previewColor1: Color(0xff06131d), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_01", category: "tables", icon: "👑", nameAr: "طاولة زمرد ملكي", nameEn: "Premium Table 01", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 3150, previewColor1: Color(0xff064e3b), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "table_premium_02", category: "tables", icon: "⭐", nameAr: "طاولة ليل ذهبي", nameEn: "Premium Table 02", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 3800, previewColor1: Color(0xff020617), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "table_premium_03", category: "tables", icon: "🏜️", nameAr: "طاولة صحراء فاخرة", nameEn: "Premium Table 03", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 4450, previewColor1: Color(0xff7c2d12), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "table_premium_04", category: "tables", icon: "🌊", nameAr: "طاولة محيط أزرق", nameEn: "Premium Table 04", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 5100, previewColor1: Color(0xff0c4a6e), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_05", category: "tables", icon: "♥", nameAr: "طاولة قصر قرمزي", nameEn: "Premium Table 05", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 5750, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfff87171)),
  StoreProduct(id: "table_premium_06", category: "tables", icon: "🌌", nameAr: "طاولة مجرة بنفسجية", nameEn: "Premium Table 06", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 6400, previewColor1: Color(0xff581c87), previewColor2: Color(0xffc084fc)),
  StoreProduct(id: "table_premium_07", category: "tables", icon: "◆", nameAr: "طاولة رخام أسود", nameEn: "Premium Table 07", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 7050, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffa3a3a3)),
  StoreProduct(id: "table_premium_08", category: "tables", icon: "◇", nameAr: "طاولة رخام أبيض", nameEn: "Premium Table 08", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 7700, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "table_premium_09", category: "tables", icon: "☕", nameAr: "طاولة خشب قهوة", nameEn: "Premium Table 09", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 8350, previewColor1: Color(0xff422006), previewColor2: Color(0xffb45309)),
  StoreProduct(id: "table_premium_10", category: "tables", icon: "♣", nameAr: "طاولة زيتوني فاخر", nameEn: "Premium Table 10", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 9000, previewColor1: Color(0xff1f3b24), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "table_premium_11", category: "tables", icon: "♦", nameAr: "طاولة روبي كازينو", nameEn: "Premium Table 11", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 9650, previewColor1: Color(0xff064e3b), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "table_premium_12", category: "tables", icon: "♠", nameAr: "طاولة ياقوت أزرق", nameEn: "Premium Table 12", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 10300, previewColor1: Color(0xff020617), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "table_premium_13", category: "tables", icon: "☀", nameAr: "طاولة كثبان ذهبية", nameEn: "Premium Table 13", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 10950, previewColor1: Color(0xff7c2d12), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "table_premium_14", category: "tables", icon: "❄", nameAr: "طاولة جليد فاخر", nameEn: "Premium Table 14", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 11600, previewColor1: Color(0xff0c4a6e), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_15", category: "tables", icon: "⚡", nameAr: "طاولة مدينة نيون", nameEn: "Premium Table 15", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 12250, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfff87171)),
  StoreProduct(id: "table_premium_16", category: "tables", icon: "🌲", nameAr: "طاولة غابة ضباب", nameEn: "Premium Table 16", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 12900, previewColor1: Color(0xff581c87), previewColor2: Color(0xffc084fc)),
  StoreProduct(id: "table_premium_17", category: "tables", icon: "👑", nameAr: "طاولة ملكي أحمر", nameEn: "Premium Table 17", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 13550, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffa3a3a3)),
  StoreProduct(id: "table_premium_18", category: "tables", icon: "🥉", nameAr: "طاولة قاعة برونزية", nameEn: "Premium Table 18", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 14200, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "table_premium_19", category: "tables", icon: "🥈", nameAr: "طاولة قاعة فضية", nameEn: "Premium Table 19", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 14850, previewColor1: Color(0xff422006), previewColor2: Color(0xffb45309)),
  StoreProduct(id: "table_premium_20", category: "tables", icon: "💎", nameAr: "طاولة قاعة ألماس", nameEn: "Premium Table 20", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 15500, previewColor1: Color(0xff1f3b24), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "table_premium_21", category: "tables", icon: "✦", nameAr: "طاولة أرابيسك أخضر", nameEn: "Premium Table 21", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 16150, previewColor1: Color(0xff064e3b), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "table_premium_22", category: "tables", icon: "✧", nameAr: "طاولة أرابيسك أزرق", nameEn: "Premium Table 22", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 16800, previewColor1: Color(0xff020617), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "table_premium_23", category: "tables", icon: "🌴", nameAr: "طاولة نخيل VIP", nameEn: "Premium Table 23", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 17450, previewColor1: Color(0xff7c2d12), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "table_premium_24", category: "tables", icon: "🌙", nameAr: "طاولة ضوء القمر", nameEn: "Premium Table 24", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 18100, previewColor1: Color(0xff0c4a6e), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_25", category: "tables", icon: "🌅", nameAr: "طاولة غروب فاخر", nameEn: "Premium Table 25", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 18750, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfff87171)),
  StoreProduct(id: "table_premium_26", category: "tables", icon: "♣", nameAr: "طاولة نادي الجاد", nameEn: "Premium Table 26", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 19400, previewColor1: Color(0xff581c87), previewColor2: Color(0xffc084fc)),
  StoreProduct(id: "table_premium_27", category: "tables", icon: "🔥", nameAr: "طاولة نار أوبسيديان", nameEn: "Premium Table 27", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 20050, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffa3a3a3)),
  StoreProduct(id: "table_premium_28", category: "tables", icon: "●", nameAr: "طاولة لؤلؤ ذهبي", nameEn: "Premium Table 28", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 20700, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "table_premium_29", category: "tables", icon: "♛", nameAr: "طاولة بلاتيني ملكي", nameEn: "Premium Table 29", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 21350, previewColor1: Color(0xff422006), previewColor2: Color(0xffb45309)),
  StoreProduct(id: "table_premium_30", category: "tables", icon: "⚔", nameAr: "طاولة فولاذ دمشقي", nameEn: "Premium Table 30", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 22000, previewColor1: Color(0xff1f3b24), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "table_premium_31", category: "tables", icon: "🌹", nameAr: "طاولة روز جولد", nameEn: "Premium Table 31", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 22650, previewColor1: Color(0xff064e3b), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "table_premium_32", category: "tables", icon: "⬢", nameAr: "طاولة سايبر أخضر", nameEn: "Premium Table 32", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 23300, previewColor1: Color(0xff020617), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "table_premium_33", category: "tables", icon: "⬡", nameAr: "طاولة سايبر بنفسجي", nameEn: "Premium Table 33", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 23950, previewColor1: Color(0xff7c2d12), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "table_premium_34", category: "tables", icon: "☪", nameAr: "طاولة مجلس ذهبي", nameEn: "Premium Table 34", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 24600, previewColor1: Color(0xff0c4a6e), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_35", category: "tables", icon: "✺", nameAr: "طاولة شامي أزرق", nameEn: "Premium Table 35", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 25250, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfff87171)),
  StoreProduct(id: "table_premium_36", category: "tables", icon: "🐚", nameAr: "طاولة لؤلؤ الخليج", nameEn: "Premium Table 36", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 25900, previewColor1: Color(0xff581c87), previewColor2: Color(0xffc084fc)),
  StoreProduct(id: "table_premium_37", category: "tables", icon: "🏆", nameAr: "طاولة بطولة ذهبية", nameEn: "Premium Table 37", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 26550, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffa3a3a3)),
  StoreProduct(id: "table_premium_38", category: "tables", icon: "VIP", nameAr: "طاولة VIP أسود", nameEn: "Premium Table 38", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 27200, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "table_premium_39", category: "tables", icon: "♚", nameAr: "طاولة رويال ماستر", nameEn: "Premium Table 39", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 27850, previewColor1: Color(0xff422006), previewColor2: Color(0xffb45309)),
  StoreProduct(id: "table_premium_40", category: "tables", icon: "🦁", nameAr: "طاولة حلبة الأساطير", nameEn: "Premium Table 40", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 28500, previewColor1: Color(0xff1f3b24), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "table_premium_41", category: "tables", icon: "🎩", nameAr: "طاولة مخمل VIP", nameEn: "Premium Table 41", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 29150, previewColor1: Color(0xff064e3b), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "table_premium_42", category: "tables", icon: "✺", nameAr: "طاولة أندلس ذهب", nameEn: "Premium Table 42", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 29800, previewColor1: Color(0xff020617), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "table_premium_43", category: "tables", icon: "✦", nameAr: "طاولة دمشق أزرق", nameEn: "Premium Table 43", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 30450, previewColor1: Color(0xff7c2d12), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "table_premium_44", category: "tables", icon: "☪", nameAr: "طاولة قدس زيتوني", nameEn: "Premium Table 44", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 31100, previewColor1: Color(0xff0c4a6e), previewColor2: Color(0xff38bdf8)),
  StoreProduct(id: "table_premium_45", category: "tables", icon: "🎩", nameAr: "طاولة طربوش باشا", nameEn: "Premium Table 45", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 31750, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfff87171)),
  StoreProduct(id: "table_premium_46", category: "tables", icon: "◆", nameAr: "طاولة نيون أحمر", nameEn: "Premium Table 46", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 32400, previewColor1: Color(0xff581c87), previewColor2: Color(0xffc084fc)),
  StoreProduct(id: "table_premium_47", category: "tables", icon: "⬢", nameAr: "طاولة نيون أخضر", nameEn: "Premium Table 47", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 33050, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffa3a3a3)),
  StoreProduct(id: "table_premium_48", category: "tables", icon: "💠", nameAr: "طاولة كريستال محيط", nameEn: "Premium Table 48", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 33700, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "table_premium_49", category: "tables", icon: "♛", nameAr: "طاولة أسود ذهبي Pro", nameEn: "Premium Table 49", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 34350, previewColor1: Color(0xff422006), previewColor2: Color(0xffb45309)),
  StoreProduct(id: "table_premium_50", category: "tables", icon: "🏆", nameAr: "طاولة ماستر أرينا", nameEn: "Premium Table 50", descriptionAr: "طاولة كبيرة منحنية بمعاينة حقيقية وتفاصيل فاخرة داخل غرفة اللعب.", descriptionEn: "Large curved game table with a live in-room preview.", price: 35000, previewColor1: Color(0xff1f3b24), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "cardback_01", category: "cards", icon: "♣", nameAr: "ظهر ورق كلاسيك أخضر", nameEn: "Card Back 01", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 2320, previewColor1: Color(0xff064e3b), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "cardback_02", category: "cards", icon: "♦", nameAr: "ظهر ورق ملكي ذهبي", nameEn: "Card Back 02", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 2840, previewColor1: Color(0xff111827), previewColor2: Color(0xfffacc15)),
  StoreProduct(id: "cardback_03", category: "cards", icon: "♠", nameAr: "ظهر ورق ليل أسود", nameEn: "Card Back 03", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 3360, previewColor1: Color(0xff020617), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "cardback_04", category: "cards", icon: "♥", nameAr: "ظهر ورق أزرق ملكي", nameEn: "Card Back 04", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 3880, previewColor1: Color(0xff1e3a8a), previewColor2: Color(0xff60a5fa)),
  StoreProduct(id: "cardback_05", category: "cards", icon: "★", nameAr: "ظهر ورق أحمر كازينو", nameEn: "Card Back 05", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 4400, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfffca5a5)),
  StoreProduct(id: "cardback_06", category: "cards", icon: "✦", nameAr: "ظهر ورق بنفسجي أسطوري", nameEn: "Card Back 06", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 4920, previewColor1: Color(0xff581c87), previewColor2: Color(0xffd8b4fe)),
  StoreProduct(id: "cardback_07", category: "cards", icon: "👑", nameAr: "ظهر ورق زمردي", nameEn: "Card Back 07", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 5440, previewColor1: Color(0xff065f46), previewColor2: Color(0xff34d399)),
  StoreProduct(id: "cardback_08", category: "cards", icon: "💎", nameAr: "ظهر ورق ياقوتي", nameEn: "Card Back 08", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 5960, previewColor1: Color(0xff881337), previewColor2: Color(0xfffb7185)),
  StoreProduct(id: "cardback_09", category: "cards", icon: "🔥", nameAr: "ظهر ورق رخام أسود", nameEn: "Card Back 09", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 6480, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffd4d4d4)),
  StoreProduct(id: "cardback_10", category: "cards", icon: "⚡", nameAr: "ظهر ورق رخام أبيض", nameEn: "Card Back 10", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 7000, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff64748b)),
  StoreProduct(id: "cardback_11", category: "cards", icon: "🌙", nameAr: "ظهر ورق خشبي", nameEn: "Card Back 11", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 7520, previewColor1: Color(0xff422006), previewColor2: Color(0xfff59e0b)),
  StoreProduct(id: "cardback_12", category: "cards", icon: "☀", nameAr: "ظهر ورق ناري", nameEn: "Card Back 12", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 8040, previewColor1: Color(0xff431407), previewColor2: Color(0xfff97316)),
  StoreProduct(id: "cardback_13", category: "cards", icon: "🦁", nameAr: "ظهر ورق ثلجي", nameEn: "Card Back 13", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 8560, previewColor1: Color(0xffe0f2fe), previewColor2: Color(0xff0284c7)),
  StoreProduct(id: "cardback_14", category: "cards", icon: "🐉", nameAr: "ظهر ورق فضي", nameEn: "Card Back 14", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 9080, previewColor1: Color(0xff1f2937), previewColor2: Color(0xffd1d5db)),
  StoreProduct(id: "cardback_15", category: "cards", icon: "🦅", nameAr: "ظهر ورق برونزي", nameEn: "Card Back 15", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 9600, previewColor1: Color(0xff431407), previewColor2: Color(0xffcd7f32)),
  StoreProduct(id: "cardback_16", category: "cards", icon: "🏆", nameAr: "ظهر ورق ألماسي", nameEn: "Card Back 16", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 10120, previewColor1: Color(0xff0f172a), previewColor2: Color(0xffe0f2fe)),
  StoreProduct(id: "cardback_17", category: "cards", icon: "♣", nameAr: "ظهر ورق فلسطيني فاخر", nameEn: "Card Back 17", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 10640, previewColor1: Color(0xff064e3b), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "cardback_18", category: "cards", icon: "♦", nameAr: "ظهر ورق خليجي لؤلؤ", nameEn: "Card Back 18", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 11160, previewColor1: Color(0xff111827), previewColor2: Color(0xfffacc15)),
  StoreProduct(id: "cardback_19", category: "cards", icon: "♠", nameAr: "ظهر ورق شامي أرابيسك", nameEn: "Card Back 19", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 11680, previewColor1: Color(0xff020617), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "cardback_20", category: "cards", icon: "♥", nameAr: "ظهر ورق مغربي زخرفي", nameEn: "Card Back 20", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 12200, previewColor1: Color(0xff1e3a8a), previewColor2: Color(0xff60a5fa)),
  StoreProduct(id: "cardback_21", category: "cards", icon: "★", nameAr: "ظهر ورق نيون سايبر", nameEn: "Card Back 21", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 12720, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfffca5a5)),
  StoreProduct(id: "cardback_22", category: "cards", icon: "✦", nameAr: "ظهر ورق جالاكسي", nameEn: "Card Back 22", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 13240, previewColor1: Color(0xff581c87), previewColor2: Color(0xffd8b4fe)),
  StoreProduct(id: "cardback_23", category: "cards", icon: "👑", nameAr: "ظهر ورق قمر الليل", nameEn: "Card Back 23", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 13760, previewColor1: Color(0xff065f46), previewColor2: Color(0xff34d399)),
  StoreProduct(id: "cardback_24", category: "cards", icon: "💎", nameAr: "ظهر ورق شمس ذهب", nameEn: "Card Back 24", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 14280, previewColor1: Color(0xff881337), previewColor2: Color(0xfffb7185)),
  StoreProduct(id: "cardback_25", category: "cards", icon: "🔥", nameAr: "ظهر ورق وردة ذهبية", nameEn: "Card Back 25", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 14800, previewColor1: Color(0xff0a0a0a), previewColor2: Color(0xffd4d4d4)),
  StoreProduct(id: "cardback_26", category: "cards", icon: "⚡", nameAr: "ظهر ورق صقر", nameEn: "Card Back 26", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 15320, previewColor1: Color(0xfff8fafc), previewColor2: Color(0xff64748b)),
  StoreProduct(id: "cardback_27", category: "cards", icon: "🌙", nameAr: "ظهر ورق أسد", nameEn: "Card Back 27", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 15840, previewColor1: Color(0xff422006), previewColor2: Color(0xfff59e0b)),
  StoreProduct(id: "cardback_28", category: "cards", icon: "☀", nameAr: "ظهر ورق تنين", nameEn: "Card Back 28", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 16360, previewColor1: Color(0xff431407), previewColor2: Color(0xfff97316)),
  StoreProduct(id: "cardback_29", category: "cards", icon: "🦁", nameAr: "ظهر ورق بطولة", nameEn: "Card Back 29", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 16880, previewColor1: Color(0xffe0f2fe), previewColor2: Color(0xff0284c7)),
  StoreProduct(id: "cardback_30", category: "cards", icon: "🐉", nameAr: "ظهر ورق VIP أسود", nameEn: "Card Back 30", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 17400, previewColor1: Color(0xff1f2937), previewColor2: Color(0xffd1d5db)),
  StoreProduct(id: "cardback_31", category: "cards", icon: "🦅", nameAr: "ظهر ورق باشا ملكي", nameEn: "Card Back 31", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 17920, previewColor1: Color(0xff431407), previewColor2: Color(0xffcd7f32)),
  StoreProduct(id: "cardback_32", category: "cards", icon: "🏆", nameAr: "ظهر ورق تركس خاص", nameEn: "Card Back 32", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 18440, previewColor1: Color(0xff0f172a), previewColor2: Color(0xffe0f2fe)),
  StoreProduct(id: "cardback_33", category: "cards", icon: "♣", nameAr: "ظهر ورق طرنيب خاص", nameEn: "Card Back 33", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 18960, previewColor1: Color(0xff064e3b), previewColor2: Color(0xfff5c542)),
  StoreProduct(id: "cardback_34", category: "cards", icon: "♦", nameAr: "ظهر ورق بلوت خاص", nameEn: "Card Back 34", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 19480, previewColor1: Color(0xff111827), previewColor2: Color(0xfffacc15)),
  StoreProduct(id: "cardback_35", category: "cards", icon: "♠", nameAr: "ظهر ورق هاند خاص", nameEn: "Card Back 35", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 20000, previewColor1: Color(0xff020617), previewColor2: Color(0xff94a3b8)),
  StoreProduct(id: "cardback_36", category: "cards", icon: "♥", nameAr: "ظهر ورق نادي خاص", nameEn: "Card Back 36", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 20520, previewColor1: Color(0xff1e3a8a), previewColor2: Color(0xff60a5fa)),
  StoreProduct(id: "cardback_37", category: "cards", icon: "★", nameAr: "ظهر ورق مهرجان خاص", nameEn: "Card Back 37", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 21040, previewColor1: Color(0xff7f1d1d), previewColor2: Color(0xfffca5a5)),
  StoreProduct(id: "cardback_38", category: "cards", icon: "✦", nameAr: "ظهر ورق أرابيسك خاص", nameEn: "Card Back 38", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 21560, previewColor1: Color(0xff581c87), previewColor2: Color(0xffd8b4fe)),
  StoreProduct(id: "cardback_39", category: "cards", icon: "👑", nameAr: "ظهر ورق ماستر", nameEn: "Card Back 39", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 22080, previewColor1: Color(0xff065f46), previewColor2: Color(0xff34d399)),
  StoreProduct(id: "cardback_40", category: "cards", icon: "💎", nameAr: "ظهر ورق أسطورة", nameEn: "Card Back 40", descriptionAr: "ظهر ورق فاخر يظهر كما هو داخل يد اللاعب وعلى الطاولة.", descriptionEn: "Premium card back applied to the player hand and game table.", price: 22600, previewColor1: Color(0xff881337), previewColor2: Color(0xfffb7185)),
  StoreProduct(id: "emoji_free_basic", category: "emoji", icon: "😀😄😂👍👏👋", nameAr: "إيموجي مجانية", nameEn: "Free Emojis", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 0),
  StoreProduct(id: "emoji_beginner_fun", category: "emoji", icon: "😊😉😎🤩🥳", nameAr: "إيموجي مبتدئ مرحة", nameEn: "Beginner Fun", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 1000),
  StoreProduct(id: "emoji_medium_react", category: "emoji", icon: "😡😢😭😱🤔☕", nameAr: "إيموجي تفاعل متوسط", nameEn: "Medium Reactions", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 5000),
  StoreProduct(id: "emoji_pro_power", category: "emoji", icon: "🔥⚡💎🏆👑🛡️", nameAr: "إيموجي محترف قوية", nameEn: "Pro Power", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 10000),
  StoreProduct(id: "emoji_legend_big", category: "emoji", icon: "🦁🐉🦅🌌💥🎆", nameAr: "إيموجي أسطورية كبيرة", nameEn: "Legendary Big", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 15000),
  StoreProduct(id: "emoji_animated_vip", category: "emoji", icon: "😂🔥👑💎⚡🏆🎉", nameAr: "إيموجي متحركة VIP", nameEn: "Animated VIP", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 15000),
  StoreProduct(id: "emoji_huge_reactions", category: "emoji", icon: "😂👑🔥😡😭🤯", nameAr: "ردود فعل عملاقة", nameEn: "Huge Reactions", descriptionAr: "حزمة ردود سريعة كبيرة مع مؤثرات وصوت داخل الدردشة واللعبة.", descriptionEn: "Large quick reactions with animation and sound.", price: 50000),
  StoreProduct(id: "xp_x1_25", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×1.25", nameEn: "XP Booster ×1.25", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 12000, multiplier: 1.25, previewColor1: Color(0xff22c55e), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "xp_x1_5", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×1.5", nameEn: "XP Booster ×1.5", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 25000, multiplier: 1.5, previewColor1: Color(0xff38bdf8), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "xp_x2", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×2", nameEn: "XP Booster ×2", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 52000, multiplier: 2.0, previewColor1: Color(0xffa855f7), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "xp_x3", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×3", nameEn: "XP Booster ×3", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 110000, multiplier: 3.0, previewColor1: Color(0xfff97316), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "xp_x4", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×4", nameEn: "XP Booster ×4", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 190000, multiplier: 4.0, previewColor1: Color(0xfffacc15), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "xp_x5", category: "boost", icon: "🚀", nameAr: "مسرّع نقاط ×5", nameEn: "XP Booster ×5", descriptionAr: "يتفعّل لمدة 24 ساعة ويضاعف XP المكتسبة من الجولات والفوز.", descriptionEn: "Activates for 24 hours and multiplies earned XP.", price: 320000, multiplier: 5.0, previewColor1: Color(0xffef4444), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "nameframe_gold_aura", category: "names", icon: "✨", nameAr: "إطار اسم هالة ذهبية", nameEn: "Gold Aura Name Frame", descriptionAr: "معاينة فورية على صورة اللاعب واسمه في البروفايل والغرف.", descriptionEn: "Live preview on the avatar and player name.", price: 9000, value: "#f5c542", previewColor1: Color(0xfff5c542), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "nameframe_emerald_lux", category: "names", icon: "💚", nameAr: "إطار اسم زمرد فاخر", nameEn: "Emerald Lux Name Frame", descriptionAr: "معاينة فورية على صورة اللاعب واسمه في البروفايل والغرف.", descriptionEn: "Live preview on the avatar and player name.", price: 11000, value: "#10b981", previewColor1: Color(0xff10b981), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "nameframe_ruby_flash", category: "names", icon: "❤️", nameAr: "إطار اسم روبي لامع", nameEn: "Ruby Flash Name Frame", descriptionAr: "معاينة فورية على صورة اللاعب واسمه في البروفايل والغرف.", descriptionEn: "Live preview on the avatar and player name.", price: 13000, value: "#ef4444", previewColor1: Color(0xffef4444), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "nameframe_diamond_pulse", category: "names", icon: "💎", nameAr: "إطار اسم ألماس نابض", nameEn: "Diamond Pulse Name Frame", descriptionAr: "معاينة فورية على صورة اللاعب واسمه في البروفايل والغرف.", descriptionEn: "Live preview on the avatar and player name.", price: 17000, value: "#e0f2fe", previewColor1: Color(0xffe0f2fe), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_red", category: "names", icon: "🅰️", nameAr: "أحمر لامع", nameEn: "Bright Red", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#ef4444", previewColor1: Color(0xffef4444), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_blue", category: "names", icon: "🅰️", nameAr: "أزرق ملكي", nameEn: "Royal Blue", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#3b82f6", previewColor1: Color(0xff3b82f6), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_green", category: "names", icon: "🅰️", nameAr: "أخضر زمردي", nameEn: "Emerald Green", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#22c55e", previewColor1: Color(0xff22c55e), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_gold", category: "names", icon: "🅰️", nameAr: "ذهبي", nameEn: "Gold", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#facc15", previewColor1: Color(0xfffacc15), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_purple", category: "names", icon: "🅰️", nameAr: "بنفسجي", nameEn: "Purple", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#a855f7", previewColor1: Color(0xffa855f7), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_cyan", category: "names", icon: "🅰️", nameAr: "سماوي", nameEn: "Cyan", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#06b6d4", previewColor1: Color(0xff06b6d4), previewColor2: const Color(0xff111827)),
  StoreProduct(id: "name_white", category: "names", icon: "🅰️", nameAr: "أبيض ألماسي", nameEn: "Diamond White", descriptionAr: "لون اسم واضح مع معاينة مباشرة على البروفايل والطاولة.", descriptionEn: "Player-name color with a live profile and table preview.", price: 6500, value: "#ffffff", previewColor1: Color(0xffffffff), previewColor2: const Color(0xff111827)),
  ...buildTimedColorProducts(),
  StoreProduct(id: "badge_king", category: "badges", icon: "👑", nameAr: "شارة الملك", nameEn: "King Badge", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 30000),
  StoreProduct(id: "badge_pro", category: "badges", icon: "🔥", nameAr: "شارة المحترف", nameEn: "Pro Badge", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 30000),
  StoreProduct(id: "badge_fairplay", category: "badges", icon: "🛡️", nameAr: "شارة اللعب النظيف", nameEn: "Fair Play Badge", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 30000),
  StoreProduct(id: "effect_gold_entry", category: "effects", icon: "✨", nameAr: "دخول ذهبي", nameEn: "Golden Entry", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 42000),
  StoreProduct(id: "effect_fire_win", category: "effects", icon: "🔥", nameAr: "احتفال فوز ناري", nameEn: "Fire Win Celebration", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 48000),
  StoreProduct(id: "effect_royal_confetti", category: "effects", icon: "🎉", nameAr: "قصاصات ملكية", nameEn: "Royal Confetti", descriptionAr: "عنصر تجميلي فاخر يظهر في الملف الشخصي وغرفة اللعب.", descriptionEn: "Premium cosmetic shown in the profile and game room.", price: 55000),
  StoreProduct(id: "cover_royal_gold", category: "covers", icon: "👑", nameAr: "غلاف الذهب الملكي", nameEn: "Royal Gold Cover", descriptionAr: "غلاف بروفايل متحرك بخطوط ذهبية هادئة.", descriptionEn: "Animated profile cover with subtle gold lines.", price: 2500, value: "cover_royal_gold", previewColor1: Color(0xff4b2d08), previewColor2: Color(0xffd39b2a)),
  StoreProduct(id: "cover_midnight", category: "covers", icon: "🌙", nameAr: "غلاف منتصف الليل", nameEn: "Midnight Cover", descriptionAr: "خلفية ليلية عميقة بوهج أزرق.", descriptionEn: "Deep night cover with blue glow.", price: 3800, value: "cover_midnight", previewColor1: Color(0xff020617), previewColor2: Color(0xff1d4ed8)),
  StoreProduct(id: "cover_emerald", category: "covers", icon: "💚", nameAr: "غلاف زمرد القصر", nameEn: "Palace Emerald Cover", descriptionAr: "غلاف أخضر فاخر مناسب للباشا والمحترفين.", descriptionEn: "Premium green cover for VIP and pro players.", price: 5200, value: "cover_emerald", previewColor1: Color(0xff022c22), previewColor2: Color(0xff10b981)),
  StoreProduct(id: "cover_crimson", category: "covers", icon: "🔥", nameAr: "غلاف القرمزي", nameEn: "Crimson Cover", descriptionAr: "خلفية قرمزية ديناميكية للفائزين.", descriptionEn: "Dynamic crimson cover for winners.", price: 6900, value: "cover_crimson", previewColor1: Color(0xff450a0a), previewColor2: Color(0xffef4444)),
  StoreProduct(id: "cover_aurora", category: "covers", icon: "🌌", nameAr: "غلاف الشفق القطبي", nameEn: "Aurora Cover", descriptionAr: "ألوان متدرجة هادئة بحركة ضوئية بسيطة.", descriptionEn: "Smooth gradients with subtle light motion.", price: 8500, value: "cover_aurora", previewColor1: Color(0xff042f2e), previewColor2: Color(0xff7c3aed)),
  StoreProduct(id: "cover_sapphire", category: "covers", icon: "💎", nameAr: "غلاف الياقوت الأزرق", nameEn: "Sapphire Cover", descriptionAr: "غلاف أزرق ملكي بإطار ألماسي.", descriptionEn: "Royal blue cover with diamond framing.", price: 11000, value: "cover_sapphire", previewColor1: Color(0xff172554), previewColor2: Color(0xff3b82f6)),
  StoreProduct(id: "cover_rose", category: "covers", icon: "🌹", nameAr: "غلاف روز غولد", nameEn: "Rose Gold Cover", descriptionAr: "خلفية أنيقة بدرجات الوردي والذهبي.", descriptionEn: "Elegant rose and gold profile cover.", price: 12500, value: "cover_rose", previewColor1: Color(0xff4c0519), previewColor2: Color(0xfffb7185)),
  StoreProduct(id: "cover_desert", category: "covers", icon: "🏜️", nameAr: "غلاف رمال الصحراء", nameEn: "Desert Sand Cover", descriptionAr: "خلفية دافئة بطابع عربي فاخر.", descriptionEn: "Warm premium cover with an Arab-inspired look.", price: 14500, value: "cover_desert", previewColor1: Color(0xff422006), previewColor2: Color(0xffd97706)),
  StoreProduct(id: "cover_obsidian", category: "covers", icon: "🖤", nameAr: "غلاف الأوبسيديان", nameEn: "Obsidian Cover", descriptionAr: "غلاف أسود معدني للنخبة.", descriptionEn: "Metallic black elite profile cover.", price: 18000, value: "cover_obsidian", previewColor1: Color(0xff030712), previewColor2: Color(0xff374151)),
  StoreProduct(id: "cover_pasha", category: "covers", icon: "🎩", nameAr: "غلاف قصر الباشا", nameEn: "Pasha Palace Cover", descriptionAr: "غلاف أحمر وذهبي مع هوية الطربوش.", descriptionEn: "Red and gold cover featuring the Pasha identity.", price: 24000, value: "cover_pasha", previewColor1: Color(0xff3f0a0a), previewColor2: Color(0xfff59e0b)),
  StoreProduct(id: "cover_cosmic", category: "covers", icon: "🪐", nameAr: "غلاف المجرة", nameEn: "Cosmic Cover", descriptionAr: "غلاف كوني أسطوري بإضاءات متحركة هادئة.", descriptionEn: "Legendary cosmic cover with subtle moving lights.", price: 32000, value: "cover_cosmic", previewColor1: Color(0xff12033a), previewColor2: Color(0xff06b6d4)),
  StoreProduct(id: "cover_elite", category: "covers", icon: "🛡️", nameAr: "غلاف النخبة البيضاء", nameEn: "White Elite Cover", descriptionAr: "غلاف فضي نظيف لأعلى المستويات.", descriptionEn: "Clean silver cover for top-level players.", price: 40000, value: "cover_elite", previewColor1: Color(0xff334155), previewColor2: Color(0xffe2e8f0)),
  StoreProduct(id: "cover_phoenix", category: "covers", icon: "🔥", nameAr: "غلاف العنقاء الذهبية", nameEn: "Golden Phoenix Cover", descriptionAr: "غلاف ناري ذهبي بانسيابية هادئة.", descriptionEn: "Golden fire cover with subtle motion.", price: 44000, value: "cover_phoenix", previewColor1: Color(0xff3b0a08), previewColor2: Color(0xffffd166)),
  StoreProduct(id: "cover_ocean", category: "covers", icon: "🌊", nameAr: "غلاف موج المحيط", nameEn: "Ocean Wave Cover", descriptionAr: "أمواج زرقاء فاخرة وهادئة.", descriptionEn: "Premium calm ocean waves.", price: 18000, value: "cover_ocean", previewColor1: Color(0xff021b36), previewColor2: Color(0xff22d3ee)),
  StoreProduct(id: "cover_neon", category: "covers", icon: "🌃", nameAr: "غلاف مدينة النيون", nameEn: "Neon City Cover", descriptionAr: "ألوان نيون عصرية ولامعة.", descriptionEn: "Modern glowing neon city.", price: 29000, value: "cover_neon", previewColor1: Color(0xff090217), previewColor2: Color(0xffec4899)),
  StoreProduct(id: "cover_forest", category: "covers", icon: "🌲", nameAr: "غلاف الغابة الملكية", nameEn: "Royal Forest Cover", descriptionAr: "غابة زمردية بهوية هادئة.", descriptionEn: "Emerald forest identity.", price: 21000, value: "cover_forest", previewColor1: Color(0xff032018), previewColor2: Color(0xff84cc16)),
  StoreProduct(id: "cover_sunset", category: "covers", icon: "🌅", nameAr: "غلاف غروب فاخر", nameEn: "Luxury Sunset Cover", descriptionAr: "غروب برتقالي ذهبي مميز.", descriptionEn: "Distinct orange-gold sunset.", price: 23000, value: "cover_sunset", previewColor1: Color(0xff431407), previewColor2: Color(0xfffbbf24)),
  StoreProduct(id: "cover_ice", category: "covers", icon: "❄️", nameAr: "غلاف الكريستال الجليدي", nameEn: "Ice Crystal Cover", descriptionAr: "كريستال سماوي متوهج.", descriptionEn: "Glowing sky-blue crystal.", price: 27000, value: "cover_ice", previewColor1: Color(0xff082f49), previewColor2: Color(0xffe0f2fe)),
  StoreProduct(id: "cover_tiger", category: "covers", icon: "🐯", nameAr: "غلاف هيبة النمر", nameEn: "Tiger Prestige Cover", descriptionAr: "هوية نمرية ذهبية قوية.", descriptionEn: "Strong golden tiger identity.", price: 36000, value: "cover_tiger", previewColor1: Color(0xff1c1005), previewColor2: Color(0xffffd166)),
  StoreProduct(id: "cover_eagle", category: "covers", icon: "🦅", nameAr: "غلاف جناح النسر", nameEn: "Eagle Wing Cover", descriptionAr: "غلاف فضي داكن للنخبة.", descriptionEn: "Dark silver elite cover.", price: 38000, value: "cover_eagle", previewColor1: Color(0xff0f172a), previewColor2: Color(0xfff8fafc)),
  StoreProduct(id: "cover_lava", category: "covers", icon: "🌋", nameAr: "غلاف الحمم الأسطورية", nameEn: "Legendary Lava Cover", descriptionAr: "حمم قرمزية متحركة بهدوء.", descriptionEn: "Subtle animated crimson lava.", price: 47000, value: "cover_lava", previewColor1: Color(0xff260303), previewColor2: Color(0xffff6b00)),
  StoreProduct(id: "cover_pearl", category: "covers", icon: "🦪", nameAr: "غلاف لؤلؤة القصر", nameEn: "Palace Pearl Cover", descriptionAr: "لؤلؤ بنفسجي أبيض أنيق.", descriptionEn: "Elegant purple-white pearl.", price: 52000, value: "cover_pearl", previewColor1: Color(0xff312e3b), previewColor2: Color(0xfffffbeb)),
];

StoreProduct? storeProductById(String id) {
  for (final product in products) {
    if (product.id == id) return product;
  }
  return null;
}

class AppLoadingScreen extends StatelessWidget {
  const AppLoadingScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 82,
              height: 82,
              alignment: Alignment.center,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primary, Theme.of(context).colorScheme.secondary]),
                boxShadow: [BoxShadow(color: Theme.of(context).colorScheme.primary.withOpacity(.25), blurRadius: 32)],
              ),
              child: const Text('W', style: TextStyle(color: Color(0xff07111c), fontSize: 38, fontWeight: FontWeight.w900)),
            ),
            const SizedBox(height: 20),
            const Text('WARQNA', style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, letterSpacing: 4)),
            const SizedBox(height: 18),
            const SizedBox(width: 150, child: LinearProgressIndicator()),
          ],
        ),
      ),
    );
  }
}

class LoginScreen extends StatefulWidget {
  final AppController controller;
  const LoginScreen({super.key, required this.controller});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final loginController = TextEditingController(text: 'Adnan');
  final passwordController = TextEditingController(text: 'Adnan123');
  final emailController = TextEditingController();
  bool registerMode = false;
  bool obscure = true;
  bool busy = false;
  String? error;

  @override
  void dispose() {
    loginController.dispose();
    passwordController.dispose();
    emailController.dispose();
    super.dispose();
  }

  Future<void> submit({bool offline = false}) async {
    setState(() { busy = true; error = null; });
    final result = registerMode
        ? await widget.controller.register(loginController.text, emailController.text, passwordController.text)
        : await widget.controller.login(loginController.text, passwordController.text, offline: offline);
    if (!mounted) return;
    setState(() { busy = false; error = result; });
  }

  Future<void> forgotPassword() async {
    final email = TextEditingController(text: emailController.text.trim());
    final sent = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text(widget.controller.localeCode == 'ar' ? 'استعادة كلمة المرور' : 'Reset password'),
        content: TextField(
          controller: email,
          keyboardType: TextInputType.emailAddress,
          autofocus: true,
          decoration: InputDecoration(
            labelText: authTextV151(widget.controller.localeCode, 'email'),
            prefixIcon: const Icon(Icons.alternate_email_rounded),
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: Text(widget.controller.localeCode == 'ar' ? 'إلغاء' : 'Cancel')),
          FilledButton(onPressed: () => Navigator.pop(dialogContext, true), child: Text(widget.controller.localeCode == 'ar' ? 'إرسال الرابط' : 'Send link')),
        ],
      ),
    ) ?? false;
    if (!sent) { email.dispose(); return; }
    final value = email.text.trim();
    email.dispose();
    if (value.isEmpty || !value.contains('@')) {
      if (mounted) showToast(context, widget.controller.localeCode == 'ar' ? 'أدخل بريدًا إلكترونيًا صحيحًا.' : 'Enter a valid email address.');
      return;
    }
    setState(() { busy = true; error = null; });
    try {
      final result = await widget.controller.api.forgotPassword(value);
      if (mounted) showToast(context, result['message']?.toString() ?? 'تم إرسال التعليمات.');
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  Future<void> chooseDemoAccount() async {
    const accounts = <(String, String, String)>[
      ('Adnan', 'Adnan123', 'مدير • 1,000,000,000,000,000,000 توكن'),
      ('Kareem', 'Kareem123', 'لاعب مستوى 42'),
      ('Rami', 'Rami12345', 'لاعب مستوى 35'),
      ('Lina', 'Lina12345', 'لاعبة مستوى 28'),
      ('Samar', 'Samar12345', 'لاعبة مستوى 24'),
      ('Layla', 'Layla12345', 'لاعبة مستوى 31'),
      ('Jameel', 'Jameel12345', 'لاعب مستوى 22'),
      ('Nour', 'Nour12345', 'لاعبة مستوى 19'),
      ('Omar', 'Omar12345', 'لاعب مستوى 27'),
      ('Sara', 'Sara12345', 'لاعبة مستوى 29'),
      ('Basel', 'Basel12345', 'لاعب مستوى 33'),
      ('Hala', 'Hala12345', 'لاعبة مستوى 25'),
      ('Yazan', 'Yazan12345', 'لاعب مستوى 30'),
    ];
    final selected = await showModalBottomSheet<(String, String, String)>(
      context: context,
      showDragHandle: true,
      builder: (sheetContext) => SafeArea(
        child: ListView(
          shrinkWrap: true,
          padding: const EdgeInsets.fromLTRB(12, 0, 12, 16),
          children: [
            const Text('حسابات التجربة', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
            const SizedBox(height: 8),
            ...accounts.map((account) => ListTile(
              leading: CircleAvatar(child: Text(account.$1.substring(0, 1))),
              title: Text(account.$1, style: const TextStyle(fontWeight: FontWeight.w900)),
              subtitle: Text('${account.$2} • ${account.$3}'),
              trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 15),
              onTap: () => Navigator.pop(sheetContext, account),
            )),
          ],
        ),
      ),
    );
    if (selected == null) return;
    loginController.text = selected.$1;
    passwordController.text = selected.$2;
    setState(() { registerMode = false; error = null; });
    await submit(offline: true);
  }

  @override
  Widget build(BuildContext context) {
    final palette = AppPalette.fromCode(widget.controller.themeCode);
    final lang = widget.controller.localeCode;
    return Scaffold(
      body: Stack(
        children: [
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                gradient: RadialGradient(
                  center: const Alignment(.8, -.9),
                  radius: 1.4,
                  colors: [palette.accent.withOpacity(.32), palette.bg, const Color(0xff030810)],
                ),
              ),
            ),
          ),
          Positioned(top: -80, right: -70, child: _GlowOrb(color: palette.gold, size: 230)),
          Positioned(bottom: -100, left: -90, child: _GlowOrb(color: palette.green, size: 260)),
          SafeArea(
            child: Center(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: ConstrainedBox(
                  constraints: const BoxConstraints(maxWidth: 440),
                  child: Column(
                    children: [
                      Container(
                        width: 92,
                        height: 92,
                        alignment: Alignment.center,
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(29),
                          gradient: LinearGradient(colors: [palette.gold, const Color(0xffb67a20)]),
                          boxShadow: [BoxShadow(color: palette.gold.withOpacity(.35), blurRadius: 34, offset: const Offset(0, 16))],
                        ),
                        child: const Text('W', style: TextStyle(color: Color(0xff15100a), fontSize: 46, fontWeight: FontWeight.w900)),
                      ),
                      const SizedBox(height: 18),
                      const Text('WARQNA', style: TextStyle(fontSize: 30, fontWeight: FontWeight.w900, letterSpacing: 5)),
                      const SizedBox(height: 5),
                      Text(authTextV151(lang, 'tagline'), style: const TextStyle(color: Colors.white60, fontWeight: FontWeight.w700)),
                      const SizedBox(height: 28),
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: palette.panel.withOpacity(.94),
                          borderRadius: BorderRadius.circular(27),
                          border: Border.all(color: Colors.white.withOpacity(.09)),
                          boxShadow: [BoxShadow(color: Colors.black.withOpacity(.38), blurRadius: 35, offset: const Offset(0, 20))],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Text(registerMode ? authTextV151(lang, 'newAccount') : authTextV151(lang, 'login'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
                            const SizedBox(height: 5),
                            Text(registerMode ? authTextV151(lang, 'registerSubtitle') : authTextV151(lang, 'loginSubtitle'), style: const TextStyle(color: Colors.white60, fontSize: 12)),
                            const SizedBox(height: 18),
                            TextField(
                              controller: loginController,
                              decoration: InputDecoration(
                                labelText: registerMode ? authTextV151(lang, 'username') : authTextV151(lang, 'userOrEmail'),
                                prefixIcon: const Icon(Icons.person_outline_rounded),
                              ),
                            ),
                            if (registerMode) ...[
                              const SizedBox(height: 11),
                              TextField(
                                controller: emailController,
                                keyboardType: TextInputType.emailAddress,
                                decoration: InputDecoration(labelText: authTextV151(lang, 'email'), prefixIcon: const Icon(Icons.alternate_email_rounded)),
                              ),
                            ],
                            const SizedBox(height: 11),
                            TextField(
                              controller: passwordController,
                              obscureText: obscure,
                              onSubmitted: (_) => submit(),
                              decoration: InputDecoration(
                                labelText: authTextV151(lang, 'password'),
                                prefixIcon: const Icon(Icons.lock_outline_rounded),
                                suffixIcon: IconButton(onPressed: () => setState(() => obscure = !obscure), icon: Icon(obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined)),
                              ),
                            ),
                            if (!registerMode) Align(
                              alignment: AlignmentDirectional.centerEnd,
                              child: TextButton.icon(
                                onPressed: busy ? null : forgotPassword,
                                icon: const Icon(Icons.key_rounded, size: 17),
                                label: Text(lang == 'ar' ? 'نسيت كلمة المرور؟' : 'Forgot password?'),
                              ),
                            ),
                            if (error != null) ...[
                              const SizedBox(height: 12),
                              Container(
                                padding: const EdgeInsets.all(13),
                                decoration: BoxDecoration(color: Colors.red.withOpacity(.12), borderRadius: BorderRadius.circular(13), border: Border.all(color: Colors.red.withOpacity(.25))),
                                child: Text(error!, style: const TextStyle(color: Color(0xffff9a9a), fontSize: 12, height: 1.5)),
                              ),
                            ],
                            const SizedBox(height: 17),
                            FilledButton.icon(
                              onPressed: busy ? null : () => submit(),
                              icon: busy ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : Icon(registerMode ? Icons.person_add_alt_1 : Icons.login_rounded),
                              label: Text(registerMode ? authTextV151(lang, 'create') : authTextV151(lang, 'secure')),
                              style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
                            ),
                            if (!registerMode) ...[
                              const SizedBox(height: 10),
                              OutlinedButton.icon(
                                onPressed: busy || warqnaProductionMode ? null : chooseDemoAccount,
                                icon: const Icon(Icons.groups_2_outlined),
                                label: Text(authTextV151(lang, 'chooseDemo')),
                                style: OutlinedButton.styleFrom(minimumSize: const Size.fromHeight(48)),
                              ),
                              const SizedBox(height: 7),
                              Text(authTextV151(lang, 'fallback'), textAlign: TextAlign.center, style: const TextStyle(color: Colors.white54, fontSize: 9, height: 1.5)),
                              Padding(padding: const EdgeInsets.symmetric(vertical: 5), child: Row(children: [const Expanded(child: Divider()), Padding(padding: const EdgeInsets.symmetric(horizontal: 9), child: Text(authTextV151(lang, 'orVia'), style: const TextStyle(fontSize: 10, color: Colors.white54))), const Expanded(child: Divider())])),
                              Row(children: [
                                Expanded(child: OutlinedButton.icon(onPressed: busy ? null : () => showToast(context, 'تسجيل Google جاهز للربط بعد إضافة مفاتيح OAuth في الخادم.'), icon: const Text('G', style: TextStyle(fontWeight: FontWeight.w900)), label: const FittedBox(fit: BoxFit.scaleDown, child: Text('Google', maxLines: 1, softWrap: false, style: TextStyle(fontSize: 10))))),
                                const SizedBox(width: 6),
                                Expanded(child: OutlinedButton.icon(onPressed: busy ? null : () => showToast(context, 'تسجيل Apple جاهز للربط بعد إضافة Service ID ومفتاح Apple.'), icon: const Icon(Icons.apple, size: 18), label: const FittedBox(fit: BoxFit.scaleDown, child: Text('Apple', maxLines: 1, softWrap: false, style: TextStyle(fontSize: 10))))),
                                const SizedBox(width: 6),
                                Expanded(child: OutlinedButton.icon(onPressed: busy ? null : () => showToast(context, 'تسجيل Facebook جاهز للربط بعد إضافة App ID وSecret.'), icon: const Text('f', style: TextStyle(fontWeight: FontWeight.w900)), label: const FittedBox(fit: BoxFit.scaleDown, child: Text('Facebook', maxLines: 1, softWrap: false, style: TextStyle(fontSize: 9))))),
                              ]),
                              const SizedBox(height: 7),
                              FilledButton.tonalIcon(onPressed: busy || warqnaProductionMode ? null : widget.controller.loginAsGuest, icon: const Icon(Icons.person_outline_rounded), label: Text(authTextV151(lang, 'guest'))) ,
                              const SizedBox(height: 5),
                              Text(authTextV151(lang, 'providerNote'), textAlign: TextAlign.center, style: TextStyle(color: palette.gold.withOpacity(.9), fontSize: 9, height: 1.5)),
                            ],
                            const SizedBox(height: 8),
                            TextButton(
                              onPressed: busy ? null : () => setState(() { registerMode = !registerMode; error = null; }),
                              child: Text(registerMode ? authTextV151(lang, 'haveAccount') : authTextV151(lang, 'noAccount')),
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      Text(warqnaProductionMode ? 'حسابات خادم حقيقية • جلسات آمنة • حماية واستعادة للحساب' : 'حسابات مستقلة • حفظ محلي آمن للتجربة • مزامنة عند ربط الخادم', textAlign: TextAlign.center, style: TextStyle(color: Colors.white30, fontSize: 9)),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _GlowOrb extends StatelessWidget {
  final Color color;
  final double size;
  const _GlowOrb({required this.color, required this.size});

  @override
  Widget build(BuildContext context) => Container(
        width: size,
        height: size,
        decoration: BoxDecoration(shape: BoxShape.circle, color: color.withOpacity(.09), boxShadow: [BoxShadow(color: color.withOpacity(.16), blurRadius: 90, spreadRadius: 28)]),
      );
}

class HomeShell extends StatefulWidget {
  final AppController controller;

  const HomeShell({super.key, required this.controller});

  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int index = 2;

  @override
  Widget build(BuildContext context) {
    final pages = [
      StorePage(controller: widget.controller),
      GamesPage(controller: widget.controller),
      HomePage(controller: widget.controller, onTab: (v) => setState(() => index = v)),
      ClubsPage(controller: widget.controller),
      EventsPage(controller: widget.controller),
    ];
    return Scaffold(
      body: SafeArea(
        bottom: false,
        child: Column(
          children: [
            PremiumTopBar(controller: widget.controller),
            Expanded(child: IndexedStack(index: index, children: pages)),
          ],
        ),
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: index,
        onDestinationSelected: (value) => setState(() => index = value),
        destinations: [
          NavigationDestination(icon: const Icon(Icons.redeem), label: L.t(widget.controller.localeCode, 'store')),
          NavigationDestination(icon: const Icon(Icons.style), label: L.t(widget.controller.localeCode, 'games')),
          NavigationDestination(icon: const Icon(Icons.home_rounded), label: L.t(widget.controller.localeCode, 'home')),
          NavigationDestination(icon: const Icon(Icons.shield), label: L.t(widget.controller.localeCode, 'clubs')),
          NavigationDestination(icon: const Icon(Icons.calendar_month), label: L.t(widget.controller.localeCode, 'events')),
        ],
      ),
    );
  }
}

class PremiumTopBar extends StatelessWidget {
  final AppController controller;

  const PremiumTopBar({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    final unread = controller.notices.where((e) => !e.read).length;
    return Container(
      padding: const EdgeInsets.fromLTRB(13, 8, 8, 8),
      decoration: BoxDecoration(
        color: Theme.of(context).scaffoldBackgroundColor.withOpacity(.95),
        border: Border(bottom: BorderSide(color: Colors.white.withOpacity(.07))),
      ),
      child: Row(
        children: [
          Expanded(
            child: InkWell(
              borderRadius: BorderRadius.circular(30),
              onTap: () => showProfile(context, controller),
              child: Row(
                children: [
                  AccountAvatar(controller: controller, size: 41),
                  const SizedBox(width: 7),
                  Expanded(child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(controller.displayName, maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(fontWeight: FontWeight.w900, color: colorFromHex(controller.selectedNameColor))),
                      Row(children: [
                        Container(width: 6, height: 6, decoration: BoxDecoration(shape: BoxShape.circle, color: controller.serverConnected ? Colors.greenAccent : Colors.amber)),
                        const SizedBox(width: 4),
                        Flexible(child: Text(controller.serverConnected ? 'متصل' : 'محلي', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 8, color: Colors.white60))),
                      ]),
                    ],
                  )),
                ],
              ),
            ),
          ),
          IconButton(
            tooltip: controller.landscapeMode ? 'العودة للوضع الطولي' : 'تفعيل العرض الأفقي',
            onPressed: controller.toggleOrientationMode,
            icon: Icon(controller.landscapeMode ? Icons.stay_current_portrait : Icons.stay_current_landscape),
          ),
          IconButton(
            tooltip: L.t(lang, 'friends'),
            onPressed: () => showFriends(context, controller),
            icon: Badge(
              isLabelVisible: controller.incomingRequests.isNotEmpty,
              label: Text('${controller.incomingRequests.length}'),
              child: const Icon(Icons.people_alt_outlined),
            ),
          ),
          if (controller.isAdmin)
            IconButton(
              tooltip: 'لوحة الإدارة',
              onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => AdminDashboardPage(controller: controller))),
              icon: const Icon(Icons.admin_panel_settings_outlined),
            ),
          IconButton(
            onPressed: () => showNotifications(context, controller),
            icon: Badge(
              isLabelVisible: unread > 0,
              label: Text('$unread'),
              child: const Icon(Icons.notifications_none_rounded),
            ),
          ),
          PopupMenuButton<String>(
            tooltip: L.t(lang, 'language'),
            icon: Text(lang.toUpperCase(), style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900)),
            onSelected: controller.changeLocale,
            itemBuilder: (_) => const [
              PopupMenuItem(value: 'ar', child: Text('العربية 🇸🇦')),
              PopupMenuItem(value: 'en', child: Text('English 🇬🇧')),
              PopupMenuItem(value: 'de', child: Text('Deutsch 🇩🇪')),
              PopupMenuItem(value: 'tr', child: Text('Türkçe 🇹🇷')),
              PopupMenuItem(value: 'fr', child: Text('Français 🇫🇷')),
              PopupMenuItem(value: 'es', child: Text('Español 🇪🇸')),
            ],
          ),
          PopupMenuButton<String>(
            tooltip: L.t(lang, 'theme'),
            icon: const Icon(Icons.palette_outlined),
            initialValue: controller.themeCode,
            onSelected: controller.changeTheme,
            itemBuilder: (_) => v151ThemeOptions
                .map((theme) => PopupMenuItem<String>(value: theme.$1, child: Row(children: [CircleAvatar(radius: 7, backgroundColor: theme.$3), const SizedBox(width: 8), Text(theme.$2)])))
                .toList(),
          ),
        ],
      ),
    );
  }
}

class HomePage extends StatelessWidget {
  final AppController controller;
  final ValueChanged<int> onTab;

  const HomePage({super.key, required this.controller, required this.onTab});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    return ListView(
      padding: const EdgeInsets.all(13),
      children: [
        Row(
          children: [
            Expanded(child: LevelCard(controller: controller)),
            const SizedBox(width: 8),
            Expanded(child: StatCard(icon: '🪙', label: L.t(lang, 'coins'), value: formatNumber(controller.coins), onTap: () => showWallet(context, controller))),
            const SizedBox(width: 8),
            Expanded(child: PashaStatCardV151(controller: controller, onTap: () => showPashaBenefits(context, controller))),
          ],
        ),
        const SizedBox(height: 13),
        HeroBanner(
          lang: lang,
          onJoin: () => showCompetitions(context, controller),
        ),
        const SizedBox(height: 13),
        GiftRoad(controller: controller),
        const SizedBox(height: 16),
        SectionTitle(title: L.t(lang, 'featured'), action: 'عرض الكل', onTap: () => onTab(1)),
        const SizedBox(height: 9),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            crossAxisSpacing: 8,
            mainAxisSpacing: 8,
            childAspectRatio: .86,
          ),
          itemCount: 3,
          itemBuilder: (_, i) => GameCard(
            game: gamesCatalog[i],
            lang: lang,
            onTap: () => showGameLobby(context, controller, gamesCatalog[i]),
          ),
        ),
        const SizedBox(height: 13),
        Row(
          children: [
            Expanded(
              child: PremiumActionButton(
                icon: Icons.handshake,
                title: L.t(lang, 'friendly'),
                color: Theme.of(context).colorScheme.secondary,
                onPressed: () => showGameLobby(context, controller, gamesCatalog[1]),
              ),
            ),
            const SizedBox(width: 9),
            Expanded(
              child: PremiumActionButton(
                icon: Icons.emoji_events,
                title: L.t(lang, 'competitions'),
                color: const Color(0xffa06f1d),
                onPressed: () => showCompetitions(context, controller),
              ),
            ),
          ],
        ),
        const SizedBox(height: 13),
        PremiumPanel(
          child: Wrap(
            alignment: WrapAlignment.spaceAround,
            runAlignment: WrapAlignment.center,
            children: [
              QuickButton(icon: '🎯', label: L.t(lang, 'challenges'), onTap: () => showChallenges(context, controller)),
              QuickButton(icon: '🏆', label: L.t(lang, 'competitions'), onTap: () => showCompetitions(context, controller)),
              QuickButton(icon: '🛡️', label: L.t(lang, 'clubs'), onTap: () => onTab(3)),
              QuickButton(icon: '🎁', label: L.t(lang, 'rewards'), onTap: () => showRewards(context, controller)),
              QuickButton(icon: '👥', label: L.t(lang, 'friends'), onTap: () => showFriends(context, controller)),
              QuickButton(icon: '⚙️', label: L.t(lang, 'settings'), onTap: () => showSettings(context, controller)),
            ],
          ),
        ),
      ],
    );
  }
}

class GamesPage extends StatefulWidget {
  final AppController controller;

  const GamesPage({super.key, required this.controller});

  @override
  State<GamesPage> createState() => _GamesPageState();
}

class _GamesPageState extends State<GamesPage> {
  String query = '';

  @override
  Widget build(BuildContext context) {
    final lang = widget.controller.localeCode;
    final visible = gamesCatalog.where((g) {
      final name = L.t(lang, g.id).toLowerCase();
      return name.contains(query.toLowerCase());
    }).toList();
    return ListView(
      padding: const EdgeInsets.all(13),
      children: [
        SectionTitle(
          title: L.t(lang, 'games'),
          action: '+ ${L.t(lang, 'createRoom')}',
          onTap: () => showCreateRoom(context, widget.controller, gamesCatalog[1]),
        ),
        const SizedBox(height: 10),
        TextField(
          onChanged: (value) => setState(() => query = value),
          decoration: InputDecoration(
            hintText: L.t(lang, 'search'),
            prefixIcon: const Icon(Icons.search),
          ),
        ),
        const SizedBox(height: 12),
        GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 3,
            crossAxisSpacing: 8,
            mainAxisSpacing: 8,
            childAspectRatio: .82,
          ),
          itemCount: visible.length,
          itemBuilder: (_, i) => GameCard(
            game: visible[i],
            lang: lang,
            onTap: () => showGameLobby(context, widget.controller, visible[i]),
          ),
        ),
        const SizedBox(height: 13),
        PremiumPanel(
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              QuickButton(icon: '📊', label: L.t(lang, 'leaderboard'), onTap: () => showLeaderboard(context)),
              QuickButton(icon: '📖', label: L.t(lang, 'rules'), onTap: () => showRules(context, lang, 'tarneeb')),
              QuickButton(icon: '🏆', label: L.t(lang, 'competitions'), onTap: () => showCompetitions(context, widget.controller)),
              QuickButton(icon: '👥', label: L.t(lang, 'friends'), onTap: () => showFriends(context, widget.controller)),
            ],
          ),
        ),
      ],
    );
  }
}

class StorePage extends StatefulWidget {
  final AppController controller;

  const StorePage({super.key, required this.controller});

  @override
  State<StorePage> createState() => _StorePageState();
}

class _StorePageState extends State<StorePage> {
  String category = 'all';
  String tier = 'all';
  String query = '';

  @override
  Widget build(BuildContext context) {
    final lang = widget.controller.localeCode;
    final visible = products.where((p) {
      if (!widget.controller.isStoreProductVisible(p)) return false;
      final categoryMatch = category == 'all' || p.category == category;
      final tierMatch = tier == 'all' || p.tier == tier;
      final text = '${p.name(lang)} ${p.description(lang)}'.toLowerCase();
      return categoryMatch && tierMatch && text.contains(query.toLowerCase());
    }).toList();
    final categories = const [
      ('all', 'الكل'),
      ('pasha', 'الباشا'),
      ('themes', 'الثيمات'),
      ('tables', 'الطاولات'),
      ('cards', 'ظهر الورق'),
      ('emoji', 'الإيموجي'),
      ('boost', 'المسرعات'),
      ('names', 'ألوان اللاعب'),
      ('chat_colors', 'ألوان الدردشة'),
      ('badges', 'الشارات'),
      ('effects', 'المؤثرات'),
      ('covers', 'أغلفة البروفايل'),
    ];
    return ListView(
      padding: const EdgeInsets.all(13),
      children: [
        SectionTitle(
          title: L.t(lang, 'store'),
          action: '🪙 ${formatNumber(widget.controller.coins)}',
          onTap: () => showWallet(context, widget.controller),
        ),
        const SizedBox(height: 10),
        PremiumPanel(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(children: [
              const Text('🛍️', style: TextStyle(fontSize: 36)),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text('${products.length} عنصر فاخر', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
                Text('طاولات ${products.where((p) => p.category == 'tables').length} • أظهر ورق ${products.where((p) => p.category == 'cards').length} • شراء بتأكيد ومعاينة مباشرة', style: const TextStyle(color: Colors.white60, fontSize: 9, height: 1.4)),
              ])),
              Chip(label: Text('${widget.controller.owned.length} مملوك')),
            ]),
          ),
        ),
        const SizedBox(height: 10),
        TextField(
          onChanged: (value) => setState(() => query = value),
          decoration: const InputDecoration(prefixIcon: Icon(Icons.search_rounded), hintText: 'ابحث في الطاولات والثيمات والبطاقات والعناصر...'),
        ),
        const SizedBox(height: 10),
        SizedBox(
          height: 48,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: categories.length,
            separatorBuilder: (_, __) => const SizedBox(width: 6),
            itemBuilder: (_, i) {
              final entry = categories[i];
              return ChoiceChip(
                label: Text(entry.$2, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w900)),
                selected: category == entry.$1,
                onSelected: (_) => setState(() => category = entry.$1),
              );
            },
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            const Text('التصنيف', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: Colors.white60)),
            const SizedBox(width: 8),
            for (final entry in const [('all', 'الكل'), ('beginner', 'مبتدئ'), ('pro', 'محترف'), ('legendary', 'أسطوري')])
              Padding(
                padding: const EdgeInsetsDirectional.only(end: 5),
                child: FilterChip(
                  label: Text(entry.$2, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w900)),
                  selected: tier == entry.$1,
                  onSelected: (_) => setState(() => tier = entry.$1),
                ),
              ),
          ],
        ),
        const SizedBox(height: 12),
        LayoutBuilder(
          builder: (context, constraints) {
            final columns = constraints.maxWidth >= 1050 ? 4 : constraints.maxWidth >= 720 ? 3 : 2;
            return GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: columns,
                crossAxisSpacing: 12,
                mainAxisSpacing: 12,
                childAspectRatio: columns == 2 ? .68 : .76,
              ),
              itemCount: visible.length,
              itemBuilder: (_, i) => ProductCard(
                controller: widget.controller,
                product: visible[i],
              ),
            );
          },
        ),
        const SizedBox(height: 12),
        PremiumPanel(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Text(
              'لا تُخصم التوكنز من اللعب أو الغرف. الخصم يتم داخل المتجر فقط وبعد رسالة تأكيد واضحة.',
              style: TextStyle(color: Colors.white.withOpacity(.65), fontSize: 11, height: 1.6),
            ),
          ),
        ),
      ],
    );
  }
}

class ClubsPage extends StatelessWidget {
  final AppController controller;

  const ClubsPage({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    final clubs = const [
      ('falcons', '🦅', 'صقور العرب', 18, 46, 315000, 'متصدر دوري المجموعات'),
      ('kings', '👑', 'ملوك الورق', 25, 50, 728000, 'مجموعة احترافية • دخول بالباشا'),
      ('friends', '🤝', 'رفاق اللعب', 12, 31, 146000, 'مجتمع ودي وتحديات يومية'),
      ('aces', '🂡', 'نخبة الآسات', 31, 48, 910000, 'بطولات أسبوعية وجوائز كبيرة'),
    ];
    final currentMatches = clubs.where((c) => c.$1 == controller.activeClub).toList();
    final current = currentMatches.isEmpty ? null : currentMatches.first;
    return ListView(
      padding: const EdgeInsets.all(13),
      children: [
        SectionTitle(title: L.t(lang, 'clubs'), action: controller.vipDays > 0 ? '👑 باشا فعّال' : 'ترقية للباشا', onTap: () => showPashaBenefits(context, controller)),
        const SizedBox(height: 10),
        if (current != null)
          PremiumPanel(
            child: Container(
              padding: const EdgeInsets.all(18),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(22),
                gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primary.withOpacity(.18), Colors.transparent]),
              ),
              child: Column(
                children: [
                  Text(current.$2, style: const TextStyle(fontSize: 64)),
                  Text(current.$3, style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 5),
                  Text(current.$7, style: const TextStyle(color: Colors.white60)),
                  const SizedBox(height: 12),
                  Row(children: [
                    Expanded(child: _ClubMetric(icon: '👥', value: '${current.$5}/50', label: 'الأعضاء')),
                    const SizedBox(width: 8),
                    Expanded(child: _ClubMetric(icon: '🪙', value: formatNumber(current.$6), label: 'الخزينة')),
                    const SizedBox(width: 8),
                    Expanded(child: _ClubMetric(icon: '⭐', value: 'LV.${current.$4}', label: 'المستوى')),
                  ]),
                  const SizedBox(height: 12),
                  Row(children: [
                    Expanded(child: FilledButton.icon(onPressed: () => showClubChallenges(context, controller, current.$3), icon: const Icon(Icons.emoji_events_outlined), label: const Text('تحديات المجموعة'))),
                    const SizedBox(width: 8),
                    Expanded(child: FilledButton.tonalIcon(onPressed: controller.leaveClub, icon: const Icon(Icons.logout), label: Text(L.t(lang, 'leaveClub')))),
                  ]),
                ],
              ),
            ),
          ),
        const SizedBox(height: 12),
        Row(children: [
          const Expanded(child: SectionTitle(title: 'المجموعات المقترحة')),
          FilledButton.tonalIcon(onPressed: controller.vipDays > 0 ? () => showCreateGroupV151(context, controller) : () => showPashaBenefits(context, controller), icon: Image.asset('assets/images/pasha.png', width: 22, height: 22), label: Text(controller.vipDays > 0 ? 'إنشاء مجموعة' : 'يتطلب باشا')),
        ]),
        const SizedBox(height: 8),
        ...clubs.map((club) => Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: PremiumPanel(
            child: InkWell(
              onTap: () => showGroupDetailV151(context, controller, club.$1, club.$2, club.$3, club.$4, club.$5, club.$6),
              borderRadius: BorderRadius.circular(22),
              child: Padding(
              padding: const EdgeInsets.all(13),
              child: Row(children: [
                Container(width: 58, height: 58, alignment: Alignment.center, decoration: BoxDecoration(color: Colors.white.withOpacity(.06), borderRadius: BorderRadius.circular(18)), child: Text(club.$2, style: const TextStyle(fontSize: 31))),
                const SizedBox(width: 11),
                Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                  Text('${club.$3} • LV.${club.$4}', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 3),
                  Text(club.$7, style: const TextStyle(color: Colors.white60, fontSize: 10)),
                  const SizedBox(height: 3),
                  Text('${club.$5}/50 عضو • خزينة ${formatNumber(club.$6)}', style: const TextStyle(color: Colors.white54, fontSize: 9)),
                ])),
                FilledButton.tonal(onPressed: controller.activeClub == club.$1 ? () => controller.leaveClub() : () { final ok = controller.joinClub(club.$1); if (!ok) showToast(context, 'غادر المجموعة الحالية قبل الانضمام إلى مجموعة أخرى.'); }, child: Text(controller.activeClub == club.$1 ? 'مغادرة' : L.t(lang, 'joinClub'))),
              ]),
            ),
            ),
          ),
        )),
      ],
    );
  }
}

class _ClubMetric extends StatelessWidget {
  final String icon;
  final String value;
  final String label;
  const _ClubMetric({required this.icon, required this.value, required this.label});
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(vertical: 9, horizontal: 5),
    decoration: BoxDecoration(color: Colors.black.withOpacity(.18), borderRadius: BorderRadius.circular(14)),
    child: Column(children: [Text(icon), Text(value, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12)), Text(label, style: const TextStyle(color: Colors.white54, fontSize: 8))]),
  );
}

class EventsPage extends StatelessWidget {
  final AppController controller;

  const EventsPage({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    final entries = const [
      ('champions', '🏆', 'بطولة الأبطال', 'طرنيب • 64 لاعب', 200),
      ('weekend', '🎉', 'تحدي نهاية الأسبوع', 'اختيار لعبة • 16 لاعب', 100),
      ('clubs_war', '🛡️', 'دوري المجموعات', 'فرق المجموعات • 32 فريقاً', 200),
      ('ramadan', '🌙', 'كأس السهرة', 'تركس وهاند • 32 لاعباً', 100),
    ];
    return ListView(
      padding: const EdgeInsets.all(13),
      children: [
        SectionTitle(title: L.t(lang, 'events'), action: '🎁 طريق الهدايا', onTap: () => showGiftRoadSheet(context, controller)),
        const SizedBox(height: 10),
        Container(
          padding: const EdgeInsets.all(17),
          decoration: BoxDecoration(borderRadius: BorderRadius.circular(24), gradient: const LinearGradient(colors: [Color(0xff5b203e), Color(0xffb56a22)])),
          child: Row(children: [
            const Text('🏆', style: TextStyle(fontSize: 62)),
            const SizedBox(width: 12),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              const Text('الموسم الملكي', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
              const Text('افز بالمباريات وارفع ترتيبك لتحصل على شارات وطاولات وتوكنز.', style: TextStyle(color: Colors.white70, fontSize: 10, height: 1.5)),
              const SizedBox(height: 7),
              LinearProgressIndicator(value: controller.giftRoadProgress / 30, minHeight: 7),
            ])),
          ]),
        ),
        const SizedBox(height: 12),
        Row(children: [
          const Expanded(child: Text('المنافسات النشطة', style: TextStyle(fontSize: 17, fontWeight: FontWeight.w900))),
          FilledButton.tonalIcon(
            onPressed: controller.vipDays > 0 ? () => showCreateCompetitionV151(context, controller) : () => showPashaBenefits(context, controller),
            icon: Image.asset('assets/images/pasha.png', width: 22, height: 22),
            label: Text(controller.vipDays > 0 ? 'إنشاء منافسة' : 'يتطلب باشا'),
          ),
        ]),
        const SizedBox(height: 10),
        ...entries.map((entry) {
          final active = controller.activeCompetition == entry.$1;
          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: PremiumPanel(
              child: Padding(
                padding: const EdgeInsets.all(13),
                child: Row(children: [
                  Text(entry.$2, style: const TextStyle(fontSize: 40)),
                  const SizedBox(width: 11),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    Text(entry.$3, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                    Text('${entry.$4} • مكافأة الفوز ${formatNumber(entry.$5)} توكن', style: const TextStyle(color: Colors.white60, fontSize: 10)),
                  ])),
                  FilledButton(onPressed: () { if (active) { controller.leaveCompetition(); showToast(context, 'تمت مغادرة المنافسة.'); } else { final ok = controller.joinCompetition(entry.$1); showToast(context, ok ? 'تم تسجيلك في ${entry.$3}.' : 'غادر المنافسة الحالية قبل الانضمام لأخرى.'); } }, child: Text(active ? 'مغادرة' : L.t(lang, 'join'))),
                ]),
              ),
            ),
          );
        }),
        const SizedBox(height: 4),
        FilledButton.icon(onPressed: () => showChallenges(context, controller), icon: const Icon(Icons.sports_esports_rounded), label: const Text('تحديات مباشرة بين اللاعبين')),
        const SizedBox(height: 9),
        PremiumListTile(icon: '🎁', title: 'المكافأة اليومية', subtitle: '100 توكن + 20 XP', action: FilledButton(onPressed: () async { await controller.claimDaily(); if (context.mounted) showToast(context, 'تمت إضافة المكافأة إلى رصيدك'); }, child: Text(L.t(lang, 'claim')))),
      ],
    );
  }
}

class LevelCard extends StatelessWidget {
  final AppController controller;

  const LevelCard({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final ratio = controller.xp / controller.xpNext;
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(11),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('🏅 ${L.t(controller.localeCode, 'level')}', style: const TextStyle(fontSize: 10, color: Colors.white60)),
            const SizedBox(height: 4),
            Text('${controller.level}  ${formatNumber(controller.xp)}/${formatNumber(controller.xpNext)}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14)),
            const SizedBox(height: 7),
            ClipRRect(
              borderRadius: BorderRadius.circular(20),
              child: LinearProgressIndicator(value: ratio, minHeight: 6),
            ),
          ],
        ),
      ),
    );
  }
}

class StatCard extends StatelessWidget {
  final String icon;
  final String label;
  final String value;
  final VoidCallback onTap;

  const StatCard({super.key, required this.icon, required this.label, required this.value, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(11),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('$icon $label', style: const TextStyle(fontSize: 10, color: Colors.white60)),
              const SizedBox(height: 6),
              Text(value, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 13)),
              const SizedBox(height: 6),
            ],
          ),
        ),
      ),
    );
  }
}

class HeroBanner extends StatelessWidget {
  final String lang;
  final VoidCallback onJoin;

  const HeroBanner({super.key, required this.lang, required this.onJoin});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: 172,
      padding: const EdgeInsets.all(18),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(24),
        gradient: const LinearGradient(
          colors: [Color(0xff3d174e), Color(0xff8f2e4e), Color(0xffd58b2a)],
        ),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(.32), blurRadius: 22, offset: const Offset(0, 13))],
      ),
      child: Row(
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('WARQNA CHAMPIONSHIP', style: TextStyle(fontSize: 10, color: Color(0xffffe2a0), fontWeight: FontWeight.w900)),
                const SizedBox(height: 5),
                Text(L.t(lang, 'champions'), style: const TextStyle(fontSize: 25, fontWeight: FontWeight.w900)),
                const SizedBox(height: 5),
                Text(L.t(lang, 'hero'), maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 11, color: Colors.white70, height: 1.5)),
                const SizedBox(height: 12),
                FilledButton(
                  onPressed: onJoin,
                  style: FilledButton.styleFrom(backgroundColor: const Color(0xffffd578), foregroundColor: const Color(0xff2a1b00)),
                  child: Text(L.t(lang, 'join')),
                ),
              ],
            ),
          ),
          const Text('🏆', style: TextStyle(fontSize: 76)),
        ],
      ),
    );
  }
}

class GiftRoad extends StatelessWidget {
  final AppController controller;

  const GiftRoad({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    const steps = <int>[5, 10, 20, 30];
    return PremiumPanel(
      child: InkWell(
        onTap: () => showGiftRoadSheet(context, controller),
        borderRadius: BorderRadius.circular(22),
        child: Padding(
          padding: const EdgeInsets.all(15),
          child: Column(
            children: [
              SectionTitle(title: L.t(controller.localeCode, 'giftRoad'), action: '${controller.giftRoadProgress} / 30', onTap: () => showGiftRoadSheet(context, controller)),
              const SizedBox(height: 10),
              ClipRRect(borderRadius: BorderRadius.circular(99), child: LinearProgressIndicator(value: controller.giftRoadProgress / 30, minHeight: 9)),
              const SizedBox(height: 12),
              Row(
                children: steps.map((step) {
                  final reached = controller.giftRoadProgress >= step;
                  final claimed = controller.claimedGiftSteps.contains(step);
                  return Expanded(child: Column(children: [
                    AnimatedContainer(duration: const Duration(milliseconds: 250), width: 43, height: 43, alignment: Alignment.center, decoration: BoxDecoration(shape: BoxShape.circle, color: claimed ? Colors.green.withOpacity(.25) : reached ? Theme.of(context).colorScheme.primary.withOpacity(.22) : Colors.white.withOpacity(.05), border: Border.all(color: claimed ? Colors.greenAccent : reached ? Theme.of(context).colorScheme.primary : Colors.white12)), child: Text(claimed ? '✓' : '🎁', style: const TextStyle(fontSize: 20))),
                    const SizedBox(height: 4),
                    Text('$step فوز', style: TextStyle(fontSize: 8, color: reached ? Colors.white : Colors.white38)),
                  ]));
                }).toList(),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class GameCard extends StatelessWidget {
  final GameInfo game;
  final String lang;
  final VoidCallback onTap;

  const GameCard({super.key, required this.game, required this.lang, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(19),
      child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(19),
          gradient: LinearGradient(colors: [game.color, Theme.of(context).colorScheme.surface]),
          border: Border.all(color: Colors.white.withOpacity(.09)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(game.icon, style: const TextStyle(fontSize: 42)),
            const SizedBox(height: 7),
            Text(L.t(lang, game.id), textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12)),
            const SizedBox(height: 4),
            Text('${formatNumber(game.players)} لاعب', style: const TextStyle(color: Colors.white60, fontSize: 8)),
          ],
        ),
      ),
    );
  }
}

class _CompactProductPreview extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;
  const _CompactProductPreview({required this.controller, required this.product});

  @override
  Widget build(BuildContext context) {
    final c1 = controller.color1For(product);
    final c2 = controller.color2For(product);
    if (product.category == 'pasha') {
      return Column(mainAxisSize: MainAxisSize.min, children: [
        Image.asset('assets/images/pasha.png', width: 72, height: 72, fit: BoxFit.contain),
        Text('${controller.durationFor(product) ?? 0} يوم', style: TextStyle(fontSize: 11, fontWeight: FontWeight.w900, color: c2)),
      ]);
    }
    if (product.category == 'covers') {
      return SizedBox(width: 140, height: 92, child: ProfileCover(coverId: product.id, height: 92, colors: <Color>[c1, c2], child: Align(alignment: Alignment.bottomCenter, child: Padding(padding: const EdgeInsets.all(7), child: Text(controller.displayName, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900))))));
    }
    if (product.category == 'tables') {
      return Container(width: 142, height: 92, decoration: BoxDecoration(borderRadius: BorderRadius.circular(25), gradient: RadialGradient(colors: [c2, c1, Color.lerp(c1, Colors.black, .55)!]), border: Border.all(color: c2, width: 3)), child: Stack(children: [const AmbientTableFX(density: 5, subtle: true), Center(child: Text(product.icon, style: const TextStyle(fontSize: 38)))]));
    }
    if (product.category == 'names' || product.category == 'chat_colors') {
      return Column(mainAxisSize: MainAxisSize.min, children: [
        GlowAvatar(text: controller.avatarEmoji, bytes: AccountAvatar(controller: controller)._decode(), size: 58, color: c1),
        const SizedBox(height: 5),
        Text(controller.displayName, style: TextStyle(color: c1, fontWeight: FontWeight.w900, shadows: [Shadow(color: c1, blurRadius: 8)])),
      ]);
    }
    if (product.category == 'themes') {
      return Container(
        width: 135,
        height: 90,
        padding: const EdgeInsets.all(9),
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(18), gradient: LinearGradient(colors: [c1, c2])),
        child: Column(
          children: [
            Row(children: [
              const CircleAvatar(radius: 10, child: Text('W', style: TextStyle(fontSize: 8))),
              const SizedBox(width: 5),
              Expanded(child: Container(height: 9, decoration: BoxDecoration(color: Colors.white24, borderRadius: BorderRadius.circular(9)))),
            ]),
            const Spacer(),
            Row(children: [
              Expanded(child: Container(height: 28, decoration: BoxDecoration(color: Colors.white12, borderRadius: BorderRadius.circular(8)))),
              const SizedBox(width: 5),
              Expanded(child: Container(height: 28, decoration: BoxDecoration(color: Colors.black26, borderRadius: BorderRadius.circular(8)))),
            ]),
          ],
        ),
      );
    }
    if (product.category == 'cards') {
      return Container(width: 58, height: 82, decoration: BoxDecoration(borderRadius: BorderRadius.circular(10), gradient: LinearGradient(colors: [c1, c2]), border: Border.all(color: Colors.white70, width: 2)), child: Center(child: Text(product.icon, style: const TextStyle(fontSize: 30))));
    }
    return FittedBox(fit: BoxFit.scaleDown, child: Text(product.icon, textAlign: TextAlign.center, style: const TextStyle(fontSize: 66)));
  }
}

class ProductCard extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;

  const ProductCard({super.key, required this.controller, required this.product});

  @override
  Widget build(BuildContext context) {
    final owned = controller.owned.contains(product.id);
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(11),
        child: Column(
          children: [
            Align(
              alignment: AlignmentDirectional.centerStart,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: product.tier == 'legendary' ? Colors.amber.withOpacity(.16) : product.tier == 'pro' ? Colors.blueAccent.withOpacity(.13) : Colors.white.withOpacity(.06),
                  borderRadius: BorderRadius.circular(20),
                  border: Border.all(color: product.previewColor1?.withOpacity(.45) ?? Colors.white12),
                ),
                child: Text(product.tierLabel(controller.localeCode), style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900)),
              ),
            ),
            const SizedBox(height: 6),
            Expanded(
              child: InkWell(
                onTap: () => showProductPreview(context, controller, product),
                borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()),
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: Colors.black.withOpacity(.14),
                    borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()),
                  ),
                  child: Center(child: _CompactProductPreview(controller: controller, product: product)),
                ),
              ),
            ),
            const SizedBox(height: 8),
            Text(controller.nameFor(product), maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14.5)),
            const SizedBox(height: 5),
            Text(controller.descriptionFor(product), maxLines: 2, overflow: TextOverflow.ellipsis, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60, fontSize: 12.0, height: 1.42)),
            const SizedBox(height: 8),
            Row(
              children: [
                Expanded(child: Text('🪙 ${formatNumber(controller.priceFor(product))}', style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900, fontSize: 11.5))),
                FilledButton(
                  onPressed: () async {
                    if (owned && !product.reusable) {
                      controller.activateProduct(product);
                      showToast(context, 'تم تفعيل ${controller.nameFor(product)}.');
                      return;
                    }
                    await showProductPreview(context, controller, product);
                  },
                  style: FilledButton.styleFrom(minimumSize: const Size(74, 44), padding: const EdgeInsets.symmetric(horizontal: 9)),
                  child: Text(owned && !product.reusable ? 'تفعيل' : L.t(controller.localeCode, 'buy'), style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900)),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}

class GameRoomPage extends StatelessWidget {
  final AppController controller;
  final GameInfo game;
  final RoomLaunchOptions options;

  const GameRoomPage({super.key, required this.controller, required this.game, this.options = const RoomLaunchOptions()});

  @override
  Widget build(BuildContext context) {
    // Voice rooms are server-backed so real players can exchange audio.
    // Normal Tarneeb retains the dedicated premium table UI.
    if (game.id == 'tarneeb' && !options.voiceEnabled && !options.joiningExisting && !controller.serverConnected) {
      return TarneebRoomPage(controller: controller, game: game);
    }
    return ServerEngineRoomPage(controller: controller, game: game, options: options);
  }
}

class TarneebRoomPage extends StatefulWidget {
  final AppController controller;
  final GameInfo game;

  const TarneebRoomPage({super.key, required this.controller, required this.game});

  @override
  State<TarneebRoomPage> createState() => _TarneebRoomPageState();
}

class _TarneebRoomPageState extends State<TarneebRoomPage> {
  late TarneebLocalEngine engine;
  Timer? timer;
  int seconds = 10;
  String? selectedCode;
  bool reactionsOpen = false;
  ReactionItem? floatingReaction;
  bool chatOpen = true;
  bool botsActing = false;
  bool rewardGranted = false;
  bool awayMode = false;
  int autoPlayedTurns = 0;
  final chatController = TextEditingController();
  final List<String> roomMessages = [
    'سامر: بالتوفيق للجميع 👋',
    'ليلى: مباراة ممتعة!',
  ];

  @override
  void initState() {
    super.initState();
    _newGame();
    timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  void _newGame() {
    engine = TarneebLocalEngine(
      targetScore: 41,
      playerNames: [widget.controller.displayName, botProfiles[3].name(widget.controller.localeCode), botProfiles[2].name(widget.controller.localeCode), botProfiles[1].name(widget.controller.localeCode)],
      difficulty: widget.controller.botDifficultyCode,
    );
    selectedCode = null;
    rewardGranted = false;
    seconds = 10;
    WidgetsBinding.instance.addPostFrameCallback((_) => _runBots());
  }

  @override
  void dispose() {
    timer?.cancel();
    chatController.dispose();
    super.dispose();
  }

  void _tick() {
    if (!mounted || botsActing || engine.phase == TarneebPhase.roundEnd || engine.phase == TarneebPhase.gameOver) return;
    if (awayMode && engine.isHumanTurn) { _autoHumanAction(); return; }
    setState(() => seconds -= 1);
    if (seconds <= 0) _autoHumanAction();
  }

  Future<void> _autoHumanAction() async {
    if (!engine.isHumanTurn) return;
    try {
      if (engine.phase == TarneebPhase.bidding) {
        final minimum = (engine.highestBid ?? 6) + 1;
        engine.bid(0, minimum <= 8 ? math.max(7, minimum).toInt() : null);
      } else if (engine.phase == TarneebPhase.chooseTrump) {
        engine.chooseTrump(0, _bestHumanTrump());
      } else if (engine.phase == TarneebPhase.playing) {
        final legal = engine.legalCards(0);
        if (legal.isNotEmpty) engine.playCard(0, legal.first);
      }
      selectedCode = null;
      seconds = 10;
      if (awayMode) {
        autoPlayedTurns += 1;
        if (autoPlayedTurns >= 3) {
          await widget.controller.recordGameExit('tarneeb');
          if (mounted) {
            showToast(context, 'تم إخراجك بعد ثلاث لفات غياب متتالية. يمكنك العودة ما لم تتجاوز حد الخروج.');
            Navigator.of(context).pop();
          }
          return;
        }
      }
      if (mounted) {
        setState(() {});
        showToast(context, awayMode
            ? 'وضع الغائب مفعل: لعب الكمبيوتر عنك دون احتساب نقاط هذه الجولة.'
            : 'انتهى الوقت؛ نفّذ الكمبيوتر حركة قانونية تلقائياً.');
      }
      await _runBots();
    } catch (_) {
      seconds = 10;
      if (mounted) setState(() {});
    }
  }

  void _maybeRewardWin() {
    if (rewardGranted || engine.phase != TarneebPhase.gameOver) return;
    rewardGranted = true;
    if (engine.winnerTeam == 0 && !awayMode) {
      widget.controller.rewardGameWin('tarneeb');
      if (mounted) showToast(context, 'فوز رائع! تمت إضافة مكافأة الفوز وXP وطريق الهدايا.');
    }
  }

  String _bestHumanTrump() {
    final counts = <String, int>{for (final suit in TarneebLocalEngine.suits) suit: 0};
    for (final card in engine.humanHand) counts[card.suit] = counts[card.suit]! + card.power;
    return counts.entries.reduce((a, b) => a.value >= b.value ? a : b).key;
  }

  Future<void> _runBots() async {
    if (botsActing || engine.currentSeat == 0 || engine.phase == TarneebPhase.roundEnd || engine.phase == TarneebPhase.gameOver) return;
    botsActing = true;
    var guard = 0;
    while (mounted && engine.currentSeat != 0 && engine.phase != TarneebPhase.roundEnd && engine.phase != TarneebPhase.gameOver && guard < 48) {
      await Future<void>.delayed(const Duration(milliseconds: 720));
      if (!mounted) break;
      engine.autoActCurrentSeat();
      seconds = 10;
      setState(() {});
      guard += 1;
    }
    botsActing = false;
    _maybeRewardWin();
  }

  Future<void> _humanBid(int? value) async {
    try {
      engine.bid(0, value);
      autoPlayedTurns = 0;
      seconds = 10;
      setState(() {});
      await _runBots();
    } catch (e) {
      if (mounted) showToast(context, e.toString().replaceFirst('Bad state: ', ''));
    }
  }

  Future<void> _chooseTrump(String suit) async {
    try {
      engine.chooseTrump(0, suit);
      autoPlayedTurns = 0;
      seconds = 10;
      setState(() {});
      await _runBots();
    } catch (e) {
      if (mounted) showToast(context, e.toString());
    }
  }

  Future<void> _playSelected() async {
    if (selectedCode == null) {
      showToast(context, 'اختر ورقة قانونية أولاً.');
      return;
    }
    final card = engine.humanHand.firstWhere((c) => c.code == selectedCode);
    try {
      engine.playCard(0, card);
      autoPlayedTurns = 0;
      selectedCode = null;
      seconds = 10;
      setState(() {});
      await _runBots();
    } catch (e) {
      if (mounted) showToast(context, e.toString().replaceFirst('Bad state: ', ''));
    }
  }

  void _nextRound() {
    engine.startNextRound();
    selectedCode = null;
    seconds = 10;
    setState(() {});
    _runBots();
  }

  String _seatBid(int seat) {
    for (final bid in engine.bids.reversed) {
      if (bid.seat == seat) return bid.amount?.toString() ?? 'سكون';
    }
    if (engine.phase == TarneebPhase.playing && engine.bidWinnerSeat == seat) {
      return '${engine.highestBid ?? ''} ${engine.trump == null ? '' : engine.suitName(engine.trump!)}';
    }
    return engine.currentSeat == seat ? 'دوره' : '—';
  }

  @override
  Widget build(BuildContext context) {
    final landscape = widget.controller.landscapeMode || MediaQuery.orientationOf(context) == Orientation.landscape;
    final body = landscape
        ? Row(
            children: [
              Expanded(flex: 7, child: _gameArea(context, landscape: true)),
              AnimatedContainer(
                duration: const Duration(milliseconds: 220),
                width: chatOpen ? 290 * widget.controller.uiChatScale : 58,
                child: chatOpen ? _chatPanel(context) : _collapsedChatButton(),
              ),
            ],
          )
        : Column(
            children: [
              Expanded(child: _gameArea(context, landscape: false)),
              if (chatOpen) SizedBox(height: 185 * widget.controller.uiChatScale, child: _chatPanel(context)) else _collapsedChatButton(),
            ],
          );

    return Scaffold(
      appBar: AppBar(
        title: Column(
          children: [
            const Text('طرنيب احترافي', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
            Text('13 ورقة • فريقان • الهدف 41 • لعب مجاني', style: TextStyle(fontSize: 9, color: Theme.of(context).colorScheme.primary)),
          ],
        ),
        centerTitle: true,
        actions: [
          if (widget.controller.vipDays > 0)
            IconButton(
              tooltip: awayMode ? 'العودة للعب' : 'وضع غائب',
              onPressed: () => setState(() { awayMode = !awayMode; widget.controller.setAwayMode(awayMode); }),
              icon: Icon(awayMode ? Icons.play_circle_fill_rounded : Icons.pause_circle_outline_rounded, color: awayMode ? Colors.amber : null),
            ),
          IconButton(onPressed: () => inviteFriendsToRoomV151(context, widget.controller, 'tarneeb'), tooltip: 'دعوة الأصدقاء', icon: const Icon(Icons.person_add_alt_1_rounded)),
          IconButton(onPressed: () => confirmLeaveGameV151(context, widget.controller, 'tarneeb'), tooltip: 'الخروج', icon: const Icon(Icons.logout_rounded, color: Colors.redAccent)),
          IconButton(onPressed: widget.controller.toggleOrientationMode, tooltip: 'طولي / عرضي', icon: Icon(widget.controller.landscapeMode ? Icons.stay_current_portrait : Icons.stay_current_landscape)),
          IconButton(onPressed: () => showRules(context, widget.controller.localeCode, 'tarneeb'), icon: const Icon(Icons.menu_book_outlined)),
          IconButton(onPressed: () => showSettings(context, widget.controller), icon: const Icon(Icons.settings_outlined)),
        ],
      ),
      body: SafeArea(child: body),
    );
  }

  Widget _gameArea(BuildContext context, {required bool landscape}) {
    return LayoutBuilder(
      builder: (context, constraints) {
        final compact = constraints.maxHeight < 590;
        return Column(
          children: [
            Padding(
              padding: EdgeInsets.fromLTRB(10, compact ? 2 : 7, 10, 4),
              child: Row(
                children: [
                  Expanded(child: ScoreBox(label: 'نحن', score: engine.scores[0])),
                  Padding(
                    padding: const EdgeInsets.symmetric(horizontal: 8),
                    child: Column(
                      children: [
                        Text('جولة ${engine.round}', style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 11)),
                        Text('${engine.roundTricks[0]} : ${engine.roundTricks[1]} لمّات', style: const TextStyle(color: Colors.white60, fontSize: 9)),
                      ],
                    ),
                  ),
                  Expanded(child: ScoreBox(label: 'هم', score: engine.scores[1])),
                ],
              ),
            ),
            Expanded(
              child: Stack(
                clipBehavior: Clip.none,
                children: [
                  Positioned.fill(
                    top: compact ? 36 : 48,
                    bottom: compact ? 72 : 92,
                    left: landscape ? 86 : 44,
                    right: landscape ? 86 : 44,
                    child: _LuxuryTable(
                      trump: engine.trump,
                      phase: engine.phase.name,
                      skinId: widget.controller.selectedTable,
                      controller: widget.controller,
                    ),
                  ),
                  Positioned(top: compact ? 38 : 51, left: 0, right: 0, child: Center(child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack))),
                  Positioned(left: landscape ? 90 : 47, top: constraints.maxHeight * .41, child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack, vertical: true)),
                  Positioned(right: landscape ? 90 : 47, top: constraints.maxHeight * .41, child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack, vertical: true)),
                  Positioned(top: 0, left: 0, right: 0, child: PlayerSeat(name: engine.playerNames[2], letter: 'ل', botProfile: botProfiles[2], bid: _seatBid(2))),
                  Positioned(left: 3, top: constraints.maxHeight * .34, child: PlayerSeat(name: engine.playerNames[1], letter: 'س', botProfile: botProfiles[3], bid: _seatBid(1), vertical: true)),
                  Positioned(right: 3, top: constraints.maxHeight * .34, child: PlayerSeat(name: engine.playerNames[3], letter: 'ج', botProfile: botProfiles[1], bid: _seatBid(3), vertical: true)),
                  Positioned(bottom: compact ? 64 : 78, left: 0, right: 0, child: PlayerSeat(name: engine.playerNames[0], letter: widget.controller.displayName.isEmpty ? '?' : widget.controller.displayName.substring(0, 1), bid: _seatBid(0), nameColor: colorFromHex(widget.controller.selectedNameColor), badge: storeProductById(widget.controller.selectedBadge)?.icon, avatarEmoji: widget.controller.avatarEmoji)),
                  Positioned(
                    right: landscape ? 92 : 48,
                    bottom: compact ? 92 : 112,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                      decoration: BoxDecoration(color: Colors.black.withOpacity(.72), borderRadius: BorderRadius.circular(18), border: Border.all(color: seconds <= 5 ? Colors.redAccent : Theme.of(context).colorScheme.primary.withOpacity(.7))),
                      child: Text('⏱ 00:${seconds.toString().padLeft(2, '0')}', style: TextStyle(color: seconds <= 5 ? Colors.redAccent : Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900, fontSize: 11)),
                    ),
                  ),
                  Positioned.fill(
                    top: compact ? 80 : 108,
                    bottom: compact ? 145 : 170,
                    left: landscape ? 160 : 90,
                    right: landscape ? 160 : 90,
                    child: _trickCenter(context),
                  ),
                  if (floatingReaction != null)
                    Positioned.fill(
                      child: Center(
                        child: FloatingReaction(
                          key: ValueKey(floatingReaction!.id),
                          reaction: floatingReaction!,
                          onCompleted: () { if (mounted) setState(() => floatingReaction = null); },
                        ),
                      ),
                    ),
                  Positioned(
                    bottom: 0,
                    left: 4,
                    right: 4,
                    child: _handWidget(context, maxWidth: constraints.maxWidth, compact: compact),
                  ),
                ],
              ),
            ),
            _actionPanel(context, compact: compact),
            _roomTools(context),
          ],
        );
      },
    );
  }

  Widget _trickCenter(BuildContext context) {
    final visibleTrick = engine.trick.isNotEmpty ? engine.trick : engine.lastTrick;
    if (visibleTrick.isEmpty) {
      return Center(
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
          decoration: BoxDecoration(color: Colors.black.withOpacity(.25), borderRadius: BorderRadius.circular(14)),
          child: Text(_phaseTitle(), style: const TextStyle(color: Colors.white54, fontWeight: FontWeight.w800)),
        ),
      );
    }
    return Center(
      child: Wrap(
        spacing: 5,
        runSpacing: 5,
        alignment: WrapAlignment.center,
        children: visibleTrick.map((play) {
          return Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text(engine.playerNames[play.seat], style: const TextStyle(fontSize: 8, color: Colors.white70, fontWeight: FontWeight.w800)),
              const SizedBox(height: 2),
              PlayingCard(label: play.card.label, width: 48, height: 70),
            ],
          );
        }).toList(),
      ),
    );
  }

  String _phaseTitle() => switch (engine.phase) {
        TarneebPhase.bidding => 'مرحلة الطلب: من 7 إلى 13',
        TarneebPhase.chooseTrump => 'اختيار نوع الطرنيب',
        TarneebPhase.playing => engine.currentSeat == 0 ? 'دورك — اتبع النوع إن كان موجوداً' : 'بانتظار ${engine.playerNames[engine.currentSeat]}',
        TarneebPhase.roundEnd => 'انتهت الجولة',
        TarneebPhase.gameOver => 'انتهت المباراة',
      };

  Widget _handWidget(BuildContext context, {required double maxWidth, required bool compact}) {
    final hand = engine.humanHand;
    if (hand.isEmpty) return const SizedBox(height: 8);
    final perRow = maxWidth >= 760 ? hand.length : 7;
    final rows = (hand.length / perRow).ceil();
    final spacing = 3.0;
    final available = maxWidth - 16 - ((perRow - 1) * spacing);
    final cardWidth = math.min(compact ? 43.0 : 52.0, math.max(34.0, available / perRow)).toDouble();
    final cardHeight = cardWidth * 1.52;
    final legal = engine.phase == TarneebPhase.playing && engine.currentSeat == 0
        ? engine.legalCards(0).map((e) => e.code).toSet()
        : hand.map((e) => e.code).toSet();
    return SizedBox(
      height: (rows * (cardHeight + 4)) + 12,
      child: Center(
        child: Wrap(
          alignment: WrapAlignment.center,
          spacing: spacing,
          runSpacing: 4,
          children: hand.map((card) {
            final selected = selectedCode == card.code;
            return Transform.translate(
              offset: Offset(0, selected ? -7 : 0),
              child: GestureDetector(
                onTap: legal.contains(card.code) && engine.phase == TarneebPhase.playing && engine.currentSeat == 0
                    ? () => setState(() => selectedCode = selected ? null : card.code)
                    : null,
                child: Opacity(
                  opacity: engine.phase == TarneebPhase.playing && engine.currentSeat == 0 && !legal.contains(card.code) ? .42 : 1,
                  child: PlayingCard(label: card.label, width: cardWidth, height: cardHeight, selected: selected),
                ),
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  double landscapeSafeWidth(double width) => width > 900 ? width * .70 : width - 12;

  Widget _actionPanel(BuildContext context, {required bool compact}) {
    if (engine.phase == TarneebPhase.roundEnd) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(10, 3, 10, 5),
        child: FilledButton.icon(onPressed: _nextRound, icon: const Icon(Icons.refresh_rounded), label: const Text('بدء الجولة التالية'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44))),
      );
    }
    if (engine.phase == TarneebPhase.gameOver) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(10, 3, 10, 5),
        child: FilledButton.icon(onPressed: () => setState(_newGame), icon: const Icon(Icons.emoji_events_rounded), label: Text('فاز فريق ${engine.teamName(engine.winnerTeam ?? 0)} — مباراة جديدة'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44))),
      );
    }
    if (engine.currentSeat != 0 || botsActing) {
      return Padding(
        padding: const EdgeInsets.fromLTRB(10, 3, 10, 5),
        child: Container(
          height: 42,
          alignment: Alignment.center,
          decoration: BoxDecoration(color: Colors.white.withOpacity(.045), borderRadius: BorderRadius.circular(14)),
          child: Text('الكمبيوتر يفكر… ${engine.playerNames[engine.currentSeat]}', style: const TextStyle(color: Colors.white60, fontWeight: FontWeight.w800)),
        ),
      );
    }
    if (engine.phase == TarneebPhase.bidding) {
      final minBid = (engine.highestBid ?? 6) + 1;
      return SizedBox(
        height: compact ? 47 : 53,
        child: ListView(
          scrollDirection: Axis.horizontal,
          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
          children: [
            for (var value = 7; value <= 13; value++)
              Padding(
                padding: const EdgeInsetsDirectional.only(end: 6),
                child: FilledButton.tonal(onPressed: value >= minBid ? () => _humanBid(value) : null, child: Text('$value')),
              ),
            OutlinedButton(onPressed: () => _humanBid(null), child: const Text('سكون')),
          ],
        ),
      );
    }
    if (engine.phase == TarneebPhase.chooseTrump) {
      const suitLabels = {'C': '♣ شجرة', 'D': '♦ ديناري', 'S': '♠ بستوني', 'H': '♥ كبة'};
      return Padding(
        padding: const EdgeInsets.fromLTRB(10, 3, 10, 5),
        child: Row(
          children: TarneebLocalEngine.suits.map((suit) => Expanded(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 3),
              child: FilledButton.tonal(onPressed: () => _chooseTrump(suit), child: FittedBox(child: Text(suitLabels[suit]!))),
            ),
          )).toList(),
        ),
      );
    }
    return Padding(
      padding: const EdgeInsets.fromLTRB(10, 3, 10, 5),
      child: FilledButton.icon(
        onPressed: selectedCode == null ? null : _playSelected,
        icon: const Icon(Icons.style_rounded),
        label: const Text('ارمِ الورقة المختارة'),
        style: FilledButton.styleFrom(backgroundColor: const Color(0xffa8383f), minimumSize: const Size.fromHeight(45)),
      ),
    );
  }

  Widget _roomTools(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.fromLTRB(10, 0, 10, 6),
      child: Column(
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              RoomTool(icon: chatOpen ? Icons.chat_bubble : Icons.chat_bubble_outline, onTap: () => setState(() => chatOpen = !chatOpen)),
              RoomTool(icon: Icons.emoji_emotions_outlined, onTap: () => setState(() => reactionsOpen = !reactionsOpen)),
              RoomTool(icon: Icons.menu_book_outlined, onTap: () => showRules(context, widget.controller.localeCode, 'tarneeb')),
              RoomTool(icon: Icons.history_rounded, onTap: () => _showGameLog(context)),
              RoomTool(icon: Icons.more_horiz, onTap: () => _showRoomMore(context)),
            ],
          ),
          AnimatedCrossFade(
            duration: const Duration(milliseconds: 180),
            crossFadeState: reactionsOpen ? CrossFadeState.showSecond : CrossFadeState.showFirst,
            firstChild: const SizedBox(height: 0),
            secondChild: ReactionDock(
              locale: widget.controller.localeCode,
              onSelected: (reaction) {
                widget.controller.playReactionFeedback(strong: reaction.animated);
                setState(() {
                  reactionsOpen = false;
                  floatingReaction = reaction;
                  roomMessages.add('${widget.controller.displayName}: ${reaction.emoji}');
                });
              },
            ),
          ),
        ],
      ),
    );
  }

  Widget _chatPanel(BuildContext context) {
    return Container(
      margin: const EdgeInsets.all(7),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface.withOpacity(.96),
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.white.withOpacity(.08)),
      ),
      child: Column(
        children: [
          ListTile(
            dense: true,
            leading: const Icon(Icons.forum_rounded),
            title: const Text('دردشة الغرفة', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12)),
            subtitle: const Text('4 لاعبين متصلين', style: TextStyle(color: Colors.greenAccent, fontSize: 8)),
            trailing: IconButton(onPressed: () => setState(() => chatOpen = false), icon: const Icon(Icons.close_rounded, size: 18)),
          ),
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.symmetric(horizontal: 9),
              itemCount: roomMessages.length,
              itemBuilder: (_, index) => Padding(
                padding: const EdgeInsets.only(bottom: 5),
                child: Text(roomMessages[index], style: const TextStyle(fontSize: 10, height: 1.35)),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(7),
            child: Row(
              children: [
                Expanded(child: TextField(controller: chatController, minLines: 1, maxLines: 2, decoration: const InputDecoration(hintText: 'اكتب رسالة...', isDense: true))),
                const SizedBox(width: 5),
                IconButton.filled(
                  onPressed: () {
                    final text = chatController.text.trim();
                    if (text.isEmpty) return;
                    setState(() => roomMessages.add('${widget.controller.displayName}: $text'));
                    chatController.clear();
                  },
                  icon: const Icon(Icons.send_rounded, size: 18),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _collapsedChatButton() => Center(
        child: IconButton.filledTonal(onPressed: () => setState(() => chatOpen = true), icon: const Icon(Icons.chat_bubble_outline_rounded)),
      );

  void _showGameLog(BuildContext context) {
    showPremiumSheet(
      context,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('سجل المباراة', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
          const SizedBox(height: 10),
          ...engine.messages.reversed.take(40).map((message) => Padding(
                padding: const EdgeInsets.only(bottom: 6),
                child: Container(padding: const EdgeInsets.all(10), decoration: BoxDecoration(color: Colors.white.withOpacity(.045), borderRadius: BorderRadius.circular(12)), child: Text(message, style: const TextStyle(fontSize: 11))),
              )),
        ],
      ),
    );
  }

  void _showRoomMore(BuildContext context) {
    showModalBottomSheet(
      context: context,
      showDragHandle: true,
      builder: (sheetContext) => SafeArea(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const ListTile(leading: Icon(Icons.verified_user_outlined), title: Text('اللعب مجاني — لا خصم من التوكنز')),
            ListTile(leading: const Icon(Icons.menu_book), title: const Text('قوانين اللعبة'), onTap: () { Navigator.pop(sheetContext); showRules(context, widget.controller.localeCode, 'tarneeb'); }),
            ListTile(leading: const Icon(Icons.settings), title: const Text('إعدادات اللعبة'), onTap: () { Navigator.pop(sheetContext); showSettings(context, widget.controller); }),
            ListTile(leading: const Icon(Icons.exit_to_app, color: Colors.redAccent), title: const Text('مغادرة الغرفة'), onTap: () { Navigator.pop(sheetContext); confirmLeaveGameV151(context, widget.controller, 'tarneeb'); }),
          ],
        ),
      ),
    );
  }
}

class PremiumCardBack extends StatelessWidget {
  final String cardBackId;
  final double width;
  final double height;
  const PremiumCardBack({super.key, required this.cardBackId, this.width = 28, this.height = 42});

  @override
  Widget build(BuildContext context) {
    final product = storeProductById(cardBackId);
    final c1 = product?.previewColor1 ?? const Color(0xff111827);
    final c2 = product?.previewColor2 ?? const Color(0xfffacc15);
    return Container(
      width: width,
      height: height,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(width * .18),
        gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [c1, Color.lerp(c1, Colors.black, .35)!]),
        border: Border.all(color: c2, width: 1.4),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(.35), blurRadius: 5, offset: const Offset(0, 3))],
      ),
      child: Container(
        width: width * .62,
        height: height * .67,
        alignment: Alignment.center,
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(width * .12), border: Border.all(color: c2.withOpacity(.7))),
        child: Text(product?.icon ?? 'W', style: TextStyle(color: c2, fontSize: width * .34, fontWeight: FontWeight.w900)),
      ),
    );
  }
}

class OpponentCardStack extends StatelessWidget {
  final String cardBackId;
  final bool vertical;
  const OpponentCardStack({super.key, required this.cardBackId, this.vertical = false});

  @override
  Widget build(BuildContext context) {
    final cards = <Widget>[
      for (var i = 0; i < 5; i++)
        Transform.translate(offset: vertical ? Offset(0, i * 3.2) : Offset(i * 3.2, 0), child: PremiumCardBack(cardBackId: cardBackId, width: 24, height: 36)),
    ];
    return SizedBox(width: vertical ? 24 : 38, height: vertical ? 50 : 36, child: Stack(children: cards));
  }
}

class _LuxuryTable extends StatelessWidget {
  final String? trump;
  final String phase;
  final String skinId;
  final AppController? controller;
  const _LuxuryTable({required this.trump, required this.phase, this.skinId = 'table_premium_01', this.controller});

  @override
  Widget build(BuildContext context) {
    final skin = storeProductById(skinId);
    final c1 = skin == null ? const Color(0xff0b4731) : (controller?.color1For(skin) ?? skin.previewColor1 ?? const Color(0xff0b4731));
    final c2 = skin == null ? const Color(0xffd6aa59) : (controller?.color2For(skin) ?? skin.previewColor2 ?? const Color(0xffd6aa59));
    final dark = Color.lerp(c1, Colors.black, .62)!;
    return Container(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(30),
        gradient: RadialGradient(center: const Alignment(0, -.25), radius: .95, colors: [c2.withOpacity(.72), c1, dark]),
        border: Border.all(color: c2, width: 5),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(.62), blurRadius: 32, offset: const Offset(0, 18)),
          BoxShadow(color: c2.withOpacity(.28), blurRadius: 22, spreadRadius: 2),
        ],
      ),
      child: Stack(
        children: [
          Positioned.fill(child: CustomPaint(painter: _TablePatternPainter(color: c2))),
          const Positioned.fill(child: AmbientTableFX(density: 9, subtle: true)),
          Center(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Text(skin?.icon ?? 'W', style: TextStyle(color: Colors.white.withOpacity(.16), fontSize: 78, fontWeight: FontWeight.w900)),
                const SizedBox(height: 4),
                Text(trump == null ? phase.toUpperCase() : 'TRUMP ${TarneebCard('A', trump!).symbol}', style: TextStyle(color: Colors.white.withOpacity(.28), fontWeight: FontWeight.w900, letterSpacing: 3, fontSize: 10)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _TablePatternPainter extends CustomPainter {
  final Color color;
  const _TablePatternPainter({this.color = Colors.white});
  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = color.withOpacity(.055)..style = PaintingStyle.stroke..strokeWidth = 1;
    for (var i = 1; i < 7; i++) {
      canvas.drawOval(Rect.fromCenter(center: Offset(size.width / 2, size.height / 2), width: size.width * i / 7, height: size.height * i / 7), paint);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class ServerEngineRoomPage extends StatefulWidget {
  final AppController controller;
  final GameInfo game;
  final RoomLaunchOptions options;
  const ServerEngineRoomPage({super.key, required this.controller, required this.game, this.options = const RoomLaunchOptions()});

  @override
  State<ServerEngineRoomPage> createState() => _ServerEngineRoomPageState();
}

class _ServerEngineRoomPageState extends State<ServerEngineRoomPage> {
  LocalGameSession? localSession;
  Map<String, dynamic>? room;
  String? error;
  String? selectedCard;
  String? selectedSquare;
  bool loading = true;
  bool sending = false;
  bool reactionsOpen = false;
  ReactionItem? floatingReaction;
  bool chatOpen = true;
  bool awayMode = false;
  int autoPlayedTurns = 0;
  int seconds = 10;
  Timer? timer;
  int chatPoll = 0;
  final serverChatController = TextEditingController();
  final List<ChatMessage> serverMessages = [];
  VoiceRoomService? voiceRoom;
  bool voicePanelExpanded = true;

  @override
  void initState() {
    super.initState();
    _create();
    timer = Timer.periodic(const Duration(seconds: 1), (_) => _tick());
  }

  @override
  void dispose() {
    timer?.cancel();
    voiceRoom?.removeListener(_onVoiceChanged);
    voiceRoom?.dispose();
    serverChatController.dispose();
    super.dispose();
  }

  Future<void> _startVoiceIfNeeded() async {
    if (!isVoiceRoom || room == null || roomCode.isEmpty) return;
    voiceRoom?.removeListener(_onVoiceChanged);
    voiceRoom?.dispose();
    final service = VoiceRoomService(api: widget.controller.api, serverConnected: widget.controller.serverConnected);
    service.addListener(_onVoiceChanged);
    voiceRoom = service;
    if (mounted) setState(() {});
    await service.join(roomCode);
  }

  void _onVoiceChanged() {
    if (mounted) setState(() {});
  }

  Future<void> _openLocalRoom([String? reason]) async {
    localSession = LocalGameSession(gameId: widget.game.id, humanName: widget.controller.displayName, difficulty: widget.controller.botDifficultyCode);
    if (!mounted) return;
    setState(() {
      room = localSession!.room();
      loading = false;
      error = null;
      seconds = widget.options.turnSeconds;
    });
    serverMessages
      ..clear()
      ..addAll([
        ChatMessage('النظام', reason ?? 'بدأت جلسة محلية كاملة. عند ربط Laravel تتحول الجلسة تلقائياً إلى لعب متزامن بين الأجهزة.', false, 'الآن'),
        ChatMessage('سامر', 'بالتوفيق! 👋', false, 'الآن'),
      ]);
    await _startVoiceIfNeeded();
  }

  Future<void> _create() async {
    if (!widget.controller.serverConnected) {
      await _openLocalRoom();
      return;
    }
    try {
      localSession = null;
      final data = widget.options.joiningExisting
          ? await widget.controller.api.joinGame(widget.options.roomCode!.trim().toUpperCase(), password: widget.options.password)
          : await widget.controller.api.createGame(
              game: widget.game.id,
              bots: _playersFor(widget.game.id) - 1,
              visibility: widget.options.visibility,
              turnSeconds: widget.options.turnSeconds,
              voiceEnabled: widget.options.voiceEnabled,
              roomName: widget.options.roomName,
              password: widget.options.password,
            );
      if (!mounted) return;
      setState(() {
        room = Map<String, dynamic>.from(data['room'] as Map);
        loading = false;
        error = null;
        seconds = int.tryParse(room?['turn_seconds']?.toString() ?? '') ?? widget.options.turnSeconds;
      });
      await _loadRoomChat();
      await _startVoiceIfNeeded();
    } on ApiException catch (e) {
      if (widget.options.joiningExisting) {
        if (!mounted) return;
        setState(() {
          loading = false;
          error = e.message;
        });
        return;
      }
      await _openLocalRoom('الخادم غير متاح؛ تم تشغيل المحرك المحلي تلقائياً دون تعطيل اللعبة.');
    } catch (_) {
      if (widget.options.joiningExisting) {
        if (!mounted) return;
        setState(() {
          loading = false;
          error = 'تعذر الانضمام إلى الغرفة. تحقق من الرمز والاتصال بالخادم.';
        });
        return;
      }
      await _openLocalRoom('الخادم غير متاح؛ تم تشغيل المحرك المحلي تلقائياً دون تعطيل اللعبة.');
    }
  }

  int _playersFor(String game) => game == 'basra' ? 2 : 4;

  Map<String, dynamic> get state {
    final value = room?['state'];
    return value is Map ? Map<String, dynamic>.from(value) : <String, dynamic>{};
  }

  List<String> get hand => (state['hand'] is List ? state['hand'] as List : const []).map((e) => e.toString()).toList();
  List<String> get legal => (state['legal_cards'] is List ? state['legal_cards'] as List : const []).map((e) => e.toString()).toList();
  List<Map<String, dynamic>> get availableActions => (state['available_actions'] is List ? state['available_actions'] as List : const [])
      .whereType<Map>()
      .map((item) => Map<String, dynamic>.from(item))
      .toList();
  String get enginePhase => state['engine_phase']?.toString() ?? state['phase']?.toString() ?? 'playing';
  String get roomCode => room?['code']?.toString() ?? '';
  bool get isVoiceRoom => widget.options.voiceEnabled || room?['voice_enabled'] == true || state['voice_enabled'] == true || state['voice_room'] == true;
  int get turnDuration => int.tryParse(room?['turn_seconds']?.toString() ?? '') ?? widget.options.turnSeconds;

  void _tick() {
    if (!mounted || loading || room == null || sending) return;
    if (awayMode) { _timeout(); return; }
    setState(() => seconds -= 1);
    chatPoll += 1;
    if (chatPoll >= 5) {
      chatPoll = 0;
      _loadRoomChat();
    }
    if (seconds <= 0) _timeout();
  }

  Future<void> _timeout() async {
    if (roomCode.isEmpty || sending) return;
    sending = true;
    try {
      if (localSession != null) {
        final updated = localSession!.timeout();
        if (mounted) setState(() { room = Map<String, dynamic>.from(updated); seconds = turnDuration; selectedCard = null; });
      } else {
        final data = await widget.controller.api.gameTimeout(roomCode);
        if (mounted) setState(() { room = Map<String, dynamic>.from(data['room'] as Map); seconds = turnDuration; selectedCard = null; });
      }
      if (awayMode) {
        autoPlayedTurns += 1;
        if (autoPlayedTurns >= 3) {
          await widget.controller.recordGameExit(widget.game.id);
          sending = false;
          if (mounted) {
            showToast(context, 'تم إخراجك بعد ثلاث لفات غياب متتالية.');
            Navigator.of(context).pop();
          }
          return;
        }
      }
    } catch (_) {
      if (mounted) setState(() => seconds = turnDuration);
    }
    sending = false;
  }

  Future<void> _action(String action, [Map<String, dynamic>? payload]) async {
    if (sending || roomCode.isEmpty) return;
    setState(() => sending = true);
    try {
      final Map<String, dynamic> updated;
      if (localSession != null) {
        updated = localSession!.action(action, payload);
      } else {
        final data = await widget.controller.api.gameAction(roomCode, action, payload);
        updated = Map<String, dynamic>.from(data['room'] as Map);
      }
      if (!mounted) return;
      setState(() {
        room = updated;
        seconds = turnDuration;
        selectedCard = null;
        sending = false;
        autoPlayedTurns = 0;
      });
      final currentState = state;
      if (currentState['game_over'] == true && currentState['winner']?.toString() == 'user:0' && !awayMode) {
        widget.controller.rewardGameWin(widget.game.id);
      }
    } on ApiException catch (e) {
      if (mounted) {
        setState(() => sending = false);
        showToast(context, e.message);
      }
    } catch (e) {
      if (mounted) {
        setState(() => sending = false);
        showToast(context, e.toString().replaceFirst('Bad state: ', ''));
      }
    }
  }

  Future<void> _loadRoomChat() async {
    if (roomCode.isEmpty || localSession != null || !widget.controller.serverConnected) return;
    try {
      final data = await widget.controller.api.roomChat(roomCode);
      final messages = data['messages'] is List ? data['messages'] as List : const [];
      final parsed = messages.map((item) {
        final map = item is Map ? Map<String, dynamic>.from(item) : <String, dynamic>{};
        return ChatMessage(
          map['name']?.toString() ?? 'لاعب',
          map['body']?.toString() ?? '',
          map['mine'] == true,
          map['time']?.toString() ?? '',
        );
      }).toList();
      if (mounted) setState(() {
        serverMessages
          ..clear()
          ..addAll(parsed);
      });
    } catch (_) {}
  }

  Future<void> _sendRoomMessage() async {
    final body = serverChatController.text.trim();
    if (body.isEmpty || roomCode.isEmpty) return;
    serverChatController.clear();
    if (localSession != null) {
      serverMessages.add(ChatMessage(widget.controller.displayName, body, true, 'الآن'));
      if (mounted) setState(() {});
      return;
    }
    try {
      final data = await widget.controller.api.sendRoomChat(roomCode, body);
      final item = data['message'];
      if (item is Map) {
        final map = Map<String, dynamic>.from(item);
        serverMessages.add(ChatMessage(
          map['name']?.toString() ?? widget.controller.displayName,
          map['body']?.toString() ?? body,
          true,
          map['time']?.toString() ?? '',
        ));
        if (mounted) setState(() {});
      }
    } on ApiException catch (e) {
      if (mounted) showToast(context, e.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Column(
          children: [
            Text(L.t(widget.controller.localeCode, widget.game.id), style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
            Text(room == null ? 'محرك لعب احترافي' : '${localSession != null ? 'محلي ذكي' : 'خادم موثوق'} • غرفة $roomCode • ${isVoiceRoom ? 'صوتية' : 'عادية'} • لعب مجاني', style: TextStyle(fontSize: 9, color: Theme.of(context).colorScheme.primary)),
          ],
        ),
        centerTitle: true,
        actions: [
          if (isVoiceRoom)
            IconButton(
              tooltip: voiceRoom?.micEnabled == false ? 'تشغيل الميكروفون' : 'كتم الميكروفون',
              onPressed: voiceRoom == null ? null : () => voiceRoom!.setMicEnabled(!voiceRoom!.micEnabled),
              icon: Icon(voiceRoom?.micEnabled == false ? Icons.mic_off_rounded : Icons.mic_rounded, color: voiceRoom?.micEnabled == false ? Colors.redAccent : Colors.greenAccent),
            ),
          if (widget.controller.vipDays > 0)
            IconButton(tooltip: awayMode ? 'العودة للعب' : 'وضع غائب', onPressed: () => setState(() { awayMode = !awayMode; widget.controller.setAwayMode(awayMode); }), icon: Icon(awayMode ? Icons.play_circle_fill_rounded : Icons.pause_circle_outline_rounded, color: awayMode ? Colors.amber : null)),
          IconButton(onPressed: () => inviteFriendsToRoomV151(context, widget.controller, widget.game.id), tooltip: 'دعوة الأصدقاء', icon: const Icon(Icons.person_add_alt_1_rounded)),
          IconButton(onPressed: () => confirmLeaveGameV151(context, widget.controller, widget.game.id), tooltip: 'الخروج', icon: const Icon(Icons.logout_rounded, color: Colors.redAccent)),
          IconButton(onPressed: widget.controller.toggleOrientationMode, tooltip: 'طولي / عرضي', icon: Icon(widget.controller.landscapeMode ? Icons.stay_current_portrait : Icons.stay_current_landscape)),
          IconButton(onPressed: () => showRules(context, widget.controller.localeCode, widget.game.id), icon: const Icon(Icons.menu_book_outlined)),
        ],
      ),
      body: SafeArea(
        child: loading
            ? const Center(child: CircularProgressIndicator())
            : error != null
                ? _errorView(context)
                : Column(
                    children: [
                      if (isVoiceRoom) _voicePanel(context),
                      Expanded(
                        child: OrientationBuilder(builder: (context, orientation) {
                          final landscape = widget.controller.landscapeMode || orientation == Orientation.landscape;
                          return landscape
                              ? Row(children: [Expanded(flex: 7, child: _engineBoard(context)), if (chatOpen) SizedBox(width: 285 * widget.controller.uiChatScale, child: _engineChat())])
                              : Column(children: [Expanded(child: _engineBoard(context)), if (chatOpen) SizedBox(height: 165 * widget.controller.uiChatScale, child: _engineChat())]);
                        }),
                      ),
                    ],
                  ),
      ),
    );
  }

  Widget _voicePanel(BuildContext context) {
    final service = voiceRoom;
    final participants = service?.participants ?? const <VoiceParticipant>[];
    final primary = Theme.of(context).colorScheme.primary;
    return AnimatedContainer(
      duration: const Duration(milliseconds: 220),
      margin: const EdgeInsets.fromLTRB(8, 5, 8, 2),
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 7),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface.withValues(alpha: .96),
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: primary.withValues(alpha: .32)),
      ),
      child: Column(
        children: [
          Row(
            children: [
              Icon(Icons.graphic_eq_rounded, color: service?.joined == true ? Colors.greenAccent : Colors.amber, size: 20),
              const SizedBox(width: 6),
              Expanded(child: Text(service?.status ?? 'تهيئة الغرفة الصوتية…', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12))),
              IconButton(
                visualDensity: VisualDensity.compact,
                tooltip: service?.micEnabled == false ? 'تشغيل الميكروفون' : 'كتم الميكروفون',
                onPressed: service == null ? null : () => service.setMicEnabled(!service.micEnabled),
                icon: Icon(service?.micEnabled == false ? Icons.mic_off_rounded : Icons.mic_rounded, size: 20, color: service?.micEnabled == false ? Colors.redAccent : Colors.greenAccent),
              ),
              IconButton(
                visualDensity: VisualDensity.compact,
                tooltip: service?.deafened == true ? 'تشغيل صوت اللاعبين' : 'كتم سماع اللاعبين',
                onPressed: service == null ? null : () => service.setDeafened(!service.deafened),
                icon: Icon(service?.deafened == true ? Icons.headset_off_rounded : Icons.headphones_rounded, size: 20, color: service?.deafened == true ? Colors.redAccent : null),
              ),
              IconButton(
                visualDensity: VisualDensity.compact,
                tooltip: voicePanelExpanded ? 'تصغير لوحة الصوت' : 'إظهار اللاعبين',
                onPressed: () => setState(() => voicePanelExpanded = !voicePanelExpanded),
                icon: Icon(voicePanelExpanded ? Icons.expand_less_rounded : Icons.expand_more_rounded, size: 20),
              ),
            ],
          ),
          if (voicePanelExpanded) ...[
            const SizedBox(height: 4),
            if (service?.localPreview == true)
              const Align(alignment: AlignmentDirectional.centerStart, child: Text('المعاينة المحلية تختبر الميكروفون فقط. الصوت بين الأجهزة يبدأ بعد نشر Laravel وربط TURN.', style: TextStyle(fontSize: 10, color: Colors.white60)))
            else if (participants.isEmpty)
              const Align(alignment: AlignmentDirectional.centerStart, child: Text('بانتظار انضمام لاعبين حقيقيين للصوت…', style: TextStyle(fontSize: 10, color: Colors.white60)))
            else
              SizedBox(
                height: 48,
                child: ListView.separated(
                  scrollDirection: Axis.horizontal,
                  itemCount: participants.length,
                  separatorBuilder: (_, __) => const SizedBox(width: 6),
                  itemBuilder: (_, index) {
                    final participant = participants[index];
                    final locallyMuted = service?.isPeerMuted(participant.userId) == true;
                    final avatarText = participant.avatar?.isNotEmpty == true
                        ? participant.avatar!
                        : (participant.name.trim().isEmpty ? '?' : participant.name.trim().substring(0, 1));
                    return InkWell(
                      borderRadius: BorderRadius.circular(13),
                      onTap: participant.self || service == null ? null : () => service.togglePeerMute(participant.userId),
                      child: Container(
                        width: 118,
                        padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 5),
                        decoration: BoxDecoration(
                          color: Colors.white.withValues(alpha: .045),
                          borderRadius: BorderRadius.circular(13),
                          border: Border.all(color: participant.online ? Colors.greenAccent.withValues(alpha: .28) : Colors.white12),
                        ),
                        child: Row(
                          children: [
                            CircleAvatar(radius: 15, child: Text(avatarText, style: const TextStyle(fontSize: 12))),
                            const SizedBox(width: 6),
                            Expanded(child: Text(participant.self ? '${participant.name} • أنت' : participant.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 10.5, fontWeight: FontWeight.w800))),
                            Icon(participant.muted || locallyMuted ? Icons.mic_off_rounded : Icons.mic_rounded, size: 14, color: participant.muted || locallyMuted ? Colors.redAccent : Colors.greenAccent),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            if (service?.error != null) ...[
              const SizedBox(height: 3),
              Align(alignment: AlignmentDirectional.centerStart, child: Text(service!.error!, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 9.5, color: Colors.orangeAccent))),
            ],
          ],
        ],
      ),
    );
  }

  Widget _errorView(BuildContext context) => Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 520),
            child: PremiumPanel(
              child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(
                  children: [
                    const Icon(Icons.dns_outlined, size: 55, color: Colors.amber),
                    const SizedBox(height: 14),
                    const Text('محرك اللعبة جاهز في Laravel', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 9),
                    Text(error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60, height: 1.65)),
                    const SizedBox(height: 14),
                    Text(widget.controller.api.baseUrl, textDirection: TextDirection.ltr, style: const TextStyle(fontSize: 10, color: Colors.white38)),
                    const SizedBox(height: 16),
                    FilledButton.icon(onPressed: _create, icon: const Icon(Icons.refresh), label: const Text('إعادة الاتصال')),
                  ],
                ),
              ),
            ),
          ),
        ),
      );

  Widget _engineBoard(BuildContext context) {
    final players = room?['players'] is List ? room!['players'] as List : const [];
    final phase = state['phase']?.toString() ?? 'playing';
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(10, 6, 10, 3),
          child: Row(
            children: [
              Chip(label: Text('المرحلة: $phase')),
              const Spacer(),
              Chip(label: Text('⏱ ${seconds.toString().padLeft(2, '0')}')),
              const SizedBox(width: 6),
              const Chip(label: Text('🛡️ بدون رسوم')),
            ],
          ),
        ),
        Expanded(
          child: Stack(
            children: [
              Positioned.fill(left: 34, right: 34, top: 34, bottom: 128, child: _LuxuryTable(trump: state['trump']?.toString(), phase: phase, skinId: widget.controller.selectedTable, controller: widget.controller)),
              Positioned(top: 40, left: 0, right: 0, child: Center(child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack))),
              Positioned(left: 40, top: 120, child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack, vertical: true)),
              if (players.length > 2) Positioned(right: 40, top: 120, child: OpponentCardStack(cardBackId: widget.controller.selectedCardBack, vertical: true)),
              for (var i = 0; i < players.length; i++) _serverPlayer(players[i] is Map ? Map<String, dynamic>.from(players[i] as Map) : {}, i, players.length),
              Positioned.fill(
                left: 80,
                right: 80,
                top: 100,
                bottom: 190,
                child: Center(child: _stateSummary()),
              ),
              if (floatingReaction != null)
                Positioned.fill(
                  child: Center(
                    child: FloatingReaction(
                      key: ValueKey(floatingReaction!.id),
                      reaction: floatingReaction!,
                      onCompleted: () { if (mounted) setState(() => floatingReaction = null); },
                    ),
                  ),
                ),
              Positioned(bottom: 0, left: 4, right: 4, child: _serverHand()),
            ],
          ),
        ),
        _serverActions(context),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceAround,
            children: [
              RoomTool(icon: chatOpen ? Icons.chat_bubble : Icons.chat_bubble_outline, onTap: () => setState(() => chatOpen = !chatOpen)),
              RoomTool(icon: Icons.emoji_emotions_outlined, onTap: () => setState(() => reactionsOpen = !reactionsOpen)),
              RoomTool(icon: Icons.menu_book_outlined, onTap: () => showRules(context, widget.controller.localeCode, widget.game.id)),
              RoomTool(icon: Icons.refresh, onTap: _timeout),
              RoomTool(icon: Icons.exit_to_app, onTap: () => confirmLeaveGameV151(context, widget.controller, widget.game.id)),
            ],
          ),
        ),
        if (reactionsOpen)
          Padding(
            padding: const EdgeInsets.all(7),
            child: ReactionDock(
              locale: widget.controller.localeCode,
              onSelected: (reaction) {
                widget.controller.playReactionFeedback(strong: reaction.animated);
                setState(() {
                  reactionsOpen = false;
                  floatingReaction = reaction;
                  serverMessages.add(ChatMessage(widget.controller.displayName, reaction.emoji, true, 'الآن'));
                });
              },
            ),
          ),
      ],
    );
  }

  Widget _serverPlayer(Map<String, dynamic> player, int index, int count) {
    final name = player['name']?.toString() ?? 'لاعب ${index + 1}';
    final profile = index == 0 ? null : botProfiles[(index - 1) % botProfiles.length];
    final seat = PlayerSeat(
      name: index == 0 ? name : profile!.name(widget.controller.localeCode),
      letter: name.isEmpty ? '?' : name.substring(0, 1),
      avatarEmoji: index == 0 ? widget.controller.avatarEmoji : null,
      botProfile: profile,
      bid: player['bot'] == true ? 'AI ${widget.controller.botDifficultyCode.toUpperCase()}' : 'LIVE',
      nameColor: index == 0 ? colorFromHex(widget.controller.selectedNameColor) : profile?.secondary ?? const Color(0xffe5e7eb),
      badge: index == 0 ? storeProductById(widget.controller.selectedBadge)?.icon : '🤖',
    );
    if (index == 0) return Positioned(bottom: 72, left: 0, right: 0, child: seat);
    if (index == 1) return Positioned(left: 0, top: 180, child: seat);
    if (index == 2) return Positioned(top: 0, left: 0, right: 0, child: seat);
    return Positioned(right: 0, top: 180, child: seat);
  }

  Widget _stateSummary() {
    final messages = state['messages'] is List ? state['messages'] as List : const [];
    final current = messages.isNotEmpty ? messages.last.toString() : 'المحرك ينتظر الحركة التالية';
    final playerNames = <String, String>{};
    final roomPlayers = room?['players'];
    if (roomPlayers is List) {
      for (final raw in roomPlayers) {
        if (raw is Map) {
          final item = Map<String, dynamic>.from(raw);
          playerNames[item['key']?.toString() ?? ''] = item['name']?.toString() ?? 'لاعب';
        }
      }
    }
    final trickCards = <Map<String, String>>[];
    final rawTrick = state['trick'];
    if (rawTrick is Map) {
      for (final entry in rawTrick.entries) {
        trickCards.add({'card': entry.value.toString(), 'name': playerNames[entry.key.toString()] ?? entry.key.toString().replaceAll('bot:', '').replaceAll('user:', '')});
      }
    } else if (rawTrick is List) {
      for (final raw in rawTrick) {
        if (raw is Map) {
          final item = Map<String, dynamic>.from(raw);
          final key = (item['player'] ?? item['user'] ?? '').toString();
          trickCards.add({'card': (item['card'] ?? item['tile'] ?? '').toString(), 'name': playerNames[key] ?? key.replaceAll('bot:', '').replaceAll('user:', '')});
        } else {
          trickCards.add({'card': raw.toString(), 'name': ''});
        }
      }
    }
    final table = state['table'] is List
        ? (state['table'] as List).map((item) => item.toString()).toList()
        : state['board'] is List
            ? (state['board'] as List).map((item) {
                if (item is Map) return (item['tile'] ?? item['card'] ?? item).toString();
                return item.toString();
              }).toList()
            : const <String>[];
    if (widget.game.id == 'chess' && state['board'] is Map) return _chessBoard(Map<String, dynamic>.from(state['board'] as Map));
    if (widget.game.id == 'backgammon' && state['points'] is Map) return _backgammonBoard(Map<String, dynamic>.from(state['points'] as Map));
    if (widget.game.id == 'jackaroo' && state['pieces'] is Map) return _jackarooBoard(Map<String, dynamic>.from(state['pieces'] as Map));
    final visible = trickCards.isNotEmpty ? trickCards : table.map((card) => {'card': card, 'name': ''}).toList();
    return Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        if (visible.isNotEmpty)
          Wrap(
            alignment: WrapAlignment.center,
            spacing: 5,
            runSpacing: 5,
            children: visible.take(12).map((item) {
              final value = item['card'] ?? '';
              final name = item['name'] ?? '';
              final domino = value.contains('-') && !value.contains('_');
              return Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  domino
                      ? Container(
                          width: 48,
                          height: 72,
                          alignment: Alignment.center,
                          decoration: BoxDecoration(color: const Color(0xfff6f1df), borderRadius: BorderRadius.circular(9), border: Border.all(color: Colors.black26, width: 2)),
                          child: Text(value, style: const TextStyle(color: Colors.black87, fontWeight: FontWeight.w900, fontSize: 13)),
                        )
                      : PlayingCard(label: _cardLabel(value), width: 45, height: 66),
                  if (name.isNotEmpty)
                    SizedBox(width: 62, child: Text(name, textAlign: TextAlign.center, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w800, color: Colors.white70))),
                ],
              );
            }).toList(),
          )
        else
          Text(current, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60, fontSize: 11, height: 1.5)),
        if (state['dice'] is List && (state['dice'] as List).isNotEmpty) ...[
          const SizedBox(height: 8),
          Text('🎲 ${(state['dice'] as List).join(' • ')}', style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
        ],
      ],
    );
  }

  Widget _chessBoard(Map<String, dynamic> board) {
    const pieces = {
      'wK': '♔', 'wQ': '♕', 'wR': '♖', 'wB': '♗', 'wN': '♘', 'wP': '♙',
      'bK': '♚', 'bQ': '♛', 'bR': '♜', 'bB': '♝', 'bN': '♞', 'bP': '♟',
    };
    return LayoutBuilder(builder: (context, constraints) {
      final size = math.min(constraints.maxWidth, constraints.maxHeight).clamp(180.0, 330.0).toDouble();
      return SizedBox(
        width: size,
        height: size,
        child: GridView.builder(
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 8),
          itemCount: 64,
          itemBuilder: (_, index) {
            final row = index ~/ 8;
            final col = index % 8;
            final rank = 8 - row;
            final file = String.fromCharCode('a'.codeUnitAt(0) + col);
            final square = '$file$rank';
            final piece = board[square]?.toString();
            final selected = selectedSquare == square;
            return GestureDetector(
              onTap: sending ? null : () {
                if (selectedSquare == null) {
                  if (piece != null) setState(() => selectedSquare = square);
                } else {
                  final from = selectedSquare!;
                  setState(() => selectedSquare = null);
                  if (from != square) _action('move_piece', {'from': from, 'to': square});
                }
              },
              child: Container(
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  color: selected
                      ? Theme.of(context).colorScheme.primary.withOpacity(.72)
                      : (row + col).isEven
                          ? const Color(0xffe8d9b5)
                          : const Color(0xff526b57),
                  border: Border.all(color: Colors.black12, width: .4),
                ),
                child: Text(pieces[piece] ?? '', style: TextStyle(fontSize: size / 13, color: piece?.startsWith('w') == true ? Colors.white : Colors.black, shadows: const [Shadow(blurRadius: 2, color: Colors.black54)])),
              ),
            );
          },
        ),
      );
    });
  }

  Widget _backgammonBoard(Map<String, dynamic> points) {
    return ConstrainedBox(
      constraints: const BoxConstraints(maxWidth: 430),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          for (final range in [List.generate(12, (i) => 24 - i), List.generate(12, (i) => i + 1)])
            Expanded(
              child: Row(
                children: range.map((number) {
                  final raw = points[number.toString()] ?? points[number];
                  final point = raw is Map ? Map<String, dynamic>.from(raw) : <String, dynamic>{};
                  final count = int.tryParse(point['count']?.toString() ?? '') ?? 0;
                  final owner = point['owner']?.toString() ?? '';
                  return Expanded(
                    child: Container(
                      margin: const EdgeInsets.all(1),
                      decoration: BoxDecoration(color: number.isEven ? const Color(0xff7d5635) : const Color(0xffd5b47a), borderRadius: BorderRadius.circular(4)),
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text('$number', style: const TextStyle(fontSize: 7, color: Colors.black54)),
                          if (count > 0) ...[
                            Text(owner.startsWith('user:') ? '●' : '○', style: const TextStyle(fontSize: 14, color: Colors.white)),
                            Text('$count', style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w900)),
                          ],
                        ],
                      ),
                    ),
                  );
                }).toList(),
              ),
            ),
        ],
      ),
    );
  }

  Widget _jackarooBoard(Map<String, dynamic> pieces) {
    return ConstrainedBox(
      constraints: const BoxConstraints(maxWidth: 390),
      child: PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: pieces.entries.map((entry) {
              final values = entry.value is List ? entry.value as List : const [];
              return Padding(
                padding: const EdgeInsets.symmetric(vertical: 4),
                child: Row(children: [
                  SizedBox(width: 75, child: Text(entry.key.toString().replaceAll('user:', '').replaceAll('bot:', ''), overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800))),
                  Expanded(
                    child: Row(
                      children: values.asMap().entries.map((piece) {
                        final progress = int.tryParse(piece.value.toString()) ?? -1;
                        return Expanded(
                          child: Container(
                            height: 28,
                            margin: const EdgeInsets.symmetric(horizontal: 2),
                            alignment: Alignment.center,
                            decoration: BoxDecoration(
                              color: progress >= 56 ? Colors.green.withOpacity(.45) : progress < 0 ? Colors.white.withOpacity(.06) : Theme.of(context).colorScheme.primary.withOpacity(.16),
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: Colors.white10),
                            ),
                            child: Text(progress < 0 ? 'بيت' : progress >= 56 ? '✓' : '$progress', style: const TextStyle(fontSize: 8, fontWeight: FontWeight.w900)),
                          ),
                        );
                      }).toList(),
                    ),
                  ),
                ]),
              );
            }).toList(),
          ),
        ),
      ),
    );
  }

  Widget _serverHand() {
    if (hand.isEmpty) return const SizedBox(height: 55, child: Center(child: Text('لا توجد أوراق ظاهرة في هذه المرحلة', style: TextStyle(color: Colors.white38, fontSize: 10))));
    return LayoutBuilder(builder: (context, constraints) {
      final perRow = constraints.maxWidth >= 760 ? hand.length : 7;
      final rows = (hand.length / perRow).ceil();
      const spacing = 3.0;
      final available = constraints.maxWidth - 12 - ((perRow - 1) * spacing);
      final cardWidth = math.min(52.0, math.max(34.0, available / perRow)).toDouble();
      final cardHeight = cardWidth * 1.5;
      return SizedBox(
        height: rows * (cardHeight + 4) + 12,
        child: Center(
          child: Wrap(
            alignment: WrapAlignment.center,
            spacing: spacing,
            runSpacing: 4,
            children: hand.map((card) {
              final selected = selectedCard == card;
              return Transform.translate(
                offset: Offset(0, selected ? -7 : 0),
                child: GestureDetector(
                  onTap: () => setState(() => selectedCard = selected ? null : card),
                  child: Opacity(
                    opacity: legal.isNotEmpty && !legal.contains(card) ? .42 : 1,
                    child: PlayingCard(label: _cardLabel(card), width: cardWidth, height: cardHeight, selected: selected),
                  ),
                ),
              );
            }).toList(),
          ),
        ),
      );
    });
  }

  String _cardLabel(String raw) {
    final cleaned = raw.replaceAll('_', '');
    return cleaned.replaceAll('C', '♣').replaceAll('D', '♦').replaceAll('S', '♠').replaceAll('H', '♥');
  }

  Widget _serverActions(BuildContext context) {
    final types = availableActions.map((item) => item['type']?.toString() ?? '').toSet();
    final widgets = <Widget>[];
    final matchingCardAction = selectedCard == null
        ? null
        : availableActions.cast<Map<String, dynamic>?>().firstWhere(
              (item) => item?['card']?.toString() == selectedCard && {'play_card', 'discard', 'move_to_foundation', 'play_tile'}.contains(item?['type']?.toString()),
              orElse: () => null,
            );

    if (selectedCard != null) {
      final fallback = widget.game.id == 'domino'
          ? 'play_tile'
          : widget.game.id.contains('hand') || widget.game.id == 'banakil' || widget.game.id == 'pinochle'
              ? (enginePhase == 'discard' ? 'discard' : 'play_card')
              : 'play_card';
      final action = matchingCardAction?['type']?.toString() ?? fallback;
      widgets.add(FilledButton.icon(
        onPressed: sending
            ? null
            : () => action == 'play_tile'
                ? _playDominoTile(selectedCard!)
                : widget.game.id == 'jackaroo'
                    ? _playJackarooCard(selectedCard!, matchingCardAction)
                    : _action(action, {'card': selectedCard, 'tile': selectedCard}),
        icon: Icon(action == 'discard' ? Icons.delete_sweep_outlined : Icons.style),
        label: Text(action == 'discard' ? 'رمي الورقة' : action == 'move_to_foundation' ? 'إلى الأساس' : action == 'play_tile' ? 'لعب الحجر' : 'لعب الورقة'),
      ));
    }

    final bids = availableActions.where((item) => item['type'] == 'bid').map((item) => int.tryParse(item['amount']?.toString() ?? '')).whereType<int>().toSet().toList()..sort();
    if (bids.isNotEmpty || enginePhase == 'bidding') {
      widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _chooseServerBid(bids), child: const Text('اختيار الطلب')));
    }
    if (types.contains('pass') || (availableActions.isEmpty && (widget.game.id == 'domino' || widget.game.id == 'backgammon'))) {
      widgets.add(OutlinedButton(onPressed: sending ? null : () => _action('pass'), child: const Text('سكون')));
    }

    final trumpActions = availableActions.where((item) => item['type'] == 'choose_trump').toList();
    if (trumpActions.isNotEmpty || enginePhase.contains('trump')) {
      final suits = trumpActions.map((item) => item['suit']?.toString()).whereType<String>().toSet().toList();
      widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _chooseServerTrump(suits), child: const Text('اختيار الحكم')));
    }

    final contracts = availableActions.where((item) => item['type'] == 'choose_contract').map((item) => item['contract']?.toString()).whereType<String>().toSet().toList();
    if (contracts.isNotEmpty || enginePhase.contains('contract')) {
      widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _chooseContract(contracts), child: const Text('اختيار العقد')));
    }

    if (types.contains('draw_deck') || (availableActions.isEmpty && enginePhase == 'draw')) {
      widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _action('draw_deck'), child: const Text('سحب من الرزمة')));
    }
    if (types.contains('draw_discard')) {
      widgets.add(OutlinedButton(onPressed: sending ? null : () => _action('draw_discard'), child: const Text('سحب المكشوف')));
    }
    if (types.contains('draw_stock')) {
      widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _action('draw_stock'), child: const Text('سحب ورقة')));
    }
    if (types.contains('organize')) {
      widgets.add(OutlinedButton.icon(onPressed: sending ? null : () => _action('organize'), icon: const Icon(Icons.auto_awesome, size: 17), label: const Text('ترتيب ذكي')));
    }
    final melds = availableActions.where((item) => item['type'] == 'meld' && item['cards'] is List).toList();
    if (melds.isNotEmpty) {
      widgets.add(FilledButton.tonalIcon(onPressed: sending ? null : () => _chooseMeld(melds), icon: const Icon(Icons.layers_outlined, size: 17), label: const Text('تنزيل مجموعة')));
    }

    if (widget.game.id == 'domino') {
      final boneyardCount = int.tryParse(state['boneyard_count']?.toString() ?? '') ?? 0;
      if (boneyardCount > 0) {
        widgets.add(FilledButton.tonal(onPressed: sending ? null : () => _action('draw'), child: const Text('سحب حجر')));
      }
    }
    if (widget.game.id == 'backgammon') {
      final moves = state['moves_left'] is List ? state['moves_left'] as List : const [];
      if (moves.isEmpty) {
        widgets.add(FilledButton.icon(onPressed: sending ? null : () => _action('roll'), icon: const Icon(Icons.casino), label: const Text('رمي النرد')));
      } else {
        widgets.add(FilledButton.icon(onPressed: sending ? null : _showBackgammonMove, icon: const Icon(Icons.open_with), label: const Text('تحريك حجر')));
      }
    }
    if (widget.game.id == 'chess') {
      widgets.add(FilledButton.icon(onPressed: sending ? null : _showChessMove, icon: const Icon(Icons.grid_4x4), label: const Text('نقلة شطرنج')));
    }

    if (types.contains('new_round') || state['game_over'] == true) {
      widgets.add(FilledButton.icon(onPressed: sending ? null : () => _action('new_round'), icon: const Icon(Icons.replay), label: const Text('إعادة اللعب')));
    }
    if (widgets.isEmpty) {
      widgets.add(FilledButton.tonal(onPressed: sending ? null : _timeout, child: Text(sending ? 'جارٍ التنفيذ…' : 'تشغيل الحركة التلقائية')));
    }
    return AnimatedSize(
      duration: const Duration(milliseconds: 180),
      child: Container(
        width: double.infinity,
        padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 5),
        child: Wrap(
          alignment: WrapAlignment.center,
          spacing: 7,
          runSpacing: 7,
          children: widgets,
        ),
      ),
    );
  }

  Future<void> _chooseServerBid(List<int> values) async {
    final options = values.isEmpty ? [for (var i = 7; i <= 13; i++) i] : values;
    final value = await showDialog<int>(context: context, builder: (dialogContext) => AlertDialog(
      title: const Text('اختر الطلب القانوني'),
      content: Wrap(spacing: 6, runSpacing: 6, children: options.map((amount) => FilledButton.tonal(onPressed: () => Navigator.pop(dialogContext, amount), child: Text('$amount'))).toList()),
    ));
    if (value != null) _action('bid', {'amount': value});
  }

  Future<void> _chooseServerTrump(List<String> allowed) async {
    const all = {'clubs': '♣', 'diamonds': '♦', 'spades': '♠', 'hearts': '♥', 'C': '♣', 'D': '♦', 'S': '♠', 'H': '♥'};
    final keys = allowed.isEmpty ? const ['C', 'D', 'S', 'H'] : allowed;
    final suit = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('اختر نوع الحكم'),
        content: Wrap(
          spacing: 8,
          runSpacing: 8,
          children: keys
              .map((key) => FilledButton.tonal(
                    onPressed: () => Navigator.pop(dialogContext, key),
                    child: Text(all[key] ?? key, style: const TextStyle(fontSize: 24)),
                  ))
              .toList(),
        ),
      ),
    );
    if (suit != null) _action('choose_trump', {'suit': suit});
  }

  Future<void> _chooseContract(List<String> values) async {
    final options = values.isEmpty ? const ['king_hearts', 'girls', 'diamonds', 'tricks', 'trix', 'complex', 'sun', 'hokm'] : values;
    const labels = {
      'king_hearts': 'شيخ الكبة',
      'girls': 'البنات',
      'queens': 'البنات',
      'diamonds': 'الديناري',
      'tricks': 'اللطوش',
      'trix': 'تركس',
      'complex': 'كمبلكس',
      'sun': 'صن',
      'hokm': 'حكم',
    };
    final contract = await showDialog<String>(context: context, builder: (dialogContext) => AlertDialog(
      title: const Text('اختر العقد المتاح'),
      content: Wrap(spacing: 6, runSpacing: 6, children: options.map((value) => FilledButton.tonal(onPressed: () => Navigator.pop(dialogContext, value), child: Text(labels[value] ?? value))).toList()),
    ));
    if (contract != null) _action('choose_contract', {'contract': contract});
  }

  Future<void> _chooseMeld(List<Map<String, dynamic>> melds) async {
    final selected = await showDialog<Map<String, dynamic>>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('اختر المجموعة القانونية'),
        content: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 440, maxHeight: 420),
          child: ListView.separated(
            shrinkWrap: true,
            itemCount: melds.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (_, index) {
              final cards = (melds[index]['cards'] as List).map((card) => _cardLabel(card.toString())).join('  ');
              return ListTile(title: Text(cards, textDirection: TextDirection.ltr), trailing: const Icon(Icons.chevron_right), onTap: () => Navigator.pop(dialogContext, melds[index]));
            },
          ),
        ),
      ),
    );
    if (selected != null) _action('meld', {'cards': selected['cards']});
  }

  Future<void> _playJackarooCard(String card, Map<String, dynamic>? action) async {
    final choices = availableActions
        .where((item) => item['type'] == 'play_card' && item['card']?.toString() == card)
        .map((item) => Map<String, dynamic>.from(item))
        .toList();
    if (choices.isEmpty && action != null) choices.add(Map<String, dynamic>.from(action));
    if (choices.isEmpty) {
      showToast(context, 'لا توجد حركة قانونية لهذه الورقة.');
      return;
    }
    Map<String, dynamic>? selected;
    if (choices.length == 1) {
      selected = choices.first;
    } else {
      selected = await showDialog<Map<String, dynamic>>(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: Text('اختر وظيفة ${_cardLabel(card)}'),
          content: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 470, maxHeight: 440),
            child: ListView.separated(
              shrinkWrap: true,
              itemCount: choices.length,
              separatorBuilder: (_, __) => const Divider(height: 1),
              itemBuilder: (_, index) {
                final item = choices[index];
                final label = item['label']?.toString() ?? 'حركة قانونية ${index + 1}';
                return ListTile(
                  leading: const Icon(Icons.route_outlined),
                  title: Text(label),
                  subtitle: item['steps2'] != null ? Text('تقسيم ${item['steps']} + ${item['steps2']}') : null,
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => Navigator.pop(dialogContext, item),
                );
              },
            ),
          ),
        ),
      );
    }
    if (selected == null) return;
    final payload = Map<String, dynamic>.from(selected)
      ..remove('type')
      ..remove('label');
    _action('play_card', payload);
  }

  Future<void> _playDominoTile(String tile) async {
    final legalSides = availableActions
        .where((item) => item['type'] == 'play_tile' && item['tile']?.toString() == tile)
        .map((item) => item['side']?.toString() ?? 'right')
        .toSet()
        .toList();
    if (legalSides.length == 1) {
      _action('play_tile', {'tile': tile, 'side': legalSides.first});
      return;
    }
    final sides = legalSides.isEmpty ? const ['left', 'right'] : legalSides;
    final side = await showDialog<String>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text('ضع الحجر $tile'),
        content: Wrap(
          spacing: 8,
          runSpacing: 8,
          children: sides.map((value) => FilledButton.tonal(
            onPressed: () => Navigator.pop(dialogContext, value),
            child: Text(value == 'left' ? 'الطرف الأيسر' : 'الطرف الأيمن'),
          )).toList(),
        ),
      ),
    );
    if (side != null) _action('play_tile', {'tile': tile, 'side': side});
  }

  Future<void> _showBackgammonMove() async {
    final moves = availableActions.where((item) => item['type'] == 'move').map((item) => Map<String, dynamic>.from(item)).toList();
    if (moves.isEmpty) {
      showToast(context, 'لا توجد حركة قانونية بهذا الرمي.');
      return;
    }
    final move = await showDialog<Map<String, dynamic>>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('اختر حركة قانونية'),
        content: ConstrainedBox(
          constraints: const BoxConstraints(maxWidth: 420, maxHeight: 430),
          child: ListView.separated(
            shrinkWrap: true,
            itemCount: moves.length,
            separatorBuilder: (_, __) => const Divider(height: 1),
            itemBuilder: (_, index) {
              final item = moves[index];
              final from = int.tryParse(item['from']?.toString() ?? '') ?? 0;
              final to = int.tryParse(item['to']?.toString() ?? '') ?? 0;
              final die = item['die']?.toString() ?? '';
              final fromLabel = (from == 0 || from == 25) ? 'البار' : '$from';
              final toLabel = (to == 0 || to == 25) ? 'إخراج' : '$to';
              return ListTile(
                leading: Icon(item['hit'] == true ? Icons.flash_on : item['bear_off'] == true ? Icons.outbond : Icons.open_with),
                title: Text('$fromLabel ← $toLabel'),
                subtitle: Text('قيمة النرد: $die${item['hit'] == true ? ' • ضرب حجر' : ''}'),
                trailing: const Icon(Icons.chevron_right),
                onTap: () => Navigator.pop(dialogContext, item),
              );
            },
          ),
        ),
      ),
    );
    if (move != null) _action('move', {'from': move['from'], 'to': move['to']});
  }

  Future<void> _showChessMove() async {
    final fromController = TextEditingController();
    final toController = TextEditingController();
    final move = await showDialog<List<String>>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('نقلة الشطرنج'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(controller: fromController, textDirection: TextDirection.ltr, decoration: const InputDecoration(labelText: 'من مثال: e2')),
          const SizedBox(height: 8),
          TextField(controller: toController, textDirection: TextDirection.ltr, decoration: const InputDecoration(labelText: 'إلى مثال: e4')),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('إلغاء')),
          FilledButton(onPressed: () {
            final from = fromController.text.trim().toLowerCase();
            final to = toController.text.trim().toLowerCase();
            if (RegExp(r'^[a-h][1-8]$').hasMatch(from) && RegExp(r'^[a-h][1-8]$').hasMatch(to)) Navigator.pop(dialogContext, [from, to]);
          }, child: const Text('تنفيذ')),
        ],
      ),
    );
    fromController.dispose();
    toController.dispose();
    if (move != null) _action('move_piece', {'from': move[0], 'to': move[1]});
  }

  Widget _engineChat() => Container(
        margin: const EdgeInsets.all(7),
        decoration: BoxDecoration(color: Theme.of(context).colorScheme.surface, borderRadius: BorderRadius.circular(20), border: Border.all(color: Colors.white.withOpacity(.08))),
        child: Column(
          children: [
            ListTile(
              dense: true,
              leading: const Icon(Icons.forum),
              title: const Text('دردشة اللعبة', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 12)),
              subtitle: Text(localSession != null ? 'دردشة محلية داخل الجلسة' : 'مزامنة حقيقية عبر Laravel', style: const TextStyle(color: Colors.greenAccent, fontSize: 8)),
              trailing: IconButton(onPressed: () => setState(() => chatOpen = false), icon: const Icon(Icons.close, size: 18)),
            ),
            Expanded(
              child: serverMessages.isEmpty
                  ? const Center(child: Text('ابدأ المحادثة مع لاعبي الغرفة.', style: TextStyle(color: Colors.white54, fontSize: 10)))
                  : ListView.builder(
                      padding: const EdgeInsets.symmetric(horizontal: 8),
                      itemCount: serverMessages.length,
                      itemBuilder: (_, index) {
                        final message = serverMessages[index];
                        return Align(
                          alignment: message.mine ? Alignment.centerRight : Alignment.centerLeft,
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 5),
                            padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 6),
                            decoration: BoxDecoration(color: message.mine ? Theme.of(context).colorScheme.primary.withOpacity(.16) : Colors.white.withOpacity(.055), borderRadius: BorderRadius.circular(11)),
                            child: Text('${message.sender}: ${message.body}', style: TextStyle(fontSize: 9, height: 1.35, fontWeight: message.mine ? FontWeight.w800 : FontWeight.w500, color: message.mine ? colorFromHex(widget.controller.selectedChatColor) : Colors.white)),
                          ),
                        );
                      },
                    ),
            ),
            Padding(
              padding: const EdgeInsets.all(7),
              child: Row(children: [
                Expanded(child: TextField(controller: serverChatController, onSubmitted: (_) => _sendRoomMessage(), decoration: const InputDecoration(hintText: 'اكتب رسالة...', isDense: true))),
                const SizedBox(width: 5),
                IconButton.filled(onPressed: _sendRoomMessage, icon: const Icon(Icons.send, size: 17)),
              ]),
            ),
          ],
        ),
      );
}


class PlayingCard extends StatelessWidget {
  final String label;
  final double width;
  final double height;
  final bool selected;

  const PlayingCard({super.key, required this.label, this.width = 46, this.height = 68, this.selected = false});

  @override
  Widget build(BuildContext context) {
    final suit = label.isNotEmpty ? label.substring(label.length - 1) : '';
    final rank = label.length > 1 ? label.substring(0, label.length - 1) : label;
    final red = suit == '♥' || suit == '♦';
    final ink = red ? const Color(0xffb42335) : const Color(0xff101419);
    final cornerSize = math.max(9.0, width * .25).toDouble();
    final centerSize = math.max(16.0, width * .48).toDouble();
    return AnimatedContainer(
      duration: const Duration(milliseconds: 170),
      curve: Curves.easeOutCubic,
      width: width,
      height: height,
      decoration: BoxDecoration(
        gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xfffffff9), Color(0xffeee7d8)]),
        borderRadius: BorderRadius.circular(math.max(7, width * .18).toDouble()),
        border: Border.all(color: selected ? Theme.of(context).colorScheme.primary : const Color(0xffc9c0ac), width: selected ? 2.4 : 1.1),
        boxShadow: [
          BoxShadow(color: selected ? Theme.of(context).colorScheme.primary.withValues(alpha: .34) : Colors.black.withValues(alpha: .34), blurRadius: selected ? 17 : 8, offset: const Offset(0, 5)),
          const BoxShadow(color: Colors.white70, blurRadius: 1, offset: Offset(-1, -1)),
        ],
      ),
      child: ClipRRect(
        borderRadius: BorderRadius.circular(math.max(6.0, width * .16).toDouble()),
        child: Stack(
          children: [
            Positioned(left: 3.5, top: 2.5, child: Column(mainAxisSize: MainAxisSize.min, children: [Text(rank, style: TextStyle(color: ink, fontWeight: FontWeight.w900, fontSize: cornerSize, height: .9)), Text(suit, style: TextStyle(color: ink, fontWeight: FontWeight.w900, fontSize: cornerSize * .85, height: .85))])),
            Center(child: Text(suit.isEmpty ? label : suit, style: TextStyle(color: ink, fontWeight: FontWeight.w900, fontSize: centerSize, shadows: const [Shadow(color: Colors.white, blurRadius: 1)]))),
            Positioned(right: 3.5, bottom: 2.5, child: Transform.rotate(angle: math.pi, child: Column(mainAxisSize: MainAxisSize.min, children: [Text(rank, style: TextStyle(color: ink, fontWeight: FontWeight.w900, fontSize: cornerSize, height: .9)), Text(suit, style: TextStyle(color: ink, fontWeight: FontWeight.w900, fontSize: cornerSize * .85, height: .85))]))),
            if (selected) Positioned.fill(child: IgnorePointer(child: DecoratedBox(decoration: BoxDecoration(borderRadius: BorderRadius.circular(math.max(7, width * .18).toDouble()), gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Theme.of(context).colorScheme.primary.withValues(alpha: .18), Colors.transparent]))))),
          ],
        ),
      ),
    );
  }
}

class PlayerSeat extends StatelessWidget {
  final String name;
  final String letter;
  final String bid;
  final bool vertical;
  final Color? nameColor;
  final String? badge;
  final String? avatarEmoji;
  final BotProfile? botProfile;

  const PlayerSeat({super.key, required this.name, required this.letter, required this.bid, this.vertical = false, this.nameColor, this.badge, this.avatarEmoji, this.botProfile});

  @override
  Widget build(BuildContext context) {
    final content = [
      botProfile == null
          ? GlowAvatar(text: avatarEmoji ?? letter, size: 43, color: nameColor ?? Theme.of(context).colorScheme.primary)
          : Bot3DAvatar(profile: botProfile!, size: 46, showLevel: true),
      const SizedBox(width: 5, height: 4),
      Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
        decoration: BoxDecoration(color: Colors.black.withOpacity(.72), borderRadius: BorderRadius.circular(9), border: Border.all(color: Colors.white.withOpacity(.09))),
        child: Text('${badge ?? ''}${badge == null ? '' : ' '}$name • $bid', style: TextStyle(fontSize: 8, fontWeight: FontWeight.w900, color: nameColor ?? Colors.white)),
      ),
    ];
    return vertical ? Column(mainAxisSize: MainAxisSize.min, children: content) : Row(mainAxisAlignment: MainAxisAlignment.center, mainAxisSize: MainAxisSize.min, children: content);
  }
}

class ScoreBox extends StatelessWidget {
  final String label;
  final int score;

  const ScoreBox({super.key, required this.label, required this.score});

  @override
  Widget build(BuildContext context) {
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 6),
        child: Column(
          children: [
            Text(label, style: const TextStyle(fontSize: 8, color: Colors.white60)),
            Text('$score', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900)),
          ],
        ),
      ),
    );
  }
}

class RoomTool extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;

  const RoomTool({super.key, required this.icon, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return IconButton.filledTonal(onPressed: onTap, icon: Icon(icon));
  }
}

class PremiumPanel extends StatelessWidget {
  final Widget child;

  const PremiumPanel({super.key, required this.child});

  @override
  Widget build(BuildContext context) {
    return DecoratedBox(
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surface,
        borderRadius: BorderRadius.circular(19),
        border: Border.all(color: Colors.white.withOpacity(.08)),
        boxShadow: [BoxShadow(color: Colors.black.withOpacity(.20), blurRadius: 14, offset: const Offset(0, 8))],
      ),
      child: child,
    );
  }
}

class PremiumAvatar extends StatelessWidget {
  final String text;
  final double size;

  const PremiumAvatar({super.key, required this.text, this.size = 43});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: const LinearGradient(colors: [Color(0xff314960), Color(0xff0b1722)]),
        border: Border.all(color: Theme.of(context).colorScheme.primary, width: 2),
      ),
      child: Text(text, style: const TextStyle(fontWeight: FontWeight.w900)),
    );
  }
}

class GlowAvatar extends StatelessWidget {
  final String text;
  final double size;
  final Color color;
  final Uint8List? bytes;
  const GlowAvatar({super.key, required this.text, required this.color, this.size = 44, this.bytes});

  @override
  Widget build(BuildContext context) => Container(
        width: size,
        height: size,
        padding: const EdgeInsets.all(2.5),
        decoration: BoxDecoration(
          shape: BoxShape.circle,
          gradient: LinearGradient(colors: [color, color.withOpacity(.3)]),
          boxShadow: [BoxShadow(color: color.withOpacity(.55), blurRadius: 16, spreadRadius: 1)],
        ),
        child: ClipOval(
          child: Container(
            alignment: Alignment.center,
            color: const Color(0xff0a1420),
            child: bytes == null ? Text(text, style: TextStyle(fontSize: size * .43, fontWeight: FontWeight.w900)) : Image.memory(bytes!, fit: BoxFit.cover, width: size, height: size),
          ),
        ),
      );
}

class AccountAvatar extends StatelessWidget {
  final AppController controller;
  final double size;
  const AccountAvatar({super.key, required this.controller, this.size = 44});

  Uint8List? _decode() {
    final raw = controller.avatarData;
    if (raw == null || raw.isEmpty) return null;
    try {
      final body = raw.contains(',') ? raw.split(',').last : raw;
      return base64Decode(body);
    } catch (_) {
      return null;
    }
  }

  @override
  Widget build(BuildContext context) => GlowAvatar(
        text: controller.avatarEmoji.isNotEmpty ? controller.avatarEmoji : (controller.displayName.isEmpty ? '?' : controller.displayName.substring(0, 1)),
        bytes: _decode(),
        size: size,
        color: colorFromHex(controller.selectedNameColor),
      );
}

class SectionTitle extends StatelessWidget {
  final String title;
  final String? action;
  final VoidCallback? onTap;

  const SectionTitle({super.key, required this.title, this.action, this.onTap});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(child: Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900))),
        if (action != null) TextButton(onPressed: onTap ?? () {}, child: Text(action!)),
      ],
    );
  }
}

class PremiumActionButton extends StatelessWidget {
  final IconData icon;
  final String title;
  final Color color;
  final VoidCallback onPressed;

  const PremiumActionButton({super.key, required this.icon, required this.title, required this.color, required this.onPressed});

  @override
  Widget build(BuildContext context) {
    return FilledButton.icon(
      onPressed: onPressed,
      icon: Icon(icon),
      label: Text(title, maxLines: 1, overflow: TextOverflow.ellipsis),
      style: FilledButton.styleFrom(backgroundColor: color, padding: const EdgeInsets.symmetric(vertical: 15)),
    );
  }
}

class QuickButton extends StatelessWidget {
  final String icon;
  final String label;
  final VoidCallback onTap;

  const QuickButton({super.key, required this.icon, required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(13),
      child: SizedBox(
        width: 58,
        height: 66,
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Text(icon, style: const TextStyle(fontSize: 23)),
            const SizedBox(height: 4),
            Text(label, textAlign: TextAlign.center, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 8, color: Colors.white60, fontWeight: FontWeight.w800)),
          ],
        ),
      ),
    );
  }
}

class PremiumListTile extends StatelessWidget {
  final String icon;
  final String title;
  final String subtitle;
  final Widget action;

  const PremiumListTile({super.key, required this.icon, required this.title, required this.subtitle, required this.action});

  @override
  Widget build(BuildContext context) {
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(
          children: [
            Container(width: 46, height: 46, alignment: Alignment.center, decoration: BoxDecoration(color: Colors.white.withOpacity(.05), borderRadius: BorderRadius.circular(14)), child: Text(icon, style: const TextStyle(fontSize: 26))),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12)), const SizedBox(height: 3), Text(subtitle, style: const TextStyle(color: Colors.white60, fontSize: 9, height: 1.4))])),
            const SizedBox(width: 7),
            action,
          ],
        ),
      ),
    );
  }
}

Future<void> showAvatarPicker(BuildContext context, AppController controller) async {
  const avatars = <String>['🦁','🦅','🐺','🦊','🐯','🐼','🌙','⭐','👑','🎩','🧠','🔥'];
  await showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    const Text('تغيير الصورة الرمزية', style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
    const SizedBox(height: 12),
    Center(child: AccountAvatar(controller: controller, size: 96)),
    const SizedBox(height: 13),
    FilledButton.icon(onPressed: () async { final err = await controller.updateAvatarFromGallery(context); if (context.mounted) showToast(context, err ?? 'تم تحديث الصورة.'); }, icon: const Icon(Icons.photo_library_outlined), label: const Text('اختيار صورة من الجهاز')),
    const SizedBox(height: 12),
    Wrap(spacing: 8, runSpacing: 8, alignment: WrapAlignment.center, children: avatars.map((emoji) => InkWell(onTap: () async { await controller.chooseAvatarEmoji(emoji); if (context.mounted) Navigator.pop(context); }, borderRadius: BorderRadius.circular(50), child: Container(width: 54, height: 54, alignment: Alignment.center, decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.white.withOpacity(.06), border: Border.all(color: emoji == controller.avatarEmoji ? Theme.of(context).colorScheme.primary : Colors.white12)), child: Text(emoji, style: const TextStyle(fontSize: 28))))).toList()),
  ]));
}

Future<void> showAvatarPreview(BuildContext context, AppController controller) async {
  await showDialog<void>(
    context: context,
    builder: (dialogContext) => Dialog(
      backgroundColor: Colors.transparent,
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 380),
        child: PremiumPanel(
          child: Padding(
            padding: const EdgeInsets.all(18),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                ProfileCover(
                  coverId: controller.selectedCover,
                  height: 300,
                  colors: coverColorsForV151(controller, controller.selectedCover),
                  child: Center(
                    child: Hero(
                      tag: 'profile-avatar-${controller.username}',
                      child: AccountAvatar(controller: controller, size: 220),
                    ),
                  ),
                ),
                const SizedBox(height: 13),
                Text(controller.displayName, style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900, color: colorFromHex(controller.selectedNameColor))),
                const SizedBox(height: 4),
                Text('@${controller.username}', style: const TextStyle(color: Colors.white60)),
                const SizedBox(height: 12),
                Row(children: [
                  Expanded(child: OutlinedButton.icon(onPressed: () => Navigator.pop(dialogContext), icon: const Icon(Icons.close), label: const Text('إغلاق'))),
                  const SizedBox(width: 8),
                  Expanded(child: FilledButton.icon(onPressed: () { Navigator.pop(dialogContext); showAvatarPicker(context, controller); }, icon: const Icon(Icons.edit_outlined), label: const Text('تعديل'))),
                ]),
              ],
            ),
          ),
        ),
      ),
    ),
  );
}

Future<void> showDeleteAccountDialog(BuildContext context, AppController controller) async {
  final password = TextEditingController();
  bool confirmed = false;
  final shouldDelete = await showDialog<bool>(context: context, builder: (dialogContext) => StatefulBuilder(builder: (context, setState) => AlertDialog(
    title: const Text('حذف الحساب نهائياً'),
    content: Column(mainAxisSize: MainAxisSize.min, children: [
      const Text('سيتم حذف الحساب والملف والمحفظة وسجل المشتريات. كما تُحذف الحسابات غير النشطة بعد 30 يوماً من الخادم، باستثناء حساب المدير.', style: TextStyle(height: 1.5)),
      const SizedBox(height: 12),
      TextField(controller: password, obscureText: true, decoration: const InputDecoration(labelText: 'كلمة المرور للتأكيد')),
      CheckboxListTile(contentPadding: EdgeInsets.zero, value: confirmed, onChanged: (v) => setState(() => confirmed = v == true), title: const Text('أفهم أن الحذف نهائي ولا يمكن التراجع عنه', style: TextStyle(fontSize: 12))),
    ]),
    actions: [TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('إلغاء')), FilledButton(onPressed: confirmed ? () => Navigator.pop(dialogContext, true) : null, style: FilledButton.styleFrom(backgroundColor: Colors.red), child: const Text('حذف نهائي'))],
  ))) ?? false;
  if (!shouldDelete) { password.dispose(); return; }
  final error = await controller.deleteAccount(password.text);
  password.dispose();
  if (!context.mounted) return;
  if (error == null) Navigator.pop(context);
  showToast(context, error ?? 'تم حذف الحساب.');
}

void showProfile(BuildContext context, AppController controller) {
  showPremiumSheet(
    context,
    child: Column(
      children: [
        ProfileCover(
          coverId: controller.selectedCover,
          height: 178,
          colors: coverColorsForV151(controller, controller.selectedCover),
          child: Align(
            alignment: Alignment.bottomCenter,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(14, 14, 14, 13),
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.end,
                children: [
                  InkWell(
                    onTap: () => showAvatarPreview(context, controller),
                    borderRadius: BorderRadius.circular(60),
                    child: Hero(tag: 'profile-avatar-${controller.username}', child: AccountAvatar(controller: controller, size: 86)),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Row(children: [
                          Flexible(child: Text(controller.displayName, overflow: TextOverflow.ellipsis, style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900, color: colorFromHex(controller.selectedNameColor), shadows: [Shadow(color: colorFromHex(controller.selectedNameColor), blurRadius: 9)]))),
                          if (controller.isAdmin) const Padding(padding: EdgeInsetsDirectional.only(start: 6), child: Icon(Icons.verified, size: 18, color: Colors.amber)),
                        ]),
                        const SizedBox(height: 3),
                        Text('@${controller.username} • ${controller.serverConnected ? 'LIVE' : 'LOCAL'}', style: const TextStyle(color: Colors.white70, fontSize: 10, fontWeight: FontWeight.w700)),
                        const SizedBox(height: 6),
                        Row(children: [
                          Image.asset('assets/images/pasha.png', width: 25, height: 25),
                          const SizedBox(width: 5),
                          Text('${controller.vipDays} ${L.t(controller.localeCode, 'days')}', style: const TextStyle(color: Color(0xffffd166), fontWeight: FontWeight.w900, fontSize: 10)),
                        ]),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(child: ProfileMetric(value: '${controller.winRate.toStringAsFixed(1)}%', label: L.t(controller.localeCode, 'winRate'))),
            const SizedBox(width: 7),
            Expanded(child: ProfileMetric(value: '${controller.gamesPlayed}', label: L.t(controller.localeCode, 'matches'))),
            const SizedBox(width: 7),
            Expanded(child: ProfileMetric(value: '${controller.level}', label: 'المستوى')),
          ],
        ),
        const SizedBox(height: 10),
        PremiumPanel(
          child: Padding(
            padding: const EdgeInsets.all(12),
            child: Row(children: [
              const Text('🪙', style: TextStyle(fontSize: 25)),
              const SizedBox(width: 9),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [const Text('رصيد التوكنز', style: TextStyle(color: Colors.white60, fontSize: 9)), FittedBox(child: Text(formatNumber(controller.coins), style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900, fontSize: 17)))])),
              Text('${controller.vipDays} يوم باشا', style: const TextStyle(color: Colors.amber, fontSize: 9, fontWeight: FontWeight.w800)),
            ]),
          ),
        ),
        const SizedBox(height: 9),
        Wrap(
          alignment: WrapAlignment.center,
          spacing: 6,
          runSpacing: 6,
          children: [
            Chip(avatar: Image.asset('assets/images/pasha.png', width: 22, height: 22), label: Text('${controller.vipDays} يوم باشا')),
            Chip(avatar: const Icon(Icons.table_restaurant_outlined, size: 16), label: Text(storeProductById(controller.selectedTable)?.name(controller.localeCode) ?? 'طاولة افتراضية')),
            Chip(avatar: const Icon(Icons.style_outlined, size: 16), label: Text(storeProductById(controller.selectedCardBack)?.name(controller.localeCode) ?? 'ظهر افتراضي')),
            Chip(avatar: const Icon(Icons.bolt, size: 16), label: Text('XP ×${controller.activeXpMultiplier.toStringAsFixed(2)}')),
            Chip(avatar: const Text('😀'), label: Text(storeProductById(controller.selectedEmojiPack)?.name(controller.localeCode) ?? 'إيموجي أساسية')),
            Chip(avatar: const Text('✨'), label: Text(storeProductById(controller.selectedEffect)?.name(controller.localeCode) ?? 'مؤثر أساسي')),
          ],
        ),
        const SizedBox(height: 11),
        SectionTitle(title: L.t(controller.localeCode, 'statistics'), action: '${controller.wins} / ${controller.losses}'),
        const SizedBox(height: 7),
        PremiumPanel(child: Padding(padding: const EdgeInsets.all(12), child: Wrap(spacing: 7, runSpacing: 7, children: [
          _AchievementChip(icon: '🏆', label: controller.wins >= 500 ? 'أسطورة الانتصارات' : 'محترف الانتصارات', unlocked: controller.wins >= 100),
          _AchievementChip(icon: '🔥', label: 'سلسلة ${controller.consecutiveLoginDays} أيام', unlocked: controller.consecutiveLoginDays >= 3),
          _AchievementChip(icon: '🎩', label: 'عضو الباشا', unlocked: controller.vipDays > 0),
          _AchievementChip(icon: '🧠', label: 'خبير المحركات', unlocked: controller.level >= 50),
        ]))),
        const SizedBox(height: 10),
        const SectionTitle(title: 'آخر المباريات', action: 'سجل مختصر'),
        const SizedBox(height: 7),
        PremiumPanel(child: Column(children: [
          _RecentMatchTile(game: 'طرنيب', result: 'فوز', score: '41–28', icon: '🂡', win: true),
          const Divider(height: 1),
          _RecentMatchTile(game: 'تركس', result: 'فوز', score: '186 نقطة', icon: '🃏', win: true),
          const Divider(height: 1),
          _RecentMatchTile(game: 'هاند', result: 'خسارة', score: '92–101', icon: '🎴', win: false),
        ])),
        const SizedBox(height: 11),
        FilledButton.tonalIcon(onPressed: () => showAvatarPicker(context, controller), icon: const Icon(Icons.add_a_photo_outlined), label: const Text('تغيير الصورة أو الرمز'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(44))),
        const SizedBox(height: 8),
        Row(
          children: [
            Expanded(child: FilledButton.tonalIcon(onPressed: () => showWallet(context, controller), icon: const Icon(Icons.account_balance_wallet_outlined), label: const Text('المحفظة'))),
            const SizedBox(width: 7),
            Expanded(child: FilledButton.tonalIcon(onPressed: () { Navigator.pop(context); showFriends(context, controller); }, icon: const Icon(Icons.people_outline), label: const Text('الأصدقاء'))),
          ],
        ),
        if (controller.isAdmin) ...[
          const SizedBox(height: 8),
          FilledButton.icon(onPressed: () { Navigator.pop(context); Navigator.push(context, MaterialPageRoute(builder: (_) => AdminDashboardPage(controller: controller))); }, icon: const Icon(Icons.admin_panel_settings_outlined), label: const Text('فتح لوحة الإدارة'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(46))),
        ],
        const SizedBox(height: 8),
        Row(children: [
          Expanded(child: OutlinedButton.icon(onPressed: () { Navigator.pop(context); showSettings(context, controller); }, icon: const Icon(Icons.settings_outlined), label: const Text('الإعدادات'))),
          const SizedBox(width: 7),
          Expanded(child: OutlinedButton.icon(onPressed: () async { Navigator.pop(context); await controller.logout(); }, icon: const Icon(Icons.logout, color: Colors.redAccent), label: const Text('الخروج'))),
        ]),
      ],
    ),
  );
}

class _AchievementChip extends StatelessWidget {
  final String icon;
  final String label;
  final bool unlocked;
  const _AchievementChip({required this.icon, required this.label, required this.unlocked});
  @override
  Widget build(BuildContext context) => Opacity(
    opacity: unlocked ? 1 : .38,
    child: Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(color: unlocked ? Theme.of(context).colorScheme.primary.withValues(alpha: .12) : Colors.white.withValues(alpha: .04), borderRadius: BorderRadius.circular(14), border: Border.all(color: unlocked ? Theme.of(context).colorScheme.primary.withValues(alpha: .4) : Colors.white12)),
      child: Row(mainAxisSize: MainAxisSize.min, children: [Text(icon), const SizedBox(width: 5), Text(label, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800))]),
    ),
  );
}

class _RecentMatchTile extends StatelessWidget {
  final String game;
  final String result;
  final String score;
  final String icon;
  final bool win;
  const _RecentMatchTile({required this.game, required this.result, required this.score, required this.icon, required this.win});
  @override
  Widget build(BuildContext context) => ListTile(
    dense: true,
    leading: Text(icon, style: const TextStyle(fontSize: 24)),
    title: Text(game, style: const TextStyle(fontWeight: FontWeight.w900)),
    subtitle: Text(score, style: const TextStyle(fontSize: 9)),
    trailing: Chip(label: Text(result, style: TextStyle(color: win ? Colors.greenAccent : Colors.redAccent, fontWeight: FontWeight.w900, fontSize: 9))),
  );
}

class ProfileMetric extends StatelessWidget {
  final String value;
  final String label;

  const ProfileMetric({super.key, required this.value, required this.label});

  @override
  Widget build(BuildContext context) {
    return PremiumPanel(child: Padding(padding: const EdgeInsets.all(11), child: Column(children: [Text(value, style: const TextStyle(fontWeight: FontWeight.w900)), Text(label, style: const TextStyle(fontSize: 8, color: Colors.white60))])));
  }
}

void showNotifications(BuildContext context, AppController controller) {
  showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(
            children: [
              Expanded(child: Text(L.t(controller.localeCode, 'notifications'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
              TextButton(onPressed: () { controller.markAllRead(); setLocalState(() {}); }, child: const Text('قراءة الكل')),
            ],
          ),
          const SizedBox(height: 8),
          if (controller.notices.isEmpty) const Padding(padding: EdgeInsets.all(30), child: Center(child: Text('لا توجد إشعارات'))),
          ...controller.notices.map((notice) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: PremiumListTile(
              icon: notice.icon,
              title: notice.title,
              subtitle: notice.body,
              action: IconButton(onPressed: () { controller.removeNotice(notice); setLocalState(() {}); }, icon: const Icon(Icons.delete_outline)),
            ),
          )),
        ],
      ),
    ),
  );
}

void showWallet(BuildContext context, AppController controller) {
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            Expanded(child: Text(L.t(controller.localeCode, 'transactions'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
            FilledButton(onPressed: () { controller.addCoins(200, 'شحن تجريبي'); Navigator.pop(context); showWallet(context, controller); }, child: const Text('+ 200')),
          ],
        ),
        const SizedBox(height: 10),
        PremiumPanel(child: Padding(padding: const EdgeInsets.all(18), child: Center(child: Text('${formatNumber(controller.coins)} 🪙', style: TextStyle(fontSize: 29, color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900))))),
        const SizedBox(height: 12),
        PremiumPanel(
          child: Column(
            children: controller.transactions.map((tx) => ListTile(
              leading: Icon(tx.amount > 0 ? Icons.south_west : Icons.north_east, color: tx.amount > 0 ? Colors.greenAccent : Colors.redAccent),
              title: Text(tx.label),
              subtitle: Text(tx.date),
              trailing: Text('${tx.amount > 0 ? '+' : ''}${formatNumber(tx.amount)}', style: TextStyle(color: tx.amount > 0 ? Colors.greenAccent : Colors.redAccent, fontWeight: FontWeight.w900)),
            )).toList(),
          ),
        ),
      ],
    ),
  );
}

Future<void> watchRewardedAd(BuildContext context, AppController controller) async {
  if (controller.rewardedAdsRemaining <= 0) {
    showToast(context, 'وصلت إلى الحد اليومي للإعلانات المكافِئة.');
    return;
  }
  bool earned;
  if (kIsWeb) {
    earned = await showDialog<bool>(context: context, barrierDismissible: false, builder: (_) => const RewardedWebPreviewDialog()) ?? false;
  } else {
    earned = await RewardedAds.show();
    if (!earned && context.mounted) showToast(context, 'الإعلان غير متاح الآن. جرّب لاحقاً.');
  }
  if (!earned || !context.mounted) return;
  final error = await controller.grantRewardedAd('reward-${DateTime.now().millisecondsSinceEpoch}');
  if (!context.mounted) return;
  showToast(context, error ?? 'تمت إضافة 50 توكن و15 XP بعد إكمال الإعلان.');
}

class RewardedWebPreviewDialog extends StatefulWidget {
  const RewardedWebPreviewDialog({super.key});
  @override
  State<RewardedWebPreviewDialog> createState() => _RewardedWebPreviewDialogState();
}

class _RewardedWebPreviewDialogState extends State<RewardedWebPreviewDialog> {
  int seconds = 7;
  Timer? timer;
  @override
  void initState() {
    super.initState();
    timer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (seconds <= 1) { timer.cancel(); setState(() => seconds = 0); } else { setState(() => seconds -= 1); }
    });
  }
  @override
  void dispose() { timer?.cancel(); super.dispose(); }
  @override
  Widget build(BuildContext context) => AlertDialog(
    title: const Text('📺 إعلان مكافأة تجريبي'),
    content: Column(mainAxisSize: MainAxisSize.min, children: [
      Container(height: 150, alignment: Alignment.center, decoration: BoxDecoration(gradient: const LinearGradient(colors: [Color(0xff10254a), Color(0xff4c1d95)]), borderRadius: BorderRadius.circular(20)), child: Column(mainAxisSize: MainAxisSize.min, children: [const Text('WARQNA REWARD', style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900)), const SizedBox(height: 8), Text(seconds == 0 ? 'اكتملت المشاهدة' : 'انتظر $seconds ثانية', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900))])),
      const SizedBox(height: 12),
      const Text('هذه معاينة الويب. في تطبيق Android وiOS يُعرض إعلان AdMob الحقيقي ولا تُصرف المكافأة إلا بعد إكماله.', textAlign: TextAlign.center, style: TextStyle(fontSize: 11, color: Colors.white60, height: 1.5)),
    ]),
    actions: [TextButton(onPressed: seconds == 0 ? () => Navigator.pop(context, true) : null, child: const Text('استلام المكافأة'))],
  );
}

void showRewards(BuildContext context, AppController controller) {
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(L.t(controller.localeCode, 'rewards'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
        const SizedBox(height: 10),
        PremiumListTile(icon: '🎁', title: 'المكافأة اليومية', subtitle: '100 توكن + 20 XP', action: FilledButton(onPressed: () async { await controller.claimDaily(); if (context.mounted) { Navigator.pop(context); showToast(context, 'تم استلام المكافأة'); } }, child: Text(L.t(controller.localeCode, 'claim')))),
        const SizedBox(height: 8),
        PremiumListTile(icon: '📺', title: 'شاهد إعلاناً واحصل على مكافأة', subtitle: '50 توكن + 15 XP • المتبقي اليوم ${controller.rewardedAdsRemaining}/5', action: FilledButton(onPressed: controller.rewardedAdsRemaining > 0 ? () async { await watchRewardedAd(context, controller); if (context.mounted) Navigator.pop(context); } : null, child: const Text('مشاهدة'))),
        const SizedBox(height: 8),
        PremiumListTile(icon: '🔥', title: 'استمرارية الدخول', subtitle: '${controller.consecutiveLoginDays} أيام • كل 3 أيام = يوم باشا مجاني', action: FilledButton.tonal(onPressed: null, child: Text('${controller.consecutiveLoginDays}/3'))),
      ],
    ),
  );
}

void showSettings(BuildContext context, AppController controller) {
  bool vibration = true;
  bool autoPlay = true;
  showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(children: [
            Expanded(child: Text(L.t(controller.localeCode, 'settings'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
            Chip(label: Text(controller.serverConnected ? 'LIVE API' : 'PWA LOCAL', style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w900))),
          ]),
          const SizedBox(height: 8),
          SwitchListTile(value: controller.soundEnabled, onChanged: (v) { controller.toggleSound(v); setLocalState(() {}); }, title: const Text('الأصوات'), subtitle: const Text('أصوات اللعب والإيموجي والتنبيهات')),
          SwitchListTile(value: vibration, onChanged: (v) => setLocalState(() => vibration = v), title: const Text('الاهتزاز'), subtitle: const Text('اهتزاز خفيف عند وصول الدور')),
          SwitchListTile(value: autoPlay, onChanged: (v) => setLocalState(() => autoPlay = v), title: const Text('اللعب التلقائي القانوني'), subtitle: const Text('يتصرف الكمبيوتر عند انتهاء وقت الدور')),
          SwitchListTile(value: controller.landscapeMode, onChanged: (_) async { await controller.toggleOrientationMode(); setLocalState(() {}); }, title: const Text('الوضع الأفقي'), subtitle: const Text('تخطيط كامل للطاولة والورق والدردشة')),
          SwitchListTile(value: controller.tableAmbientEffects, onChanged: (v) { controller.updateNoCodeDesign(ambientEffects: v); setLocalState(() {}); }, title: const Text('مؤثرات الطاولة الهادئة'), subtitle: const Text('إضاءات وحركة خفيفة بدون تشتيت')),
          const Divider(),
          ListTile(leading: AccountAvatar(controller: controller, size: 42), title: const Text('الصورة الشخصية'), subtitle: const Text('معاينة وقص قبل الاعتماد'), trailing: const Icon(Icons.chevron_right), onTap: () => showAvatarPicker(context, controller)),
          ListTile(
            leading: SizedBox(width: 52, height: 38, child: ClipRRect(borderRadius: BorderRadius.circular(10), child: ProfileCover(coverId: controller.selectedCover, colors: coverColorsForV151(controller, controller.selectedCover), child: const Center(child: Icon(Icons.person, size: 18))))),
            title: Text(L.t(controller.localeCode, 'covers')),
            subtitle: Text(storeProductById(controller.selectedCover)?.name(controller.localeCode) ?? controller.selectedCover),
            trailing: const Icon(Icons.storefront_outlined),
            onTap: () { Navigator.pop(context); Navigator.push(context, MaterialPageRoute(builder: (_) => Scaffold(appBar: AppBar(title: Text(L.t(controller.localeCode, 'covers'))), body: StorePage(controller: controller)))); },
          ),
          ListTile(
            leading: const Icon(Icons.smart_toy_outlined),
            title: Text(L.t(controller.localeCode, 'difficulty')),
            subtitle: Text(L.t(controller.localeCode, controller.botDifficultyCode)),
            trailing: DropdownButton<String>(
              value: controller.botDifficultyCode,
              underline: const SizedBox.shrink(),
              items: const ['easy','normal','pro','master'].map((value) => DropdownMenuItem(value: value, child: Text(value.toUpperCase(), style: const TextStyle(fontSize: 10)))).toList(),
              onChanged: (value) { if (value != null) { controller.changeBotDifficulty(value); setLocalState(() {}); } },
            ),
          ),
          const Divider(),
          ListTile(leading: const Icon(Icons.language), title: Text(L.t(controller.localeCode, 'language')), subtitle: Text(controller.localeCode.toUpperCase()), trailing: PopupMenuButton<String>(onSelected: (v) { controller.changeLocale(v); setLocalState(() {}); }, itemBuilder: (_) => const [PopupMenuItem(value:'ar',child:Text('العربية')),PopupMenuItem(value:'en',child:Text('English')),PopupMenuItem(value:'de',child:Text('Deutsch')),PopupMenuItem(value:'tr',child:Text('Türkçe')),PopupMenuItem(value:'fr',child:Text('Français')),PopupMenuItem(value:'es',child:Text('Español'))])),
          ListTile(
            leading: const Icon(Icons.palette_outlined),
            title: Text(L.t(controller.localeCode, 'theme')),
            subtitle: Text(controller.themeCode),
            trailing: PopupMenuButton<String>(
              onSelected: (v) { controller.changeTheme(v); setLocalState(() {}); },
              itemBuilder: (_) => const [
                PopupMenuItem(value:'dark',child:Text('داكن فاخر')), PopupMenuItem(value:'emerald',child:Text('زمردي')), PopupMenuItem(value:'royal',child:Text('ملكي')),
                PopupMenuItem(value:'purple',child:Text('بنفسجي')), PopupMenuItem(value:'classic',child:Text('كلاسيكي')), PopupMenuItem(value:'crimson',child:Text('قرمزي أسطوري')),
                PopupMenuItem(value:'midnight',child:Text('منتصف الليل')), PopupMenuItem(value:'aurora',child:Text('الشفق الفاخر')), PopupMenuItem(value:'obsidian',child:Text('أوبسيديان')),
                PopupMenuItem(value:'rose_gold',child:Text('روز غولد')), PopupMenuItem(value:'desert',child:Text('رمال الصحراء')), PopupMenuItem(value:'forest',child:Text('الغابة الملكية')),
                PopupMenuItem(value:'ice',child:Text('الكريستال الجليدي')),
              ],
            ),
          ),
          ListTile(leading: const Icon(Icons.tune_rounded), title: Text(L.t(controller.localeCode, 'noCode')), subtitle: const Text('حجم الأزرار والخط والحواف والمؤثرات مع معاينة فورية'), trailing: const Icon(Icons.chevron_right), onTap: () { Navigator.pop(context); showNoCodeDesignerSheet(context, controller); }),
          ListTile(leading: const Icon(Icons.install_mobile_rounded), title: const Text('تثبيت التطبيق على الهاتف'), subtitle: const Text('استخدم زر التثبيت أو إضافة إلى الشاشة الرئيسية من المتصفح.')),
          ListTile(leading: const Icon(Icons.security_rounded, color: Colors.lightBlueAccent), title: Text(v153Text(controller.localeCode, 'productionCenter')), subtitle: Text(v153Text(controller.localeCode, 'productionCenterHint')), trailing: const Icon(Icons.chevron_right), onTap: () { Navigator.pop(context); openProductionCenterV153(context, controller); }),
          const ListTile(leading: Icon(Icons.history_toggle_off_rounded), title: Text('الحساب غير النشط'), subtitle: Text('يُحذف الحساب غير الإداري بعد 30 يوماً دون فتحه عند تشغيل Scheduler.')),
          const Divider(),
          if (!controller.isAdmin) OutlinedButton.icon(onPressed: () => showDeleteAccountDialog(context, controller), icon: const Icon(Icons.delete_forever, color: Colors.redAccent), label: Text(L.t(controller.localeCode, 'deleteAccount'), style: const TextStyle(color: Colors.redAccent))),
          if (controller.isAdmin) const ListTile(leading: Icon(Icons.shield_outlined, color: Colors.amber), title: Text('حساب المدير محمي'), subtitle: Text('Adnan: مستوى 90+، 1000 يوم باشا، ورصيد إدارة غير محدود.')),
          const SizedBox(height: 8),
          FilledButton(onPressed: () { Navigator.pop(context); showToast(context, 'تم حفظ الإعدادات وتطبيقها على التطبيق.'); }, child: Text(L.t(controller.localeCode, 'save'))),
        ],
      ),
    ),
  );
}

void showNoCodeDesignerSheet(BuildContext context, AppController controller) {
  showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(L.t(controller.localeCode, 'noCode'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
          const SizedBox(height: 5),
          const Text('تتحول المعاينة فوراً أثناء السحب، وتُحفظ القيم لكل جلسة وحساب.', style: TextStyle(color: Colors.white60, height: 1.5)),
          const SizedBox(height: 14),
          PremiumPanel(child: Padding(padding: const EdgeInsets.all(14), child: Column(children: [
            Container(height: 92, alignment: Alignment.center, decoration: BoxDecoration(gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primary.withValues(alpha: .22), Colors.white.withValues(alpha: .03)]), borderRadius: BorderRadius.circular(controller.uiRadius)), child: FilledButton.icon(onPressed: () {}, icon: const Icon(Icons.auto_awesome), label: const Text('معاينة الزر الفاخر'))),
            const SizedBox(height: 10),
            Text('نص تجريبي • ${controller.uiFontScale.toStringAsFixed(2)}×', style: TextStyle(fontSize: 15 * controller.uiFontScale, fontWeight: FontWeight.w800)),
          ]))),
          const SizedBox(height: 12),
          Text('ارتفاع الأزرار: ${controller.uiButtonHeight.round()} px', style: const TextStyle(fontWeight: FontWeight.w800)),
          Slider(min: 38, max: 64, divisions: 26, value: controller.uiButtonHeight, onChanged: (value) { controller.updateNoCodeDesign(buttonHeight: value); setLocalState(() {}); }),
          Text('استدارة الحواف: ${controller.uiRadius.round()} px', style: const TextStyle(fontWeight: FontWeight.w800)),
          Slider(min: 8, max: 32, divisions: 24, value: controller.uiRadius, onChanged: (value) { controller.updateNoCodeDesign(radius: value); setLocalState(() {}); }),
          Text('مقياس الخط: ${controller.uiFontScale.toStringAsFixed(2)}×', style: const TextStyle(fontWeight: FontWeight.w800)),
          Slider(min: .85, max: 1.35, divisions: 25, value: controller.uiFontScale, onChanged: (value) { controller.updateNoCodeDesign(fontScale: value); setLocalState(() {}); }),
          Text('حجم الدردشة: ${controller.uiChatScale.toStringAsFixed(2)}×', style: const TextStyle(fontWeight: FontWeight.w800)),
          Slider(min: .8, max: 1.35, divisions: 22, value: controller.uiChatScale, onChanged: (value) { controller.updateNoCodeDesign(chatScale: value); setLocalState(() {}); }),
          const Text('اللون الرئيسي للأزرار', style: TextStyle(fontWeight: FontWeight.w800)),
          const SizedBox(height: 6),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: v151AccentColors.map((hex) => InkWell(
              onTap: () { controller.updateNoCodeDesign(accentHex: hex); setLocalState(() {}); },
              borderRadius: BorderRadius.circular(30),
              child: Container(width: 38, height: 38, decoration: BoxDecoration(shape: BoxShape.circle, color: colorFromHex(hex), border: Border.all(color: controller.uiAccentHex == hex ? Colors.white : Colors.white24, width: controller.uiAccentHex == hex ? 3 : 1))),
            )).toList(),
          ),
          const SizedBox(height: 8),
          SwitchListTile(value: controller.tableAmbientEffects, onChanged: (value) { controller.updateNoCodeDesign(ambientEffects: value); setLocalState(() {}); }, title: const Text('الحركة الهادئة داخل الطاولة')),
          const SizedBox(height: 8),
          Row(children: [
            Expanded(child: OutlinedButton(onPressed: () { controller.updateNoCodeDesign(buttonHeight: 48, radius: 18, fontScale: 1, chatScale: 1, accentHex: '#ffcf67', ambientEffects: true); setLocalState(() {}); }, child: const Text('استعادة الافتراضي'))),
            const SizedBox(width: 8),
            Expanded(child: FilledButton(onPressed: () => Navigator.pop(context), child: Text(L.t(controller.localeCode, 'save')))),
          ]),
        ],
      ),
    ),
  );
}

void showGiftRoadSheet(BuildContext context, AppController controller) {
  const steps = <(int, String, String)>[
    (5, '🎁', '50 توكن'),
    (10, '💎', '100 توكن'),
    (20, '👑', '100 توكن'),
    (30, '🏆', '200 توكن'),
  ];
  showPremiumSheet(
    context,
    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      const Text('طريق الهدايا', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
      const SizedBox(height: 5),
      const Text('كل فوز قانوني يرفع تقدمك خطوة. استلم المكافآت عند بلوغ المراحل.', style: TextStyle(color: Colors.white60, height: 1.5)),
      const SizedBox(height: 12),
      LinearProgressIndicator(value: controller.giftRoadProgress / 30, minHeight: 9),
      const SizedBox(height: 13),
      ...steps.map((entry) {
        final available = controller.giftRoadProgress >= entry.$1;
        final claimed = controller.claimedGiftSteps.contains(entry.$1);
        return Padding(padding: const EdgeInsets.only(bottom: 8), child: PremiumListTile(
          icon: entry.$2,
          title: 'مرحلة ${entry.$1} انتصار',
          subtitle: entry.$3,
          action: FilledButton.tonal(
            onPressed: available && !claimed ? () { final ok = controller.claimGiftRoad(entry.$1); showToast(context, ok ? 'تم استلام ${entry.$3}.' : 'المكافأة غير متاحة.'); Navigator.pop(context); } : null,
            child: Text(claimed ? 'مستلمة' : available ? 'استلام' : '${controller.giftRoadProgress}/${entry.$1}'),
          ),
        ));
      }),
    ]),
  );
}

void showPashaBenefits(BuildContext context, AppController controller) {
  final pasha = products.where((p) => p.category == 'pasha').toList();
  showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    Center(child: Image.asset('assets/images/pasha.png', width: 104, height: 104, fit: BoxFit.contain)),
    const Text('مزايا الباشا', textAlign: TextAlign.center, style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
    const SizedBox(height: 8),
    const Text('شارة ذهبية • 20 XP لكل جولة • إنشاء مجموعات ومنافسات • صلاحية إدارة الغرفة • أولوية دخول وملف شخصي فاخر.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white60, height: 1.6)),
    const SizedBox(height: 12),
    SizedBox(height: 118, child: ListView.separated(scrollDirection: Axis.horizontal, itemCount: pasha.length, separatorBuilder: (_, __) => const SizedBox(width: 8), itemBuilder: (_, i) { final product = pasha[i]; return SizedBox(width: 148, child: FilledButton.tonal(onPressed: () => showProductPreview(context, controller, product), child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Image.asset('assets/images/pasha.png', width: 34, height: 34), const SizedBox(height: 3), Text('${controller.durationFor(product) ?? 0} يوم', style: const TextStyle(fontWeight: FontWeight.w900)), Text('🪙 ${formatNumber(controller.priceFor(product))}', style: const TextStyle(fontSize: 9))]))); })),
  ]));
}

void showClubChallenges(BuildContext context, AppController controller, String clubName) {
  showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    Text('تحديات $clubName', style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
    const SizedBox(height: 9),
    PremiumListTile(icon: '⚔️', title: 'فوزان في الطرنيب', subtitle: 'مكافأة 200 توكن + 100 XP', action: FilledButton(onPressed: () { controller.joinChallenge('club_tarneeb'); Navigator.pop(context); showToast(context, 'تم تفعيل تحدي المجموعة.'); }, child: const Text('ابدأ'))),
    const SizedBox(height: 8),
    PremiumListTile(icon: '🎴', title: 'فوز في الهاند أو البناكل', subtitle: 'مكافأة 100 توكن + نقطة للمجموعة', action: FilledButton(onPressed: () { controller.joinChallenge('club_rummy'); Navigator.pop(context); showToast(context, 'تم تفعيل التحدي.'); }, child: const Text('ابدأ'))),
  ]));
}

Future<void> openGameRoom(BuildContext context, AppController controller, GameInfo game, {RoomLaunchOptions options = const RoomLaunchOptions()}) async {
  if (!controller.canEnterGame(game.id)) {
    showToast(context, 'وصلت إلى 3 مغادرات لهذه اللعبة، ولا يمكنك العودة إلى الجلسة نفسها.');
    return;
  }
  if (!controller.enterGame(game.id)) {
    showToast(context, 'أنت داخل لعبة أخرى. غادر اللعبة الحالية قبل بدء لعبة جديدة.');
    return;
  }
  final previousLandscape = controller.landscapeMode;
  await controller.setLandscapeMode(true);
  if (!context.mounted) return;
  await Navigator.push(context, MaterialPageRoute(builder: (_) => GameRoomPage(controller: controller, game: game, options: options)));
  controller.leaveGame(game.id);
  if (!previousLandscape) await controller.setLandscapeMode(false);
}

void showChallenges(BuildContext context, AppController controller) {
  final challenges = <(String, String, String, String, int)>[
    ('duel_tarneeb', '⚔️', 'تحدي طرنيب مباشر', 'tarneeb', 200),
    ('duel_trix', '👑', 'تحدي تركس ملكي', 'trix', 200),
    ('duel_hand', '🎴', 'تحدي هاند سريع', 'hand', 100),
    ('duel_basra', '♦️', 'تحدي باصرة', 'basra', 50),
  ];
  showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    const Text('تحديات اللاعبين', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
    const SizedBox(height: 4),
    const Text('اختر تحدياً. الفوز يمنحك توكنز وXP ويرفع طريق الهدايا، بدون رسوم دخول.', style: TextStyle(color: Colors.white60, height: 1.5)),
    const SizedBox(height: 10),
    ...challenges.map((entry) => Padding(padding: const EdgeInsets.only(bottom: 9), child: PremiumListTile(icon: entry.$2, title: entry.$3, subtitle: '${L.t(controller.localeCode, entry.$4)} • مكافأة ${formatNumber(entry.$5)}', action: FilledButton(onPressed: () async { if (!controller.joinChallenge(entry.$1)) { showToast(context, 'غادر التحدي الحالي أولاً.'); return; } final game = gamesCatalog.firstWhere((g) => g.id == entry.$4); Navigator.pop(context); showCreateRoom(context, controller, game); controller.leaveChallenge(); }, child: const Text('تحدَّ الآن'))))),
  ]));
}

void showCompetitions(BuildContext context, AppController controller) {
  final competitions = <(String, String, String, int, String)>[
    ('champions', '🏆', 'بطولة الأبطال', 200, 'tarneeb'),
    ('weekend', '🎉', 'تحدي نهاية الأسبوع', 100, 'trix'),
    ('rummy_cup', '🎴', 'كأس الهاند والبناكل', 200, 'hand'),
  ];
  showPremiumSheet(
    context,
    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      Text(L.t(controller.localeCode, 'competitions'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
      const SizedBox(height: 10),
      ...competitions.map((entry) {
        final active = controller.activeCompetition == entry.$1;
        return Padding(padding: const EdgeInsets.only(bottom: 9), child: PremiumListTile(icon: entry.$2, title: entry.$3, subtitle: 'مكافأة الفوز ${formatNumber(entry.$4)} توكن • دخول مجاني', action: FilledButton(onPressed: () { if (active) { controller.leaveCompetition(); showToast(context, 'تمت مغادرة المنافسة.'); } else { final ok = controller.joinCompetition(entry.$1); showToast(context, ok ? 'تم تسجيلك في ${entry.$3}.' : 'غادر المنافسة الحالية قبل الانضمام لأخرى.'); } }, child: Text(active ? 'مغادرة' : L.t(controller.localeCode, 'join')))));
      }),
      const SizedBox(height: 4),
      FilledButton.tonalIcon(onPressed: () { Navigator.pop(context); showChallenges(context, controller); }, icon: const Icon(Icons.bolt_rounded), label: const Text('تحديات فورية بين اللاعبين')),
    ]),
  );
}

void showGameLobby(BuildContext context, AppController controller, GameInfo game) {
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Center(child: Text(game.icon, style: const TextStyle(fontSize: 75))),
        const SizedBox(height: 5),
        Center(child: Text(L.t(controller.localeCode, game.id), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900))),
        Center(child: Text('${formatNumber(game.players)} لاعب متصل', style: const TextStyle(color: Colors.white60))),
        const SizedBox(height: 15),
        Row(
          children: [
            Expanded(child: FilledButton.icon(onPressed: () { Navigator.pop(context); showPlayModePicker(context, controller, game); }, icon: const Icon(Icons.play_arrow), label: Text(L.t(controller.localeCode, 'friendly')))),
            const SizedBox(width: 8),
            Expanded(child: FilledButton.tonalIcon(onPressed: () => showCompetitions(context, controller), icon: const Icon(Icons.emoji_events), label: Text(L.t(controller.localeCode, 'competitions')))),
          ],
        ),
        const SizedBox(height: 10),
        PremiumPanel(
          child: Wrap(
            alignment: WrapAlignment.spaceAround,
            runAlignment: WrapAlignment.center,
            spacing: 4,
            runSpacing: 6,
            children: [
              QuickButton(icon: '📖', label: L.t(controller.localeCode, 'rules'), onTap: () => showRules(context, controller.localeCode, game.id)),
              QuickButton(icon: '📊', label: L.t(controller.localeCode, 'leaderboard'), onTap: () => showLeaderboard(context)),
              QuickButton(icon: '➕', label: L.t(controller.localeCode, 'createRoom'), onTap: () => showCreateRoom(context, controller, game)),
              QuickButton(icon: '🌐', label: L.t(controller.localeCode, 'openRooms'), onTap: () => showAvailableRooms(context, controller, game)),
              QuickButton(icon: '🔑', label: L.t(controller.localeCode, 'joinByCode'), onTap: () => showJoinRoomByCode(context, controller, game)),
              QuickButton(icon: '👥', label: L.t(controller.localeCode, 'friends'), onTap: () => showFriends(context, controller)),
            ],
          ),
        ),
      ],
    ),
  );
}

Future<void> showAvailableRooms(BuildContext context, AppController controller, GameInfo game) async {
  if (!controller.serverConnected) {
    showToast(context, L.t(controller.localeCode, 'serverRoomsNeedBackend'));
    return;
  }
  try {
    final data = await controller.api.availableRooms(game.id);
    final rooms = data['rooms'] is List ? data['rooms'] as List : const [];
    if (!context.mounted) return;
    showPremiumSheet(
      context,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(L.t(controller.localeCode, 'openRooms'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
          const SizedBox(height: 5),
          Text(L.t(controller.localeCode, 'openRoomsHint'), style: const TextStyle(color: Colors.white60)),
          const SizedBox(height: 12),
          if (rooms.isEmpty)
            Padding(padding: const EdgeInsets.symmetric(vertical: 28), child: Center(child: Text(L.t(controller.localeCode, 'noOpenRooms'), style: const TextStyle(color: Colors.white60))))
          else
            ...rooms.map((raw) {
              final room = raw is Map ? Map<String, dynamic>.from(raw) : <String, dynamic>{};
              final voice = room['voice_enabled'] == true || room['voice_enabled'] == 1;
              final code = room['code']?.toString() ?? '';
              return Padding(
                padding: const EdgeInsets.only(bottom: 8),
                child: PremiumListTile(
                  icon: voice ? '🎙️' : '🃏',
                  title: room['name']?.toString() ?? code,
                  subtitle: '${voice ? L.t(controller.localeCode, 'voiceGame') : L.t(controller.localeCode, 'normalGame')} • ${room['players'] ?? 1}/${room['max_players'] ?? 4} • ${room['turn_seconds'] ?? 10}s • $code',
                  action: FilledButton(
                    onPressed: code.isEmpty ? null : () async {
                      Navigator.pop(context);
                      await openGameRoom(context, controller, game, options: RoomLaunchOptions(roomCode: code, voiceEnabled: voice));
                    },
                    child: Text(L.t(controller.localeCode, 'join')),
                  ),
                ),
              );
            }),
        ],
      ),
    );
  } on ApiException catch (e) {
    if (context.mounted) showToast(context, e.message);
  } catch (_) {
    if (context.mounted) showToast(context, L.t(controller.localeCode, 'roomsLoadFailed'));
  }
}

void showJoinRoomByCode(BuildContext context, AppController controller, GameInfo game) {
  final codeController = TextEditingController();
  final passwordController = TextEditingController();
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(L.t(controller.localeCode, 'joinByCode'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
        const SizedBox(height: 7),
        Text(L.t(controller.localeCode, 'joinByCodeHint'), style: const TextStyle(color: Colors.white60, height: 1.45)),
        const SizedBox(height: 12),
        TextField(controller: codeController, textCapitalization: TextCapitalization.characters, decoration: InputDecoration(labelText: L.t(controller.localeCode, 'roomCode'), prefixIcon: const Icon(Icons.tag_rounded))),
        const SizedBox(height: 9),
        TextField(controller: passwordController, obscureText: true, decoration: InputDecoration(labelText: '${L.t(controller.localeCode, 'password')} (${L.t(controller.localeCode, 'optional')})', prefixIcon: const Icon(Icons.lock_outline_rounded))),
        const SizedBox(height: 14),
        FilledButton.icon(
          onPressed: () async {
            final code = codeController.text.trim().toUpperCase();
            if (code.length < 4) {
              showToast(context, L.t(controller.localeCode, 'enterRoomCode'));
              return;
            }
            Navigator.pop(context);
            await openGameRoom(context, controller, game, options: RoomLaunchOptions(roomCode: code, password: passwordController.text.trim()));
          },
          icon: const Icon(Icons.login_rounded),
          label: Text(L.t(controller.localeCode, 'joinRoom')),
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
        ),
      ],
    ),
  );
}

void showPlayModePicker(BuildContext context, AppController controller, GameInfo game) {
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Text(L.t(controller.localeCode, 'chooseGameMode'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
        const SizedBox(height: 6),
        Text(L.t(controller.localeCode, 'chooseGameModeHint'), style: const TextStyle(color: Colors.white60, height: 1.5)),
        const SizedBox(height: 14),
        Row(
          children: [
            Expanded(
              child: _GameModeCard(
                icon: Icons.style_rounded,
                title: L.t(controller.localeCode, 'normalGame'),
                subtitle: L.t(controller.localeCode, 'normalGameHint'),
                color: Theme.of(context).colorScheme.primary,
                onTap: () {
                  Navigator.pop(context);
                  showCreateRoom(context, controller, game);
                },
              ),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: _GameModeCard(
                icon: Icons.mic_rounded,
                title: L.t(controller.localeCode, 'voiceGame'),
                subtitle: L.t(controller.localeCode, 'voiceGameHint'),
                color: Colors.greenAccent,
                onTap: () {
                  Navigator.pop(context);
                  showCreateRoom(context, controller, game, initialVoice: true);
                },
              ),
            ),
          ],
        ),
      ],
    ),
  );
}

class _GameModeCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final Color color;
  final VoidCallback onTap;
  const _GameModeCard({required this.icon, required this.title, required this.subtitle, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(20),
      child: Container(
        constraints: const BoxConstraints(minHeight: 160),
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: color.withValues(alpha: .08),
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: color.withValues(alpha: .35)),
        ),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(width: 54, height: 54, decoration: BoxDecoration(shape: BoxShape.circle, color: color.withValues(alpha: .16)), child: Icon(icon, color: color, size: 29)),
            const SizedBox(height: 10),
            Text(title, textAlign: TextAlign.center, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
            const SizedBox(height: 5),
            Text(subtitle, maxLines: 3, overflow: TextOverflow.ellipsis, textAlign: TextAlign.center, style: const TextStyle(fontSize: 11, height: 1.4, color: Colors.white60)),
          ],
        ),
      ),
    );
  }
}

void showCreateRoom(BuildContext context, AppController controller, GameInfo game, {bool initialVoice = false}) {
  final nameController = TextEditingController(text: '${L.t(controller.localeCode, game.id)} • ${controller.displayName}');
  final passwordController = TextEditingController();
  var visibility = 'public';
  var voiceEnabled = initialVoice;
  var turnSeconds = 10;
  showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Text(L.t(controller.localeCode, 'createRoom'), style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
          const SizedBox(height: 5),
          Text(L.t(controller.localeCode, 'roomModeDescription'), style: const TextStyle(color: Colors.white60, height: 1.45)),
          const SizedBox(height: 12),
          TextField(controller: nameController, decoration: InputDecoration(labelText: L.t(controller.localeCode, 'roomName'), prefixIcon: const Icon(Icons.meeting_room_outlined))),
          const SizedBox(height: 10),
          SegmentedButton<bool>(
            segments: [
              ButtonSegment(value: false, icon: const Icon(Icons.style_rounded), label: Text(L.t(controller.localeCode, 'normalGame'))),
              ButtonSegment(value: true, icon: const Icon(Icons.mic_rounded), label: Text(L.t(controller.localeCode, 'voiceGame'))),
            ],
            selected: {voiceEnabled},
            onSelectionChanged: (values) => setLocalState(() => voiceEnabled = values.first),
            showSelectedIcon: true,
          ),
          if (voiceEnabled) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: Colors.greenAccent.withValues(alpha: .07), borderRadius: BorderRadius.circular(14), border: Border.all(color: Colors.greenAccent.withValues(alpha: .25))),
              child: Row(children: [const Icon(Icons.verified_user_outlined, color: Colors.greenAccent), const SizedBox(width: 8), Expanded(child: Text(L.t(controller.localeCode, 'voicePrivacyHint'), style: const TextStyle(fontSize: 11, height: 1.45)))]),
            ),
          ],
          const SizedBox(height: 10),
          DropdownButtonFormField<String>(
            value: visibility,
            decoration: InputDecoration(labelText: L.t(controller.localeCode, 'roomVisibility'), prefixIcon: const Icon(Icons.public_rounded)),
            items: [
              DropdownMenuItem(value: 'public', child: Text(L.t(controller.localeCode, 'publicRoom'))),
              DropdownMenuItem(value: 'friends', child: Text(L.t(controller.localeCode, 'friendsRoom'))),
              DropdownMenuItem(value: 'private', child: Text(L.t(controller.localeCode, 'privateRoom'))),
            ],
            onChanged: (value) => setLocalState(() => visibility = value ?? 'public'),
          ),
          if (visibility == 'private') ...[
            const SizedBox(height: 9),
            TextField(controller: passwordController, obscureText: true, decoration: InputDecoration(labelText: L.t(controller.localeCode, 'password'), prefixIcon: const Icon(Icons.lock_outline_rounded))),
          ],
          const SizedBox(height: 10),
          DropdownButtonFormField<int>(
            value: turnSeconds,
            decoration: InputDecoration(labelText: L.t(controller.localeCode, 'turnSpeed'), prefixIcon: const Icon(Icons.timer_outlined)),
            items: const [
              DropdownMenuItem(value: 5, child: Text('5 ثوانٍ • سريعة')),
              DropdownMenuItem(value: 7, child: Text('7 ثوانٍ • متوسطة')),
              DropdownMenuItem(value: 10, child: Text('10 ثوانٍ • هادئة')),
            ],
            onChanged: (value) => setLocalState(() => turnSeconds = value ?? 10),
          ),
          const SizedBox(height: 14),
          FilledButton.icon(
            onPressed: () async {
              if (nameController.text.trim().isEmpty) {
                showToast(context, L.t(controller.localeCode, 'enterRoomName'));
                return;
              }
              if (visibility == 'private' && passwordController.text.trim().length < 3) {
                showToast(context, L.t(controller.localeCode, 'enterRoomPassword'));
                return;
              }
              final options = RoomLaunchOptions(
                roomName: nameController.text.trim(),
                voiceEnabled: voiceEnabled,
                visibility: visibility,
                password: visibility == 'private' ? passwordController.text.trim() : null,
                turnSeconds: turnSeconds,
              );
              Navigator.pop(context);
              await openGameRoom(context, controller, game, options: options);
            },
            icon: Icon(voiceEnabled ? Icons.mic_rounded : Icons.play_arrow_rounded),
            label: Text(voiceEnabled ? L.t(controller.localeCode, 'createVoiceRoom') : L.t(controller.localeCode, 'createNormalRoom')),
            style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(54)),
          ),
        ],
      ),
    ),
  );
}

Future<void> showProductPreview(BuildContext context, AppController controller, StoreProduct product) {
  return showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        _ProductLivePreview(controller: controller, product: product),
        const SizedBox(height: 14),
        Text(controller.nameFor(product), textAlign: TextAlign.center, style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
        const SizedBox(height: 6),
        Text(controller.descriptionFor(product), textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60, height: 1.55)),
        const SizedBox(height: 12),
        Row(
          children: [
            Expanded(child: _StoreFact(icon: '🪙', label: 'السعر', value: formatNumber(controller.priceFor(product)))),
            const SizedBox(width: 8),
            Expanded(child: _StoreFact(icon: '🛡️', label: 'الحالة', value: controller.owned.contains(product.id) && !product.reusable ? 'مملوك' : product.reusable ? 'قابل للتجديد' : 'متاح')),
            const SizedBox(width: 8),
            Expanded(child: _StoreFact(icon: '⭐', label: 'الفئة', value: product.tierLabel(controller.localeCode))),
          ],
        ),
        if (controller.durationFor(product) != null) ...[const SizedBox(height: 8), _StoreFact(icon: '⏳', label: 'المدة', value: '${controller.durationFor(product)} أيام')],
        const SizedBox(height: 13),
        FilledButton.icon(
          onPressed: controller.owned.contains(product.id) && !product.reusable
              ? () {
                  controller.activateProduct(product);
                  Navigator.pop(context);
                  showToast(context, 'تم تفعيل ${controller.nameFor(product)}.');
                }
              : () async {
                  final confirmed = await showDialog<bool>(
                    context: context,
                    builder: (dialogContext) => AlertDialog(
                      title: const Text('تأكيد الشراء'),
                      content: Text('هل أنت متأكد أنك تريد شراء ${controller.nameFor(product)} مقابل ${formatNumber(controller.priceFor(product))} توكن؟\n\nلن يتم الخصم إلا بعد التأكيد.'),
                      actions: [
                        TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('إلغاء')),
                        FilledButton(onPressed: () => Navigator.pop(dialogContext, true), child: const Text('نعم، شراء')),
                      ],
                    ),
                  );
                  if (confirmed != true || !context.mounted) return;
                  final ok = await controller.buy(product);
                  if (!context.mounted) return;
                  Navigator.pop(context);
                  showToast(context, ok ? 'تم الشراء وإضافة العنصر إلى مقتنياتك.' : 'تعذر الشراء أو الرصيد غير كافٍ.');
                },
          icon: const Icon(Icons.shopping_bag_outlined),
          label: Text(controller.owned.contains(product.id) && !product.reusable ? 'تفعيل العنصر' : '${L.t(controller.localeCode, 'buy')} • ${formatNumber(controller.priceFor(product))} 🪙'),
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(50)),
        ),
      ],
    ),
  );
}

class _ProductLivePreview extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;
  const _ProductLivePreview({required this.controller, required this.product});

  @override
  Widget build(BuildContext context) {
    final c1 = controller.color1For(product);
    final c2 = controller.color2For(product);
    Widget preview;
    if (product.category == 'tables') {
      preview = Container(
        width: 290,
        height: 160,
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(34),
          gradient: RadialGradient(colors: [c2.withOpacity(.88), c1, Color.lerp(c1, Colors.black, .58)!]),
          border: Border.all(color: c2, width: 5),
          boxShadow: [BoxShadow(color: c2.withOpacity(.24), blurRadius: 26, spreadRadius: 2), BoxShadow(color: Colors.black.withOpacity(.5), blurRadius: 24, offset: const Offset(0, 13))],
        ),
        child: Stack(
          children: [
            Center(child: Text(product.icon, style: TextStyle(color: Colors.white.withOpacity(.25), fontSize: 65, fontWeight: FontWeight.w900))),
            Positioned(left: 18, right: 18, bottom: 12, child: Text(controller.displayName, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white70, fontSize: 12, fontWeight: FontWeight.w900))),
          ],
        ),
      );
    } else if (product.category == 'cards') {
      preview = Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          PlayingCard(label: 'A♠', width: 78, height: 114),
          Transform.translate(
            offset: const Offset(-17, 8),
            child: Container(
              width: 78,
              height: 114,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(13),
                gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [c1, c2]),
                border: Border.all(color: Colors.white.withOpacity(.55), width: 2),
                boxShadow: [BoxShadow(color: c2.withOpacity(.35), blurRadius: 18)],
              ),
              child: Center(child: Text(product.icon, style: const TextStyle(fontSize: 38))),
            ),
          ),
        ],
      );
    } else if (product.category == 'names') {
      preview = Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          GlowAvatar(text: controller.avatarEmoji, bytes: AccountAvatar(controller: controller)._decode(), size: 72, color: c1),
          const SizedBox(width: 14),
          Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisSize: MainAxisSize.min, children: [
            Text(controller.displayName, style: TextStyle(color: c1, fontSize: 25, fontWeight: FontWeight.w900, shadows: [Shadow(color: c1.withOpacity(.55), blurRadius: 12), const Shadow(color: Colors.black, blurRadius: 8)])),
            Text('المستوى ${controller.level} • ${controller.vipDays > 0 ? 'باشا' : 'لاعب'}', style: const TextStyle(color: Colors.white60, fontSize: 11)),
          ]),
        ],
      );
    } else if (product.category == 'chat_colors') {
      preview = Container(
        width: 285,
        padding: const EdgeInsets.all(15),
        decoration: BoxDecoration(color: const Color(0xff111827), borderRadius: BorderRadius.circular(22), border: Border.all(color: c1.withOpacity(.55))),
        child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.start, children: [
          Row(children: [GlowAvatar(text: controller.avatarEmoji, bytes: AccountAvatar(controller: controller)._decode(), size: 42, color: c1), const SizedBox(width: 9), Text(controller.displayName, style: TextStyle(color: c1, fontWeight: FontWeight.w900, shadows: [Shadow(color: c1, blurRadius: 10)]))]),
          const SizedBox(height: 10),
          Container(padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: c1.withOpacity(.13), borderRadius: BorderRadius.circular(controller.uiRadius.clamp(10, 26).toDouble()), border: Border.all(color: c1.withOpacity(.35))), child: Text('رسالة تجريبية بلون الدردشة المختار', style: TextStyle(color: c1, fontWeight: FontWeight.w800))),
        ]),
      );
    } else if (product.category == 'themes') {
      preview = Container(
        width: 290,
        height: 155,
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(24), gradient: LinearGradient(colors: [c1, c2.withOpacity(.85)]), boxShadow: [BoxShadow(color: c2.withOpacity(.25), blurRadius: 22)]),
        child: Column(children: [
          Row(children: [const CircleAvatar(radius: 18, child: Text('W')), const SizedBox(width: 9), const Expanded(child: Text('معاينة الثيم', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 15))), Container(width: 52, height: 24, decoration: BoxDecoration(color: Colors.white.withOpacity(.16), borderRadius: BorderRadius.circular(13)))]),
          const Spacer(),
          Row(children: [Expanded(child: Container(height: 50, decoration: BoxDecoration(color: Colors.white.withOpacity(.11), borderRadius: BorderRadius.circular(14)))), const SizedBox(width: 8), Expanded(child: Container(height: 50, decoration: BoxDecoration(color: Colors.black.withOpacity(.18), borderRadius: BorderRadius.circular(14))))]),
        ]),
      );
    } else if (product.category == 'pasha') {
      preview = Column(mainAxisSize: MainAxisSize.min, children: [
        Image.asset('assets/images/pasha.png', width: 116, height: 116, fit: BoxFit.contain),
        Text('${controller.durationFor(product) ?? 0} يوم', style: TextStyle(color: Theme.of(context).colorScheme.primary, fontSize: 22, fontWeight: FontWeight.w900)),
        const Text('تحكم بالغرفة • شارة خاصة • XP إضافي', style: TextStyle(color: Colors.white60, fontSize: 11)),
      ]);
    } else if (product.category == 'covers') {
      preview = SizedBox(width: 320, child: ProfileCover(coverId: product.id, height: 175, colors: <Color>[c1, c2], child: Align(alignment: Alignment.bottomCenter, child: Padding(padding: const EdgeInsets.all(14), child: Row(children: [AccountAvatar(controller: controller, size: 58), const SizedBox(width: 10), Expanded(child: Text(controller.displayName, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)))])))));
    } else if (product.category == 'boost') {
      preview = Container(
        width: 190,
        height: 145,
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(30), gradient: LinearGradient(colors: [c1.withOpacity(.9), c2]), boxShadow: [BoxShadow(color: c1.withOpacity(.45), blurRadius: 28)]),
        child: Center(child: Column(mainAxisSize: MainAxisSize.min, children: [const Text('🚀', style: TextStyle(fontSize: 58)), Text('×${product.multiplier ?? 1}', style: const TextStyle(fontSize: 28, fontWeight: FontWeight.w900))])),
      );
    } else if (product.category == 'emoji') {
      preview = Padding(padding: const EdgeInsets.all(16), child: FittedBox(fit: BoxFit.scaleDown, child: Text(product.icon, textAlign: TextAlign.center, style: const TextStyle(fontSize: 70))));
    } else {
      preview = Text(product.icon, style: const TextStyle(fontSize: 105));
    }
    return Container(
      height: 220,
      alignment: Alignment.center,
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(.17),
        borderRadius: BorderRadius.circular(25),
        border: Border.all(color: Colors.white.withOpacity(.08)),
      ),
      child: preview,
    );
  }
}

class _StoreFact extends StatelessWidget {
  final String icon;
  final String label;
  final String value;
  const _StoreFact({required this.icon, required this.label, required this.value});
  @override
  Widget build(BuildContext context) => PremiumPanel(child: Padding(padding: const EdgeInsets.all(11), child: Column(children: [Text('$icon $label', style: const TextStyle(color: Colors.white60, fontSize: 9)), const SizedBox(height: 4), Text(value, style: const TextStyle(fontWeight: FontWeight.w900))])));
}

void showRules(BuildContext context, String lang, String gameId) {
  final info = gameRuleBook[gameId] ?? gameRuleBook['tarneeb']!;
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          children: [
            Container(width: 52, height: 52, alignment: Alignment.center, decoration: BoxDecoration(color: Theme.of(context).colorScheme.primary.withOpacity(.12), borderRadius: BorderRadius.circular(16)), child: Text(info.icon, style: const TextStyle(fontSize: 29))),
            const SizedBox(width: 11),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('${L.t(lang, 'rules')} — ${L.t(lang, gameId)}', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900)),
              Text('${info.players} لاعبين • ${info.deck} • ${info.hand}', style: const TextStyle(color: Colors.white54, fontSize: 9)),
            ])),
          ],
        ),
        const SizedBox(height: 13),
        _RuleSection(title: 'طريقة اللعب', icon: Icons.style_rounded, text: info.play),
        const SizedBox(height: 8),
        _RuleSection(title: 'الحركات القانونية', icon: Icons.verified_outlined, text: info.legal),
        const SizedBox(height: 8),
        _RuleSection(title: 'الفوز والخسارة', icon: Icons.emoji_events_outlined, text: info.win),
        const SizedBox(height: 8),
        _RuleSection(title: 'العدالة والمحرك', icon: Icons.security_rounded, text: 'التوزيع يتم بالخلط داخل المحرك، وتتحقق الجهة الخادمة من كل حركة قبل اعتمادها. لا تُكشف أوراق الخصوم، ولا تُخصم توكنز من اللعب.'),
      ],
    ),
  );
}

class GameRuleInfo {
  final String icon;
  final String players;
  final String deck;
  final String hand;
  final String play;
  final String legal;
  final String win;
  const GameRuleInfo({required this.icon, required this.players, required this.deck, required this.hand, required this.play, required this.legal, required this.win});
}

const gameRuleBook = <String, GameRuleInfo>{
  'tarneeb': GameRuleInfo(icon:'🂡',players:'4',deck:'52 ورقة',hand:'13 ورقة لكل لاعب',play:'فريقان متقابلان. تبدأ المزايدة من 7 حتى 13، وصاحب أعلى طلب يختار نوع الطرنيب ثم يقود أول لمّة.',legal:'يجب اتباع نوع الورقة المتصدرة عند توفره. إذا تعذر ذلك يجوز لعب أي ورقة، والطرنيب يتفوق على الأنواع الأخرى.',win:'إذا حقق فريق صاحب الطلب قيمة طلبه يضيف عدد لمّاته، وإلا تُخصم قيمة الطلب منه. يفوز أول فريق يبلغ الهدف المحدد.'),
  'syrian_tarneeb': GameRuleInfo(icon:'🇸🇾',players:'4',deck:'52 ورقة',hand:'13 ورقة',play:'طرنيب شراكة بنمط هدف 61 نقطة مع مزايدة واختيار الحكم.',legal:'اتباع النوع إلزامي، وتُحسم اللمّة بأعلى ورقة من النوع المتصدر ما لم يُلعب الحكم.',win:'تُحتسب نتيجة العقد واللمّات وفق نمط طرنيب 61، والفريق الأعلى عند بلوغ الهدف يفوز.'),
  'tarneeb_400': GameRuleInfo(icon:'4️⃣',players:'4',deck:'52 ورقة',hand:'13 ورقة',play:'لعبة شراكة بهدف 400 نقطة وحكم ثابت للكبة، مع طلبات فردية تُجمع للفريق.',legal:'اتباع النوع إلزامي، وتُراعى أولوية الحكم وترتيب الأوراق في كل لمّة.',win:'تُضاف أو تُخصم نقاط الطلب لكل لاعب وفق نجاحه، ويكسب الفريق الذي يبلغ 400 وفق شروط النهاية.'),
  'trix': GameRuleInfo(icon:'🃏',players:'4',deck:'52 ورقة',hand:'13 ورقة',play:'لكل لاعب مملكة يختار فيها عقود شيخ الكبة والبنات والديناري واللطوش وتركس.',legal:'تختلف الحركة بحسب العقد، مع اتباع النوع في عقود اللمّات وترتيب تنازلي خاص في عقد تركس.',win:'بعد اكتمال الممالك والعقود تُجمع النقاط، ويفوز صاحب أعلى مجموع.'),
  'trix_partner': GameRuleInfo(icon:'👥',players:'4',deck:'52 ورقة',hand:'13 ورقة',play:'قواعد تركس نفسها ضمن فريقين متقابلين، وتُدار الممالك والعقود بنظام الشراكة.',legal:'يفرض المحرك قيود كل عقد واتباع النوع، ويجمع نتيجة الشريكين.',win:'يفوز الفريق صاحب أعلى مجموع بعد اكتمال العقود.'),
  'trix_complex': GameRuleInfo(icon:'👑',players:'4',deck:'52 ورقة',hand:'13 ورقة',play:'عقد الكمبلكس يجمع عقوبات شيخ الكبة والبنات والديناري واللطوش، بينما يُلعب تركس كعقد مستقل.',legal:'اتباع النوع إلزامي في اللمّات، وتطبق كل عقوبات الكمبلكس على الأوراق المأخوذة.',win:'تُجمع نتائج جولات الكمبلكس وتركس، والأعلى نقاطاً يفوز.'),
  'hand': GameRuleInfo(icon:'🂮',players:'2–5',deck:'مجموعتان + جوكران',hand:'14 ثم 15 للبادي',play:'يسحب اللاعب من الرزمة أو المكشوف، ينزل مجموعات أو تسلسلات صحيحة، ثم يرمي ورقة لينهي دوره.',legal:'لا يجوز إنهاء الدور دون رمي، ويجب احترام حد النزول وترتيب المجموعات ومنع التركيبات غير الصحيحة.',win:'تنتهي الجولة عند إنهاء يد لاعب، وتُحسب قيمة الأوراق المتبقية على الآخرين عبر عدة جولات.'),
  'hand_partner': GameRuleInfo(icon:'🤝',players:'4',deck:'106 أوراق',hand:'14 ورقة',play:'هاند لفريقين، مع نزول وتركيب مشترك بين الشريكين بعد فتح الفريق.',legal:'سحب ثم نزول/تركيب ثم رمي، ولا يجوز استخدام مجموعات الخصم.',win:'تُجمع نقاط الفريق وتخصم الأوراق المتبقية، والفريق الأعلى بعد الجولات يفوز.'),
  'saudi_hand': GameRuleInfo(icon:'🇸🇦',players:'2–5',deck:'106 أوراق',hand:'14 ورقة',play:'سحب ثم نزول مجموعات أو تسلسلات وتركيب ثم رمي، وفق نمط الهاند السعودي.',legal:'يطبق المحرك شروط النزول والجوكر والبناكل ومنع الرميات غير القانونية.',win:'الفائز يفرغ يده أولاً وتُحتسب الأوراق المتبقية على الخاسرين.'),
  'banakil': GameRuleInfo(icon:'🎴',players:'2–4',deck:'مجموعتان + جوكران',hand:'14 ورقة',play:'تكوين مجموعات وتسلسلات باستخدام الأوراق الطبيعية والبناكل/الجوكر ضمن ضوابطها.',legal:'يجب السحب أولاً والرمي أخيراً، ولا تُقبل مجموعة تخالف الحد الأدنى أو نسبة الأوراق الطبيعية.',win:'ينهي اللاعب أو الفريق يده ويجمع نقاط النزول مقابل خصم الأوراق الباقية.'),
  'pinochle': GameRuleInfo(icon:'🂫',players:'2–4',deck:'48 ورقة',hand:'12 ورقة',play:'مزايدة ثم اختيار الحكم وإعلان المشاريع، وبعدها لعب اللمّات.',legal:'اتباع النوع والحكم وإجبار الفوز عند توفره وفق نمط البناكل الكلاسيكي.',win:'تجمع نقاط المشاريع واللمّات ويجب تحقيق العقد، وإلا تُخصم قيمة المزايدة.'),
  'baloot': GameRuleInfo(icon:'♠️',players:'4',deck:'32 ورقة',hand:'8 أوراق',play:'فريقان يختاران صن أو حكم ثم يلعبان ثماني لمّات مع مشاريع اختيارية.',legal:'اتباع النوع إلزامي، وفي الحكم تطبق قواعد القطع والعلو وترتيب الحكم الخاص.',win:'تُحسب قيم الأوراق والمشاريع، ويفوز الفريق الذي يصل للهدف قبل منافسه.'),
  'domino': GameRuleInfo(icon:'🁫',players:'2–4',deck:'28 قطعة دبل-ستة',hand:'7 قطع',play:'توضع قطعة على أحد طرفي السلسلة بشرط مطابقة الرقم المفتوح.',legal:'إن لم توجد قطعة صالحة يسحب أو يمر اللاعب حسب النمط. لا يمكن اللعب على طرف غير مطابق.',win:'يفوز من ينهي قطعه أولاً؛ وعند الإغلاق يفوز الأقل مجموعاً وتُحسب قطع الآخرين.'),
  'basra': GameRuleInfo(icon:'♦️',players:'2',deck:'52 ورقة',hand:'4 أوراق كل دفعة',play:'يلعب كل لاعب ورقة لالتقاط ورقة مماثلة أو مجموعة تساوي قيمتها.',legal:'الولد يلتقط أوراق الطاولة وفق القاعدة، ولسبعة الديناري تأثير خاص، والباصرة تحصل عند تنظيف الطاولة بشروطها.',win:'تُحسب أكثرية الأوراق والديناري والآسات والباصرات، والأعلى نقاطاً يفوز.'),
  'jackaroo': GameRuleInfo(icon:'🎲',players:'4',deck:'بطاقات حركة',hand:'4 أوراق',play:'فريقان يحركان أربعة أحجار لكل لاعب من البيت إلى المسار ثم الأمان.',legal:'كل بطاقة لها قدرة حركة محددة، ولا يسمح بالوقوف على حجر صديق أو تجاوز قيود منطقة الأمان.',win:'يفوز الفريق الذي يُدخل أحجاره الثمانية إلى الأمان أولاً.'),
  'backgammon': GameRuleInfo(icon:'🎲',players:'2',deck:'لوح + نردان',hand:'15 حجراً',play:'تحرك الأحجار حسب نتيجة النرد باتجاه منطقة الإخراج.',legal:'يجب إدخال الحجر المضروب أولاً، ولا يجوز النزول على نقطة مغلقة بقطعتين للخصم.',win:'يفوز من يخرج جميع أحجاره أولاً، مع احتساب جامون أو باكجامون عند انطباقه.'),
  'chess': GameRuleInfo(icon:'♛',players:'2',deck:'لوح 8×8',hand:'16 قطعة',play:'شطرنج قياسي بالتناوب الأبيض ثم الأسود.',legal:'لا يسمح بنقلة تترك الملك في كش، وتطبق التبييت والأخذ بالتجاوز والترقية بشروطها.',win:'كش مات أو استسلام، والتعادل بالتكرار أو الجمود أو القواعد المعتمدة.'),
  'solitaire_multiplayer': GameRuleInfo(icon:'🂠',players:'1–4',deck:'52 ورقة',hand:'7 أعمدة',play:'سباق لترتيب الأوراق في الأساسات حسب النوع من الآس إلى الملك.',legal:'تنقل الرزم بترتيب تنازلي وتناوب الألوان، وتُكشف الأوراق عند تحريرها.',win:'يفوز من يكمل الأساسات أولاً أو يحقق أعلى نتيجة ضمن الوقت.'),
};

class _RuleSection extends StatelessWidget {
  final String title;
  final IconData icon;
  final String text;
  const _RuleSection({required this.title, required this.icon, required this.text});
  @override
  Widget build(BuildContext context) => PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Icon(icon, color: Theme.of(context).colorScheme.primary),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(title, style: const TextStyle(fontWeight: FontWeight.w900)), const SizedBox(height: 5), Text(text, style: const TextStyle(color: Colors.white70, height: 1.65, fontSize: 11))])),
          ]),
        ),
      );
}

void showLeaderboard(BuildContext context) {
  final entries = const [('🥇','ياسر','18,420 XP'),('🥈','ليلى','17,980 XP'),('🥉','سامر','16,800 XP'),('4','أحمد','15,120 XP')];
  showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('لوحة الصدارة', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900)),
        const SizedBox(height: 10),
        ...entries.map((e) => Padding(padding: const EdgeInsets.only(bottom: 8), child: PremiumListTile(icon: e.$1, title: e.$2, subtitle: e.$3, action: const SizedBox(width: 1)))),
      ],
    ),
  );
}

class SocialHubPage extends StatefulWidget {
  final AppController controller;
  const SocialHubPage({super.key, required this.controller});

  @override
  State<SocialHubPage> createState() => _SocialHubPageState();
}

class _SocialHubPageState extends State<SocialHubPage> with SingleTickerProviderStateMixin {
  late final TabController tabs;
  final searchController = TextEditingController();
  List<LocalFriend> searchResults = const [];
  bool searching = false;

  @override
  void initState() {
    super.initState();
    tabs = TabController(length: 4, vsync: this);
  }

  @override
  void dispose() {
    tabs.dispose();
    searchController.dispose();
    super.dispose();
  }

  Future<void> search() async {
    final query = searchController.text.trim();
    if (query.isEmpty) return;
    setState(() => searching = true);
    if (widget.controller.serverConnected) {
      try {
        final data = await widget.controller.api.searchPlayers(query);
        final users = data['users'] is List ? data['users'] as List : const [];
        searchResults = users.map((item) {
          final map = item is Map ? Map<String, dynamic>.from(item) : <String, dynamic>{};
          return LocalFriend(
            int.tryParse(map['id']?.toString() ?? '') ?? 0,
            map['display_name']?.toString() ?? map['username']?.toString() ?? 'لاعب',
            map['username']?.toString() ?? '',
            online: map['online'] == true,
            activity: map['online'] == true ? 'متصل الآن' : 'غير متصل',
          );
        }).where((e) => e.id > 0).toList();
      } catch (e) {
        if (mounted) showToast(context, e.toString());
      }
    } else {
      final candidates = const [
        LocalFriend(6, 'ياسر', 'Yasser', online: true, activity: 'يلعب بلوت'),
        LocalFriend(7, 'رنا', 'Rana', online: false, activity: 'آخر ظهور أمس'),
        LocalFriend(8, 'كريم', 'Kareem', online: true, activity: 'في بطولة الأبطال'),
      ];
      searchResults = candidates.where((e) => e.name.contains(query) || e.username.toLowerCase().contains(query.toLowerCase())).toList();
    }
    if (mounted) setState(() => searching = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('مركز الأصدقاء', style: TextStyle(fontWeight: FontWeight.w900)),
        bottom: TabBar(
          controller: tabs,
          isScrollable: true,
          tabs: [
            Tab(text: 'الأصدقاء (${widget.controller.friends.length})'),
            Tab(text: 'الطلبات (${widget.controller.incomingRequests.length})'),
            const Tab(text: 'إضافة لاعب'),
            Tab(text: 'المحظورون (${widget.controller.blocked.length})'),
          ],
        ),
      ),
      body: TabBarView(
        controller: tabs,
        children: [
          _friendsTab(),
          _requestsTab(),
          _searchTab(),
          _blockedTab(),
        ],
      ),
    );
  }

  Widget _friendsTab() {
    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        PremiumPanel(
          child: Padding(
            padding: const EdgeInsets.all(13),
            child: Row(children: [
              const Icon(Icons.security_rounded, color: Colors.greenAccent),
              const SizedBox(width: 9),
              const Expanded(child: Text('الدردشة والتحويل متاحان للأصدقاء المقبولين فقط. عمولة تحويل التوكنز 10% تذهب للإدارة.', style: TextStyle(color: Colors.white60, fontSize: 10, height: 1.5))),
              Text(widget.controller.serverConnected ? 'LIVE' : 'LOCAL', style: TextStyle(color: widget.controller.serverConnected ? Colors.greenAccent : Colors.amber, fontWeight: FontWeight.w900, fontSize: 9)),
            ]),
          ),
        ),
        const SizedBox(height: 10),
        if (widget.controller.friends.isEmpty) const _EmptyState(icon: Icons.people_outline, title: 'لا يوجد أصدقاء بعد'),
        ...widget.controller.friends.map((friend) => Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: PremiumListTile(
            icon: friend.name.substring(0, 1),
            title: '${friend.name}  ${friend.online ? '●' : '○'}',
            subtitle: '@${friend.username} • ${friend.activity}',
            action: Row(mainAxisSize: MainAxisSize.min, children: [
              IconButton(onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => PrivateChatPage(controller: widget.controller, friend: friend))), icon: const Icon(Icons.chat_bubble_outline_rounded)),
              PopupMenuButton<String>(
                onSelected: (value) async {
                  if (value == 'transfer') _showTransfer(friend);
                  if (value == 'invite') showToast(context, 'تم إرسال دعوة لعبة إلى ${friend.name}.');
                  if (value == 'block') {
                    if (widget.controller.serverConnected) {
                      try { await widget.controller.api.blockUser(friend.id); } catch (_) {}
                    }
                    widget.controller.blockFriend(friend);
                    if (mounted) setState(() {});
                  }
                },
                itemBuilder: (_) => const [
                  PopupMenuItem(value: 'invite', child: Text('دعوة إلى لعبة')),
                  PopupMenuItem(value: 'transfer', child: Text('إرسال توكنز')),
                  PopupMenuItem(value: 'block', child: Text('حظر اللاعب')),
                ],
              ),
            ]),
          ),
        )),
      ],
    );
  }

  Widget _requestsTab() {
    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        const SectionTitle(title: 'طلبات واردة'),
        const SizedBox(height: 8),
        if (widget.controller.incomingRequests.isEmpty) const _EmptyState(icon: Icons.person_add_disabled_outlined, title: 'لا توجد طلبات واردة'),
        ...widget.controller.incomingRequests.map((friend) => Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: PremiumListTile(
            icon: friend.name.substring(0, 1),
            title: friend.name,
            subtitle: '@${friend.username} • ${friend.activity}',
            action: Row(mainAxisSize: MainAxisSize.min, children: [
              IconButton.filledTonal(onPressed: () { widget.controller.acceptFriend(friend); setState(() {}); }, icon: const Icon(Icons.check, color: Colors.greenAccent)),
              IconButton(onPressed: () { widget.controller.rejectFriend(friend); setState(() {}); }, icon: const Icon(Icons.close, color: Colors.redAccent)),
            ]),
          ),
        )),
        const SizedBox(height: 12),
        const SectionTitle(title: 'طلبات مرسلة'),
        const SizedBox(height: 8),
        if (widget.controller.outgoingRequests.isEmpty) const Text('لا توجد طلبات معلقة.', style: TextStyle(color: Colors.white54)),
        ...widget.controller.outgoingRequests.map((friend) => Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: PremiumListTile(icon: friend.name.substring(0, 1), title: friend.name, subtitle: 'بانتظار القبول', action: TextButton(onPressed: () { widget.controller.cancelFriendRequest(friend); setState(() {}); }, child: const Text('إلغاء'))),
        )),
      ],
    );
  }

  Widget _searchTab() {
    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        Row(children: [
          Expanded(child: TextField(controller: searchController, onSubmitted: (_) => search(), decoration: const InputDecoration(hintText: 'اسم اللاعب أو اسم المستخدم', prefixIcon: Icon(Icons.search)))),
          const SizedBox(width: 7),
          IconButton.filled(onPressed: searching ? null : search, icon: searching ? const SizedBox(width: 17, height: 17, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.search)),
        ]),
        const SizedBox(height: 12),
        if (searchResults.isEmpty) const _EmptyState(icon: Icons.manage_search, title: 'ابحث عن لاعب لإرسال طلب صداقة'),
        ...searchResults.map((friend) => Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: PremiumListTile(
            icon: friend.name.substring(0, 1),
            title: friend.name,
            subtitle: '@${friend.username} • ${friend.activity}',
            action: FilledButton.tonal(
              onPressed: () async {
                try {
                  if (widget.controller.serverConnected) await widget.controller.api.requestFriend(friend.id);
                  widget.controller.sendFriendRequest(friend);
                  if (mounted) showToast(context, 'تم إرسال طلب الصداقة إلى ${friend.name}.');
                } catch (e) {
                  if (mounted) showToast(context, e.toString());
                }
              },
              child: const Text('إضافة'),
            ),
          ),
        )),
      ],
    );
  }

  Widget _blockedTab() {
    return ListView(
      padding: const EdgeInsets.all(12),
      children: [
        if (widget.controller.blocked.isEmpty) const _EmptyState(icon: Icons.block_outlined, title: 'قائمة الحظر فارغة'),
        ...widget.controller.blocked.map((friend) => Padding(
          padding: const EdgeInsets.only(bottom: 8),
          child: PremiumListTile(
            icon: friend.name.substring(0, 1),
            title: friend.name,
            subtitle: '@${friend.username}',
            action: TextButton(onPressed: () async {
              if (widget.controller.serverConnected) {
                try { await widget.controller.api.unblockUser(friend.id); } catch (_) {}
              }
              widget.controller.unblockFriend(friend);
              if (mounted) setState(() {});
            }, child: const Text('إلغاء الحظر')),
          ),
        )),
      ],
    );
  }

  Future<void> _showTransfer(LocalFriend friend) async {
    final amountController = TextEditingController();
    await showDialog<void>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: Text('إرسال توكنز إلى ${friend.name}'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(controller: amountController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'المبلغ', prefixIcon: Icon(Icons.toll_rounded))),
          const SizedBox(height: 9),
          const Text('سيُخصم المبلغ من رصيدك بالإضافة إلى عمولة إدارة 10%. يصل المبلغ كاملاً إلى المستلم.', style: TextStyle(color: Colors.white60, fontSize: 10, height: 1.5)),
        ]),
        actions: [
          TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('إلغاء')),
          FilledButton(onPressed: () async {
            final amount = int.tryParse(amountController.text) ?? 0;
            String? result;
            if (widget.controller.serverConnected) {
              try {
                await widget.controller.api.transferTokens(friend.username, amount);
              } on ApiException catch (e) {
                result = e.message;
              }
            } else {
              result = await widget.controller.transferLocal(friend.username, amount);
            }
            if (!dialogContext.mounted) return;
            if (result == null) {
              Navigator.pop(dialogContext);
              showToast(context, 'تم التحويل وخصم عمولة الإدارة 10%.');
            } else {
              showToast(dialogContext, result);
            }
          }, child: const Text('تأكيد التحويل')),
        ],
      ),
    );
    amountController.dispose();
  }
}

class PrivateChatPage extends StatefulWidget {
  final AppController controller;
  final LocalFriend friend;
  const PrivateChatPage({super.key, required this.controller, required this.friend});

  @override
  State<PrivateChatPage> createState() => _PrivateChatPageState();
}

class _PrivateChatPageState extends State<PrivateChatPage> {
  final messageController = TextEditingController();
  bool loading = false;

  @override
  void dispose() {
    messageController.dispose();
    super.dispose();
  }

  Future<void> send() async {
    final body = messageController.text.trim();
    if (body.isEmpty) return;
    setState(() => loading = true);
    try {
      if (widget.controller.serverConnected) await widget.controller.api.sendMessage(widget.friend.id, body);
      widget.controller.sendLocalMessage(widget.friend, body);
      messageController.clear();
    } catch (e) {
      if (mounted) showToast(context, e.toString());
    }
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    final messages = widget.controller.privateChats[widget.friend.id] ?? const <ChatMessage>[];
    return Scaffold(
      appBar: AppBar(
        title: Row(children: [
          PremiumAvatar(text: widget.friend.name.substring(0, 1), size: 38),
          const SizedBox(width: 9),
          Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(widget.friend.name, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 14)), Text(widget.friend.online ? 'متصل الآن' : widget.friend.activity, style: TextStyle(color: widget.friend.online ? Colors.greenAccent : Colors.white54, fontSize: 8))]),
        ]),
        actions: [IconButton(onPressed: () => showToast(context, 'تم إرسال دعوة لعبة.'), icon: const Icon(Icons.sports_esports_outlined))],
      ),
      body: Column(children: [
        Expanded(
          child: ListView.builder(
            padding: const EdgeInsets.all(12),
            itemCount: messages.length,
            itemBuilder: (_, index) {
              final message = messages[index];
              return Align(
                alignment: message.mine ? Alignment.centerRight : Alignment.centerLeft,
                child: Container(
                  constraints: const BoxConstraints(maxWidth: 310),
                  margin: const EdgeInsets.only(bottom: 8),
                  padding: const EdgeInsets.fromLTRB(12, 9, 12, 7),
                  decoration: BoxDecoration(color: message.mine ? Theme.of(context).colorScheme.primary.withOpacity(.18) : Colors.white.withOpacity(.06), borderRadius: BorderRadius.only(topLeft: const Radius.circular(17), topRight: const Radius.circular(17), bottomLeft: Radius.circular(message.mine ? 17 : 4), bottomRight: Radius.circular(message.mine ? 4 : 17))),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(message.body, style: TextStyle(height: 1.45, color: message.mine ? colorFromHex(widget.controller.selectedChatColor) : Colors.white, fontWeight: message.mine ? FontWeight.w800 : FontWeight.w500)), const SizedBox(height: 3), Text(message.time, style: const TextStyle(color: Colors.white38, fontSize: 8))]),
                ),
              );
            },
          ),
        ),
        SafeArea(top: false, child: Padding(padding: const EdgeInsets.all(9), child: Row(children: [
          IconButton(onPressed: () => showToast(context, 'اختر إيموجي أو ملصقاً.'), icon: const Icon(Icons.emoji_emotions_outlined)),
          Expanded(child: TextField(controller: messageController, onSubmitted: (_) => send(), decoration: const InputDecoration(hintText: 'اكتب رسالة...'))),
          const SizedBox(width: 6),
          IconButton.filled(onPressed: loading ? null : send, icon: const Icon(Icons.send_rounded)),
        ]))),
      ]),
    );
  }
}

class _EmptyState extends StatelessWidget {
  final IconData icon;
  final String title;
  const _EmptyState({required this.icon, required this.title});
  @override
  Widget build(BuildContext context) => Padding(padding: const EdgeInsets.all(38), child: Column(children: [Icon(icon, size: 55, color: Colors.white24), const SizedBox(height: 10), Text(title, textAlign: TextAlign.center, style: const TextStyle(color: Colors.white54))]));
}

class AdminDashboardPage extends StatefulWidget {
  final AppController controller;
  const AdminDashboardPage({super.key, required this.controller});

  @override
  State<AdminDashboardPage> createState() => _AdminDashboardPageState();
}

class _AdminDashboardPageState extends State<AdminDashboardPage> with SingleTickerProviderStateMixin {
  late final TabController tabs;
  Map<String, dynamic>? serverData;
  bool loading = false;

  @override
  void initState() {
    super.initState();
    tabs = TabController(length: 6, vsync: this);
    _load();
  }

  @override
  void dispose() {
    tabs.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    if (!widget.controller.serverConnected) return;
    setState(() => loading = true);
    try {
      serverData = await widget.controller.api.adminDashboard();
    } catch (e) {
      if (mounted) showToast(context, e.toString());
    }
    if (mounted) setState(() => loading = false);
  }

  @override
  Widget build(BuildContext context) {
    if (!widget.controller.isAdmin) return const Scaffold(body: Center(child: Text('غير مصرح.')));
    return Scaffold(
      appBar: AppBar(
        title: const Text('لوحة إدارة Warqna', style: TextStyle(fontWeight: FontWeight.w900)),
        actions: [IconButton(onPressed: _load, icon: const Icon(Icons.refresh))],
        bottom: TabBar(controller: tabs, isScrollable: true, tabs: const [Tab(text:'نظرة عامة'),Tab(text:'الألعاب'),Tab(text:'المتجر'),Tab(text:'اللاعبون'),Tab(text:'مصمم بدون كود'),Tab(text:'النظام')]),
      ),
      body: loading ? const Center(child: CircularProgressIndicator()) : TabBarView(controller: tabs, children: [_overview(), _games(), _store(), _users(), _designer(), _system()]),
    );
  }

  Widget _overview() {
    final stats = serverData?['stats'] is Map ? Map<String, dynamic>.from(serverData!['stats'] as Map) : <String, dynamic>{};
    return GridView.count(
      padding: const EdgeInsets.all(12),
      crossAxisCount: MediaQuery.sizeOf(context).width > 700 ? 4 : 2,
      crossAxisSpacing: 9,
      mainAxisSpacing: 9,
      childAspectRatio: 1.35,
      children: [
        _AdminMetric(icon:'👥',label:'اللاعبون',value:stats['users']?.toString() ?? 'محلي'),
        _AdminMetric(icon:'🎮',label:'الغرف النشطة',value:stats['active_rooms']?.toString() ?? '0'),
        _AdminMetric(icon:'🏆',label:'المنافسات',value:stats['tournaments']?.toString() ?? '3'),
        _AdminMetric(icon:'🪙',label:'رصيد Adnan',value:formatNumber(widget.controller.coins)),
        _AdminMetric(icon:'🧠',label:'محركات فعالة',value:'${gamesCatalog.length}'),
        _AdminMetric(icon:'🛒',label:'عناصر المتجر',value:'${products.length}'),
      ],
    );
  }

  Widget _games() => ListView(
    padding: const EdgeInsets.all(12),
    children: [
      const _AdminInfo(text:'كل الألعاب الظاهرة تعمل بمحرك محلي داخل PWA عند غياب الخادم، وتنتقل تلقائياً إلى Laravel عند الاتصال للتحقق السلطوي من الحركات.'),
      const SizedBox(height: 10),
      ...gamesCatalog.map((game) => Padding(padding: const EdgeInsets.only(bottom: 8), child: PremiumListTile(icon:game.icon,title:L.t(widget.controller.localeCode,game.id),subtitle:'محرك فعال • لعب مجاني • تحقق من الحركات',action:Switch(value:true,onChanged:(_)=>showToast(context,'يتم حفظ التفعيل من API الإدارة عند الاتصال.'))))),
    ],
  );

  Widget _store() => AdminStoreStudioV151(controller: widget.controller);

  Widget _users() => ListView(
    padding: const EdgeInsets.all(12),
    children: [
      PremiumListTile(icon:'A',title:'Adnan',subtitle:'مدير • ${formatNumber(widget.controller.coins)} توكن',action:const Chip(label:Text('ADMIN'))),
      const SizedBox(height:8),
      ...widget.controller.friends.map((friend)=>Padding(padding:const EdgeInsets.only(bottom:8),child:PremiumListTile(icon:friend.name.substring(0,1),title:friend.name,subtitle:'@${friend.username} • ${friend.activity}',action:PopupMenuButton<String>(itemBuilder:(_)=>const [PopupMenuItem(value:'grant',child:Text('منح 200 توكن')),PopupMenuItem(value:'ban',child:Text('حظر الحساب'))],onSelected:(value)=>showToast(context,value=='grant'?'تم تسجيل عملية المنح.':'تم تحديث حالة الحساب.'))))),
    ],
  );

  Widget _designer() => ListView(
    padding: const EdgeInsets.all(12),
    children: [
      const _AdminInfo(text:'استوديو مرئي لإدارة الشكل العام بدون كتابة كود. المعاينة تُطبق فوراً على الأزرار والخطوط والحواف والطاولات.'),
      const SizedBox(height: 10),
      PremiumPanel(child: Padding(padding: const EdgeInsets.all(14), child: Column(children: [
        Container(height: 110, alignment: Alignment.center, decoration: BoxDecoration(gradient: LinearGradient(colors:[Theme.of(context).colorScheme.primary.withValues(alpha:.25), Colors.white.withValues(alpha:.03)]), borderRadius: BorderRadius.circular(widget.controller.uiRadius)), child: FilledButton.icon(onPressed:(){}, icon:const Icon(Icons.auto_awesome), label:const Text('معاينة مباشرة'))),
        const SizedBox(height: 10),
        Text('حجم الخط الحالي ${widget.controller.uiFontScale.toStringAsFixed(2)}×', style: TextStyle(fontSize: 16 * widget.controller.uiFontScale, fontWeight: FontWeight.w900)),
      ]))),
      const SizedBox(height: 12),
      Text('ارتفاع الزر: ${widget.controller.uiButtonHeight.round()} px'),
      Slider(min:38,max:64,divisions:26,value:widget.controller.uiButtonHeight,onChanged:(value){widget.controller.updateNoCodeDesign(buttonHeight:value);setState((){});}),
      Text('استدارة الحواف: ${widget.controller.uiRadius.round()} px'),
      Slider(min:8,max:32,divisions:24,value:widget.controller.uiRadius,onChanged:(value){widget.controller.updateNoCodeDesign(radius:value);setState((){});}),
      Text('مقياس الخط: ${widget.controller.uiFontScale.toStringAsFixed(2)}×'),
      Slider(min:.85,max:1.35,divisions:25,value:widget.controller.uiFontScale,onChanged:(value){widget.controller.updateNoCodeDesign(fontScale:value);setState((){});}),
      Text('حجم الدردشة: ${widget.controller.uiChatScale.toStringAsFixed(2)}×'),
      Slider(min:.8,max:1.35,divisions:22,value:widget.controller.uiChatScale,onChanged:(value){widget.controller.updateNoCodeDesign(chatScale:value);setState((){});}),
      const Text('اللون الرئيسي'),
      const SizedBox(height:6),
      Wrap(spacing:8,runSpacing:8,children:v151AccentColors.map((hex)=>InkWell(onTap:(){widget.controller.updateNoCodeDesign(accentHex:hex);setState((){});},child:Container(width:36,height:36,decoration:BoxDecoration(shape:BoxShape.circle,color:colorFromHex(hex),border:Border.all(color:widget.controller.uiAccentHex==hex?Colors.white:Colors.white24,width:widget.controller.uiAccentHex==hex?3:1))))).toList()),
      const SizedBox(height:8),
      SwitchListTile(value:widget.controller.tableAmbientEffects,onChanged:(value){widget.controller.updateNoCodeDesign(ambientEffects:value);setState((){});},title:const Text('مؤثرات الطاولة الهادئة')),
      ListTile(leading:const Icon(Icons.smart_toy_outlined),title:const Text('مستوى البوتات الافتراضي'),trailing:DropdownButton<String>(value:widget.controller.botDifficultyCode,items:const ['easy','normal','pro','master'].map((value)=>DropdownMenuItem(value:value,child:Text(value.toUpperCase()))).toList(),onChanged:(value){if(value!=null){widget.controller.changeBotDifficulty(value);setState((){});}})),
      const SizedBox(height: 8),
      FilledButton.tonalIcon(onPressed:(){widget.controller.updateNoCodeDesign(buttonHeight:48,radius:18,fontScale:1,chatScale:1,accentHex:'#ffcf67',ambientEffects:true);setState((){});},icon:const Icon(Icons.restore),label:const Text('استعادة التصميم الافتراضي')),
    ],
  );

  Widget _system() => ListView(
    padding: const EdgeInsets.all(12),
    children: [
      _AdminInfo(text:'حالة الاتصال: ${widget.controller.serverConnected ? 'متصل بـ Laravel API' : 'وضع محلي'}.\nAPI: ${widget.controller.api.baseUrl}'),
      const SizedBox(height:10),
      SwitchListTile(value:widget.controller.soundEnabled,onChanged:widget.controller.toggleSound,title:const Text('الأصوات'),subtitle:const Text('أصوات اللعب والإيموجي والتنبيهات')),
      ListTile(leading:const Icon(Icons.language),title:const Text('اللغة'),trailing:Text(widget.controller.localeCode.toUpperCase())),
      ListTile(leading:const Icon(Icons.palette_outlined),title:const Text('الثيم'),trailing:Text(widget.controller.themeCode)),
      const SizedBox(height:12),
      FilledButton.tonalIcon(onPressed:() async { await widget.controller.logout(); if (context.mounted) Navigator.pop(context); },icon:const Icon(Icons.logout),label:const Text('تسجيل الخروج')),
    ],
  );
}

class _AdminMetric extends StatelessWidget {
  final String icon,label,value;
  const _AdminMetric({required this.icon,required this.label,required this.value});
  @override
  Widget build(BuildContext context)=>PremiumPanel(child:Padding(padding:const EdgeInsets.all(14),child:Column(mainAxisAlignment:MainAxisAlignment.center,children:[Text(icon,style:const TextStyle(fontSize:29)),const SizedBox(height:6),FittedBox(child:Text(value,style:const TextStyle(fontSize:18,fontWeight:FontWeight.w900))),Text(label,style:const TextStyle(color:Colors.white54,fontSize:9))])));
}

class _AdminInfo extends StatelessWidget {
  final String text;
  const _AdminInfo({required this.text});
  @override
  Widget build(BuildContext context)=>PremiumPanel(child:Padding(padding:const EdgeInsets.all(14),child:Row(crossAxisAlignment:CrossAxisAlignment.start,children:[Icon(Icons.info_outline,color:Theme.of(context).colorScheme.primary),const SizedBox(width:9),Expanded(child:Text(text,style:const TextStyle(color:Colors.white70,height:1.55,fontSize:11)))])));
}


void showFriends(BuildContext context, AppController controller) {
  Navigator.push(context, MaterialPageRoute(builder: (_) => SocialHubPage(controller: controller)));
}

void showChat(BuildContext context) {
  final controller = TextEditingController();
  final messages = <String>['سامر: بالتوفيق للجميع 👋', 'أنت: مباراة جميلة، لنبدأ!'];
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    showDragHandle: true,
    builder: (sheetContext) => StatefulBuilder(
      builder: (context, setLocalState) => Padding(
        padding: EdgeInsets.only(bottom: MediaQuery.of(context).viewInsets.bottom),
        child: SizedBox(
          height: 470,
          child: Column(
            children: [
              const ListTile(title: Text('دردشة الغرفة', style: TextStyle(fontWeight: FontWeight.w900)), trailing: Text('● متصل', style: TextStyle(color: Colors.greenAccent, fontSize: 10))),
              Expanded(
                child: ListView.builder(
                  padding: const EdgeInsets.all(12),
                  itemCount: messages.length,
                  itemBuilder: (_, i) => Align(
                    alignment: messages[i].startsWith('أنت') ? Alignment.centerRight : Alignment.centerLeft,
                    child: Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Chip(label: Text(messages[i])),
                    ),
                  ),
                ),
              ),
              Padding(
                padding: const EdgeInsets.all(10),
                child: Row(
                  children: [
                    Expanded(child: TextField(controller: controller, decoration: const InputDecoration(hintText: 'اكتب رسالة...'))),
                    const SizedBox(width: 7),
                    IconButton.filled(
                      onPressed: () {
                        final text = controller.text.trim();
                        if (text.isEmpty) return;
                        setLocalState(() => messages.add('أنت: $text'));
                        controller.clear();
                      },
                      icon: const Icon(Icons.send),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    ),
  );
}

Future<void> showPremiumSheet(BuildContext context, {required Widget child}) async {
  await showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    showDragHandle: true,
    backgroundColor: Theme.of(context).colorScheme.surface,
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.vertical(top: Radius.circular(27))),
    builder: (context) => SafeArea(
      child: ConstrainedBox(
        constraints: BoxConstraints(maxHeight: MediaQuery.sizeOf(context).height * .9),
        child: SingleChildScrollView(padding: const EdgeInsets.fromLTRB(14, 0, 14, 18), child: child),
      ),
    ),
  );
}

void showToast(BuildContext context, String message) {
  ScaffoldMessenger.of(context)
    ..hideCurrentSnackBar()
    ..showSnackBar(SnackBar(content: Text(message), behavior: SnackBarBehavior.floating));
}

List<String> activeEmojiList(AppController controller) {
  return switch (controller.selectedEmojiPack) {
    'emoji_beginner_fun' => const ['😊', '😉', '😎', '🤩', '🥳'],
    'emoji_medium_react' => const ['😡', '😢', '😭', '😱', '🤔', '☕'],
    'emoji_pro_power' => const ['🔥', '⚡', '💎', '🏆', '👑', '🛡️'],
    'emoji_legend_big' => const ['🦁', '🐉', '🦅', '🌌', '💥', '🎆'],
    'emoji_animated_vip' => const ['😂', '🔥', '👑', '💎', '⚡', '🏆', '🎉'],
    'emoji_huge_reactions' => const ['😂', '👑', '🔥', '😡', '😭', '🤯'],
    _ => const ['👍', '😂', '😍', '😮', '😢', '😡'],
  };
}

Color colorFromHex(String value) {
  final cleaned = value.replaceAll('#', '').trim();
  final normalized = cleaned.length == 6 ? 'FF$cleaned' : cleaned;
  return Color(int.tryParse(normalized, radix: 16) ?? 0xffffffff);
}

String formatNumber(Object value) {
  final raw = value is BigInt
      ? value.toString()
      : value is num
          ? value.round().toString()
          : value.toString();
  final negative = raw.startsWith('-');
  final text = negative ? raw.substring(1) : raw;
  final buffer = StringBuffer(negative ? '-' : '');
  for (var i = 0; i < text.length; i++) {
    if (i > 0 && (text.length - i) % 3 == 0) buffer.write(',');
    buffer.write(text[i]);
  }
  return buffer.toString();
}
