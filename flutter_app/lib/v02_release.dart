part of 'main.dart';

const int prizeBoxDailyLimitV02 = 4;
const List<String> prizeBoxKeysV02 = <String>[
  'crimson_lion',
  'emerald_eagle',
  'bronze_dragon',
  'obsidian',
  'royal_amethyst',
  'diamond_phoenix',
];

String ticketAssetV02(String value) => 'assets/images/v02/tickets/ticket_$value.png';
String prizeBoxAssetV02(String key, [String suffix = '']) =>
    'assets/images/v02/prize_boxes/$key${suffix.isEmpty ? '' : '_$suffix'}.png';

class V02Text {
  static const Map<String, Map<String, String>> _data = <String, Map<String, String>>{
    'ar': <String, String>{
      'title': 'صندوق الجوائز اليومي',
      'subtitle': 'أكمل مباراة لتحصل على صندوق حسب النتيجة • بحد أقصى 4 يومياً',
      'myBoxes': 'صناديقي',
      'showcase': 'مجموعة الصناديق الملكية',
      'open': 'فتح الصندوق',
      'opened': 'تم فتحه',
      'empty': 'لا توجد صناديق جاهزة الآن',
      'emptyHint': 'أكمل مباراة كاملة، وسيصل صندوق مناسب للنتيجة مباشرة إلى هذه الصفحة.',
      'today': 'حصيلة اليوم',
      'remaining': 'المتبقي اليوم',
      'reward': 'مكافأتك',
      'close': 'متابعة',
      'syncError': 'تعذر مزامنة صناديق الجوائز. يمكنك متابعة اللعب أوفلاين.',
      'openError': 'تعذر فتح الصندوق الآن.',
      'pasha': 'يوم باشا',
      'writingColor': 'لون كتابة لمدة يوم',
      'playerColor': 'لون لاعب لمدة يوم',
      'profileCover': 'غلاف شخصي لمدة 3 أيام',
      'tokens': 'توكنز مجانية',
      'ticket200': 'تذكرة مسابقة 200',
      'expires': 'تنتهي الصلاحية',
      'perWin': 'صندوق عن كل مباراة مكتملة',
      'dailyMax': 'الحد اليومي 4',
      'frontOpen': 'فتح أمامي سينمائي',
      'newBox': 'حصلت على صندوق جوائز جديد!',
    },
    'en': <String, String>{
      'title': 'Daily Prize Box',
      'subtitle': 'Complete a game to earn an outcome-based box • Up to 4 per day',
      'myBoxes': 'My boxes',
      'showcase': 'Royal box collection',
      'open': 'Open box',
      'opened': 'Opened',
      'empty': 'No prize boxes ready',
      'emptyHint': 'Complete a full game and the matching box will appear here immediately.',
      'today': 'Today',
      'remaining': 'Remaining today',
      'reward': 'Your reward',
      'close': 'Continue',
      'syncError': 'Prize boxes could not be synchronized. Offline play remains available.',
      'openError': 'The prize box could not be opened now.',
      'pasha': 'One Pasha day',
      'writingColor': 'Writing color for one day',
      'playerColor': 'Player color for one day',
      'profileCover': 'Profile cover for 3 days',
      'tokens': 'Free tokens',
      'ticket200': 'Competition ticket 200',
      'expires': 'Expires',
      'perWin': 'One box per completed game',
      'dailyMax': 'Daily limit 4',
      'frontOpen': 'Cinematic front opening',
      'newBox': 'You earned a new prize box!',
    },
    'de': <String, String>{
      'title': 'Tägliche Preisbox',
      'subtitle': 'Gewinne ein Spiel und erhalte eine Box • Maximal 4 pro Tag',
      'myBoxes': 'Meine Boxen', 'showcase': 'Königliche Boxsammlung', 'open': 'Box öffnen', 'opened': 'Geöffnet',
      'empty': 'Keine Preisbox bereit', 'emptyHint': 'Gewinne ein abgeschlossenes Spiel; die Box erscheint sofort hier.',
      'today': 'Heute', 'remaining': 'Heute verbleibend', 'reward': 'Deine Belohnung', 'close': 'Weiter',
      'syncError': 'Preisboxen konnten nicht synchronisiert werden. Offline-Spiel bleibt verfügbar.', 'openError': 'Die Box kann derzeit nicht geöffnet werden.',
      'pasha': 'Ein Pascha-Tag', 'writingColor': 'Schreibfarbe für einen Tag', 'playerColor': 'Spielerfarbe für einen Tag',
      'profileCover': 'Profilcover für 3 Tage', 'tokens': 'Kostenlose Token', 'ticket200': 'Wettbewerbsticket 200',
      'expires': 'Läuft ab', 'perWin': 'Eine Box pro Sieg', 'dailyMax': 'Tageslimit 4', 'frontOpen': 'Filmische Frontöffnung', 'newBox': 'Neue Preisbox erhalten!',
    },
    'tr': <String, String>{
      'title': 'Günlük Ödül Sandığı',
      'subtitle': 'Bir oyun kazan ve sandık al • Günde en fazla 4 sandık',
      'myBoxes': 'Sandıklarım', 'showcase': 'Kraliyet sandık koleksiyonu', 'open': 'Sandığı aç', 'opened': 'Açıldı',
      'empty': 'Hazır ödül sandığı yok', 'emptyHint': 'Tamamlanmış bir oyun kazan; sandık hemen burada görünür.',
      'today': 'Bugün', 'remaining': 'Bugün kalan', 'reward': 'Ödülün', 'close': 'Devam',
      'syncError': 'Ödül sandıkları eşitlenemedi. Çevrimdışı oyun kullanılabilir.', 'openError': 'Sandık şu anda açılamadı.',
      'pasha': 'Bir günlük Paşa', 'writingColor': 'Bir günlük yazı rengi', 'playerColor': 'Bir günlük oyuncu rengi',
      'profileCover': '3 günlük profil kapağı', 'tokens': 'Ücretsiz jeton', 'ticket200': '200 yarışma bileti',
      'expires': 'Bitiş', 'perWin': 'Her galibiyete bir sandık', 'dailyMax': 'Günlük sınır 4', 'frontOpen': 'Sinematik ön açılış', 'newBox': 'Yeni ödül sandığı kazandın!',
    },
    'fr': <String, String>{
      'title': 'Coffre quotidien de récompenses',
      'subtitle': 'Gagnez une partie pour recevoir un coffre • 4 maximum par jour',
      'myBoxes': 'Mes coffres', 'showcase': 'Collection royale', 'open': 'Ouvrir', 'opened': 'Ouvert',
      'empty': 'Aucun coffre disponible', 'emptyHint': 'Gagnez une partie terminée et le coffre apparaîtra immédiatement ici.',
      'today': "Aujourd'hui", 'remaining': "Restants aujourd'hui", 'reward': 'Votre récompense', 'close': 'Continuer',
      'syncError': 'Impossible de synchroniser les coffres. Le jeu hors ligne reste disponible.', 'openError': "Impossible d'ouvrir le coffre maintenant.",
      'pasha': 'Un jour Pacha', 'writingColor': "Couleur d'écriture pour un jour", 'playerColor': 'Couleur du joueur pour un jour',
      'profileCover': 'Couverture de profil pour 3 jours', 'tokens': 'Jetons gratuits', 'ticket200': 'Ticket de compétition 200',
      'expires': 'Expire', 'perWin': 'Un coffre par victoire', 'dailyMax': 'Limite quotidienne 4', 'frontOpen': 'Ouverture frontale cinématique', 'newBox': 'Nouveau coffre gagné !',
    },
    'es': <String, String>{
      'title': 'Cofre diario de premios',
      'subtitle': 'Gana una partida y recibe un cofre • Máximo 4 al día',
      'myBoxes': 'Mis cofres', 'showcase': 'Colección real', 'open': 'Abrir cofre', 'opened': 'Abierto',
      'empty': 'No hay cofres disponibles', 'emptyHint': 'Gana una partida completa y el cofre aparecerá aquí de inmediato.',
      'today': 'Hoy', 'remaining': 'Restantes hoy', 'reward': 'Tu recompensa', 'close': 'Continuar',
      'syncError': 'No se pudieron sincronizar los cofres. El juego sin conexión sigue disponible.', 'openError': 'No se pudo abrir el cofre ahora.',
      'pasha': 'Un día de Pasha', 'writingColor': 'Color de escritura por un día', 'playerColor': 'Color de jugador por un día',
      'profileCover': 'Portada de perfil por 3 días', 'tokens': 'Tokens gratis', 'ticket200': 'Entrada de competición 200',
      'expires': 'Caduca', 'perWin': 'Un cofre por victoria', 'dailyMax': 'Límite diario 4', 'frontOpen': 'Apertura frontal cinematográfica', 'newBox': '¡Has ganado un nuevo cofre!',
    },
  };

