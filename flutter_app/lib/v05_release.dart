part of 'main.dart';

const bool warqnaProductionModeV05 = bool.fromEnvironment('WARQNA_PRODUCTION_MODE', defaultValue: false);
const String warqnaVersionV05 = '0.5.0';
const int warqnaBuildV05 = 500;

class V05Text {
  static const Map<String, Map<String, String>> data = <String, Map<String, String>>{
    'ar': <String, String>{
      'favoriteGames': 'ألعابي في الرئيسية',
      'chooseFavoriteGames': 'اختر من لعبة واحدة إلى 4 ألعاب',
      'save': 'حفظ الاختيار',
      'openRoomsHub': 'الغرف المفتوحة',
      'openRoomsSubtitle': 'غرف حقيقية ومفتوحة لكل لعبة مع صور اللاعبين قبل الدخول',
      'localRoom': 'غرفة فورية محلية',
      'storePurchaseSuccess': 'تم الشراء والتفعيل بنجاح.',
      'storePurchaseOffline': 'تم الشراء محليًا وإضافة العنصر إلى مقتنياتك.',
      'storePurchaseInsufficient': 'رصيد التوكنز غير كافٍ لهذا العنصر.',
      'storePurchaseUnavailable': 'تعذر الوصول إلى المتجر الآن. حاول مجددًا.',
      'challengeRoad': 'طريق التحدي',
      'challengeSubtitle': 'اختر لعبة وأكمل المراحل قبل نفاد المحاولات الخمس',
      'attempts': 'المحاولات',
      'stage': 'المرحلة',
      'startChallenge': 'بدء التحدي',
      'continueChallenge': 'متابعة التحدي',
      'levelRewards': 'مكافآت المستويات',
      'globalSettings': 'الإعدادات السريعة',
      'theme': 'الثيم',
      'language': 'اللغة',
      'fontSize': 'حجم الخط',
      'dark': 'غامق',
      'light': 'فاتح',
      'blue': 'أزرق',
      'sky': 'سماوي',
      'green': 'أخضر',
      'greenLight': 'أخضر فاتح',
      'gold': 'ذهبي',
      'purple': 'بنفسجي',
      'pinkLight': 'وردي فاتح',
      'redFezOnly': 'طربوش الباشا الأحمر الأصلي',
      'adminDesigner': 'المصمم الشامل بدون كود',
      'livePlayers': 'اللاعبون داخل الغرفة',
      'antiCheat': 'حماية اللعب ومكافحة الغش مفعلة',
      'fairDeal': 'توزيع عادل متوازن القوة',
      'autoExit': 'الخروج التلقائي بعد 3 أدوار غياب',
    },
    'en': <String, String>{
      'favoriteGames': 'Home games', 'chooseFavoriteGames': 'Choose 1 to 4 games', 'save': 'Save selection',
      'openRoomsHub': 'Open rooms', 'openRoomsSubtitle': 'Live open rooms for every game with player avatars before joining', 'localRoom': 'Instant local room',
      'storePurchaseSuccess': 'Purchase completed and activated.', 'storePurchaseOffline': 'Purchased locally and added to your inventory.',
      'storePurchaseInsufficient': 'You do not have enough tokens for this item.', 'storePurchaseUnavailable': 'The store is unavailable now. Please try again.',
      'challengeRoad': 'Challenge road', 'challengeSubtitle': 'Choose a game and clear the stages before your five attempts run out',
      'attempts': 'Attempts', 'stage': 'Stage', 'startChallenge': 'Start challenge', 'continueChallenge': 'Continue challenge',
      'levelRewards': 'Level rewards', 'globalSettings': 'Quick settings', 'theme': 'Theme', 'language': 'Language', 'fontSize': 'Font size',
      'dark': 'Dark', 'light': 'Light', 'blue': 'Blue', 'sky': 'Sky blue', 'green': 'Green', 'greenLight': 'Light green', 'gold': 'Gold',
      'purple': 'Purple', 'pinkLight': 'Light pink', 'redFezOnly': 'Original red Pasha fez', 'adminDesigner': 'No-code universal designer',
      'livePlayers': 'Players in room', 'antiCheat': 'Anti-cheat protection enabled', 'fairDeal': 'Fair strength-balanced deal', 'autoExit': 'Auto-exit after 3 missed turns',
    },
    'de': <String, String>{
      'favoriteGames': 'Startseiten-Spiele', 'chooseFavoriteGames': '1 bis 4 Spiele auswählen', 'save': 'Auswahl speichern',
      'openRoomsHub': 'Offene Räume', 'openRoomsSubtitle': 'Offene Räume mit Spielerbildern vor dem Beitritt', 'localRoom': 'Sofortiger lokaler Raum',
      'storePurchaseSuccess': 'Kauf abgeschlossen und aktiviert.', 'storePurchaseOffline': 'Lokal gekauft und zum Inventar hinzugefügt.',
      'storePurchaseInsufficient': 'Nicht genügend Token.', 'storePurchaseUnavailable': 'Shop derzeit nicht erreichbar.',
      'challengeRoad': 'Herausforderungsweg', 'challengeSubtitle': 'Spiel wählen und Stufen mit fünf Versuchen abschließen', 'attempts': 'Versuche', 'stage': 'Stufe',
      'startChallenge': 'Herausforderung starten', 'continueChallenge': 'Fortsetzen', 'levelRewards': 'Level-Belohnungen', 'globalSettings': 'Schnelleinstellungen',
      'theme': 'Design', 'language': 'Sprache', 'fontSize': 'Schriftgröße', 'dark': 'Dunkel', 'light': 'Hell', 'blue': 'Blau', 'sky': 'Himmelblau',
      'green': 'Grün', 'greenLight': 'Hellgrün', 'gold': 'Gold', 'purple': 'Violett', 'pinkLight': 'Hellrosa', 'redFezOnly': 'Original roter Pascha-Fes',
      'adminDesigner': 'Universeller No-Code-Designer', 'livePlayers': 'Spieler im Raum', 'antiCheat': 'Anti-Cheat aktiv', 'fairDeal': 'Fair verteilte Kartenstärke', 'autoExit': 'Auto-Ausstieg nach 3 verpassten Zügen',
    },
    'tr': <String, String>{
      'favoriteGames': 'Ana sayfa oyunları', 'chooseFavoriteGames': '1 ile 4 oyun seç', 'save': 'Seçimi kaydet',
      'openRoomsHub': 'Açık odalar', 'openRoomsSubtitle': 'Katılmadan önce oyuncu görselleriyle canlı açık odalar', 'localRoom': 'Anında yerel oda',
      'storePurchaseSuccess': 'Satın alma tamamlandı ve etkinleştirildi.', 'storePurchaseOffline': 'Yerel satın alındı ve envantere eklendi.',
      'storePurchaseInsufficient': 'Yeterli jeton yok.', 'storePurchaseUnavailable': 'Mağazaya şu anda ulaşılamıyor.',
      'challengeRoad': 'Meydan okuma yolu', 'challengeSubtitle': 'Bir oyun seç ve beş hakkın bitmeden aşamaları tamamla', 'attempts': 'Haklar', 'stage': 'Aşama',
      'startChallenge': 'Başlat', 'continueChallenge': 'Devam et', 'levelRewards': 'Seviye ödülleri', 'globalSettings': 'Hızlı ayarlar',
      'theme': 'Tema', 'language': 'Dil', 'fontSize': 'Yazı boyutu', 'dark': 'Koyu', 'light': 'Açık', 'blue': 'Mavi', 'sky': 'Gökyüzü mavisi',
      'green': 'Yeşil', 'greenLight': 'Açık yeşil', 'gold': 'Altın', 'purple': 'Mor', 'pinkLight': 'Açık pembe', 'redFezOnly': 'Orijinal kırmızı Paşa fesi',
      'adminDesigner': 'Kodsuz evrensel tasarımcı', 'livePlayers': 'Odadaki oyuncular', 'antiCheat': 'Hile koruması açık', 'fairDeal': 'Adil güçlü kart dağıtımı', 'autoExit': '3 kaçırılan turdan sonra otomatik çıkış',
    },
    'fr': <String, String>{
      'favoriteGames': "Jeux d'accueil", 'chooseFavoriteGames': 'Choisissez de 1 à 4 jeux', 'save': 'Enregistrer',
      'openRoomsHub': 'Salles ouvertes', 'openRoomsSubtitle': 'Salles en direct avec avatars avant de rejoindre', 'localRoom': 'Salle locale instantanée',
      'storePurchaseSuccess': 'Achat effectué et activé.', 'storePurchaseOffline': "Acheté localement et ajouté à l'inventaire.",
      'storePurchaseInsufficient': 'Jetons insuffisants.', 'storePurchaseUnavailable': 'Boutique indisponible pour le moment.',
      'challengeRoad': 'Parcours de défi', 'challengeSubtitle': 'Choisissez un jeu et terminez les étapes avec cinq essais', 'attempts': 'Essais', 'stage': 'Étape',
      'startChallenge': 'Démarrer', 'continueChallenge': 'Continuer', 'levelRewards': 'Récompenses de niveau', 'globalSettings': 'Réglages rapides',
      'theme': 'Thème', 'language': 'Langue', 'fontSize': 'Taille du texte', 'dark': 'Sombre', 'light': 'Clair', 'blue': 'Bleu', 'sky': 'Bleu ciel',
      'green': 'Vert', 'greenLight': 'Vert clair', 'gold': 'Or', 'purple': 'Violet', 'pinkLight': 'Rose clair', 'redFezOnly': 'Fez Pacha rouge original',
      'adminDesigner': 'Designer universel sans code', 'livePlayers': 'Joueurs dans la salle', 'antiCheat': 'Anti-triche activé', 'fairDeal': 'Distribution équitable et équilibrée', 'autoExit': 'Sortie auto après 3 tours manqués',
    },
    'es': <String, String>{
      'favoriteGames': 'Juegos de inicio', 'chooseFavoriteGames': 'Elige de 1 a 4 juegos', 'save': 'Guardar',
      'openRoomsHub': 'Salas abiertas', 'openRoomsSubtitle': 'Salas en vivo con avatares antes de entrar', 'localRoom': 'Sala local instantánea',
      'storePurchaseSuccess': 'Compra completada y activada.', 'storePurchaseOffline': 'Comprado localmente y añadido al inventario.',
      'storePurchaseInsufficient': 'No tienes suficientes fichas.', 'storePurchaseUnavailable': 'La tienda no está disponible ahora.',
      'challengeRoad': 'Camino del desafío', 'challengeSubtitle': 'Elige un juego y supera etapas con cinco intentos', 'attempts': 'Intentos', 'stage': 'Etapa',
      'startChallenge': 'Empezar', 'continueChallenge': 'Continuar', 'levelRewards': 'Recompensas de nivel', 'globalSettings': 'Ajustes rápidos',
      'theme': 'Tema', 'language': 'Idioma', 'fontSize': 'Tamaño de texto', 'dark': 'Oscuro', 'light': 'Claro', 'blue': 'Azul', 'sky': 'Celeste',
      'green': 'Verde', 'greenLight': 'Verde claro', 'gold': 'Dorado', 'purple': 'Morado', 'pinkLight': 'Rosa claro', 'redFezOnly': 'Fez Pasha rojo original',
      'adminDesigner': 'Diseñador universal sin código', 'livePlayers': 'Jugadores en la sala', 'antiCheat': 'Protección anti-trampas activa', 'fairDeal': 'Reparto justo y equilibrado', 'autoExit': 'Salida automática tras 3 turnos ausentes',
    },
  };

