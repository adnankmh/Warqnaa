part of 'main.dart';

/// v170 concentrates the narrow-screen, public-profile and room-control polish
/// in one part so the stable Android boot path from v165 remains untouched.

Widget buildV170TopBar(BuildContext context, AppController controller) {
  final lang = controller.localeCode;
  final unread = controller.notices.where((notice) => !notice.read).length;

  Widget profile() => InkWell(
        borderRadius: BorderRadius.circular(30),
        onTap: () {
          AppSounds.fire('profile_open');
          showProfile(context, controller);
        },
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            _PashaColorAvatarV170(
              name: controller.displayName,
              emoji: controller.avatarEmoji,
              bytes: AccountAvatar(controller: controller)._decode(),
              color: colorFromHex(controller.selectedNameColor),
              pasha: controller.vipDays > 0,
              size: 42,
            ),
            const SizedBox(width: 8),
            Flexible(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    controller.displayName,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: TextStyle(
                      fontWeight: FontWeight.w900,
                      color: colorFromHex(controller.selectedNameColor),
                      shadows: [Shadow(color: colorFromHex(controller.selectedNameColor).withValues(alpha: .55), blurRadius: 8)],
                    ),
                  ),
                  Text(
                    '${controller.countryFlag} ${controller.level} LV • ${controller.serverConnected ? 'LIVE' : 'LOCAL'}',
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: const TextStyle(fontSize: 9, color: Colors.white60),
                  ),
                ],
              ),
            ),
          ],
        ),
      );

  Widget brand({double size = 54}) => Semantics(
        label: 'Warqna',
        image: true,
        child: Image.asset(
          'assets/images/brand/warqna_logo.png',
          width: size,
          height: size,
          fit: BoxFit.contain,
          filterQuality: FilterQuality.high,
        ),
      );

  Widget notifications() => IconButton(
        tooltip: L.t(lang, 'notifications'),
        onPressed: () => showNotifications(context, controller),
        icon: Badge(
          isLabelVisible: unread > 0,
          label: Text('$unread'),
          child: const Icon(Icons.notifications_none_rounded),
        ),
      );

  Widget friends() => IconButton(
        tooltip: L.t(lang, 'friends'),
        onPressed: () => showFriends(context, controller),
        icon: Badge(
          isLabelVisible: controller.incomingRequests.isNotEmpty,
          label: Text('${controller.incomingRequests.length}'),
          child: const Icon(Icons.people_alt_outlined),
        ),
      );

  Widget compactMenu() => PopupMenuButton<String>(
        tooltip: 'المزيد',
        icon: const Icon(Icons.more_horiz_rounded),
        onSelected: (value) {
          switch (value) {
            case 'orientation':
              controller.toggleOrientationMode();
              return;
            case 'settings':
              showSettings(context, controller);
              return;
            case 'connection':
              showConnectionDiagnosticsDialog(context, controller);
              return;
            case 'logout':
              controller.logout();
              return;
          }
        },
        itemBuilder: (_) => [
          const PopupMenuItem(value: 'orientation', child: ListTile(leading: Icon(Icons.screen_rotation), title: Text('تدوير الشاشة'))),
          const PopupMenuItem(value: 'connection', child: ListTile(leading: Icon(Icons.wifi_tethering), title: Text('فحص الاتصال'))),
          const PopupMenuItem(value: 'settings', child: ListTile(leading: Icon(Icons.settings), title: Text('الإعدادات'))),
          const PopupMenuDivider(),
          const PopupMenuItem(value: 'logout', child: ListTile(leading: Icon(Icons.logout, color: Colors.redAccent), title: Text('تسجيل الخروج'))),
        ],
      );

  Widget language() => PopupMenuButton<String>(
        tooltip: L.t(lang, 'language'),
        icon: Text(lang.toUpperCase(), style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900)),
        onSelected: controller.changeLocale,
        itemBuilder: (_) => const [
          PopupMenuItem(value: 'ar', child: Text('العربية 🇵🇸')),
          PopupMenuItem(value: 'en', child: Text('English 🇬🇧')),
          PopupMenuItem(value: 'de', child: Text('Deutsch 🇩🇪')),
          PopupMenuItem(value: 'tr', child: Text('Türkçe 🇹🇷')),
          PopupMenuItem(value: 'fr', child: Text('Français 🇫🇷')),
          PopupMenuItem(value: 'es', child: Text('Español 🇪🇸')),
        ],
      );

  Widget fontMenu() => PopupMenuButton<String>(
        tooltip: L.t(lang, 'font'),
        icon: const Icon(Icons.font_download_outlined),
        onSelected: controller.changeFontFamily,
        itemBuilder: (_) => const [
          PopupMenuItem(value: 'Roboto', child: Text('Roboto')),
          PopupMenuItem(value: 'Arial', child: Text('Arial')),
          PopupMenuItem(value: 'serif', child: Text('Serif')),
          PopupMenuItem(value: 'monospace', child: Text('Monospace')),
        ],
      );

  Widget themeMenu() => PopupMenuButton<String>(
        tooltip: L.t(lang, 'theme'),
        icon: const Icon(Icons.palette_outlined),
        initialValue: controller.themeCode,
        onSelected: controller.changeTheme,
        itemBuilder: (_) => v151ThemeOptions
            .map((theme) => PopupMenuItem<String>(
                  value: theme.$1,
                  child: Row(children: [CircleAvatar(radius: 7, backgroundColor: theme.$3), const SizedBox(width: 8), Text(theme.$2)]),
                ))
            .toList(),
      );

  return Container(
    decoration: BoxDecoration(
      color: Theme.of(context).scaffoldBackgroundColor.withValues(alpha: .97),
      border: Border(bottom: BorderSide(color: Colors.white.withValues(alpha: .07))),
    ),
    child: LayoutBuilder(
      builder: (context, constraints) {
        final narrow = constraints.maxWidth < 610;
        if (!narrow) {
          return Padding(
            padding: const EdgeInsets.fromLTRB(13, 8, 8, 8),
            child: Row(children: [
              brand(size: 58),
              const SizedBox(width: 9),
              Expanded(child: profile()),
              friends(),
              if (controller.isAdmin)
                IconButton(
                  tooltip: 'لوحة الإدارة',
                  onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => AdminDashboardPage(controller: controller))),
                  icon: const Icon(Icons.admin_panel_settings_outlined),
                ),
              notifications(),
              fontMenu(),
              IconButton(onPressed: () => controller.adjustFontScale(-.08), icon: const Text('A−', style: TextStyle(fontWeight: FontWeight.w900))),
              IconButton(onPressed: () => controller.adjustFontScale(.08), icon: const Text('A+', style: TextStyle(fontWeight: FontWeight.w900))),
              language(),
              themeMenu(),
              compactMenu(),
            ]),
          );
        }
        return Padding(
          padding: const EdgeInsets.fromLTRB(10, 7, 7, 6),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Row(children: [brand(size: 46), const SizedBox(width: 6), Expanded(child: profile()), friends(), notifications(), compactMenu()]),
              const SizedBox(height: 2),
              SizedBox(
                height: 42,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: [
                    themeMenu(),
                    language(),
                    IconButton(onPressed: () => controller.adjustFontScale(.08), icon: const Text('+A', style: TextStyle(fontWeight: FontWeight.w900))),
                    IconButton(onPressed: () => controller.adjustFontScale(-.08), icon: const Text('−A', style: TextStyle(fontWeight: FontWeight.w900))),
                    fontMenu(),
                    IconButton(onPressed: controller.toggleOrientationMode, icon: Icon(controller.landscapeMode ? Icons.stay_current_portrait : Icons.stay_current_landscape)),
                    if (controller.isAdmin)
                      IconButton(
                        onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => AdminDashboardPage(controller: controller))),
                        icon: const Icon(Icons.admin_panel_settings_outlined),
                      ),
                  ],
                ),
              ),
            ],
          ),
        );
      },
    ),
  );
}