  static String t(String lang, String key) => _data[lang]?[key] ?? _data['en']![key] ?? key;
}

extension WarqnaV02Controller on AppController {
  String get _todayV02 => DateTime.now().toIso8601String().substring(0, 10);

  void normalizePrizeBoxesV02() {
    prizeBoxesDateV02 = _todayV02;
    prizeBoxesV02.removeWhere((box) {
      final rawDate = (box['awarded_date'] ?? box['created_at'])?.toString() ?? '';
      final date = rawDate.length >= 10 ? rawDate.substring(0, 10) : rawDate;
      final created = DateTime.tryParse(box['created_at']?.toString() ?? '');
      return date != _todayV02 && created != null && DateTime.now().difference(created).inDays > 30;
    });
  }

  List<Map<String, dynamic>> get prizeBoxesTodayV02 => prizeBoxesV02.where((box) {
        final raw = (box['awarded_date'] ?? box['created_at'])?.toString() ?? '';
        return raw.startsWith(_todayV02);
      }).toList(growable: false);

  List<Map<String, dynamic>> get unopenedPrizeBoxesV02 =>
      prizeBoxesTodayV02.where((box) => box['opened_at'] == null || box['opened_at'].toString().isEmpty).toList(growable: false);

  int get prizeBoxesEarnedTodayV02 => prizeBoxesTodayV02.length.clamp(0, prizeBoxDailyLimitV02).toInt();
  int get prizeBoxesRemainingTodayV02 => math.max(0, prizeBoxDailyLimitV02 - prizeBoxesEarnedTodayV02);

