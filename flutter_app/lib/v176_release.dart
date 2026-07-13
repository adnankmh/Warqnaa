part of 'main.dart';

Map<String, DateTime> decodeDateTimeMapV176(String? raw) {
  if (raw == null || raw.trim().isEmpty) return <String, DateTime>{};
  try {
    final decoded = jsonDecode(raw);
    if (decoded is! Map) return <String, DateTime>{};
    final result = <String, DateTime>{};
    for (final entry in decoded.entries) {
      final value = DateTime.tryParse(entry.value?.toString() ?? '');
      if (value != null) result[entry.key.toString()] = value;
    }
    return result;
  } catch (_) {
    return <String, DateTime>{};
  }
}

List<Map<String, dynamic>> decodeMapListV176(String? raw) {
  if (raw == null || raw.trim().isEmpty) return <Map<String, dynamic>>[];
  try {
    final decoded = jsonDecode(raw);
    if (decoded is! List) return <Map<String, dynamic>>[];
    return decoded.whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList();
  } catch (_) {
    return <Map<String, dynamic>>[];
  }
}

extension WarqnaV176Controller on AppController {
  String? _packProductIdV176(Map<String, dynamic> reward) {
    final explicit = reward['store_item_key']?.toString().trim();
    if (explicit != null && explicit.isNotEmpty) return explicit;
    final type = reward['type']?.toString();
    final value = reward['value']?.toString();
    if (type == 'name_color') return 'daily_pack_name_gold_24h_v176';
    if (type == 'chat_color') return 'daily_pack_chat_cyan_24h_v176';
    if (type == 'xp_booster') return 'daily_pack_xp_15x_6h_v176';
    if (type == 'table' && value != null && value.isNotEmpty) return value;
    return null;
  }

  DateTime? _packExpiryV176(Map<String, dynamic> reward) {
    final serverExpiry = DateTime.tryParse(reward['expires_at']?.toString() ?? '');
    if (serverExpiry != null) return serverExpiry.toLocal();
    final hours = int.tryParse(reward['duration_hours']?.toString() ?? '') ?? 0;
    return hours > 0 ? DateTime.now().add(Duration(hours: hours)) : null;
  }

  void applyDailyPackRewardV176(Map<String, dynamic> reward, Map<String, dynamic> response) {
    final type = reward['type']?.toString() ?? 'gift';
    final value = reward['value']?.toString();
    final expiry = _packExpiryV176(reward);
    final productId = _packProductIdV176(reward);

    if (productId != null) {
      owned.add(productId);
      if (expiry != null) packInventoryExpiriesV176[productId] = expiry;
    }

    if (type == 'name_color' && value != null) {
      selectedNameColor = value;
      nameColorExpiresAt = expiry;
    } else if (type == 'chat_color' && value != null) {
      selectedChatColor = value;
      chatColorExpiresAt = expiry;
    } else if (type == 'xp_booster') {
      activeXpMultiplier = double.tryParse(value ?? '') ?? 1.5;
      boosterExpiresAtV173 = expiry;
    } else if (type == 'table' && value != null) {
      selectedTable = value;
      temporaryTableExpiresAtV173 = expiry;
    }

    final tickets = response['tickets'];
    if (tickets is Map) {
      competitionTickets
        ..clear()
        ..addAll(tickets.map((key, item) => MapEntry(int.tryParse(key.toString()) ?? 0, int.tryParse(item.toString()) ?? 0)));
    }
    syncPackInventoryV176(response['inventory']);

    final historyItem = <String, dynamic>{
      'type': type,
      'value': value,
      'product_id': productId,
      'label_ar': reward['label_ar']?.toString() ?? dailyPackReward ?? 'هدية يومية',
      'icon': reward['icon']?.toString() ?? '🎁',
      'rarity': reward['rarity']?.toString() ?? 'common',
      'expires_at': expiry?.toIso8601String(),
      'opened_at': reward['opened_at']?.toString() ?? DateTime.now().toIso8601String(),
    };
    dailyPackHistoryV176.insert(0, historyItem);
    if (dailyPackHistoryV176.length > 20) {
      dailyPackHistoryV176.removeRange(20, dailyPackHistoryV176.length);
    }
    lastDailyPackRevealV176 = Map<String, dynamic>.from(historyItem);
  }