class ResponsiveAccountStatsV170 extends StatelessWidget {
  final AppController controller;
  const ResponsiveAccountStatsV170({super.key, required this.controller});

  @override
  Widget build(BuildContext context) => Row(children: [
        Expanded(
          child: _AccountMetricV170(
            icon: '🏅',
            label: L.t(controller.localeCode, 'level'),
            value: 'LV.${controller.level}',
            details: '${formatNumber(controller.xp)} / ${formatNumber(controller.xpNext)} XP',
            progress: controller.xpNext <= 0 ? 0 : (controller.xp / controller.xpNext).clamp(0, 1).toDouble(),
            onTap: () => showProfile(context, controller),
          ),
        ),
        const SizedBox(width: 8),
        Expanded(
          child: _AccountMetricV170(
            icon: '🪙',
            label: L.t(controller.localeCode, 'coins'),
            value: formatNumber(controller.coins),
            details: 'الرصيد المتاح',
            onTap: () => showWallet(context, controller),
          ),
        ),
      ]);
}

class _AccountMetricV170 extends StatelessWidget {
  final String icon;
  final String label;
  final String value;
  final String details;
  final double? progress;
  final Color? accent;
  final VoidCallback onTap;
  const _AccountMetricV170({required this.icon,required this.label,required this.value,required this.details,required this.onTap,this.progress,this.accent});
  @override
  Widget build(BuildContext context)=>InkWell(
    onTap:onTap,
    borderRadius:BorderRadius.circular(18),
    child:PremiumPanel(child:Container(
      constraints:const BoxConstraints(minHeight:94),
      padding:const EdgeInsets.all(11),
      child:Column(crossAxisAlignment:CrossAxisAlignment.start,mainAxisAlignment:MainAxisAlignment.center,children:[
        Row(children:[Text(icon,style:const TextStyle(fontSize:18)),const SizedBox(width:5),Expanded(child:Text(label,maxLines:1,overflow:TextOverflow.ellipsis,style:const TextStyle(color:Colors.white60,fontSize:10,fontWeight:FontWeight.w800)))]),
        const SizedBox(height:4),
        FittedBox(fit:BoxFit.scaleDown,alignment:AlignmentDirectional.centerStart,child:Text(value,maxLines:1,style:TextStyle(fontWeight:FontWeight.w900,fontSize:17,color:accent))),
        const SizedBox(height:3),
        Text(details,maxLines:1,overflow:TextOverflow.ellipsis,style:const TextStyle(color:Colors.white54,fontSize:9)),
        if(progress!=null)...[const SizedBox(height:6),ClipRRect(borderRadius:BorderRadius.circular(20),child:LinearProgressIndicator(value:progress,minHeight:6))],
      ]),
    )),
  );
}