  static String t(String lang, String key) => data[lang]?[key] ?? data['en']?[key] ?? key;
}

const Set<String> freeThemeCodesV05 = <String>{
  'dark', 'light', 'royal', 'sky', 'emerald', 'green_light', 'gold', 'purple', 'pink_light',
};

const List<(String, String)> quickThemeChoicesV05 = <(String, String)>[
  ('dark', 'dark'),
  ('light', 'light'),
  ('royal', 'blue'),
  ('sky', 'sky'),
  ('emerald', 'green'),
  ('green_light', 'greenLight'),
  ('gold', 'gold'),
  ('purple', 'purple'),
  ('pink_light', 'pinkLight'),
];

String botNameV05(String locale, int index) {
  const ar = <String>['عدنان', 'بيان', 'كنان', 'جميل', 'رعد', 'عاصم', 'معتصم', 'حسام', 'جنان', 'حور', 'جنات', 'آلاء', 'أفنان', 'شهد', 'حلا', 'شذى', 'قمر'];
  const en = <String>['Adnan', 'Bayan', 'Kenan', 'Jamil', 'Raad', 'Asim', 'Moatasem', 'Hossam', 'Janan', 'Hoor', 'Jannat', 'Alaa', 'Afnan', 'Shahd', 'Hala', 'Shatha', 'Qamar'];
  final names = locale == 'ar' ? ar : en;
  return names[index.abs() % names.length];
}