  void upsertPrizeBoxV02(Map<String, dynamic> raw) {
    final box = Map<String, dynamic>.from(raw);
    final id = box['id']?.toString();
    if (id == null || id.isEmpty) return;
    final index = prizeBoxesV02.indexWhere((item) => item['id']?.toString() == id);
    if (index < 0) {
      prizeBoxesV02.insert(0, box);
    } else {
      prizeBoxesV02[index] = <String, dynamic>{...prizeBoxesV02[index], ...box};
    }
    normalizePrizeBoxesV02();
  }

  void syncPrizeBoxesV02(dynamic raw) {
    if (raw == null) return;
    dynamic list = raw;
    if (raw is Map) list = raw['boxes'] ?? raw['items'];
    if (list is! List) return;
    for (final item in list) {
      if (item is Map) upsertPrizeBoxV02(Map<String, dynamic>.from(item));
    }
  }

  Future<void> refreshPrizeBoxesV02() async {
    normalizePrizeBoxesV02();
    if (!serverConnected || api.token == null || api.token!.isEmpty) {
      await _save();
      refreshUi();
      return;
    }
    final data = await api.prizeBoxesV02();
    syncPrizeBoxesV02(data);
    syncPackInventoryV176(data['inventory']);
    await _save();
    refreshUi();
  }

  void awardLocalPrizeBoxV02(String gameId, {bool won = true, String mode = 'normal'}) {
    normalizePrizeBoxesV02();
    if (prizeBoxesEarnedTodayV02 >= prizeBoxDailyLimitV02) return;
    final competition = <String>{'tournament','competition','sponsored','seasonal'}.contains(mode);
    final sequence = prizeBoxesEarnedTodayV02 + wins + gameId.hashCode.abs();
    final String boxKey;
    final String tier;
    if (competition && won) { boxKey = 'diamond_phoenix'; tier = 'legendary'; }
    else if (competition) { boxKey = 'royal_amethyst'; tier = 'epic'; }
    else if (won) { boxKey = <String>['emerald_eagle','bronze_dragon'][sequence % 2]; tier = 'strong'; }
    else { boxKey = <String>['crimson_lion','obsidian'][sequence % 2]; tier = 'simple'; }
    final now = DateTime.now();
    final box = <String, dynamic>{
      'id': 'local-${now.microsecondsSinceEpoch}',
      'box_key': boxKey,
      'source_type': competition ? 'offline_competition_complete' : 'offline_game_complete',
      'source_key': 'offline:$gameId:${won ? 'win' : 'loss'}:${now.microsecondsSinceEpoch}',
      'awarded_date': _todayV02,
      'created_at': now.toIso8601String(),
      'opened_at': null,
      'payload': <String,dynamic>{'game_key':gameId,'won':won,'mode':mode,'tier':tier,'version':'V0.3.1'},
    };
    upsertPrizeBoxV02(box);
    final title = switch (tier) { 'legendary'=>'صندوق أسطوري!', 'epic'=>'صندوق منافسة رائع!', 'strong'=>'صندوق فوز قوي!', _=>'صندوق مشاركة جديد!' };
    notices.insert(0, AppNotice('🎁', title, 'تمت إضافة الصندوق إلى صفحة صناديق الجوائز.'));
    unawaited(_save());
    refreshUi();
  }