class HomeQuickActionsV170 extends StatelessWidget {
  final AppController controller;
  final ValueChanged<int> onTab;
  const HomeQuickActionsV170({super.key, required this.controller, required this.onTab});

  @override
  Widget build(BuildContext context) {
    final lang = controller.localeCode;
    Widget action(String icon, String label, VoidCallback tap) => Expanded(
          child: InkWell(
            onTap: tap,
            borderRadius: BorderRadius.circular(17),
            child: Container(
              constraints: const BoxConstraints(minHeight: 86),
              padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 11),
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: .045), borderRadius: BorderRadius.circular(17), border: Border.all(color: Colors.white10)),
              child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [Text(icon, style: const TextStyle(fontSize: 25)), const SizedBox(height: 5), FittedBox(child: Text(label, maxLines: 1, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 12)))]),
            ),
          ),
        );
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(9),
        child: Column(children: [
          Row(children: [
            action('🎯', L.t(lang, 'challenges'), () => showChallenges(context, controller)),
            const SizedBox(width: 7),
            action('🎁', L.t(lang, 'rewards'), () => showRewards(context, controller)),
            const SizedBox(width: 7),
            action('⚙️', L.t(lang, 'settings'), () => showSettings(context, controller)),
          ]),
          const SizedBox(height: 7),
          Row(children: [
            action('🏆', L.t(lang, 'competitions'), () => showCompetitions(context, controller)),
            const SizedBox(width: 7),
            action('🛡️', L.t(lang, 'clubs'), () => onTab(3)),
            const SizedBox(width: 7),
            action('👥', L.t(lang, 'friends'), () => showFriends(context, controller)),
          ]),
        ]),
      ),
    );
  }
}

class ProductCardV170 extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;
  const ProductCardV170({super.key, required this.controller, required this.product});

  @override
  Widget build(BuildContext context) {
    final owned = controller.isOwnedActiveV176(product.id);
    final expiryLabel = controller.expiryForProductV176(product.id) == null ? null : controller.remainingForProductV176(product.id);
    final isPasha = product.category == 'pasha';
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(children: [
          Row(children: [
            Container(padding:const EdgeInsets.symmetric(horizontal:9,vertical:5),decoration:BoxDecoration(color:Colors.white.withValues(alpha:.07),borderRadius:BorderRadius.circular(20),border:Border.all(color:Colors.white12)),child:Text(product.tierLabel(controller.localeCode),style:const TextStyle(fontSize:10,fontWeight:FontWeight.w900))),
            const Spacer(),
            if (owned) const Icon(Icons.verified_rounded,color:Colors.greenAccent,size:20),
          ]),
          const SizedBox(height:7),
          Expanded(child:Container(width:double.infinity,padding:EdgeInsets.all(isPasha?6:0),decoration:BoxDecoration(color:Colors.black.withValues(alpha:.14),borderRadius:BorderRadius.circular(20)),child:Center(child:isPasha
              ? Image.asset('assets/images/pasha.png',fit:BoxFit.contain,width:double.infinity,height:double.infinity,filterQuality:FilterQuality.high,errorBuilder:(_,__,___)=>const Text('👑',style:TextStyle(fontSize:72)))
              : InkWell(onTap:()=>showProductPreview(context,controller,product),borderRadius:BorderRadius.circular(20),child:Center(child:_CompactProductPreview(controller:controller,product:product)))))),
          const SizedBox(height:9),
          Text(controller.nameFor(product),textAlign:TextAlign.center,maxLines:2,overflow:TextOverflow.ellipsis,style:const TextStyle(fontWeight:FontWeight.w900,fontSize:16,height:1.25)),
          const SizedBox(height:5),
          Text(controller.descriptionFor(product),textAlign:TextAlign.center,maxLines:2,overflow:TextOverflow.ellipsis,style:const TextStyle(color:Colors.white60,fontSize:12,height:1.45)),
          const SizedBox(height:8),
          FittedBox(fit:BoxFit.scaleDown,child:Text(product.price == 0 && owned ? '🎁 هدية الحزمة اليومية' : '🪙 ${formatNumber(controller.priceFor(product))}',style:TextStyle(color:Theme.of(context).colorScheme.primary,fontWeight:FontWeight.w900,fontSize:15))),
          if (expiryLabel != null) ...[const SizedBox(height:5), Container(padding:const EdgeInsets.symmetric(horizontal:9,vertical:5),decoration:BoxDecoration(color:Colors.orangeAccent.withValues(alpha:.12),borderRadius:BorderRadius.circular(20),border:Border.all(color:Colors.orangeAccent.withValues(alpha:.35))),child:Text('⌛ $expiryLabel',textAlign:TextAlign.center,style:const TextStyle(color:Colors.orangeAccent,fontSize:10,fontWeight:FontWeight.w900)))],
          const SizedBox(height:8),
          if (isPasha)
            SizedBox(width:double.infinity,child:FilledButton.icon(onPressed:() async { if(owned && !product.reusable){ controller.activateProduct(product); showToast(context,'تم تفعيل ${controller.nameFor(product)}.'); return; } final confirmed=await showDialog<bool>(context:context,builder:(dialogContext)=>AlertDialog(icon:Image.asset('assets/images/pasha.png',height:100,fit:BoxFit.contain),title:Text(controller.nameFor(product)),content:Text('${controller.descriptionFor(product)}\n\nالسعر: ${formatNumber(controller.priceFor(product))} توكن'),actions:[TextButton(onPressed:()=>Navigator.pop(dialogContext,false),child:const Text('إلغاء')),FilledButton(onPressed:()=>Navigator.pop(dialogContext,true),child:Text(owned?'تفعيل':'شراء'))])); if(confirmed==true && context.mounted){ if(owned){controller.activateProduct(product);showToast(context,'تم التفعيل.');}else{final ok=await controller.buy(product);if(context.mounted)showToast(context, ok ? 'تم شراء الباشا وتفعيله.' : (controller.lastPurchaseErrorV022 ?? 'تعذر إكمال الشراء.'));}} },icon:const Icon(Icons.workspace_premium),label:Text(owned?'تفعيل الباشا':'شراء الباشا'),style:FilledButton.styleFrom(minimumSize:const Size.fromHeight(50))))
          else
            Row(children:[Expanded(child:OutlinedButton.icon(onPressed:()=>showProductPreview(context,controller,product),icon:const Icon(Icons.visibility_outlined,size:17),label:Text(L.t(controller.localeCode,'preview'),maxLines:1,overflow:TextOverflow.ellipsis),style:OutlinedButton.styleFrom(minimumSize:const Size.fromHeight(48),padding:const EdgeInsets.symmetric(horizontal:8)))),const SizedBox(width:7),Expanded(child:FilledButton(onPressed:() async { if(owned && !product.reusable){controller.activateProduct(product);showToast(context,'تم تفعيل ${controller.nameFor(product)}.');return;}await showProductPreview(context,controller,product);},style:FilledButton.styleFrom(minimumSize:const Size.fromHeight(48),padding:const EdgeInsets.symmetric(horizontal:8)),child:FittedBox(child:Text(owned&&!product.reusable?'تفعيل':L.t(controller.localeCode,'buy'),style:const TextStyle(fontWeight:FontWeight.w900)))))]),
        ]),
      ),
    );
  }
}

