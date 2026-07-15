part of 'main.dart';

/// V183: responsive desktop shell, adaptive previews and data-driven store rules.
const String warqnaaV183Release = '0.3.2+183';

bool isDesktopWebV183(double width) => kIsWeb && width >= 1024;
bool isWideDesktopV183(double width) => kIsWeb && width >= 1440;

int raisedStorePriceV183(StoreProduct product) {
  if (product.price <= 0) return product.price;
  if (product.category == 'pasha' || product.category == 'competition_ticket') return product.price;
  if (product.category == 'boost') return product.price; // Exact color-booster economy.
  final multiplier = switch (product.category) {
    'tables' => 2.40,
    'cards' => 2.10,
    'emoji' => 2.20,
    'themes' => 1.90,
    'effects' => 2.25,
    'covers' => 2.05,
    'badges' => 2.00,
    'names' => 1.85,
    'chat_colors' => 1.85,
    _ => 2.00,
  };
  final raw = (product.price * multiplier).round();
  // Luxury-store rounding avoids random-looking prices.
  if (raw < 1000) return ((raw + 49) ~/ 50) * 50;
  if (raw < 10000) return ((raw + 249) ~/ 250) * 250;
  return ((raw + 499) ~/ 500) * 500;
}

int boosterValidityDaysV183(String id) => switch (id) {
  'booster_yellow_v183' => 7,
  'booster_green_v183' => 8,
  'booster_red_v183' => 9,
  'booster_blue_v183' => 10,
  'booster_black_v183' => 11,
  'booster_silver_v183' => 12,
  'booster_gold_v183' => 14,
  _ => 10,
};

String boosterColorNameV183(StoreProduct product, String lang) {
  final color = product.id.replaceAll('booster_', '').replaceAll('_v183', '');
  const ar = <String, String>{
    'yellow': 'الأصفر', 'green': 'الأخضر', 'red': 'الأحمر', 'blue': 'الأزرق',
    'black': 'الأسود', 'silver': 'الفضي', 'gold': 'الذهبي',
  };
  const en = <String, String>{
    'yellow': 'Yellow', 'green': 'Green', 'red': 'Red', 'blue': 'Blue',
    'black': 'Black', 'silver': 'Silver', 'gold': 'Gold',
  };
  return lang == 'ar' ? (ar[color] ?? color) : (en[color] ?? color);
}

class DesktopShellNavigationV183 extends StatelessWidget {
  final AppController controller;
  final int selectedIndex;
  final ValueChanged<int> onSelected;
  const DesktopShellNavigationV183({
    super.key,
    required this.controller,
    required this.selectedIndex,
    required this.onSelected,
  });

  @override
  Widget build(BuildContext context) {
    final destinations = <(IconData, String)>[
      (Icons.redeem_rounded, L.t(controller.localeCode, 'store')),
      (Icons.style_rounded, L.t(controller.localeCode, 'games')),
      (Icons.home_rounded, L.t(controller.localeCode, 'home')),
      (Icons.shield_rounded, L.t(controller.localeCode, 'clubs')),
      (Icons.calendar_month_rounded, L.t(controller.localeCode, 'events')),
    ];
    return Container(
      width: 224,
      padding: const EdgeInsets.fromLTRB(12, 18, 12, 14),
      decoration: BoxDecoration(
        color: Theme.of(context).colorScheme.surfaceContainer.withValues(alpha: .96),
        border: BorderDirectional(end: BorderSide(color: Theme.of(context).dividerColor.withValues(alpha: .25))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Row(children: [
            Image.asset('assets/images/brand/warqna_logo.png', width: 46, height: 46, fit: BoxFit.contain),
            const SizedBox(width: 9),
            const Expanded(child: Text('Warqnaa', style: TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
          ]),
          const SizedBox(height: 20),
          for (var i = 0; i < destinations.length; i++) ...[
            _DesktopDestinationV183(
              icon: destinations[i].$1,
              label: destinations[i].$2,
              selected: selectedIndex == i,
              onTap: () => onSelected(i),
            ),
            const SizedBox(height: 7),
          ],
          const Spacer(),
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: Theme.of(context).colorScheme.primary.withValues(alpha: .10),
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: Theme.of(context).colorScheme.primary.withValues(alpha: .24)),
            ),
            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(controller.displayName, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900)),
              const SizedBox(height: 4),
              Text('LV.${controller.level}  •  🪙 ${formatNumber(controller.coins)}', style: TextStyle(fontSize: 11, color: Theme.of(context).colorScheme.onSurfaceVariant)),
            ]),
          ),
        ],
      ),
    );
  }
}