class PashaFezV05 extends StatelessWidget {
  final double size;
  const PashaFezV05({super.key, this.size = 48});

  @override
  Widget build(BuildContext context) => Image.asset(
        'assets/images/pasha.png',
        width: size,
        height: size * .72,
        fit: BoxFit.contain,
        alignment: Alignment.center,
        filterQuality: FilterQuality.high,
        errorBuilder: (_, __, ___) => _RedFezFallbackV05(width: size, height: size * .72),
      );
}

class V05GlobalControlsOverlay extends StatelessWidget {
  final AppController controller;
  final Widget child;
  const V05GlobalControlsOverlay({super.key, required this.controller, required this.child});

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: <Widget>[
        Positioned.fill(child: child),
        PositionedDirectional(
          end: 10,
          bottom: controller.isAuthenticated ? 86 : 18,
          child: SafeArea(
            child: Material(
              color: Colors.transparent,
              child: PopupMenuButton<String>(
                tooltip: V05Text.t(controller.localeCode, 'globalSettings'),
                onSelected: (value) {
                  if (value.startsWith('lang:')) controller.changeLocale(value.substring(5));
                  if (value.startsWith('theme:')) controller.changeTheme(value.substring(6));
                  if (value == 'font:+') controller.adjustFontScale(.08);
                  if (value == 'font:-') controller.adjustFontScale(-.08);
                },
                itemBuilder: (context) => <PopupMenuEntry<String>>[
                  PopupMenuItem<String>(enabled: false, child: Text(V05Text.t(controller.localeCode, 'language'), style: const TextStyle(fontWeight: FontWeight.w900))),
                  const PopupMenuItem<String>(value: 'lang:ar', child: Text('العربية')),
                  const PopupMenuItem<String>(value: 'lang:en', child: Text('English')),
                  const PopupMenuItem<String>(value: 'lang:de', child: Text('Deutsch')),
                  const PopupMenuItem<String>(value: 'lang:tr', child: Text('Türkçe')),
                  const PopupMenuItem<String>(value: 'lang:fr', child: Text('Français')),
                  const PopupMenuItem<String>(value: 'lang:es', child: Text('Español')),
                  const PopupMenuDivider(),
                  PopupMenuItem<String>(enabled: false, child: Text(V05Text.t(controller.localeCode, 'theme'), style: const TextStyle(fontWeight: FontWeight.w900))),
                  for (final entry in quickThemeChoicesV05)
                    PopupMenuItem<String>(value: 'theme:${entry.$1}', child: Text(V05Text.t(controller.localeCode, entry.$2))),
                  const PopupMenuDivider(),
                  PopupMenuItem<String>(value: 'font:+', child: Text('${V05Text.t(controller.localeCode, 'fontSize')}  A+')),
                  PopupMenuItem<String>(value: 'font:-', child: Text('${V05Text.t(controller.localeCode, 'fontSize')}  A−')),
                ],
                child: Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: Theme.of(context).colorScheme.surface.withValues(alpha: .96),
                    shape: BoxShape.circle,
                    border: Border.all(color: Theme.of(context).colorScheme.primary.withValues(alpha: .65)),
                    boxShadow: const <BoxShadow>[BoxShadow(color: Colors.black45, blurRadius: 14, offset: Offset(0, 5))],
                  ),
                  child: const Icon(Icons.tune_rounded, size: 21),
                ),
              ),
            ),
          ),
        ),
      ],
    );
  }
}

extension WarqnaV05Controller on AppController {
  List<GameInfo> get favoriteGamesV05 {
    final resolved = favoriteGameIdsV05
        .map((id) => gamesCatalog.where((game) => game.id == id).firstOrNull)
        .whereType<GameInfo>()
        .take(4)
        .toList(growable: false);
    return resolved.isEmpty ? gamesCatalog.take(3).toList(growable: false) : resolved;
  }

  Future<void> setFavoriteGamesV05(Iterable<String> ids) async {
    final safe = ids.where((id) => gamesCatalog.any((game) => game.id == id)).toSet();
    if (safe.isEmpty || safe.length > 4) return;
    favoriteGameIdsV05
      ..clear()
      ..addAll(safe);
    await _save();
    refreshUi();
  }

  bool get localEconomyAllowedV05 => !warqnaProductionModeV05 || !serverConnected;