class _PashaColorAvatarV170 extends StatelessWidget {
  final String name;
  final String emoji;
  final Uint8List? bytes;
  final Color color;
  final bool pasha;
  final double size;
  const _PashaColorAvatarV170({required this.name, required this.emoji, required this.bytes, required this.color, required this.pasha, required this.size});

  @override
  Widget build(BuildContext context) => SizedBox(
        width: size + (pasha ? 8 : 0),
        height: size + (pasha ? 8 : 0),
        child: Stack(clipBehavior: Clip.none, children: [
          Positioned.fill(
            child: Container(
              decoration: BoxDecoration(shape: BoxShape.circle, boxShadow: [BoxShadow(color: color.withValues(alpha: .58), blurRadius: pasha ? 18 : 8, spreadRadius: pasha ? 2 : 0)]),
              child: GlowAvatar(text: emoji.isEmpty ? name.characters.first : emoji, bytes: bytes, size: size, color: color),
            ),
          ),
          if (pasha)
            Positioned(
              top: -8,
              right: -5,
              child: Image.asset('assets/images/pasha.png', width: size * .75, height: size * .55, fit: BoxFit.contain),
            ),
        ]),
      );
}

Future<void> showPublicPlayerProfileV170(BuildContext context, AppController controller, LocalFriend friend) async {
  AppSounds.fire('profile_open');
  LocalFriend visible = friend;
  if (controller.serverConnected && friend.id > 0) {
    try {
      final response = await controller.api.publicPlayerProfile(friend.id);
      final map = response['user'] is Map ? Map<String, dynamic>.from(response['user'] as Map) : <String, dynamic>{};
      visible = LocalFriend(
        friend.id,
        map['display_name']?.toString() ?? friend.name,
        map['username']?.toString() ?? friend.username,
        online: map['online'] == true,
        activity: map['online'] == true ? 'متصل الآن' : 'غير متصل',
        level: int.tryParse(map['level']?.toString() ?? '') ?? friend.level,
        countryCode: (map['country_code']?.toString() ?? friend.countryCode).toUpperCase(),
        avatar: map['avatar']?.toString() ?? friend.avatar,
        pashaDays: int.tryParse(map['pasha_days']?.toString() ?? '') ?? friend.pashaDays,
        gamesPlayed: int.tryParse(map['games_played']?.toString() ?? '') ?? friend.gamesPlayed,
        wins: int.tryParse(map['wins']?.toString() ?? '') ?? friend.wins,
        xp: int.tryParse(map['xp']?.toString() ?? '') ?? friend.xp,
        xpNext: int.tryParse(map['xp_next']?.toString() ?? '') ?? friend.xpNext,
        roundPoints: int.tryParse(map['round_points']?.toString() ?? '') ?? friend.roundPoints,
        tournamentPoints: int.tryParse(map['tournament_points']?.toString() ?? '') ?? friend.tournamentPoints,
        clubPoints: int.tryParse(map['club_points']?.toString() ?? '') ?? friend.clubPoints,
        nameColor: map['name_color']?.toString() ?? friend.nameColor,
        badge: map['badge']?.toString() ?? friend.badge,
        clubName: map['club'] is Map ? (map['club'] as Map)['name']?.toString() : friend.clubName,
        clubLogo: map['club'] is Map ? (map['club'] as Map)['logo']?.toString() : friend.clubLogo,
        clubLevel: map['club'] is Map ? (int.tryParse((map['club'] as Map)['level']?.toString() ?? '') ?? friend.clubLevel) : friend.clubLevel,
      );
    } catch (_) {}
  }
  if (!context.mounted) return;
  final country = countryByCode(visible.countryCode);
  final color = colorFromHex(visible.nameColor);
  final winRate = visible.gamesPlayed <= 0 ? 0 : ((visible.wins / visible.gamesPlayed) * 100).round();
  await showPremiumSheet(
    context,
    child: Column(children: [
      Container(
        width: double.infinity,
        padding: const EdgeInsets.fromLTRB(18, 24, 18, 18),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(26),
          gradient: LinearGradient(colors: [color.withValues(alpha: .33), Theme.of(context).colorScheme.surface]),
          border: Border.all(color: color.withValues(alpha: .55)),
        ),
        child: Column(children: [
          _PashaColorAvatarV170(name: visible.name, emoji: visible.avatar?.isNotEmpty == true ? visible.avatar! : visible.name.characters.first, bytes: null, color: color, pasha: visible.pashaDays > 0, size: 104),
          const SizedBox(height: 12),
          Text(visible.name, textAlign: TextAlign.center, style: TextStyle(fontSize: 25, fontWeight: FontWeight.w900, color: color, shadows: [Shadow(color: color, blurRadius: 12)])),
          Text('@${visible.username}', style: const TextStyle(color: Colors.white60)),
          const SizedBox(height: 7),
          Text('${country.flag} ${country.name(controller.localeCode)} • المستوى ${visible.level}', style: const TextStyle(fontWeight: FontWeight.w900)),
          if (visible.badge != null && visible.badge!.isNotEmpty) Padding(padding: const EdgeInsets.only(top: 6), child: Chip(label: Text(visible.badge!))),
        ]),
      ),
      const SizedBox(height: 12),
      ClubIdentityV022(name: visible.clubName, logo: visible.clubLogo, level: visible.clubLevel),
      if (visible.clubName != null) const SizedBox(height: 10),
      Row(children: [
        Expanded(child: ProfileMetric(value: '${visible.level}', label: 'المستوى')),
        const SizedBox(width: 7),
        Expanded(child: ProfileMetric(value: '${visible.gamesPlayed}', label: 'المباريات')),
        const SizedBox(width: 7),
        Expanded(child: ProfileMetric(value: '$winRate%', label: 'نسبة الفوز')),
      ]),
      const SizedBox(height: 10),
      PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(13),
          child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
            Row(children: [
              const Icon(Icons.auto_graph_rounded, color: Colors.lightBlueAccent),
              const SizedBox(width: 7),
              Expanded(child: Text('تقدم المستوى ${visible.level}', style: const TextStyle(fontWeight: FontWeight.w900))),
              Text('${formatNumber(visible.xp)} / ${formatNumber(visible.xpNext)} XP', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900, fontSize: 10)),
            ]),
            const SizedBox(height: 8),
            ClipRRect(borderRadius: BorderRadius.circular(20), child: LinearProgressIndicator(value: visible.xpNext <= 0 ? 0 : (visible.xp / visible.xpNext).clamp(0, 1).toDouble(), minHeight: 8)),
            const SizedBox(height: 10),
            Row(children: [
              Expanded(child: ProfileMetric(value: '${visible.roundPoints}', label: 'نقاط الجولات')),
              const SizedBox(width: 6),
              Expanded(child: ProfileMetric(value: '${visible.tournamentPoints}', label: 'المسابقات')),
              const SizedBox(width: 6),
              Expanded(child: ProfileMetric(value: '${visible.clubPoints}', label: 'النادي')),
            ]),
          ]),
        ),
      ),
      const SizedBox(height: 10),
      PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(13),
          child: Row(children: [
            Icon(visible.online ? Icons.circle : Icons.circle_outlined, size: 13, color: visible.online ? Colors.greenAccent : Colors.white38),
            const SizedBox(width: 8),
            Expanded(child: Text(visible.online ? 'متصل الآن' : visible.activity, style: const TextStyle(fontWeight: FontWeight.w800))),
            if (visible.pashaDays > 0) Flexible(child: Row(mainAxisSize: MainAxisSize.min, children: [Image.asset('assets/images/pasha.png', width: 27, height: 21, fit: BoxFit.contain), const SizedBox(width: 4), Flexible(child: Text('باشا ${visible.pashaDays} يوم', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900)))])),
          ]),
        ),
      ),
      const SizedBox(height: 10),
      const Text('رصيد التوكنز خاص بصاحب الحساب ولا يظهر في البروفايل العام.', style: TextStyle(color: Colors.white54, fontSize: 10)),
    ]),
  );
}