class _DesktopDestinationV183 extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool selected;
  final VoidCallback onTap;
  const _DesktopDestinationV183({required this.icon, required this.label, required this.selected, required this.onTap});
  @override
  Widget build(BuildContext context) => Material(
    color: selected ? Theme.of(context).colorScheme.primary.withValues(alpha: .15) : Colors.transparent,
    borderRadius: BorderRadius.circular(16),
    child: InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
        child: Row(children: [
          Icon(icon, color: selected ? Theme.of(context).colorScheme.primary : Theme.of(context).colorScheme.onSurfaceVariant),
          const SizedBox(width: 12),
          Expanded(child: Text(label, style: TextStyle(fontWeight: selected ? FontWeight.w900 : FontWeight.w700))),
        ]),
      ),
    ),
  );
}

class AdaptiveTablePreviewV183 extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;
  final bool compact;
  final bool gameplay;
  const AdaptiveTablePreviewV183({
    super.key,
    required this.controller,
    required this.product,
    this.compact = false,
    this.gameplay = false,
  });

  @override
  Widget build(BuildContext context) {
    final c1 = controller.color1For(product);
    final c2 = controller.color2For(product);
    final radius = compact ? 18.0 : gameplay ? 28.0 : 30.0;
    return LayoutBuilder(builder: (context, constraints) {
      final availableWidth = constraints.maxWidth.isFinite ? constraints.maxWidth : (compact ? 190.0 : 720.0);
      final width = availableWidth.clamp(compact ? 150.0 : 260.0, gameplay ? 1500.0 : 920.0).toDouble();
      final ratio = gameplay ? 16 / 10 : 16 / 9;
      return Center(
        child: SizedBox(
          width: width,
          child: AspectRatio(
            aspectRatio: ratio,
            child: Container(
              padding: EdgeInsets.all(compact ? 4 : 7),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(radius),
                gradient: RadialGradient(colors: [c2.withValues(alpha: .78), c1, Color.lerp(c1, Colors.black, .60)!]),
                border: Border.all(color: c2.withValues(alpha: .84), width: compact ? 2 : 3),
                boxShadow: [BoxShadow(color: c2.withValues(alpha: .22), blurRadius: compact ? 12 : 28, offset: Offset(0, compact ? 5 : 12))],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(radius - 6),
                child: product.imageAsset == null
                    ? Stack(fit: StackFit.expand, children: [
                        const AmbientTableFX(density: 7, subtle: true),
                        Center(child: Text(product.icon, style: TextStyle(fontSize: compact ? 40 : 74))),
                      ])
                    : ColoredBox(
                        color: Color.lerp(c1, Colors.black, .48)!,
                        child: Image.asset(
                          product.imageAsset!,
                          fit: BoxFit.contain,
                          alignment: Alignment.center,
                          filterQuality: gameplay ? FilterQuality.medium : FilterQuality.high,
                          errorBuilder: (_, __, ___) => Center(child: Text(product.icon, style: TextStyle(fontSize: compact ? 40 : 74))),
                        ),
                      ),
              ),
            ),
          ),
        ),
      );
    });
  }
}

class CompetitionTicketPreviewV183 extends StatelessWidget {
  final String denomination;
  final bool compact;
  const CompetitionTicketPreviewV183({super.key, required this.denomination, this.compact = false});

