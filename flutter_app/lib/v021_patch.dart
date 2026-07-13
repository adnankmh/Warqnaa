part of 'main.dart';

/// Patch V0.2.1: unified player-profile navigation and responsive card hands.
/// Any reusable player identity can call [openPlayerProfileV021].
Future<void> openPlayerProfileV021(
  BuildContext context,
  AppController controller, {
  int? userId,
  required String name,
  String? username,
  String? avatar,
  int level = 1,
  String countryCode = 'PS',
  bool online = false,
  String activity = 'لاعب ورقنا',
  int pashaDays = 0,
  int gamesPlayed = 0,
  int wins = 0,
  int xp = 0,
  int xpNext = 100,
  int roundPoints = 0,
  int tournamentPoints = 0,
  int clubPoints = 0,
  String nameColor = '#facc15',
  String? badge,
}) async {
  final normalizedName = name.trim().toLowerCase();
  final normalizedUsername = (username ?? '').trim().toLowerCase();
  final ownNames = <String>{
    controller.displayName.trim().toLowerCase(),
    controller.username.trim().toLowerCase(),
  };
  final isOwn = (userId != null && controller.currentUserId != null && userId == controller.currentUserId) ||
      ownNames.contains(normalizedName) ||
      (normalizedUsername.isNotEmpty && ownNames.contains(normalizedUsername));

  if (isOwn) {
    showProfile(context, controller);
    return;
  }

  await showPublicPlayerProfileV170(
    context,
    controller,
    LocalFriend(
      userId ?? 0,
      name.trim().isEmpty ? 'لاعب' : name.trim(),
      (username == null || username.trim().isEmpty) ? name.trim().replaceAll(' ', '_') : username.trim(),
      online: online,
      activity: activity,
      level: level,
      countryCode: countryCode,
      avatar: avatar,
      pashaDays: pashaDays,
      gamesPlayed: gamesPlayed,
      wins: wins,
      xp: xp,
      xpNext: xpNext,
      roundPoints: roundPoints,
      tournamentPoints: tournamentPoints,
      clubPoints: clubPoints,
      nameColor: nameColor,
      badge: badge,
    ),
  );
}

LocalFriend botProfileFriendV021(BotProfile profile, String locale) => LocalFriend(
      -profile.seed,
      profile.name(locale),
      'BOT_${profile.id}',
      online: true,
      activity: profile.style(locale),
      level: profile.level,
      countryCode: 'PS',
      avatar: '🤖',
      gamesPlayed: 2500 + profile.seed * 21,
      wins: 1450 + profile.seed * 12,
      xp: profile.level * 820,
      xpNext: xpNeededForLevelV175(profile.level),
      roundPoints: profile.level * 44,
      tournamentPoints: profile.level * 17,
      clubPoints: profile.level * 9,
      nameColor: colorToHex(profile.secondary),
      badge: profile.difficulty == BotDifficulty.master ? 'MASTER AI' : 'PRO AI',
    );

class PlayerIdentityTapV021 extends StatelessWidget {
  final AppController controller;
  final Widget child;
  final int? userId;
  final String name;
  final String? username;
  final String? avatar;
  final int level;
  final String countryCode;
  final bool online;
  final String activity;
  final int pashaDays;
  final int gamesPlayed;
  final int wins;
  final int xp;
  final int xpNext;
  final int roundPoints;
  final int tournamentPoints;
  final int clubPoints;
  final String nameColor;
  final String? badge;

  const PlayerIdentityTapV021({
    super.key,
    required this.controller,
    required this.child,
    required this.name,
    this.userId,
    this.username,
    this.avatar,
    this.level = 1,
    this.countryCode = 'PS',
    this.online = false,
    this.activity = 'لاعب ورقنا',
    this.pashaDays = 0,
    this.gamesPlayed = 0,
    this.wins = 0,
    this.xp = 0,
    this.xpNext = 100,
    this.roundPoints = 0,
    this.tournamentPoints = 0,
    this.clubPoints = 0,
    this.nameColor = '#facc15',
    this.badge,
  });

  @override
  Widget build(BuildContext context) => Semantics(
        button: true,
        label: 'فتح ملف $name',
        child: InkWell(
          borderRadius: BorderRadius.circular(18),
          onTap: () => openPlayerProfileV021(
            context,
            controller,
            userId: userId,
            name: name,
            username: username,
            avatar: avatar,
            level: level,
            countryCode: countryCode,
            online: online,
            activity: activity,
            pashaDays: pashaDays,
            gamesPlayed: gamesPlayed,
            wins: wins,
            xp: xp,
            xpNext: xpNext,
            roundPoints: roundPoints,
            tournamentPoints: tournamentPoints,
            clubPoints: clubPoints,
            nameColor: nameColor,
            badge: badge,
          ),
          child: child,
        ),
      );
}

/// Calculates a card width that keeps the complete hand visible without any
/// horizontal ListView or drag. Cards remain centered and shrink uniformly.
double visibleCardWidthV021(double availableWidth, int count, {double preferred = 56, double gap = 2}) {
  if (count <= 0) return preferred;
  final usable = math.max(120.0, availableWidth - 16 - (count - 1) * gap);
  return math.min(preferred, usable / count).clamp(20.0, preferred).toDouble();
}