List<int> v170MinLevelOptions(int currentLevel) {
  final values = <int>{1, 5, 10, 20, 30, 50, currentLevel}.where((value) => value <= currentLevel).toList();
  values.sort();
  return values;
}

List<int> v170AllowedPlayerCounts(String gameId) {
  switch (gameId) {
    case 'pinochle':
    case 'banakil':
      return const [2, 4];
    case 'hand':
    case 'saudi_hand':
      return const [2, 3, 4, 5];
    case 'hand_partner':
      return const [4];
    default:
      return const [4];
  }
}

Future<void> inviteFriendV170(BuildContext context, AppController controller, LocalFriend friend) async {
  final code = controller.activeRoomCode;
  if (code == null || code.isEmpty) {
    showToast(context, 'أنشئ غرفة أو ادخل غرفة أولاً ثم أرسل الدعوة.');
    return;
  }
  try {
    if (controller.serverConnected) {
      final result = await controller.api.inviteFriendToRoom(friend.id, code);
      if (context.mounted) showToast(context, result['message']?.toString() ?? 'تم إرسال الدعوة.');
    } else {
      if (context.mounted) showToast(context, 'تم تسجيل دعوة محلية إلى ${friend.name}.');
    }
    AppSounds.fire('invite');
  } catch (error) {
    if (context.mounted) showToast(context, friendlyErrorMessage(error, controller.localeCode));
  }
}