  void syncPackInventoryV176(dynamic rawInventory) {
    if (rawInventory is! List) return;
    final now = DateTime.now();
    final seenKeys = <String>{};
    for (final raw in rawInventory) {
      if (raw is! Map) continue;
      final item = Map<String, dynamic>.from(raw);
      final storeRaw = item['store_item'];
      final store = storeRaw is Map ? Map<String, dynamic>.from(storeRaw) : <String, dynamic>{};
      final key = (store['key'] ?? item['store_item_key'] ?? item['key'])?.toString().trim();
      if (key == null || key.isEmpty || !seenKeys.add(key)) continue;
      final expiry = DateTime.tryParse(item['expires_at']?.toString() ?? '')?.toLocal();
      final active = item['active'] == true || item['active'] == 1 || item['active']?.toString() == '1';
      if (!active || (expiry != null && !expiry.isAfter(now))) {
        owned.remove(key);
        packInventoryExpiriesV176.remove(key);
        continue;
      }
      owned.add(key);
      if (expiry == null) {
        packInventoryExpiriesV176.remove(key);
      } else {
        packInventoryExpiriesV176[key] = expiry;
      }
    }
  }

  void purgeExpiredPackInventoryV176(DateTime now) {
    final expired = packInventoryExpiriesV176.entries
        .where((entry) => !entry.value.isAfter(now))
        .map((entry) => entry.key)
        .toList(growable: false);
    for (final productId in expired) {
      packInventoryExpiriesV176.remove(productId);
      owned.remove(productId);
      if (selectedTable == productId) {
        selectedTable = 'table_premium_01';
        temporaryTableExpiresAtV173 = null;
      }
      if (productId == 'daily_pack_name_gold_24h_v176') {
        selectedNameColor = '#facc15';
        nameColorExpiresAt = null;
      }
      if (productId == 'daily_pack_chat_cyan_24h_v176') {
        selectedChatColor = '#ffffff';
        chatColorExpiresAt = null;
      }
      if (productId == 'daily_pack_xp_15x_6h_v176') {
        activeXpMultiplier = 1.0;
        boosterExpiresAtV173 = null;
      }
      if (productId == 'daily_prize_cover_v02') {
        selectedCover = 'cover_royal_gold';
      }
    }
  }

  DateTime? expiryForProductV176(String productId) {
    final expiry = packInventoryExpiriesV176[productId];
    if (expiry == null) return null;
    if (!expiry.isAfter(DateTime.now())) {
      purgeExpiredPackInventoryV176(DateTime.now());
      return null;
    }
    return expiry;
  }

  bool isOwnedActiveV176(String productId) {
    final expiry = packInventoryExpiriesV176[productId];
    if (expiry != null && !expiry.isAfter(DateTime.now())) return false;
    return owned.contains(productId);
  }

  int get activeOwnedCountV176 => owned.where(isOwnedActiveV176).length;

  String remainingForProductV176(String productId) {
    final expiry = expiryForProductV176(productId);
    if (expiry == null) return 'دائم';
    final remaining = expiry.difference(DateTime.now());
    if (remaining.inSeconds <= 0) return 'منتهي';
    if (remaining.inDays > 0) return 'باقي ${remaining.inDays} يوم و${remaining.inHours.remainder(24)} ساعة';
    if (remaining.inHours > 0) return 'باقي ${remaining.inHours} ساعة و${remaining.inMinutes.remainder(60)} دقيقة';
    return 'باقي ${math.max(1, remaining.inMinutes)} دقيقة';
  }

  String? productDurationLabelV176(StoreProduct product) {
    if (product.durationHours != null && product.durationHours! > 0) {
      final hours = product.durationHours!;
      return hours % 24 == 0 ? '${hours ~/ 24} يوم' : '$hours ساعة';
    }
    final days = durationFor(product);
    return days == null || days <= 0 ? null : '$days أيام';
  }

  bool historyRewardActiveV176(Map<String, dynamic> reward) {
    final expiry = DateTime.tryParse(reward['expires_at']?.toString() ?? '');
    return expiry == null || expiry.isAfter(DateTime.now());
  }

  String historyRewardStatusV176(Map<String, dynamic> reward) {
    final expiry = DateTime.tryParse(reward['expires_at']?.toString() ?? '');
    if (expiry == null) return 'أضيفت مباشرة إلى الرصيد';
    final productId = reward['product_id']?.toString();
    if (productId == null || productId.isEmpty) return 'صالحة حتى ${expiry.toLocal().toString().substring(0, 16)}';
    return remainingForProductV176(productId);
  }
}