  Map<String, dynamic> _localRewardV02([String boxKey = 'crimson_lion']) {
    final random = math.Random(DateTime.now().microsecondsSinceEpoch);
    final simple = <String>['tokens','writing_color','player_color'];
    final strong = <String>['tokens','writing_color','player_color','profile_cover','ticket'];
    final epic = <String>['ticket','profile_cover','pasha_day','tokens','writing_color','player_color'];
    final legendary = <String>['ticket','ticket','pasha_day','pasha_day','tokens','profile_cover'];
    final pool = switch (boxKey) {
      'diamond_phoenix' => legendary,
      'royal_amethyst' => epic,
      'emerald_eagle' || 'bronze_dragon' => strong,
      _ => simple,
    };
    final type = pool[random.nextInt(pool.length)];
    final legendaryBox = boxKey == 'diamond_phoenix';
    final simpleBox = boxKey == 'crimson_lion' || boxKey == 'obsidian';
    return switch (type) {
      'pasha_day' => <String, dynamic>{'type': type, 'value': '1', 'label_ar':'يوم باشا', 'duration_hours': 24, 'expires_at': DateTime.now().add(const Duration(days: 1)).toIso8601String()},
      'writing_color' => <String, dynamic>{'type': type, 'value': '#22d3ee', 'label_ar':'لون كتابة لمدة يوم', 'store_item_key': 'daily_pack_chat_cyan_24h_v176', 'duration_hours': 24, 'expires_at': DateTime.now().add(const Duration(days: 1)).toIso8601String()},
      'player_color' => <String, dynamic>{'type': type, 'value': '#facc15', 'label_ar':'لون لاعب لمدة يوم', 'store_item_key': 'daily_pack_name_gold_24h_v176', 'duration_hours': 24, 'expires_at': DateTime.now().add(const Duration(days: 1)).toIso8601String()},
      'profile_cover' => <String, dynamic>{'type': type, 'value': 'cover_v02_royal', 'label_ar':'غلاف ملكي لمدة 3 أيام', 'store_item_key': 'daily_prize_cover_v02', 'duration_hours': 72, 'expires_at': DateTime.now().add(const Duration(days: 3)).toIso8601String()},
      'tokens' => <String, dynamic>{'type': type, 'value': '${simpleBox ? (random.nextInt(3)+1)*50 : legendaryBox ? (random.nextInt(3)+2)*250 : (random.nextInt(8)+3)*50}', 'label_ar':'توكنز مجانية', 'duration_hours': 0},
      _ => <String, dynamic>{'type': 'ticket', 'value': legendaryBox ? '500' : '200', 'label_ar':'تذكرة مسابقة ${legendaryBox ? 500 : 200}', 'duration_hours': 0},
    };
  }

  void _applyPrizeRewardV02(Map<String, dynamic> reward, Map<String, dynamic> response) {
    final type = reward['type']?.toString() ?? '';
    final value = reward['value']?.toString() ?? '';
    final expiry = DateTime.tryParse(reward['expires_at']?.toString() ?? '')?.toLocal();
    if (type == 'pasha_day') {
      final profile = response['profile'];
      vipDays = profile is Map ? (int.tryParse(profile['pasha_days']?.toString() ?? '') ?? vipDays + 1) : vipDays + 1;
    } else if (type == 'writing_color') {
      applyDailyPackRewardV176(<String, dynamic>{...reward, 'type': 'chat_color', 'label_ar': 'لون كتابة لمدة يوم'}, response);
    } else if (type == 'player_color') {
      applyDailyPackRewardV176(<String, dynamic>{...reward, 'type': 'name_color', 'label_ar': 'لون لاعب لمدة يوم'}, response);
    } else if (type == 'profile_cover') {
      selectedCover = value.isEmpty ? 'cover_v02_royal' : value;
      owned.add('daily_prize_cover_v02');
      if (expiry != null) packInventoryExpiriesV176['daily_prize_cover_v02'] = expiry;
      dailyPackHistoryV176.insert(0, <String, dynamic>{
        ...reward,
        'product_id': 'daily_prize_cover_v02',
        'label_ar': 'غلاف شخصي لمدة 3 أيام',
        'opened_at': DateTime.now().toIso8601String(),
      });
    } else if (type == 'tokens' && !serverConnected) {
      coins += BigInt.from(int.tryParse(value) ?? 0);
    } else if (type == 'ticket' && !serverConnected) {
      final denomination = int.tryParse(value) ?? 200;
      competitionTickets[denomination] = (competitionTickets[denomination] ?? 0) + 1;
    }
    final wallet = response['wallet'];
    if (wallet is Map) coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
    final tickets = response['tickets'];
    if (tickets is Map) {
      competitionTickets
        ..clear()
        ..addAll(tickets.map((key, item) => MapEntry(int.tryParse(key.toString()) ?? 0, int.tryParse(item.toString()) ?? 0)));
    }
    syncPackInventoryV176(response['inventory']);
  }