Future<void> inviteAllFriendsV170(BuildContext context, AppController controller) async {
  final code = controller.activeRoomCode;
  if (code == null || code.isEmpty) {
    showToast(context, 'لا توجد غرفة فعالة لإرسال الدعوات.');
    return;
  }
  try {
    if (controller.serverConnected) {
      final result = await controller.api.inviteAllFriendsToRoom(code);
      if (context.mounted) showToast(context, result['message']?.toString() ?? 'تم إرسال الدعوة لكل الأصدقاء.');
    } else {
      if (context.mounted) showToast(context, 'تم إرسال الدعوة المحلية إلى ${controller.friends.length} صديق.');
    }
    AppSounds.fire('invite');
  } catch (error) {
    if (context.mounted) showToast(context, friendlyErrorMessage(error, controller.localeCode));
  }
}

class OpenRoomCardV170 extends StatelessWidget {
  final AppController controller;
  final GameInfo game;
  final Map<String, dynamic> room;
  final VoidCallback? onJoin;
  const OpenRoomCardV170({super.key, required this.controller, required this.game, required this.room, required this.onJoin});

  @override
  Widget build(BuildContext context) {
    final voice = room['voice_enabled'] == true || room['voice_enabled'] == 1;
    final rawAvatars = room['avatars'] is List ? room['avatars'] as List : const [];
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Row(children: [
          ClipRRect(borderRadius: BorderRadius.circular(16), child: Image.asset(gameArtAsset(game.id), width: 72, height: 72, fit: BoxFit.cover)),
          const SizedBox(width: 11),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(room['name']?.toString() ?? room['code']?.toString() ?? 'غرفة', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
            const SizedBox(height: 4),
            Text('${voice ? '🎙️ صوتية' : '🃏 عادية'} • ${room['players'] ?? 1}/${room['max_players'] ?? 4} • LV.${room['min_level'] ?? 1}+', style: const TextStyle(color: Colors.white60, fontSize: 10)),
            const SizedBox(height: 7),
            SizedBox(height: 34, child: Row(children: [
              for (var index = 0; index < (room['max_players'] as num? ?? 4).toInt().clamp(1, 6); index++) ...[
                if (index > 0) const SizedBox(width: 5),
                if (index < rawAvatars.length && rawAvatars[index] is Map)
                  InkWell(
                    onTap: () {
                      final avatar = Map<String, dynamic>.from(rawAvatars[index] as Map);
                      openPlayerProfileV021(context, controller,
                        userId: int.tryParse(avatar['id']?.toString() ?? ''),
                        name: avatar['name']?.toString() ?? 'لاعب',
                        username: avatar['username']?.toString(),
                        avatar: avatar['avatar']?.toString(),
                        nameColor: avatar['name_color']?.toString() ?? '#38bdf8',
                        countryCode: avatar['country_code']?.toString() ?? 'PS',
                        online: avatar['connected'] == true,
                      );
                    },
                    borderRadius: BorderRadius.circular(20),
                    child: CircleAvatar(
                      radius: 16,
                      backgroundColor: colorFromHex((rawAvatars[index] as Map)['name_color']?.toString() ?? '#38bdf8'),
                      child: Text(((rawAvatars[index] as Map)['name']?.toString() ?? '؟').characters.first, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w900)),
                    ),
                  )
                else
                  CircleAvatar(radius: 16, backgroundColor: Colors.white.withValues(alpha: .05), child: const Icon(Icons.person_add_alt_1, size: 14, color: Colors.white24)),
              ],
            ])),
          ])),
          FilledButton(onPressed: onJoin, child: Text(L.t(controller.localeCode, 'join'))),
        ]),
      ),
    );
  }
}