  @override
  Widget build(BuildContext context) {
    return AspectRatio(
      aspectRatio: 16 / 10,
      child: Stack(
        fit: StackFit.expand,
        children: [
          Image.asset(ticketAssetV02(denomination), fit: BoxFit.contain, filterQuality: FilterQuality.high),
          Align(
            alignment: const Alignment(0, .30),
            child: Container(
              constraints: BoxConstraints(minWidth: compact ? 54 : 92),
              padding: EdgeInsets.symmetric(horizontal: compact ? 7 : 14, vertical: compact ? 3 : 6),
              decoration: BoxDecoration(
                gradient: const LinearGradient(colors: [Color(0xff4b2614), Color(0xff7a4522), Color(0xff3b1d10)]),
                borderRadius: BorderRadius.circular(compact ? 8 : 12),
                border: Border.all(color: const Color(0xff2b1209), width: compact ? 1 : 2),
                boxShadow: const [BoxShadow(color: Colors.black54, blurRadius: 8, offset: Offset(0, 4)), BoxShadow(color: Color(0x66ffffff), blurRadius: 2, offset: Offset(0, -1))],
              ),
              child: Text(
                denomination,
                textAlign: TextAlign.center,
                style: TextStyle(
                  color: const Color(0xfffff3c4),
                  fontSize: compact ? 13 : 24,
                  fontWeight: FontWeight.w900,
                  letterSpacing: .4,
                  shadows: const [Shadow(color: Colors.black, blurRadius: 2, offset: Offset(0, 2)), Shadow(color: Color(0xffd5a447), blurRadius: 7)],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}


String _multiplierLabelV183(double? value) {
  if (value == null) return '1';
  if (value == value.roundToDouble()) return value.toStringAsFixed(0);
  return value.toStringAsFixed(value * 10 == (value * 10).roundToDouble() ? 1 : 2);
}

class BoosterPreviewV183 extends StatelessWidget {
  final StoreProduct product;
  final bool compact;
  const BoosterPreviewV183({super.key, required this.product, this.compact = false});

  @override
  Widget build(BuildContext context) {
    final asset = product.imageAsset;
    return LayoutBuilder(builder: (context, constraints) {
      final maxWidth = constraints.maxWidth.isFinite ? constraints.maxWidth : (compact ? 180.0 : 420.0);
      return Center(
        child: SizedBox(
          width: maxWidth.clamp(compact ? 130.0 : 240.0, compact ? 240.0 : 520.0).toDouble(),
          child: AspectRatio(
            aspectRatio: 1,
            child: Stack(children: [
              Positioned.fill(
                child: asset == null
                    ? const Center(child: Text('🚀', style: TextStyle(fontSize: 72)))
                    : Image.asset(asset, fit: BoxFit.contain, filterQuality: FilterQuality.high),
              ),
              PositionedDirectional(
                end: compact ? 5 : 16,
                bottom: compact ? 5 : 14,
                child: Container(
                  padding: EdgeInsets.symmetric(horizontal: compact ? 7 : 12, vertical: compact ? 4 : 7),
                  decoration: BoxDecoration(
                    color: const Color(0xdd071019),
                    borderRadius: BorderRadius.circular(14),
                    border: Border.all(color: Colors.white38),
                    boxShadow: const [BoxShadow(color: Colors.black54, blurRadius: 10, offset: Offset(0, 4))],
                  ),
                  child: Text(
                    '×${_multiplierLabelV183(product.multiplier)}',
                    style: TextStyle(fontSize: compact ? 14 : 24, fontWeight: FontWeight.w900, color: Colors.white),
                  ),
                ),
              ),
            ]),
          ),
        ),
      );
    });
  }
}

class DesignerQuickControlsV183 extends StatelessWidget {
  final AppController controller;
  const DesignerQuickControlsV183({super.key, required this.controller});

  @override
  Widget build(BuildContext context) => PremiumPanel(
    child: Padding(
      padding: const EdgeInsets.all(13),
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        const Text('مركز التعديل المباشر V183', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)),
        const SizedBox(height: 5),
        Text('تعديل الأسعار، الإتاحة، النصوص، الألوان، المدة، المعاينات، المسرعات، الإيموت والأصوات مع حفظ محلي ومزامنة الخادم.', style: TextStyle(color: Theme.of(context).colorScheme.onSurfaceVariant, fontSize: 10, height: 1.5)),
        const SizedBox(height: 10),
        Wrap(spacing: 8, runSpacing: 8, children: [
          FilledButton.tonalIcon(
            onPressed: controller.isPrimaryAdmin ? () => Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => Scaffold(appBar: AppBar(title: const Text('استديو المتجر الشامل')), body: SafeArea(child: AdminStoreStudioV151(controller: controller))))) : null,
            icon: const Icon(Icons.storefront_rounded),
            label: const Text('استديو كل عناصر المتجر'),
          ),
          for (final item in const <(String, String, IconData)>[
            ('مسرعات النقاط', 'xp_booster', Icons.rocket_launch_rounded),
            ('الإيموت والحزم', 'emoji_pack', Icons.emoji_emotions_rounded),
            ('الأصوات والمؤثرات', 'audio', Icons.graphic_eq_rounded),
            ('المعاينات والأحجام', 'preview_layout', Icons.aspect_ratio_rounded),
            ('قواعد ومحركات الألعاب', 'game_rules', Icons.rule_rounded),
          ])
            OutlinedButton.icon(
              onPressed: controller.isPrimaryAdmin ? () => Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => DesignerEntityManagerV173(controller: controller, initialEntityType: item.$2, title: item.$1))) : null,
              icon: Icon(item.$3),
              label: Text(item.$1),
            ),
        ]),
      ]),
    ),
  );
}

class HomeDashboardV183 extends StatelessWidget {
  final AppController controller;
  final ValueChanged<int> onTab;
  const HomeDashboardV183({super.key, required this.controller, required this.onTab});