  Future<Map<String, dynamic>?> openPrizeBoxV02(Map<String, dynamic> source) async {
    final box = Map<String, dynamic>.from(source);
    if (box['opened_at'] != null && box['opened_at'].toString().isNotEmpty) return null;
    final id = box['id']?.toString() ?? '';
    if (serverConnected && !id.startsWith('local-')) {
      final parsedId = int.tryParse(id);
      if (parsedId == null) return null;
      final data = await api.openPrizeBoxV02(parsedId);
      final openedRaw = data['box'];
      if (openedRaw is Map) upsertPrizeBoxV02(Map<String, dynamic>.from(openedRaw));
      final rewardRaw = data['reward'];
      if (rewardRaw is! Map) return null;
      final reward = Map<String, dynamic>.from(rewardRaw);
      _applyPrizeRewardV02(reward, data);
      await _save();
      refreshUi();
      return reward;
    }
    final reward = _localRewardV02(box['box_key']?.toString() ?? 'crimson_lion');
    box['opened_at'] = DateTime.now().toIso8601String();
    box['reward'] = reward;
    box['reward_type'] = reward['type'];
    box['reward_key'] = reward['value'];
    upsertPrizeBoxV02(box);
    _applyPrizeRewardV02(reward, const <String, dynamic>{});
    await _save();
    refreshUi();
    return reward;
  }
}

String prizeRewardLabelV02(String lang, Map<String, dynamic> reward) {
  final type = reward['type']?.toString() ?? '';
  return switch (type) {
    'pasha_day' => V02Text.t(lang, 'pasha'),
    'writing_color' || 'chat_color' => V02Text.t(lang, 'writingColor'),
    'player_color' || 'name_color' => V02Text.t(lang, 'playerColor'),
    'profile_cover' => V02Text.t(lang, 'profileCover'),
    'tokens' => '${reward['value'] ?? 0} ${V02Text.t(lang, 'tokens')}',
    'ticket' => '${lang == 'ar' ? 'تذكرة مسابقة' : 'Competition ticket'} ${reward['value'] ?? 200}',
    _ => V02Text.t(lang, 'reward'),
  };
}

String prizeRewardAssetV02(Map<String, dynamic> reward) {
  final type = reward['type']?.toString() ?? '';
  return switch (type) {
    'pasha_day' => 'assets/images/v02/rewards/pasha_day.png',
    'writing_color' || 'chat_color' => 'assets/images/v02/rewards/writing_color.png',
    'player_color' || 'name_color' => 'assets/images/v02/rewards/player_color.png',
    'profile_cover' => 'assets/images/v02/rewards/profile_cover.png',
    'tokens' => 'assets/images/v02/rewards/tokens.png',
    'ticket' => ticketAssetV02(reward['value']?.toString() ?? '200'),
    _ => 'assets/images/v02/rewards/tokens.png',
  };
}

class PrizeBoxesHomeCardV02 extends StatelessWidget {
  final AppController controller;
  final VoidCallback onOpen;
  const PrizeBoxesHomeCardV02({super.key, required this.controller, required this.onOpen});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    final unopened = controller.unopenedPrizeBoxesV02.length;
    return InkWell(
      onTap: onOpen,
      borderRadius: BorderRadius.circular(24),
      child: PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Row(children: [
            SizedBox(width: 92, height: 76, child: Image.asset(prizeBoxAssetV02(prizeBoxKeysV02[controller.prizeBoxesEarnedTodayV02 % prizeBoxKeysV02.length]), fit: BoxFit.contain)),
            const SizedBox(width: 10),
            Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(V02Text.t(lang, 'title'), style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
              const SizedBox(height: 4),
              Text(V02Text.t(lang, 'subtitle'), style: TextStyle(color: Theme.of(context).colorScheme.onSurfaceVariant, fontSize: 10, height: 1.45)),
              const SizedBox(height: 7),
              Wrap(spacing: 6, runSpacing: 6, children: [
                Chip(label: Text('${V02Text.t(lang, 'today')}: ${controller.prizeBoxesEarnedTodayV02}/$prizeBoxDailyLimitV02')),
                if (unopened > 0) Chip(avatar: const Icon(Icons.lock_open_rounded, size: 16), label: Text('$unopened')),
              ]),
            ])),
            const Icon(Icons.chevron_right_rounded),
          ]),
        ),
      ),
    );
  }
}