  Future<bool> buyLocalV05(StoreProduct product) async {
    final price = priceFor(product);
    if (coins < BigInt.from(price)) {
      lastPurchaseErrorV05 = V05Text.t(localeCode, 'storePurchaseInsufficient');
      refreshUi();
      return false;
    }
    coins -= BigInt.from(price);
    if (product.category == 'competition_ticket') {
      final denomination = int.tryParse(product.value ?? '') ?? 0;
      if (denomination > 0) competitionTickets[denomination] = (competitionTickets[denomination] ?? 0) + 1;
    } else if (!product.reusable) {
      owned.add(product.id);
    }
    activateProduct(product);
    transactions.insert(0, TokenTransaction('شراء ${nameFor(product, 'ar')}', -price, 'الآن'));
    lastPurchaseErrorV05 = null;
    await _save();
    refreshUi();
    return true;
  }

  void resetChallengeV05(String gameId) {
    challengeGameV05 = gameId;
    challengeStageV05 = 0;
    challengeLivesV05 = 5;
    unawaited(_save());
    refreshUi();
  }

  void recordChallengeResultV05({required bool won}) {
    if (won) {
      challengeStageV05 = math.min(15, challengeStageV05 + 1);
    } else {
      challengeLivesV05 = math.max(0, challengeLivesV05 - 1);
    }
    if (challengeLivesV05 == 0) {
      challengeStageV05 = 0;
      challengeLivesV05 = 5;
    }
    unawaited(_save());
    refreshUi();
  }
}

class FavoriteGamesSectionV05 extends StatelessWidget {
  final AppController controller;
  const FavoriteGamesSectionV05({super.key, required this.controller});

  Future<void> _pick(BuildContext context) async {
    final selected = <String>{...controller.favoriteGameIdsV05};
    final result = await showDialog<Set<String>>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setState) => AlertDialog(
          title: Text(V05Text.t(controller.localeCode, 'chooseFavoriteGames')),
          content: SizedBox(
            width: 520,
            child: ListView(
              shrinkWrap: true,
              children: <Widget>[
                for (final game in gamesCatalog)
                  CheckboxListTile(
                    value: selected.contains(game.id),
                    title: Text(L.t(controller.localeCode, game.id), style: const TextStyle(fontWeight: FontWeight.w800)),
                    secondary: ClipRRect(borderRadius: BorderRadius.circular(10), child: Image.asset(gameArtAsset(game.id), width: 46, height: 46, fit: BoxFit.cover)),
                    onChanged: (checked) {
                      setState(() {
                        if (checked == true && selected.length < 4) selected.add(game.id);
                        if (checked == false && selected.length > 1) selected.remove(game.id);
                      });
                    },
                  ),
              ],
            ),
          ),
          actions: <Widget>[
            TextButton(onPressed: () => Navigator.pop(dialogContext), child: const Text('إلغاء')),
            FilledButton(onPressed: () => Navigator.pop(dialogContext, selected), child: Text(V05Text.t(controller.localeCode, 'save'))),
          ],
        ),
      ),
    );
    if (result != null) await controller.setFavoriteGamesV05(result);
  }

  @override
  Widget build(BuildContext context) {
    final games = controller.favoriteGamesV05;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: <Widget>[
        SectionTitle(
          title: V05Text.t(controller.localeCode, 'favoriteGames'),
          action: '${games.length}/4 • تعديل',
          onTap: () => _pick(context),
        ),
        const SizedBox(height: 9),
        LayoutBuilder(
          builder: (context, constraints) {
            final columns = constraints.maxWidth < 430 ? 2 : math.min(4, games.length);
            return GridView.builder(
              shrinkWrap: true,
              physics: const NeverScrollableScrollPhysics(),
              gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: math.max(1, columns),
                crossAxisSpacing: 8,
                mainAxisSpacing: 8,
                childAspectRatio: columns <= 2 ? .92 : .84,
              ),
              itemCount: games.length,
              itemBuilder: (context, index) => GameCard(
                game: games[index],
                lang: controller.localeCode,
                onTap: () => showGameLobby(context, controller, games[index]),
              ),
            );
          },
        ),
      ],
    );
  }
}

List<Map<String, dynamic>> localOpenRoomsV05(AppController controller, GameInfo game) {
  final maxPlayers = v170AllowedPlayerCounts(game.id).last;
  return List<Map<String, dynamic>>.generate(3, (index) {
    final count = math.min(maxPlayers - 1, index + 1);
    return <String, dynamic>{
      'code': 'LOCAL-${game.id.toUpperCase()}-${index + 1}',
      'name': '${V05Text.t(controller.localeCode, 'localRoom')} ${index + 1}',
      'voice_enabled': index == 1,
      'players': count,
      'max_players': maxPlayers,
      'min_level': index * 5 + 1,
      'local': true,
      'avatars': <Map<String, dynamic>>[
        for (var player = 0; player < count; player++)
          <String, dynamic>{
            'name': botNameV05(controller.localeCode, player + index * 3),
            'name_color': <String>['#38bdf8', '#facc15', '#22c55e', '#e879f9'][(player + index) % 4],
          },
      ],
    };
  });
}

class ChallengeRoadCardV05 extends StatelessWidget {
  final AppController controller;
  const ChallengeRoadCardV05({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final stage = controller.challengeStageV05;
    return InkWell(
      borderRadius: BorderRadius.circular(24),
      onTap: () => showChallengeRoadV05(context, controller),
      child: PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(14),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: <Widget>[
            Row(children: <Widget>[
              const Icon(Icons.route_rounded, color: Colors.amber, size: 34),
              const SizedBox(width: 10),
              Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
                Text(V05Text.t(controller.localeCode, 'challengeRoad'), style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w900)),
                Text(V05Text.t(controller.localeCode, 'challengeSubtitle'), style: const TextStyle(color: Colors.white60, fontSize: 10, height: 1.4)),
              ])),
              Chip(label: Text('❤️ ${controller.challengeLivesV05}')),
            ]),
            const SizedBox(height: 12),
            ClipRRect(borderRadius: BorderRadius.circular(99), child: LinearProgressIndicator(value: stage / 15, minHeight: 12)),
            const SizedBox(height: 6),
            Text('${V05Text.t(controller.localeCode, 'stage')} $stage / 15 • ${L.t(controller.localeCode, controller.challengeGameV05)}', style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 11)),
          ]),
        ),
      ),
    );
  }
}