class BotAvatarV170 extends StatelessWidget {
  final String name;
  final int seed;
  final double size;
  const BotAvatarV170({super.key, required this.name, required this.seed, this.size = 58});
  @override
  Widget build(BuildContext context) {
    final palettes = <List<Color>>[
      const [Color(0xff00d4ff), Color(0xff16213e)],
      const [Color(0xffffbf69), Color(0xff5f0f40)],
      const [Color(0xff80ed99), Color(0xff22577a)],
      const [Color(0xffc77dff), Color(0xff240046)],
    ];
    final palette = palettes[seed.abs() % palettes.length];
    return Container(
      width: size,
      height: size,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        gradient: RadialGradient(center: const Alignment(-.35, -.45), colors: [Colors.white, palette[0], palette[1]]),
        border: Border.all(color: palette[0], width: 2.5),
        boxShadow: [BoxShadow(color: palette[0].withValues(alpha: .45), blurRadius: 18)],
      ),
      child: Stack(alignment: Alignment.center, children: [
        Icon(Icons.smart_toy_rounded, size: size * .55, color: palette[1]),
        Positioned(bottom: 2, child: Container(padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1), decoration: BoxDecoration(color: Colors.black54, borderRadius: BorderRadius.circular(8)), child: Text(name, maxLines: 1, style: TextStyle(fontSize: size * .12, fontWeight: FontWeight.w900)))),
      ]),
    );
  }
}

Future<void> showChallengesV170(BuildContext context, AppController controller) async {
  final challenges = <Map<String, Object>>[
    {'id':'royal_tarneeb','icon':'⚔️','title':'سلسلة الطرنيب الملكية','game':'tarneeb','target':5,'progress':2,'reward':500,'xp':420,'tier':'أسطوري'},
    {'id':'trix_master','icon':'👑','title':'طريق محترف التركس','game':'trix','target':4,'progress':1,'reward':350,'xp':300,'tier':'محترف'},
    {'id':'hand_streak','icon':'🎴','title':'سلسلة انتصارات الهاند','game':'hand','target':3,'progress':0,'reward':300,'xp':250,'tier':'متقدم'},
    {'id':'social_room','icon':'🎙️','title':'تحدي اللعب الاجتماعي','game':'tarneeb','target':3,'progress':1,'reward':220,'xp':180,'tier':'يومي'},
  ];
  await showPremiumSheet(
    context,
    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      Row(children: [
        Container(width: 54, height: 54, alignment: Alignment.center, decoration: BoxDecoration(gradient: const LinearGradient(colors:[Color(0xffffcf67),Color(0xff8b5e11)]), borderRadius: BorderRadius.circular(18)), child: const Text('🎯', style: TextStyle(fontSize: 29))),
        const SizedBox(width: 10),
        const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('مركز التحديات', style: TextStyle(fontSize: 23, fontWeight: FontWeight.w900)),
          Text('مهام يومية وأسبوعية وسلاسل تنافسية بجوائز متدرجة.', style: TextStyle(color: Colors.white60, fontSize: 11, height: 1.5)),
        ])),
      ]),
      const SizedBox(height: 14),
      for (final challenge in challenges)
        Padding(
          padding: const EdgeInsets.only(bottom: 10),
          child: PremiumPanel(
            child: Container(
              padding: const EdgeInsets.all(13),
              decoration: BoxDecoration(borderRadius: BorderRadius.circular(22), gradient: LinearGradient(colors:[Theme.of(context).colorScheme.primary.withValues(alpha:.14), Colors.transparent])),
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                Row(children:[
                  Text(challenge['icon']!.toString(), style: const TextStyle(fontSize: 34)),
                  const SizedBox(width: 9),
                  Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children:[
                    Text(challenge['title']!.toString(), style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
                    Text('${challenge['tier']} • ${L.t(controller.localeCode, challenge['game']!.toString())}', style: const TextStyle(color: Colors.white54, fontSize: 10)),
                  ])),
                  Container(padding: const EdgeInsets.symmetric(horizontal:9,vertical:5), decoration: BoxDecoration(color: Colors.amber.withValues(alpha:.12), borderRadius: BorderRadius.circular(14)), child: Text('🪙 ${challenge['reward']}', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900, fontSize: 11))),
                ]),
                const SizedBox(height: 10),
                ClipRRect(borderRadius: BorderRadius.circular(20), child: LinearProgressIndicator(value: (challenge['progress'] as int)/(challenge['target'] as int), minHeight: 9)),
                const SizedBox(height: 6),
                Row(children:[
                  Expanded(child: Text('${challenge['progress']}/${challenge['target']} • +${challenge['xp']} XP', style: const TextStyle(color: Colors.white60, fontSize: 10))),
                  FilledButton.tonal(
                    onPressed: () {
                      final id=challenge['id']!.toString();
                      if (!controller.joinChallenge(id)) { showToast(context, 'غادر التحدي الحالي أولاً.'); return; }
                      final game=gamesCatalog.firstWhere((item)=>item.id==challenge['game']);
                      Navigator.pop(context);
                      showCreateRoom(context, controller, game);
                    },
                    child: const Text('ابدأ الآن'),
                  ),
                ]),
              ]),
            ),
          ),
        ),
    ]),
  );
}