class PrizeBoxesPageV02 extends StatefulWidget {
  final AppController controller;
  const PrizeBoxesPageV02({super.key, required this.controller});
  @override
  State<PrizeBoxesPageV02> createState() => _PrizeBoxesPageV02State();
}

class _PrizeBoxesPageV02State extends State<PrizeBoxesPageV02> {
  bool loading = false;
  String? error;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _refresh());
  }

  Future<void> _refresh() async {
    if (loading) return;
    setState(() { loading = true; error = null; });
    try {
      await widget.controller.refreshPrizeBoxesV02();
    } catch (_) {
      if (mounted) error = V02Text.t(widget.controller.localeCode, 'syncError');
    }
    if (mounted) setState(() => loading = false);
  }

  Future<void> _open(Map<String, dynamic> box) async {
    if (loading) return;
    setState(() { loading = true; error = null; });
    try {
      final reward = await widget.controller.openPrizeBoxV02(box);
      if (!mounted) return;
      if (reward == null) {
        setState(() { loading = false; error = V02Text.t(widget.controller.localeCode, 'openError'); });
        return;
      }
      setState(() => loading = false);
      await showDialog<void>(
        context: context,
        barrierDismissible: false,
        builder: (_) => PrizeBoxOpeningDialogV02(
          locale: widget.controller.localeCode,
          boxKey: box['box_key']?.toString() ?? prizeBoxKeysV02.first,
          reward: reward,
        ),
      );
    } catch (_) {
      if (mounted) setState(() { loading = false; error = V02Text.t(widget.controller.localeCode, 'openError'); });
    }
  }

  @override
  Widget build(BuildContext context) {
    final controller = widget.controller;
    final lang = controller.localeCode;
    final boxes = controller.prizeBoxesTodayV02;
    return Scaffold(
      appBar: AppBar(
        title: Text(V02Text.t(lang, 'title')),
        actions: [IconButton(onPressed: loading ? null : _refresh, icon: const Icon(Icons.refresh_rounded))],
      ),
      body: RefreshIndicator(
        onRefresh: _refresh,
        child: ListView(
          padding: const EdgeInsets.all(13),
          children: [
            PremiumPanel(child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                Text(V02Text.t(lang, 'subtitle'), textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
                const SizedBox(height: 10),
                ClipRRect(borderRadius: BorderRadius.circular(30), child: LinearProgressIndicator(value: controller.prizeBoxesEarnedTodayV02 / prizeBoxDailyLimitV02, minHeight: 13)),
                const SizedBox(height: 8),
                Row(children: [
                  Expanded(child: _PrizeStatV02(icon: Icons.card_giftcard_rounded, label: V02Text.t(lang, 'today'), value: '${controller.prizeBoxesEarnedTodayV02}/$prizeBoxDailyLimitV02')),
                  const SizedBox(width: 8),
                  Expanded(child: _PrizeStatV02(icon: Icons.hourglass_bottom_rounded, label: V02Text.t(lang, 'remaining'), value: '${controller.prizeBoxesRemainingTodayV02}')),
                ]),
              ]),
            )),
            const SizedBox(height: 13),
            SectionTitle(title: V02Text.t(lang, 'showcase'), action: V02Text.t(lang, 'frontOpen')),
            const SizedBox(height: 9),
            SizedBox(
              height: 132,
              child: ListView.separated(
                scrollDirection: Axis.horizontal,
                itemCount: prizeBoxKeysV02.length,
                separatorBuilder: (_, __) => const SizedBox(width: 8),
                itemBuilder: (_, index) => Container(
                  width: 160,
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withValues(alpha: .35), borderRadius: BorderRadius.circular(20), border: Border.all(color: Theme.of(context).colorScheme.outlineVariant)),
                  child: Image.asset(prizeBoxAssetV02(prizeBoxKeysV02[index]), fit: BoxFit.contain, filterQuality: FilterQuality.high),
                ),
              ),
            ),
            const SizedBox(height: 14),
            SectionTitle(title: V02Text.t(lang, 'myBoxes'), action: '${controller.unopenedPrizeBoxesV02.length}'),
            const SizedBox(height: 9),
            if (loading) const Padding(padding: EdgeInsets.all(20), child: Center(child: CircularProgressIndicator())),
            if (error != null) Padding(padding: const EdgeInsets.only(bottom: 10), child: Text(error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.orangeAccent))),
            if (!loading && boxes.isEmpty)
              PremiumPanel(child: Padding(
                padding: const EdgeInsets.all(24),
                child: Column(children: [
                  const Icon(Icons.redeem_rounded, size: 54, color: Colors.amber),
                  const SizedBox(height: 9),
                  Text(V02Text.t(lang, 'empty'), style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                  const SizedBox(height: 5),
                  Text(V02Text.t(lang, 'emptyHint'), textAlign: TextAlign.center, style: TextStyle(color: Theme.of(context).colorScheme.onSurfaceVariant, height: 1.5)),
                ]),
              )),
            ...boxes.map((box) => Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: PrizeBoxCardV02(controller: controller, box: box, onOpen: () => _open(box)),
            )),
            const SizedBox(height: 30),
          ],
        ),
      ),
    );
  }
}