Future<void> showChallengeRoadV05(BuildContext context, AppController controller) async {
  var selectedGame = controller.challengeGameV05;
  await showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setState) => Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: <Widget>[
        Text(V05Text.t(controller.localeCode, 'challengeRoad'), style: const TextStyle(fontSize: 23, fontWeight: FontWeight.w900)),
        const SizedBox(height: 5),
        Text(V05Text.t(controller.localeCode, 'challengeSubtitle'), style: const TextStyle(color: Colors.white60)),
        const SizedBox(height: 12),
        DropdownButtonFormField<String>(
          initialValue: selectedGame,
          decoration: InputDecoration(labelText: L.t(controller.localeCode, 'games')),
          items: gamesCatalog.map((game) => DropdownMenuItem<String>(value: game.id, child: Text(L.t(controller.localeCode, game.id)))).toList(),
          onChanged: (value) => setState(() => selectedGame = value ?? selectedGame),
        ),
        const SizedBox(height: 14),
        SizedBox(
          height: 88,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: 15,
            separatorBuilder: (_, __) => const SizedBox(width: 7),
            itemBuilder: (context, index) {
              final number = index + 1;
              final completed = number <= controller.challengeStageV05;
              final active = number == controller.challengeStageV05 + 1;
              return Container(
                width: 62,
                alignment: Alignment.center,
                decoration: BoxDecoration(
                  gradient: LinearGradient(colors: completed ? const <Color>[Color(0xff15803d), Color(0xff22c55e)] : active ? const <Color>[Color(0xffa16207), Color(0xfffacc15)] : const <Color>[Color(0xff111827), Color(0xff1f2937)]),
                  borderRadius: BorderRadius.circular(18),
                  border: Border.all(color: active ? Colors.white70 : Colors.white10),
                ),
                child: Column(mainAxisAlignment: MainAxisAlignment.center, children: <Widget>[
                  Text('$number', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900)),
                  Text(number % 5 == 0 ? '🎁' : number % 3 == 0 ? '⚡' : '🪙', style: const TextStyle(fontSize: 18)),
                ]),
              );
            },
          ),
        ),
        const SizedBox(height: 12),
        Row(children: <Widget>[
          Expanded(child: _StoreFact(icon: '❤️', label: V05Text.t(controller.localeCode, 'attempts'), value: '${controller.challengeLivesV05}/5')),
          const SizedBox(width: 8),
          Expanded(child: _StoreFact(icon: '🏁', label: V05Text.t(controller.localeCode, 'stage'), value: '${controller.challengeStageV05}/15')),
        ]),
        const SizedBox(height: 14),
        FilledButton.icon(
          onPressed: () async {
            final error = await controller.startChallengeServerV05(selectedGame, stages: 15);
            if (!context.mounted) return;
            if (error != null) {
              showToast(context, error);
              return;
            }
            final game = gamesCatalog.firstWhere((item) => item.id == selectedGame);
            Navigator.pop(context);
            showGameLobby(context, controller, game);
          },
          icon: const Icon(Icons.play_arrow_rounded),
          label: Text(controller.challengeStageV05 == 0 ? V05Text.t(controller.localeCode, 'startChallenge') : V05Text.t(controller.localeCode, 'continueChallenge')),
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(54)),
        ),
      ]),
    ),
  );
}

class V05ThreeDButton extends StatelessWidget {
  final VoidCallback? onPressed;
  final Object icon;
  final Object label;
  final Color? color;
  const V05ThreeDButton({super.key, required this.onPressed, required this.icon, required this.label, this.color});

  @override
  Widget build(BuildContext context) {
    final base = color ?? Theme.of(context).colorScheme.primary;
    final iconWidget = icon is Widget ? icon as Widget : Icon(icon as IconData);
    final labelWidget = label is Widget ? label as Widget : Text(label.toString(), textAlign: TextAlign.center);
    return DecoratedBox(
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(17),
        boxShadow: <BoxShadow>[BoxShadow(color: base.withValues(alpha: .38), blurRadius: 14, offset: const Offset(0, 6))],
      ),
      child: FilledButton.icon(
        onPressed: onPressed,
        icon: iconWidget,
        label: labelWidget,
        style: FilledButton.styleFrom(
          backgroundColor: base,
          foregroundColor: ThemeData.estimateBrightnessForColor(base) == Brightness.dark ? Colors.white : Colors.black87,
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(17), side: BorderSide(color: Colors.white.withValues(alpha: .22))),
        ),
      ),
    );
  }
}

class _RedFezFallbackV05 extends StatelessWidget {
  final double width;
  final double height;
  const _RedFezFallbackV05({this.width = 54, this.height = 40});

  @override
  Widget build(BuildContext context) => SizedBox(
        width: width,
        height: height,
        child: CustomPaint(painter: _RedFezPainterV05()),
      );
}