  Widget _games(BuildContext context, double width) {
    final lang = controller.localeCode;
    final selectedGames = controller.homeGames;
    final columns = width >= 1180 ? 4 : width >= 760 ? 3 : 2;
    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      SectionTitle(
        title: L.t(lang, 'homeGames'),
        action: L.t(lang, 'customize'),
        onTap: () => showHomeGamesSelector(context, controller),
      ),
      const SizedBox(height: 10),
      GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
          crossAxisCount: math.min(columns, selectedGames.length).clamp(1, 4).toInt(),
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          childAspectRatio: width >= 900 ? 1.34 : .92,
        ),
        itemCount: selectedGames.length,
        itemBuilder: (_, i) => GameCard(
          game: selectedGames[i],
          lang: lang,
          onTap: () => showGameLobby(context, controller, selectedGames[i]),
        ),
      ),
    ]);
  }

  Widget _playActions(BuildContext context) {
    final lang = controller.localeCode;
    return Row(children: [
      Expanded(child: PremiumActionButton(
        icon: Icons.handshake,
        title: L.t(lang, 'friendly'),
        color: Theme.of(context).colorScheme.secondary,
        onPressed: () => showGameLobby(context, controller, gamesCatalog[1]),
      )),
      const SizedBox(width: 12),
      Expanded(child: PremiumActionButton(
        icon: Icons.emoji_events,
        title: L.t(lang, 'competitions'),
        color: const Color(0xffa06f1d),
        onPressed: () => showCompetitions(context, controller),
      )),
    ]);
  }

  @override
  Widget build(BuildContext context) => LayoutBuilder(builder: (context, constraints) {
    final desktop = isDesktopWebV183(constraints.maxWidth);
    if (!desktop) {
      return ListView(
        padding: const EdgeInsets.all(13),
        children: [
          ResponsiveAccountStatsV170(controller: controller),
          const SizedBox(height: 13),
          HeroBanner(lang: controller.localeCode, onJoin: () => showCompetitions(context, controller)),
          const SizedBox(height: 13),
          LuckyWheelHomeCardV182(controller: controller),
          const SizedBox(height: 13),
          PrizeBoxesHomeCardV02(controller: controller, onOpen: () => Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => PrizeBoxesPageV02(controller: controller)))),
          const SizedBox(height: 13),
          GiftRoad(controller: controller),
          const SizedBox(height: 16),
          _games(context, constraints.maxWidth),
          const SizedBox(height: 13),
          _playActions(context),
          const SizedBox(height: 13),
          HomeQuickActionsV170(controller: controller, onTab: onTab),
        ],
      );
    }

    final sideWidth = constraints.maxWidth >= 1450 ? 430.0 : 370.0;
    return SingleChildScrollView(
      padding: const EdgeInsets.fromLTRB(22, 18, 22, 30),
      child: Row(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Expanded(
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            ResponsiveAccountStatsV170(controller: controller),
            const SizedBox(height: 16),
            HeroBanner(lang: controller.localeCode, onJoin: () => showCompetitions(context, controller)),
            const SizedBox(height: 18),
            _games(context, constraints.maxWidth - sideWidth - 34),
            const SizedBox(height: 16),
            _playActions(context),
          ]),
        ),
        const SizedBox(width: 18),
        SizedBox(
          width: sideWidth,
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            LuckyWheelHomeCardV182(controller: controller),
            const SizedBox(height: 14),
            PrizeBoxesHomeCardV02(controller: controller, onOpen: () => Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => PrizeBoxesPageV02(controller: controller)))),
            const SizedBox(height: 14),
            GiftRoad(controller: controller),
            const SizedBox(height: 14),
            HomeQuickActionsV170(controller: controller, onTab: onTab),
          ]),
        ),
      ]),
    );
  });
}