class PackInventoryStripV176 extends StatelessWidget {
  final AppController controller;
  final VoidCallback onOpenInventory;
  const PackInventoryStripV176({super.key, required this.controller, required this.onOpenInventory});

  @override
  Widget build(BuildContext context) {
    final rewards = controller.dailyPackHistoryV176.take(4).toList(growable: false);
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Row(children: [
            const Text('🎒', style: TextStyle(fontSize: 28)),
            const SizedBox(width: 8),
            const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text('مقتنيات الحزم', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w900)),
              Text('الجوائز المؤقتة تظهر هنا وفي المتجر مع وقت الانتهاء.', style: TextStyle(color: Colors.white60, fontSize: 10)),
            ])),
            TextButton.icon(onPressed: onOpenInventory, icon: const Icon(Icons.inventory_2_outlined, size: 18), label: const Text('عرض الكل')),
          ]),
          const SizedBox(height: 9),
          SizedBox(
            height: 92,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: rewards.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (_, index) {
                final reward = rewards[index];
                final active = controller.historyRewardActiveV176(reward);
                return Container(
                  width: 175,
                  padding: const EdgeInsets.all(10),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(17),
                    color: Colors.white.withValues(alpha: .045),
                    border: Border.all(color: active ? Colors.amberAccent.withValues(alpha: .35) : Colors.white12),
                  ),
                  child: Row(children: [
                    Text(reward['icon']?.toString() ?? '🎁', style: const TextStyle(fontSize: 29)),
                    const SizedBox(width: 8),
                    Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
                      Text(reward['label_ar']?.toString() ?? 'هدية', maxLines: 2, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 11)),
                      const SizedBox(height: 4),
                      Text(active ? controller.historyRewardStatusV176(reward) : 'انتهت الصلاحية', maxLines: 1, overflow: TextOverflow.ellipsis, style: TextStyle(color: active ? Colors.lightGreenAccent : Colors.redAccent, fontSize: 9, fontWeight: FontWeight.w800)),
                    ])),
                  ]),
                );
              },
            ),
          ),
        ]),
      ),
    );
  }
}

Future<void> showDailyPackV176(BuildContext context, AppController controller) {
  return showDialog<void>(
    context: context,
    barrierDismissible: false,
    barrierColor: Colors.black.withValues(alpha: .82),
    builder: (_) => DailyPackOpeningDialogV176(controller: controller),
  );
}

class DailyPackOpeningDialogV176 extends StatefulWidget {
  final AppController controller;
  const DailyPackOpeningDialogV176({super.key, required this.controller});

  @override
  State<DailyPackOpeningDialogV176> createState() => _DailyPackOpeningDialogV176State();
}

class _DailyPackOpeningDialogV176State extends State<DailyPackOpeningDialogV176> with SingleTickerProviderStateMixin {
  late final AnimationController animationController;
  bool opening = false;
  bool revealed = false;
  String? error;
  Map<String, dynamic>? reward;

  @override
  void initState() {
    super.initState();
    animationController = AnimationController(vsync: this, duration: const Duration(milliseconds: 1100));
  }

  @override
  void dispose() {
    animationController.dispose();
    super.dispose();
  }

  Color rarityColor(String rarity) {
    switch (rarity) {
      case 'legendary':
        return const Color(0xffffc857);
      case 'epic':
        return const Color(0xffc084fc);
      case 'rare':
        return const Color(0xff22d3ee);
      default:
        return const Color(0xff94a3b8);
    }
  }

  String rarityLabel(String rarity) {
    switch (rarity) {
      case 'legendary':
        return 'أسطورية';
      case 'epic':
        return 'ملحمية';
      case 'rare':
        return 'نادرة';
      default:
        return 'مميزة';
    }
  }

  Future<void> openPack() async {
    if (opening || !widget.controller.dailyPackAvailableV173) return;
    setState(() {
      opening = true;
      error = null;
    });
    AppSounds.fire('tap');
    unawaited(HapticFeedback.mediumImpact());
    animationController.repeat();
    await Future<void>.delayed(const Duration(milliseconds: 650));
    final openError = await widget.controller.openDailyPackV173();
    await Future<void>.delayed(const Duration(milliseconds: 900));
    if (!mounted) return;
    animationController.stop();
    if (openError != null) {
      setState(() {
        opening = false;
        error = openError;
      });
      return;
    }
    AppSounds.fire('purchase');
    unawaited(HapticFeedback.heavyImpact());
    setState(() {
      opening = false;
      revealed = true;
      reward = widget.controller.lastDailyPackRevealV176;
    });
  }