class _RedFezPainterV05 extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final body = Paint()..shader = const LinearGradient(colors: <Color>[Color(0xffe1263f), Color(0xff8c0719)]).createShader(Offset.zero & size);
    final gold = Paint()..color = const Color(0xffffce57)..strokeWidth = math.max(1.5, size.width * .045)..style = PaintingStyle.stroke..strokeCap = StrokeCap.round;
    final bodyRect = Rect.fromLTWH(size.width * .16, size.height * .17, size.width * .66, size.height * .66);
    canvas.drawRRect(RRect.fromRectAndRadius(bodyRect, Radius.circular(size.width * .13)), body);
    canvas.drawOval(Rect.fromLTWH(size.width * .16, size.height * .09, size.width * .66, size.height * .25), Paint()..color = const Color(0xffef3850));
    canvas.drawArc(Rect.fromLTWH(size.width * .50, size.height * .08, size.width * .34, size.height * .42), -1.7, 2.2, false, gold);
    final tassel = Paint()..color = const Color(0xffffcf58)..strokeWidth = math.max(1.3, size.width * .035)..strokeCap = StrokeCap.round;
    canvas.drawLine(Offset(size.width * .81, size.height * .49), Offset(size.width * .88, size.height * .79), tassel);
    for (var i = 0; i < 4; i++) {
      canvas.drawLine(Offset(size.width * (.85 + i * .02), size.height * .76), Offset(size.width * (.82 + i * .035), size.height * .94), tassel);
    }
  }

  @override
  bool shouldRepaint(covariant CustomPainter oldDelegate) => false;
}

class ClubIdentityV05 extends StatelessWidget {
  final Map<String, dynamic> club;
  final double size;
  const ClubIdentityV05({super.key, required this.club, this.size = 68});

  @override
  Widget build(BuildContext context) {
    final image = club['image_url']?.toString();
    final logo = club['logo']?.toString();
    final fallbackLogo = logo != null && logo.isNotEmpty ? logo : '🛡️';
    return Container(
      width: size,
      height: size,
      alignment: Alignment.center,
      clipBehavior: Clip.antiAlias,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(size * .28),
        gradient: const LinearGradient(colors: <Color>[Color(0xff162d50), Color(0xff4b1f70)]),
        border: Border.all(color: Colors.white24),
      ),
      child: image != null && image.isNotEmpty
          ? Image.network(image, width: size, height: size, fit: BoxFit.cover, errorBuilder: (_, __, ___) => Text(fallbackLogo, style: TextStyle(fontSize: size * .47)))
          : Text(fallbackLogo, style: TextStyle(fontSize: size * .47)),
    );
  }
}

class V05ClubsPage extends StatefulWidget {
  final AppController controller;
  const V05ClubsPage({super.key, required this.controller});

  @override
  State<V05ClubsPage> createState() => _V05ClubsPageState();
}

class _V05ClubsPageState extends State<V05ClubsPage> {
  bool loading = true;
  String? error;
  List<Map<String, dynamic>> clubs = <Map<String, dynamic>>[];

  @override
  void initState() {
    super.initState();
    unawaited(_load());
  }

  List<Map<String, dynamic>> get _fallback => <Map<String, dynamic>>[
        <String, dynamic>{'id': 1, 'key': 'falcons', 'name': 'صقور العرب', 'logo': '🦅', 'level': 18, 'members_count': 46, 'description': 'دوري مجموعات وتحديات يومية', 'viewer_role': widget.controller.activeClub == 'falcons' ? 'member' : null},
        <String, dynamic>{'id': 2, 'key': 'kings', 'name': 'ملوك الورق', 'logo': '👑', 'level': 25, 'members_count': 50, 'description': 'مجموعة تنافسية احترافية', 'viewer_role': widget.controller.activeClub == 'kings' ? 'member' : null},
        <String, dynamic>{'id': 3, 'key': 'aces', 'name': 'نخبة الآسات', 'logo': '🂡', 'level': 31, 'members_count': 48, 'description': 'بطولات أسبوعية وسجل نشاط كامل', 'viewer_role': widget.controller.activeClub == 'aces' ? 'member' : null},
      ];