class GroupInnovationHubV170 extends StatelessWidget {
  final AppController controller;
  const GroupInnovationHubV170({super.key, required this.controller});

  @override
  Widget build(BuildContext context) {
    final items=<({String icon,String title,String subtitle})>[
      (icon:'📣',title:'إعلانات مثبتة',subtitle:'نشر أخبار المجموعة وجدولة التنبيهات للأعضاء.'),
      (icon:'🧑‍✈️',title:'المشرفون والصلاحيات',subtitle:'قبول الطلبات، إنشاء المسابقات، وإدارة الإعلانات.'),
      (icon:'🏆',title:'دوري المجموعة',subtitle:'مواسم داخلية، جدول مباريات، ونقاط أسبوعية.'),
      (icon:'🎯',title:'مهام جماعية',subtitle:'تحديات تعاونية بخزينة ومكافآت مشتركة.'),
      (icon:'📊',title:'سجل الإدارة',subtitle:'توثيق القرارات والتحويلات ونتائج المسابقات.'),
      (icon:'🛡️',title:'مركز النزاهة',subtitle:'بلاغات، مراجعة المباريات، ومنع التخريب والغش.'),
    ];
    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children:[
      const SectionTitle(title:'مركز المجموعة الاحترافي'),
      const SizedBox(height:8),
      LayoutBuilder(builder:(context,constraints){
        final columns=constraints.maxWidth>=760?3:2;
        return GridView.builder(
          shrinkWrap:true,
          physics:const NeverScrollableScrollPhysics(),
          itemCount:items.length,
          gridDelegate:SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount:columns,crossAxisSpacing:8,mainAxisSpacing:8,mainAxisExtent:142),
          itemBuilder:(context,index){
            final item=items[index];
            return PremiumPanel(child:InkWell(
              borderRadius:BorderRadius.circular(20),
              onTap:()=>showToast(context,'${item.title}: جاهز ضمن صلاحيات المجموعة.'),
              child:Padding(padding:const EdgeInsets.all(11),child:Column(mainAxisAlignment:MainAxisAlignment.center,children:[
                Text(item.icon,style:const TextStyle(fontSize:30)),
                const SizedBox(height:6),
                FittedBox(child:Text(item.title,style:const TextStyle(fontWeight:FontWeight.w900,fontSize:12))),
                const SizedBox(height:5),
                Text(item.subtitle,textAlign:TextAlign.center,maxLines:3,overflow:TextOverflow.ellipsis,style:const TextStyle(color:Colors.white54,fontSize:9,height:1.35)),
              ])),
            ));
          },
        );
      }),
    ]);
  }
}

class TarneebBidButtonV170 extends StatelessWidget {
  final String label;
  final String? subtitle;
  final bool selected;
  final VoidCallback? onPressed;
  const TarneebBidButtonV170({super.key,required this.label,this.subtitle,this.selected=false,this.onPressed});
  @override
  Widget build(BuildContext context)=>AnimatedContainer(
    duration:const Duration(milliseconds:180),
    decoration:BoxDecoration(
      borderRadius:BorderRadius.circular(16),
      gradient:LinearGradient(colors:selected?[Theme.of(context).colorScheme.primary,const Color(0xff7c4a08)]:[Colors.white.withValues(alpha:.08),Colors.white.withValues(alpha:.035)]),
      border:Border.all(color:selected?Theme.of(context).colorScheme.primary:Colors.white12,width:selected?2:1),
      boxShadow:onPressed==null
          ? const []
          : [
              BoxShadow(color:(selected?Theme.of(context).colorScheme.primary:Colors.black).withValues(alpha:selected ? .38 : .34),blurRadius:selected?18:8,offset:const Offset(0,6)),
              BoxShadow(color:Colors.white.withValues(alpha:selected ? .20 : .07),blurRadius:2,offset:const Offset(0,-1)),
            ],
    ),
    child:InkWell(
      onTap:onPressed,
      borderRadius:BorderRadius.circular(16),
      child:Transform.translate(
        offset:onPressed==null?const Offset(0,2):Offset.zero,
        child:Padding(
        padding:const EdgeInsets.symmetric(horizontal:14,vertical:11),
        child:Column(mainAxisSize:MainAxisSize.min,children:[
          Text(label,style:const TextStyle(fontWeight:FontWeight.w900,fontSize:15)),
          if(subtitle!=null) Text(subtitle!,style:const TextStyle(fontSize:9,color:Colors.white60)),
        ]),
      )),
    ),
  );
}