class _PrizeStatV02 extends StatelessWidget {
  final IconData icon;
  final String label;
  final String value;
  const _PrizeStatV02({required this.icon, required this.label, required this.value});
  @override
  Widget build(BuildContext context) => Container(
        padding: const EdgeInsets.all(10),
        decoration: BoxDecoration(color: Theme.of(context).colorScheme.surfaceContainerHighest.withValues(alpha: .42), borderRadius: BorderRadius.circular(16)),
        child: Row(children: [Icon(icon, color: Colors.amber), const SizedBox(width: 8), Expanded(child: Text(label, style: TextStyle(color: Theme.of(context).colorScheme.onSurfaceVariant, fontSize: 10))), Text(value, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 16))]),
      );
}

class PrizeBoxCardV02 extends StatelessWidget {
  final AppController controller;
  final Map<String, dynamic> box;
  final VoidCallback onOpen;
  const PrizeBoxCardV02({super.key, required this.controller, required this.box, required this.onOpen});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    final opened = box['opened_at'] != null && box['opened_at'].toString().isNotEmpty;
    final boxKey = box['box_key']?.toString() ?? prizeBoxKeysV02.first;
    final rewardRaw = box['reward'] ?? box['payload'];
    final reward = rewardRaw is Map ? Map<String, dynamic>.from(rewardRaw) : <String, dynamic>{};
    return PremiumPanel(child: Padding(
      padding: const EdgeInsets.all(12),
      child: Row(children: [
        SizedBox(width: 132, height: 105, child: Image.asset(prizeBoxAssetV02(boxKey), fit: BoxFit.contain, filterQuality: FilterQuality.high)),
        const SizedBox(width: 10),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Text(V02Text.t(lang, 'title'), style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
          const SizedBox(height: 4),
          Text(opened && reward.isNotEmpty ? prizeRewardLabelV02(lang, reward) : V02Text.t(lang, 'perWin'), style: TextStyle(color: opened ? const Color(0xff16a34a) : Theme.of(context).colorScheme.onSurfaceVariant, fontSize: 11)),
          const SizedBox(height: 9),
          FilledButton.icon(
            onPressed: opened ? null : onOpen,
            icon: Icon(opened ? Icons.verified_rounded : Icons.lock_open_rounded),
            label: Text(opened ? V02Text.t(lang, 'opened') : V02Text.t(lang, 'open')),
          ),
        ])),
      ]),
    ));
  }
}

class PrizeBoxOpeningDialogV02 extends StatefulWidget {
  final String locale;
  final String boxKey;
  final Map<String, dynamic> reward;
  const PrizeBoxOpeningDialogV02({super.key, required this.locale, required this.boxKey, required this.reward});
  @override
  State<PrizeBoxOpeningDialogV02> createState() => _PrizeBoxOpeningDialogV02State();
}

class _PrizeBoxOpeningDialogV02State extends State<PrizeBoxOpeningDialogV02> with SingleTickerProviderStateMixin {
  late final AnimationController animation;
  bool completed = false;

  @override
  void initState() {
    super.initState();
    animation = AnimationController(vsync: this, duration: const Duration(seconds: 5))
      ..addStatusListener((status) {
        if (status == AnimationStatus.completed && mounted) setState(() => completed = true);
      })
      ..forward();
    AppSounds.fire('reward');
  }

  @override
  void dispose() {
    animation.dispose();
    super.dispose();
  }

  double _interval(double value, double begin, double end) => ((value - begin) / (end - begin)).clamp(0.0, 1.0).toDouble();