  Future<void> _load() async {
    try {
      if (!widget.controller.serverConnected) {
        clubs = _fallback;
      } else {
        final data = await widget.controller.api.clubsV05();
        final raw = data['clubs'];
        clubs = raw is List ? raw.whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList() : _fallback;
      }
    } catch (exception) {
      error = exception.toString();
      clubs = _fallback;
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> _openClub(Map<String, dynamic> club) async {
    var details = club;
    final id = int.tryParse(club['id']?.toString() ?? '');
    if (id != null && widget.controller.serverConnected) {
      try {
        final data = await widget.controller.api.clubV05(id);
        if (data['club'] is Map) details = Map<String, dynamic>.from(data['club'] as Map);
      } catch (_) {}
    }
    if (!mounted) return;
    await showPremiumSheet(
      context,
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: <Widget>[
        Row(children: <Widget>[
          ClubIdentityV05(club: details, size: 78),
          const SizedBox(width: 12),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
            Text(details['name']?.toString() ?? 'مجموعة ورقنا', style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
            Text('LV.${details['level'] ?? 1} • ${details['members_count'] ?? 0} عضو', style: const TextStyle(color: Colors.white60)),
          ])),
        ]),
        const SizedBox(height: 12),
        Text(details['description']?.toString() ?? 'مجموعة ألعاب ومنافسات وتحديات.', style: const TextStyle(height: 1.6)),
        const SizedBox(height: 14),
        Wrap(spacing: 8, runSpacing: 8, children: const <Widget>[
          Chip(avatar: Icon(Icons.emoji_events_outlined, size: 17), label: Text('المنافسات')),
          Chip(avatar: Icon(Icons.track_changes, size: 17), label: Text('التحديات')),
          Chip(avatar: Icon(Icons.history, size: 17), label: Text('سجل المجموعة')),
          Chip(avatar: Icon(Icons.security, size: 17), label: Text('صلاحيات متعددة')),
        ]),
        const SizedBox(height: 14),
        if (details['viewer_role'] == 'owner' || details['viewer_role'] == 'moderator' || widget.controller.isAdmin)
          V05ThreeDButton(
            label: 'إدارة الصورة والأعضاء والصلاحيات',
            icon: Icons.admin_panel_settings_outlined,
            onPressed: () => showClubManagerV05(context, widget.controller, details),
          )
        else
          V05ThreeDButton(label: 'طلب الانضمام', icon: Icons.group_add_outlined, onPressed: () => showToast(context, 'تم إرسال طلب الانضمام.')),
      ]),
    );
  }

  @override
  Widget build(BuildContext context) => RefreshIndicator(
        onRefresh: _load,
        child: ListView(
          padding: const EdgeInsets.all(14),
          children: <Widget>[
            SectionTitle(title: L.t(widget.controller.localeCode, 'clubs'), action: 'تحديث', onTap: _load),
            const SizedBox(height: 10),
            if (widget.controller.activeClubName != null)
              PremiumPanel(
                child: Padding(
                  padding: const EdgeInsets.all(14),
                  child: Row(children: <Widget>[
                    ClubIdentityV05(club: <String, dynamic>{'image_url': widget.controller.activeClubImage, 'logo': widget.controller.activeClubLogo}, size: 66),
                    const SizedBox(width: 11),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: <Widget>[
                      const Text('ناديك الحالي', style: TextStyle(color: Colors.white54, fontSize: 10)),
                      Text(widget.controller.activeClubName!, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.w900)),
                      const Text('يظهر اسم وصورة النادي داخل بروفايلك العام.', style: TextStyle(color: Colors.white60, fontSize: 10)),
                    ])),
                  ]),
                ),
              ),
            const SizedBox(height: 10),
            GroupCommandCenterV173(controller: widget.controller),
            const SizedBox(height: 12),
            if (loading) const Center(child: Padding(padding: EdgeInsets.all(30), child: CircularProgressIndicator())),
            if (error != null) Text('تم فتح البيانات المحلية لأن الخادم غير متاح.', style: TextStyle(color: Theme.of(context).colorScheme.error)),
            for (final club in clubs)
              Padding(
                padding: const EdgeInsets.only(bottom: 10),
                child: PremiumPanel(
                  child: ListTile(
                    onTap: () => _openClub(club),
                    leading: ClubIdentityV05(club: club, size: 58),
                    title: Text(club['name']?.toString() ?? 'مجموعة', style: const TextStyle(fontWeight: FontWeight.w900)),
                    subtitle: Text('LV.${club['level'] ?? 1} • ${club['members_count'] ?? 0} عضو\n${club['description'] ?? ''}', maxLines: 2, overflow: TextOverflow.ellipsis),
                    trailing: const Icon(Icons.arrow_forward_ios_rounded, size: 16),
                    isThreeLine: true,
                  ),
                ),
              ),
          ],
        ),
      );
}

Future<void> showClubManagerV05(BuildContext context, AppController controller, Map<String, dynamic> club) async {
  final id = int.tryParse(club['id']?.toString() ?? '');
  final name = TextEditingController(text: club['name']?.toString() ?? '');
  final image = TextEditingController(text: club['image_url']?.toString() ?? '');
  await showPremiumSheet(
    context,
    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: <Widget>[
      const Text('إدارة المجموعة', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
      const SizedBox(height: 10),
      TextField(controller: name, decoration: const InputDecoration(labelText: 'اسم المجموعة')),
      const SizedBox(height: 8),
      TextField(controller: image, decoration: const InputDecoration(labelText: 'رابط أو Data URL لصورة المجموعة'), maxLines: 2),
      const SizedBox(height: 10),
      const Text('يمكن منح أكثر من صلاحية لأكثر من عضو: قبول الأعضاء، إدارة الأعضاء، إنشاء المنافسات، الدردشة، الإعلانات، تعديل الهوية، وسجل المجموعة.', style: TextStyle(color: Colors.white60, height: 1.6)),
      const SizedBox(height: 12),
      V05ThreeDButton(
        label: 'حفظ هوية المجموعة',
        icon: Icons.save_outlined,
        onPressed: () async {
          if (id != null && controller.serverConnected) {
            try {
              await controller.api.updateClubV05(id, <String, dynamic>{'name': name.text.trim(), 'image_url': image.text.trim()});
            } catch (exception) {
              if (context.mounted) showToast(context, exception.toString());
              return;
            }
          }
          controller.activeClubName = name.text.trim();
          controller.activeClubImage = image.text.trim();
          await controller._save();
          controller.refreshUi();
          if (context.mounted) Navigator.pop(context);
        },
      ),
    ]),
  );
}

extension V05ServerFeatures on AppController {
  bool hasAdminPermissionV05(String permission) => isAdmin && (username.trim().toLowerCase() == 'adnan' || adminPermissionsV05.isEmpty || adminPermissionsV05.contains('all') || adminPermissionsV05.contains(permission));

  Future<String?> startChallengeServerV05(String gameId, {int stages = 15}) async {
    if (!serverConnected) {
      resetChallengeV05(gameId);
      return null;
    }
    try {
      final data = await api.startChallengeRoadV05(gameId, stages: stages);
      final run = data['run'];
      if (run is Map) {
        challengeGameV05 = run['game']?.toString() ?? gameId;
        challengeStageV05 = int.tryParse(run['current_stage']?.toString() ?? '') ?? 0;
        challengeLivesV05 = int.tryParse(run['lives_remaining']?.toString() ?? '') ?? 5;
      }
      await _save();
      refreshUi();
      return null;
    } on ApiException catch (error) {
      return error.message;
    } catch (_) {
      return V05Text.t(localeCode, 'storePurchaseUnavailable');
    }
  }
}

class AdminUsersPanelV05 extends StatelessWidget {
  final AppController controller;
  final List<Map<String, dynamic>> users;
  final Future<void> Function() onChanged;
  const AdminUsersPanelV05({
    super.key,
    required this.controller,
    required this.users,
    required this.onChanged,
  });