  @override
  Widget build(BuildContext context) {
    final currentReward = reward ?? widget.controller.lastDailyPackRevealV176 ?? <String, dynamic>{};
    final rarity = currentReward['rarity']?.toString() ?? 'common';
    final accent = rarityColor(rarity);
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.symmetric(horizontal: 18, vertical: 24),
      child: ConstrainedBox(
        constraints: const BoxConstraints(maxWidth: 520),
        child: Container(
          padding: const EdgeInsets.all(18),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(32),
            gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xff071827), Color(0xff15112b), Color(0xff2a1227)]),
            border: Border.all(color: (revealed ? accent : Colors.amberAccent).withValues(alpha: .5), width: 1.5),
            boxShadow: [BoxShadow(color: (revealed ? accent : Colors.purpleAccent).withValues(alpha: .28), blurRadius: 42, spreadRadius: 4)],
          ),
          child: AnimatedSwitcher(
            duration: const Duration(milliseconds: 650),
            switchInCurve: Curves.elasticOut,
            child: revealed ? _buildReward(currentReward, accent, rarity) : _buildChest(),
          ),
        ),
      ),
    );
  }

  Widget _buildChest() {
    final available = widget.controller.dailyPackAvailableV173;
    return Column(key: const ValueKey('chest'), mainAxisSize: MainAxisSize.min, children: [
      Row(children: [
        const Expanded(child: Text('الحزمة الملكية اليومية', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900))),
        IconButton(onPressed: opening ? null : () => Navigator.pop(context), icon: const Icon(Icons.close_rounded)),
      ]),
      const Text('افتحها مرة واحدة يومياً واكتشف جائزة تُضاف مباشرة إلى المتجر أو رصيدك.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white70, height: 1.5)),
      const SizedBox(height: 20),
      SizedBox(
        height: 210,
        child: Stack(alignment: Alignment.center, children: [
          for (var i = 0; i < 12; i++)
            AnimatedBuilder(
              animation: animationController,
              builder: (_, child) {
                final angle = (i / 12 * math.pi * 2) + animationController.value * math.pi * 2;
                final radius = opening ? 78.0 + 10 * math.sin(animationController.value * math.pi * 2 + i) : 72.0;
                return Transform.translate(offset: Offset(math.cos(angle) * radius, math.sin(angle) * radius), child: child);
              },
              child: Text(i.isEven ? '✦' : '•', style: TextStyle(color: i % 3 == 0 ? Colors.amberAccent : Colors.purpleAccent, fontSize: i.isEven ? 20 : 16)),
            ),
          AnimatedBuilder(
            animation: animationController,
            builder: (_, child) {
              final pulse = opening ? 1 + math.sin(animationController.value * math.pi * 4) * .08 : 1.0;
              final shake = opening ? math.sin(animationController.value * math.pi * 10) * 5 : 0.0;
              return Transform.translate(offset: Offset(shake, 0), child: Transform.scale(scale: pulse, child: child));
            },
            child: Container(
              width: 145,
              height: 145,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(34),
                gradient: const LinearGradient(colors: [Color(0xfff59e0b), Color(0xff9333ea), Color(0xffdb2777)]),
                boxShadow: [BoxShadow(color: Colors.amberAccent.withValues(alpha: .35), blurRadius: 45, spreadRadius: 3)],
              ),
              child: const Center(child: Text('🎁', style: TextStyle(fontSize: 86))),
            ),
          ),
        ]),
      ),
      if (opening) ...[
        const Text('جاري اختيار جائزتك بأمان من الخادم...', style: TextStyle(color: Colors.amberAccent, fontWeight: FontWeight.w900)),
        const SizedBox(height: 12),
        const LinearProgressIndicator(minHeight: 8),
      ] else ...[
        if (error != null) ...[
          Container(width: double.infinity, padding: const EdgeInsets.all(12), decoration: BoxDecoration(color: Colors.redAccent.withValues(alpha: .12), borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.redAccent.withValues(alpha: .35))), child: Text(error!, textAlign: TextAlign.center, style: const TextStyle(color: Colors.redAccent, fontWeight: FontWeight.w800))),
          const SizedBox(height: 12),
        ],
        FilledButton.icon(
          onPressed: available ? openPack : null,
          icon: const Icon(Icons.auto_awesome_rounded),
          label: Text(available ? 'افتح الحزمة الآن' : 'تم فتح حزمة اليوم'),
          style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(56)),
        ),
      ],
      const SizedBox(height: 12),
      Wrap(spacing: 7, runSpacing: 7, alignment: WrapAlignment.center, children: const [
        Chip(avatar: Text('🎨'), label: Text('ألوان مؤقتة')),
        Chip(avatar: Text('⚡'), label: Text('مسرّعات XP')),
        Chip(avatar: Text('🎴'), label: Text('طاولات نادرة')),
        Chip(avatar: Text('🎟️'), label: Text('تذاكر')),
      ]),
    ]);
  }

  Widget _buildReward(Map<String, dynamic> item, Color accent, String rarity) {
    final label = item['label_ar']?.toString() ?? widget.controller.dailyPackReward ?? 'هدية يومية';
    final productId = item['product_id']?.toString();
    final hasStoreItem = productId != null && productId.isNotEmpty;
    final expiryText = hasStoreItem ? widget.controller.remainingForProductV176(productId) : 'أضيفت مباشرة إلى حسابك';
    return TweenAnimationBuilder<double>(
      key: const ValueKey('reward'),
      duration: const Duration(milliseconds: 900),
      curve: Curves.elasticOut,
      tween: Tween<double>(begin: .45, end: 1),
      builder: (_, scale, child) => Transform.scale(scale: scale, child: child),
      child: Column(mainAxisSize: MainAxisSize.min, children: [
        Align(alignment: AlignmentDirectional.centerEnd, child: IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close_rounded))),
        Container(padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 7), decoration: BoxDecoration(color: accent.withValues(alpha: .14), borderRadius: BorderRadius.circular(30), border: Border.all(color: accent.withValues(alpha: .55))), child: Text('جائزة ${rarityLabel(rarity)}', style: TextStyle(color: accent, fontWeight: FontWeight.w900))),
        const SizedBox(height: 18),
        Container(width: 165, height: 165, decoration: BoxDecoration(shape: BoxShape.circle, gradient: RadialGradient(colors: [accent.withValues(alpha: .42), accent.withValues(alpha: .08), Colors.transparent]), border: Border.all(color: accent.withValues(alpha: .55), width: 2), boxShadow: [BoxShadow(color: accent.withValues(alpha: .35), blurRadius: 48)]), child: Center(child: Text(item['icon']?.toString() ?? '🎁', style: const TextStyle(fontSize: 92)))),
        const SizedBox(height: 16),
        const Text('مبروك!', style: TextStyle(fontSize: 30, fontWeight: FontWeight.w900)),
        const SizedBox(height: 7),
        Text(label, textAlign: TextAlign.center, style: TextStyle(fontSize: 18, fontWeight: FontWeight.w900, color: accent)),
        const SizedBox(height: 12),
        Container(width: double.infinity, padding: const EdgeInsets.all(13), decoration: BoxDecoration(color: Colors.white.withValues(alpha: .05), borderRadius: BorderRadius.circular(18), border: Border.all(color: Colors.white12)), child: Column(children: [
          Text(hasStoreItem ? 'تمت إضافة الجائزة إلى مقتنياتك في المتجر وتفعيلها مباشرة.' : 'تمت إضافة الجائزة مباشرة إلى رصيد حسابك.', textAlign: TextAlign.center, style: const TextStyle(color: Colors.white70, height: 1.5)),
          const SizedBox(height: 6),
          Text('⌛ $expiryText', style: const TextStyle(color: Colors.lightGreenAccent, fontWeight: FontWeight.w900)),
        ])),
        const SizedBox(height: 16),
        FilledButton.icon(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.inventory_2_rounded), label: Text(hasStoreItem ? 'العودة إلى المتجر ومشاهدة الجائزة' : 'استمرار'), style: FilledButton.styleFrom(minimumSize: const Size.fromHeight(54), backgroundColor: accent, foregroundColor: Colors.black)),
      ]),
    );
  }
}