  @override
  Widget build(BuildContext context) {
    final label = prizeRewardLabelV02(widget.locale, widget.reward);
    return Dialog.fullscreen(
      backgroundColor: const Color(0xff040812),
      child: SafeArea(
        child: AnimatedBuilder(
          animation: animation,
          builder: (context, _) {
            final p = animation.value;
            final shake = p < .28 ? math.sin(p * 85) * (1 - p / .28) * 8 : 0.0;
            final lid = Curves.easeOutBack.transform(_interval(p, .30, .66));
            final panel = Curves.easeInOutCubic.transform(_interval(p, .34, .70));
            final reveal = Curves.elasticOut.transform(_interval(p, .58, .92));
            final glow = .25 + .75 * math.sin(math.min(1, p / .62) * math.pi);
            return Stack(children: [
              Positioned.fill(child: DecoratedBox(decoration: BoxDecoration(
                gradient: RadialGradient(center: const Alignment(0, -.08), radius: 1.05, colors: [Colors.amber.withValues(alpha: .18 * glow), const Color(0xff07101f), const Color(0xff02040a)]),
              ))),
              for (var i = 0; i < 16; i++)
                Positioned(
                  left: MediaQuery.sizeOf(context).width * ((i * 37) % 100) / 100,
                  top: MediaQuery.sizeOf(context).height * (.15 + ((i * 19) % 65) / 100),
                  child: Opacity(opacity: _interval(p, .38, .82) * (1 - _interval(p, .82, 1)), child: Transform.scale(scale: .5 + (i % 4) * .25, child: const Icon(Icons.auto_awesome, color: Colors.amberAccent, size: 18))),
                ),
              Center(child: Transform.translate(
                offset: Offset(shake, 40),
                child: SizedBox(
                  width: math.min(560.0, MediaQuery.sizeOf(context).width * .94),
                  height: math.min(590.0, MediaQuery.sizeOf(context).height * .72),
                  child: Stack(alignment: Alignment.center, children: [
                    Positioned(bottom: 45, left: 0, right: 0, child: Image.asset(prizeBoxAssetV02(widget.boxKey, 'body'), height: 390, fit: BoxFit.contain)),
                    Positioned(
                      bottom: 45 - panel * 88,
                      left: 0,
                      right: 0,
                      child: Transform(
                        alignment: Alignment.bottomCenter,
                        transform: Matrix4.identity()..setEntry(3, 2, .0012)..rotateX(panel * 1.18),
                        child: Opacity(opacity: 1 - reveal * .35, child: Image.asset(prizeBoxAssetV02(widget.boxKey, 'front_panel'), height: 390, fit: BoxFit.contain)),
                      ),
                    ),
                    Positioned(
                      bottom: 45 + lid * 175,
                      left: 0,
                      right: 0,
                      child: Transform(
                        alignment: Alignment.bottomCenter,
                        transform: Matrix4.identity()..setEntry(3, 2, .001)..rotateX(-lid * .62),
                        child: Image.asset(prizeBoxAssetV02(widget.boxKey, 'lid'), height: 390, fit: BoxFit.contain),
                      ),
                    ),
                    Positioned(
                      bottom: 110 + reveal * 185,
                      child: Opacity(
                        opacity: _interval(p, .58, .78),
                        child: Transform.scale(
                          scale: .22 + .78 * reveal,
                          child: Container(
                            width: 220,
                            height: 220,
                            decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.white.withValues(alpha: .05), boxShadow: [BoxShadow(color: Colors.amberAccent.withValues(alpha: .58 * reveal), blurRadius: 70, spreadRadius: 18)]),
                            padding: const EdgeInsets.all(18),
                            child: Image.asset(prizeRewardAssetV02(widget.reward), fit: BoxFit.contain, filterQuality: FilterQuality.high),
                          ),
                        ),
                      ),
                    ),
                  ]),
                ),
              )),
              Positioned(
                left: 22,
                right: 22,
                bottom: 24,
                child: AnimatedOpacity(
                  duration: const Duration(milliseconds: 350),
                  opacity: p > .78 ? 1 : 0,
                  child: Column(children: [
                    Text(V02Text.t(widget.locale, 'reward'), style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900, fontSize: 14)),
                    const SizedBox(height: 4),
                    Text(label, textAlign: TextAlign.center, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 25)),
                    const SizedBox(height: 12),
                    FilledButton.icon(
                      onPressed: completed ? () => Navigator.of(context).pop() : null,
                      icon: const Icon(Icons.check_circle_outline_rounded),
                      label: Text(V02Text.t(widget.locale, 'close')),
                      style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(52)),
                    ),
                  ]),
                ),
              ),
            ]);
          },
        ),
      ),
    );
  }
}