  @override
  Widget build(BuildContext context) {
    final resolvedUsers = users.isNotEmpty
        ? users
        : <Map<String, dynamic>>[
            <String, dynamic>{
              'id': controller.currentUserId,
              'username': controller.username,
              'level': controller.level,
              'tokens': controller.coins.toString(),
              'is_admin': controller.isAdmin,
              'admin_permissions': <String, bool>{for (final permission in controller.adminPermissionsV05) permission: true},
            },
          ];
    return ListView(
      padding: const EdgeInsets.all(12),
      children: <Widget>[
        const _AdminInfo(text: 'إدارة حقيقية للحسابات من الخادم: منح توكنز، إرسال طلب صداقة، الحظر، المستوى، ومنح أكثر من صلاحية إدارية. كل إجراء يُسجل في سجل الإدارة.'),
        const SizedBox(height: 10),
        for (final user in resolvedUsers)
          Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: PremiumPanel(
              child: ListTile(
                onTap: () => _showActions(context, user),
                leading: CircleAvatar(
                  child: Text((user['username']?.toString().trim().isNotEmpty == true ? user['username'].toString().trim().substring(0, 1) : '?').toUpperCase()),
                ),
                title: Text(user['username']?.toString() ?? 'Player', style: const TextStyle(fontWeight: FontWeight.w900)),
                subtitle: Text('LV.${user['level'] ?? 1} • 🪙 ${user['tokens'] ?? 0}'),
                trailing: Wrap(
                  crossAxisAlignment: WrapCrossAlignment.center,
                  children: <Widget>[
                    if (user['is_admin'] == true || user['is_admin'] == 1) const Chip(label: Text('ADMIN')),
                    const Icon(Icons.chevron_right_rounded),
                  ],
                ),
              ),
            ),
          ),
      ],
    );
  }

  Future<void> _showActions(BuildContext context, Map<String, dynamic> user) async {
    final id = int.tryParse(user['id']?.toString() ?? '');
    if (id == null) {
      showToast(context, 'لا يمكن تنفيذ الإجراء على حساب محلي غير متزامن.');
      return;
    }
    final amountController = TextEditingController(text: '200');
    final levelController = TextEditingController(text: user['level']?.toString() ?? '1');
    const permissionKeys = <String>[
      'dashboard', 'users', 'economy', 'store', 'games', 'rooms', 'clubs', 'challenges',
      'competitions', 'designer', 'translations', 'themes', 'ads', 'moderation', 'releases',
    ];
    final raw = user['admin_permissions'];
    final permissions = <String, bool>{
      for (final key in permissionKeys)
        key: raw is Map && (raw[key] == true || raw[key] == 1),
    };
    await showPremiumSheet(
      context,
      child: StatefulBuilder(
        builder: (sheetContext, setSheetState) => Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            Text(user['username']?.toString() ?? 'Player', style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
            const SizedBox(height: 12),
            TextField(controller: amountController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'التوكنز المراد منحها')),
            const SizedBox(height: 8),
            V05ThreeDButton(
              onPressed: () => _execute(sheetContext, id, 'grant_tokens', amount: int.tryParse(amountController.text)),
              icon: Icons.toll_rounded,
              label: 'منح التوكنز من الإدارة',
            ),
            const SizedBox(height: 8),
            V05ThreeDButton(
              onPressed: () => _execute(sheetContext, id, 'send_friend_request'),
              icon: Icons.person_add_alt_1_rounded,
              label: 'إرسال طلب صداقة من المدير',
              color: const Color(0xff0f766e),
            ),
            const SizedBox(height: 8),
            TextField(controller: levelController, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'المستوى')),
            const SizedBox(height: 8),
            V05ThreeDButton(
              onPressed: () => _execute(sheetContext, id, 'set_level', level: int.tryParse(levelController.text)),
              icon: Icons.military_tech_rounded,
              label: 'حفظ المستوى',
              color: const Color(0xff7c3aed),
            ),
            const SizedBox(height: 12),
            const Text('الصلاحيات الإدارية المفوضة', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
            const SizedBox(height: 6),
            Wrap(
              spacing: 6,
              runSpacing: 6,
              children: <Widget>[
                for (final key in permissionKeys)
                  FilterChip(
                    label: Text(key),
                    selected: permissions[key] ?? false,
                    onSelected: (value) => setSheetState(() => permissions[key] = value),
                  ),
              ],
            ),
            const SizedBox(height: 10),
            V05ThreeDButton(
              onPressed: () => _execute(sheetContext, id, 'set_admin_permissions', permissions: permissions),
              icon: Icons.admin_panel_settings_rounded,
              label: 'حفظ الصلاحيات المتعددة',
              color: const Color(0xffa16207),
            ),
            const SizedBox(height: 10),
            Row(
              children: <Widget>[
                Expanded(child: OutlinedButton.icon(onPressed: () => _execute(sheetContext, id, 'ban'), icon: const Icon(Icons.block), label: const Text('حظر'))),
                const SizedBox(width: 8),
                Expanded(child: OutlinedButton.icon(onPressed: () => _execute(sheetContext, id, 'unban'), icon: const Icon(Icons.check_circle_outline), label: const Text('إلغاء الحظر'))),
              ],
            ),
          ],
        ),
      ),
    );
    amountController.dispose();
    levelController.dispose();
  }

  Future<void> _execute(
    BuildContext context,
    int userId,
    String action, {
    int? amount,
    int? level,
    Map<String, bool>? permissions,
  }) async {
    if (!controller.serverConnected) {
      showToast(context, 'هذا الإجراء يحتاج اتصالاً بالخادم.');
      return;
    }
    try {
      await controller.api.adminUserAction(userId, action, amount: amount, level: level, permissions: permissions);
      if (!context.mounted) return;
      showToast(context, 'تم تنفيذ الإجراء وتسجيله بنجاح.');
      Navigator.pop(context);
      await onChanged();
    } on ApiException catch (error) {
      if (context.mounted) showToast(context, error.message);
    } catch (_) {
      if (context.mounted) showToast(context, 'تعذر تنفيذ الإجراء الآن.');
    }
  }
}
