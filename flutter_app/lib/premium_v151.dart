part of 'main.dart';

const Map<String, Map<String, Object>> demoAccounts = <String, Map<String, Object>>{
  'adnan': <String, Object>{'password': 'Adnan123', 'name': 'Adnan', 'coins': '1000000000000000000', 'admin': true, 'level': 99},
  'kareem': <String, Object>{'password': 'Kareem123', 'name': 'كريم', 'coins': '250000', 'admin': false, 'level': 42},
  'rami': <String, Object>{'password': 'Rami12345', 'name': 'رامي', 'coins': '180000', 'admin': false, 'level': 35},
  'lina': <String, Object>{'password': 'Lina12345', 'name': 'لينا', 'coins': '120000', 'admin': false, 'level': 28},
  'samar': <String, Object>{'password': 'Samar12345', 'name': 'سمر', 'coins': '95000', 'admin': false, 'level': 24},
  'layla': <String, Object>{'password': 'Layla12345', 'name': 'ليلى', 'coins': '110000', 'admin': false, 'level': 31},
  'jameel': <String, Object>{'password': 'Jameel12345', 'name': 'جميل', 'coins': '88000', 'admin': false, 'level': 22},
  'nour': <String, Object>{'password': 'Nour12345', 'name': 'نور', 'coins': '76000', 'admin': false, 'level': 19},
  'omar': <String, Object>{'password': 'Omar12345', 'name': 'عمر', 'coins': '68000', 'admin': false, 'level': 27},
  'sara': <String, Object>{'password': 'Sara12345', 'name': 'سارة', 'coins': '72000', 'admin': false, 'level': 29},
  'basel': <String, Object>{'password': 'Basel12345', 'name': 'باسل', 'coins': '84000', 'admin': false, 'level': 33},
  'hala': <String, Object>{'password': 'Hala12345', 'name': 'هالة', 'coins': '61000', 'admin': false, 'level': 25},
  'yazan': <String, Object>{'password': 'Yazan12345', 'name': 'يزن', 'coins': '79000', 'admin': false, 'level': 30},
};

String demoAvatarFor(String username) => switch (username.trim().toLowerCase()) {
      'adnan' => '🦁',
      'kareem' => '🦅',
      'rami' => '🐺',
      'lina' => '🌹',
      'samar' => '🦋',
      'layla' => '🌙',
      'jameel' => '🐯',
      'nour' => '⭐',
      'omar' => '🛡️',
      'sara' => '👑',
      'basel' => '🔥',
      'hala' => '💎',
      'yazan' => '⚡',
      _ => '🎮',
    };


String colorToHex(Color color) => '#${(color.toARGB32() & 0xFFFFFF).toRadixString(16).padLeft(6, '0')}';

Map<String, String> decodeStringMapV151(String? raw) {
  if (raw == null || raw.trim().isEmpty) return <String, String>{};
  try {
    final decoded = jsonDecode(raw);
    if (decoded is! Map) return <String, String>{};
    return decoded.map<String, String>((key, value) => MapEntry(key.toString(), value.toString()));
  } catch (_) {
    return <String, String>{};
  }
}

Map<String, int> decodeIntMap(String? raw) {
  if (raw == null || raw.trim().isEmpty) return <String, int>{};
  try {
    final decoded = jsonDecode(raw);
    if (decoded is! Map) return <String, int>{};
    return decoded.map<String, int>((key, value) => MapEntry(key.toString(), int.tryParse(value.toString()) ?? 0));
  } catch (_) {
    return <String, int>{};
  }
}

const List<(String, String, Color)> v151ThemeOptions = <(String, String, Color)>[
  ('dark', 'ثيم غامق', Color(0xffffcf67)),
  ('light', 'ثيم فاتح', Color(0xffe2e8f0)),
  ('blue', 'ثيم أزرق', Color(0xff3b82f6)),
  ('sky', 'ثيم أزرق سماوي', Color(0xff38bdf8)),
  ('green', 'ثيم أخضر', Color(0xff10b981)),
  ('light_green', 'ثيم أخضر فاتح', Color(0xff84cc16)),
  ('gold', 'ثيم ذهبي', Color(0xfff59e0b)),
  ('purple', 'ثيم بنفسجي', Color(0xffa855f7)),
  ('light_pink', 'ثيم وردي فاتح', Color(0xfffb7185)),
];

const List<String> v151AccentColors = <String>[
  '#ffcf67', '#f59e0b', '#ef4444', '#fb7185', '#a855f7', '#6366f1', '#3b82f6', '#38bdf8', '#22d3ee', '#14b8a6', '#10b981', '#84cc16', '#e2e8f0',
];

Future<void> confirmLeaveGameV151(BuildContext context, AppController controller, String gameId) async {
  final current = controller.exitsForGame(gameId);
  final confirmed = await showDialog<bool>(
        context: context,
        builder: (dialogContext) => AlertDialog(
          title: const Text('تأكيد الخروج من اللعبة'),
          content: Text('استخدمت $current من أصل 3 مغادرات. بعد المرة الثالثة لن تتمكن من العودة إلى الجلسة نفسها.\n\nهل تريد الخروج الآن؟'),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('البقاء')),
            FilledButton.icon(
              onPressed: () => Navigator.pop(dialogContext, true),
              icon: const Icon(Icons.logout_rounded),
              label: const Text('خروج'),
              style: FilledButton.styleFrom(backgroundColor: Colors.redAccent),
            ),
          ],
        ),
      ) ??
      false;
  if (!confirmed || !context.mounted) return;
  final exits = await controller.recordGameExit(gameId);
  if (!context.mounted) return;
  Navigator.pop(context, true);
  showToast(context, exits >= 3 ? 'تم الخروج. وصلت إلى الحد الأقصى لهذه الجلسة.' : 'تم الخروج. المتبقي ${3 - exits} مرات.');
}

Future<void> inviteFriendsToRoomV151(BuildContext context, AppController controller, String gameId) async {
  final selected = <int>{};
  await showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) => Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          const Text('دعوة الأصدقاء للغرفة', style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
          const SizedBox(height: 5),
          const Text('يمكن دعوة الأصدقاء المتصلين وغير المتصلين. تصل الدعوة كإشعار Push، وعند الضغط عليها تفتح الغرفة مباشرة.', style: TextStyle(color: Colors.white60, height: 1.5)),
          const SizedBox(height: 10),
          Row(children: [
            Expanded(
              child: FilledButton.tonalIcon(
                onPressed: controller.friends.isEmpty
                    ? null
                    : () async {
                        Navigator.pop(context);
                        await inviteAllFriendsV170(context, controller);
                      },
                icon: const Icon(Icons.group_add_rounded),
                label: const FittedBox(child: Text('دعوة كل الأصدقاء')),
              ),
            ),
            const SizedBox(width: 8),
            OutlinedButton(
              onPressed: controller.friends.isEmpty
                  ? null
                  : () => setLocalState(() {
                        if (selected.length == controller.friends.length) {
                          selected.clear();
                        } else {
                          selected
                            ..clear()
                            ..addAll(controller.friends.map((friend) => friend.id));
                        }
                      }),
              child: Text(selected.length == controller.friends.length && selected.isNotEmpty ? 'إلغاء الكل' : 'تحديد الكل'),
            ),
          ]),
          const SizedBox(height: 10),
          ...controller.friends.map((friend) => CheckboxListTile(
                value: selected.contains(friend.id),
                onChanged: (value) => setLocalState(() {
                  if (value == true) {
                    selected.add(friend.id);
                  } else {
                    selected.remove(friend.id);
                  }
                }),
                secondary: Stack(clipBehavior: Clip.none, children: [
                  CircleAvatar(child: Text(friend.name.isEmpty ? '?' : friend.name.characters.first)),
                  PositionedDirectional(
                    end: -2,
                    bottom: -2,
                    child: Container(width: 12, height: 12, decoration: BoxDecoration(shape: BoxShape.circle, color: friend.online ? Colors.greenAccent : Colors.grey, border: Border.all(color: Colors.black, width: 2))),
                  ),
                ]),
                title: Text(friend.name, style: const TextStyle(fontWeight: FontWeight.w900)),
                subtitle: Text(friend.online ? 'متصل الآن • ${friend.activity}' : 'غير متصل • ستصل الدعوة كإشعار'),
              )),
          const SizedBox(height: 10),
          FilledButton.icon(
            onPressed: selected.isEmpty
                ? null
                : () async {
                    final chosen = controller.friends.where((friend) => selected.contains(friend.id)).toList();
                    Navigator.pop(context);
                    for (final friend in chosen) {
                      await inviteFriendV170(context, controller, friend);
                    }
                  },
            icon: const Icon(Icons.send_rounded),
            label: Text('إرسال ${selected.length} دعوة'),
          ),
        ],
      ),
    ),
  );
}

Future<void> showCreateGroupV151(BuildContext context, AppController controller) async {
  final name = TextEditingController();
  final description = TextEditingController();
  await showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(children: [PashaHatV173(controller: controller, width: 62, height: 42), const SizedBox(width: 8), const Expanded(child: Text('إنشاء مجموعة باشا', style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900)))]),
        const SizedBox(height: 10),
        TextField(controller: name, decoration: const InputDecoration(labelText: 'اسم المجموعة', prefixIcon: Icon(Icons.shield_outlined))),
        const SizedBox(height: 9),
        TextField(controller: description, maxLines: 3, decoration: const InputDecoration(labelText: 'الوصف والقواعد')),
        const SizedBox(height: 9),
        const _AdminInfo(text: 'المجموعة تبدأ في الدوري البرونزي بسعة 20 عضواً. ترتفع السعة والمكافآت مع المستوى، وتُجمع نقاط أسبوعية وخزينة مشتركة.'),
        const SizedBox(height: 10),
        FilledButton.icon(
          onPressed: () {
            if (name.text.trim().length < 3) { showToast(context, 'أدخل اسماً واضحاً للمجموعة.'); return; }
            controller.joinClub('custom_${DateTime.now().millisecondsSinceEpoch}');
            Navigator.pop(context);
            showToast(context, 'تم إنشاء مجموعة ${name.text.trim()} والبدء في الدوري البرونزي.');
          },
          icon: const Icon(Icons.add_circle_outline_rounded),
          label: const Text('إنشاء المجموعة'),
        ),
      ],
    ),
  );
  name.dispose();
  description.dispose();
}

Future<void> showGroupDetailV151(
  BuildContext context,
  AppController controller,
  String id,
  String icon,
  String name,
  int level,
  int members,
  int treasury,
) async {
  final memberRows = <(String, String, bool, int)>[
    (controller.displayName, controller.username, true, 940),
    ('سامر', 'Samer', true, 760),
    ('ليلى', 'Layla', true, 620),
    ('جميل', 'Jameel', false, 510),
    ('نور', 'Nour', false, 430),
  ];
  await showPremiumSheet(
    context,
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Center(child: Text(icon, style: const TextStyle(fontSize: 72))),
        Text(name, textAlign: TextAlign.center, style: const TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
        const SizedBox(height: 4),
        Text('الدوري ${level >= 30 ? 'الماسي' : level >= 20 ? 'البلاتيني' : 'الذهبي'} • المستوى $level', textAlign: TextAlign.center, style: const TextStyle(color: Colors.white60)),
        const SizedBox(height: 12),
        Row(children: [
          Expanded(child: _ClubMetric(icon: '👥', value: '$members/50', label: 'الأعضاء')),
          const SizedBox(width: 7),
          Expanded(child: _ClubMetric(icon: '🟢', value: '${memberRows.where((m) => m.$3).length}', label: 'متصل')),
          const SizedBox(width: 7),
          Expanded(child: _ClubMetric(icon: '🪙', value: formatNumber(treasury), label: 'الخزينة')),
        ]),
        const SizedBox(height: 12),
        const Text('الأعضاء والمساهمة الأسبوعية', style: TextStyle(fontSize: 16, fontWeight: FontWeight.w900)),
        const SizedBox(height: 7),
        ...memberRows.map((member) => PremiumListTile(
              icon: member.$1.substring(0, 1),
              title: member.$1,
              subtitle: '@${member.$2} • ${member.$4} نقطة مجموعة',
              action: Row(mainAxisSize: MainAxisSize.min, children: [
                Container(width: 8, height: 8, decoration: BoxDecoration(shape: BoxShape.circle, color: member.$3 ? Colors.greenAccent : Colors.white24)),
                const SizedBox(width: 5),
                Text(member.$3 ? 'متصل' : 'غير متصل', style: TextStyle(fontSize: 9, color: member.$3 ? Colors.greenAccent : Colors.white54)),
              ]),
            )),
        const SizedBox(height: 10),
        const _AdminInfo(text: 'تُحسب نقاط المجموعة من الفوز والنشاط. اللعب مع أعضاء المجموعة يمنح مضاعف فريق، وتوزّع الخزينة وفق الصلاحيات والمساهمة.'),
      ],
    ),
  );
}

Future<void> showCreateCompetitionV151(BuildContext context, AppController controller) async {
  var game = 'tarneeb';
  var stages = 2;
  var entryFee = 100;
  final name = TextEditingController(text: 'منافسة ${controller.displayName}');
  await showPremiumSheet(
    context,
    child: StatefulBuilder(
      builder: (context, setLocalState) {
        final seatsPerMatch = game == 'basra' ? 2 : 4;
        final totalPlayers = seatsPerMatch * math.pow(2, stages - 1).toInt();
        final gross = totalPlayers * entryFee;
        final houseFee = (gross * .05).round();
        final prizePool = gross - houseFee;
        return Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(children: [PashaHatV173(controller: controller, width: 62, height: 42), const SizedBox(width: 8), const Expanded(child: Text('إنشاء منافسة', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)))]),
            const SizedBox(height: 10),
            TextField(controller: name, decoration: const InputDecoration(labelText: 'اسم المنافسة')),
            const SizedBox(height: 9),
            DropdownButtonFormField<String>(
              initialValue: game,
              decoration: const InputDecoration(labelText: 'اللعبة'),
              items: gamesCatalog.map((item) => DropdownMenuItem(value: item.id, child: Text('${item.icon} ${L.t(controller.localeCode, item.id)}'))).toList(),
              onChanged: (value) => setLocalState(() => game = value ?? game),
            ),
            const SizedBox(height: 9),
            DropdownButtonFormField<int>(
              initialValue: stages,
              decoration: const InputDecoration(labelText: 'عدد المراحل'),
              items: const [1, 2, 3, 4].map((value) => DropdownMenuItem(value: value, child: Text('$value مرحلة'))).toList(),
              onChanged: (value) => setLocalState(() => stages = value ?? stages),
            ),
            const SizedBox(height: 9),
            DropdownButtonFormField<int>(
              initialValue: entryFee,
              decoration: const InputDecoration(labelText: 'رسوم الدخول'),
              items: const [0, 100, 500, 1000, 5000].map((value) => DropdownMenuItem(value: value, child: Text('$value توكن'))).toList(),
              onChanged: (value) => setLocalState(() => entryFee = value ?? entryFee),
            ),
            const SizedBox(height: 12),
            PremiumPanel(
              child: Padding(
                padding: const EdgeInsets.all(13),
                child: Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    Chip(label: Text('$totalPlayers لاعب')),
                    Chip(label: Text('$seatsPerMatch مقعد/مباراة')),
                    Chip(label: Text('خروج مغلوب')),
                    Chip(label: Text('صندوق ${formatNumber(prizePool)}')),
                    Chip(label: Text('رسوم إدارة ${formatNumber(houseFee)}')),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 9),
            const Text('التوزيع المقترح: الأول 60%، الثاني 30%، والمربع الذهبي أو رسوم الإدارة 10%. تتغير القيم حسب حجم المنافسة.', style: TextStyle(color: Colors.white60, fontSize: 10, height: 1.5)),
            const SizedBox(height: 12),
            FilledButton.icon(
              onPressed: () {
                final id = 'custom_comp_${DateTime.now().millisecondsSinceEpoch}';
                if (!controller.joinCompetition(id)) { showToast(context, 'غادر المنافسة الحالية أولاً.'); return; }
                Navigator.pop(context);
                showToast(context, 'تم إنشاء المنافسة وحجز $totalPlayers مقعداً بنظام خروج المغلوب.');
              },
              icon: const Icon(Icons.emoji_events_outlined),
              label: const Text('إنشاء ونشر المنافسة'),
            ),
          ],
        );
      },
    ),
  );
  name.dispose();
}

class AdminStoreStudioV151 extends StatefulWidget {
  final AppController controller;
  const AdminStoreStudioV151({super.key, required this.controller});

  @override
  State<AdminStoreStudioV151> createState() => _AdminStoreStudioV151State();
}

class _AdminStoreStudioV151State extends State<AdminStoreStudioV151> {
  String group = 'visual';
  String category = 'tables';
  String query = '';
  int page = 0;
  static const int pageSize = 12;

  static const groups = <(String, String, IconData, List<String>)>[
    ('visual', 'المظهر واللعب', Icons.palette_outlined, ['tables', 'cards', 'themes']),
    ('identity', 'هوية اللاعب', Icons.account_circle_outlined, ['names', 'chat_colors', 'covers', 'badges', 'effects']),
    ('rewards', 'المكافآت والعضويات', Icons.workspace_premium_outlined, ['pasha', 'boost', 'competition_ticket']),
    ('social', 'التفاعل', Icons.emoji_emotions_outlined, ['emoji']),
  ];

  static const categoryLabels = <String, String>{
    'pasha': 'الباشا',
    'themes': 'الثيمات',
    'tables': 'الطاولات',
    'cards': 'ظهر الورق',
    'emoji': 'الإيموجز',
    'boost': 'المسرعات',
    'competition_ticket': 'تذاكر المنافسات',
    'names': 'ألوان اللاعب',
    'chat_colors': 'ألوان الدردشة',
    'covers': 'الأغلفة',
    'badges': 'الشارات',
    'effects': 'المؤثرات',
  };

  List<String> get activeCategories => groups.firstWhere((item) => item.$1 == group).$4;

  void selectGroup(String value) {
    final selected = groups.firstWhere((item) => item.$1 == value);
    setState(() {
      group = value;
      category = selected.$4.first;
      page = 0;
    });
  }

  @override
  Widget build(BuildContext context) {
    final normalizedQuery = query.trim().toLowerCase();
    final items = products.where((product) {
      if (product.category != category) return false;
      if (product.id.startsWith('table_reference_')) return false;
      if (normalizedQuery.isEmpty) return true;
      return product.nameAr.toLowerCase().contains(normalizedQuery) ||
          product.nameEn.toLowerCase().contains(normalizedQuery) ||
          product.id.toLowerCase().contains(normalizedQuery);
    }).toList();
    final pageCount = math.max(1, (items.length / pageSize).ceil());
    final safePage = page.clamp(0, pageCount - 1).toInt();
    final from = safePage * pageSize;
    final visibleItems = items.skip(from).take(pageSize).toList();

    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.fromLTRB(12, 12, 12, 8),
          child: Column(
            children: [
              const _AdminInfo(text: 'إدارة المتجر مقسمة إلى مجموعات وتبويبات وصفحات قصيرة. لن تحتاج إلى النزول لمسافات طويلة، ويمكنك الوصول لأي فئة أو عنصر بسرعة.'),
              const SizedBox(height: 10),
              Wrap(
                alignment: WrapAlignment.center,
                spacing: 7,
                runSpacing: 7,
                children: groups.map((item) => ChoiceChip(
                  avatar: Icon(item.$3, size: 17),
                  label: Text(item.$2, style: const TextStyle(fontWeight: FontWeight.w900)),
                  selected: group == item.$1,
                  onSelected: (_) => selectGroup(item.$1),
                )).toList(),
              ),
              const SizedBox(height: 9),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(7),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: .035),
                  borderRadius: BorderRadius.circular(16),
                  border: Border.all(color: Colors.white.withValues(alpha: .07)),
                ),
                child: Wrap(
                  alignment: WrapAlignment.center,
                  spacing: 6,
                  runSpacing: 6,
                  children: activeCategories.map((value) => ChoiceChip(
                    label: Text(categoryLabels[value] ?? value, style: const TextStyle(fontWeight: FontWeight.w900)),
                    selected: category == value,
                    onSelected: (_) => setState(() { category = value; page = 0; }),
                  )).toList(),
                ),
              ),
              const SizedBox(height: 9),
              Row(children: [
                Expanded(
                  child: TextField(
                    onChanged: (value) => setState(() { query = value; page = 0; }),
                    decoration: const InputDecoration(prefixIcon: Icon(Icons.search), hintText: 'ابحث بالاسم أو المعرّف...'),
                  ),
                ),
                const SizedBox(width: 8),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 11, vertical: 12),
                  decoration: BoxDecoration(color: Theme.of(context).colorScheme.primary.withValues(alpha: .12), borderRadius: BorderRadius.circular(14)),
                  child: Text('${items.length} عنصر', style: const TextStyle(fontWeight: FontWeight.w900)),
                ),
              ]),
            ],
          ),
        ),
        Expanded(
          child: visibleItems.isEmpty
              ? const Center(child: Text('لا توجد عناصر مطابقة في هذا التبويب.', style: TextStyle(color: Colors.white54)))
              : LayoutBuilder(
                  builder: (context, constraints) {
                    final columns = constraints.maxWidth >= 1500 ? 6 : constraints.maxWidth >= 1180 ? 5 : constraints.maxWidth >= 900 ? 4 : constraints.maxWidth >= 620 ? 3 : 2;
                    return GridView.builder(
                      padding: const EdgeInsets.fromLTRB(12, 0, 12, 10),
                      gridDelegate: SliverGridDelegateWithFixedCrossAxisCount(
                        crossAxisCount: columns,
                        crossAxisSpacing: 10,
                        mainAxisSpacing: 10,
                        childAspectRatio: columns <= 2 ? .64 : columns >= 5 ? .88 : .78,
                      ),
                      itemCount: visibleItems.length,
                      itemBuilder: (_, index) => _AdminStoreItemCard(
                        controller: widget.controller,
                        product: visibleItems[index],
                        onChanged: () => setState(() {}),
                      ),
                    );
                  },
                ),
        ),
        if (items.length > pageSize)
          SafeArea(
            top: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(12, 2, 12, 10),
              child: Row(children: [
                IconButton.filledTonal(
                  onPressed: safePage > 0 ? () => setState(() => page = safePage - 1) : null,
                  icon: const Icon(Icons.chevron_right_rounded),
                  tooltip: 'الصفحة السابقة',
                ),
                Expanded(
                  child: Text(
                    'صفحة ${safePage + 1} من $pageCount  •  العناصر ${from + 1}–${math.min(from + visibleItems.length, items.length)}',
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 11),
                  ),
                ),
                IconButton.filledTonal(
                  onPressed: safePage + 1 < pageCount ? () => setState(() => page = safePage + 1) : null,
                  icon: const Icon(Icons.chevron_left_rounded),
                  tooltip: 'الصفحة التالية',
                ),
              ]),
            ),
          ),
      ],
    );
  }
}

class _AdminStoreItemCard extends StatelessWidget {
  final AppController controller;
  final StoreProduct product;
  final VoidCallback onChanged;
  const _AdminStoreItemCard({required this.controller, required this.product, required this.onChanged});

  @override
  Widget build(BuildContext context) {
    final visible = controller.isStoreProductVisible(product);
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(10),
        child: Column(
          children: [
            Expanded(child: InkWell(onTap: () => showProductPreview(context, controller, product), child: Center(child: _CompactProductPreview(controller: controller, product: product)))),
            const SizedBox(height: 7),
            Text(controller.nameFor(product, 'ar'), maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900)),
            Text('${formatNumber(controller.priceFor(product))} توكن', style: TextStyle(color: Theme.of(context).colorScheme.primary, fontWeight: FontWeight.w900)),
            const SizedBox(height: 7),
            Row(children: [
              Expanded(child: OutlinedButton.icon(onPressed: () => _edit(context), icon: const Icon(Icons.edit_outlined, size: 17), label: const Text('تعديل'))),
              const SizedBox(width: 5),
              IconButton.filledTonal(onPressed: () async { await controller.updateStoreProductAdmin(product, visible: !visible); onChanged(); }, tooltip: visible ? 'إخفاء' : 'إظهار', icon: Icon(visible ? Icons.visibility : Icons.visibility_off)),
            ]),
          ],
        ),
      ),
    );
  }

  Future<void> _edit(BuildContext context) async {
    final price = TextEditingController(text: controller.priceFor(product).toString());
    final name = TextEditingController(text: controller.nameFor(product, 'ar'));
    final description = TextEditingController(text: controller.descriptionFor(product, 'ar'));
    final duration = TextEditingController(text: (controller.durationFor(product) ?? 0).toString());
    final color1 = TextEditingController(text: colorToHex(controller.color1For(product)));
    final color2 = TextEditingController(text: colorToHex(controller.color2For(product)));
    final saved = await showDialog<bool>(
          context: context,
          builder: (dialogContext) => Dialog(
            insetPadding: const EdgeInsets.all(18),
            child: ConstrainedBox(
              constraints: const BoxConstraints(maxWidth: 720, maxHeight: 780),
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Text('تعديل ${controller.nameFor(product, 'ar')}', style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
                    const SizedBox(height: 12),
                    SizedBox(height: 210, child: _ProductLivePreview(controller: controller, product: product)),
                    const SizedBox(height: 12),
                    TextField(controller: name, decoration: const InputDecoration(labelText: 'اسم العنصر الظاهر', prefixIcon: Icon(Icons.title_rounded))),
                    const SizedBox(height: 9),
                    TextField(controller: description, minLines: 2, maxLines: 4, decoration: const InputDecoration(labelText: 'الوصف', prefixIcon: Icon(Icons.description_outlined))),
                    const SizedBox(height: 9),
                    Row(children: [
                      Expanded(child: TextField(controller: price, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'السعر بالتوكنز', prefixIcon: Icon(Icons.toll_rounded)))),
                      const SizedBox(width: 8),
                      Expanded(child: TextField(controller: duration, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'المدة بالأيام (0 = دائم)', prefixIcon: Icon(Icons.timer_outlined)))),
                    ]),
                    const SizedBox(height: 9),
                    Row(children: [
                      Expanded(child: TextField(controller: color1, decoration: const InputDecoration(labelText: 'اللون الأول HEX', prefixIcon: Icon(Icons.color_lens_outlined)))),
                      const SizedBox(width: 8),
                      Expanded(child: TextField(controller: color2, decoration: const InputDecoration(labelText: 'اللون الثاني HEX', prefixIcon: Icon(Icons.gradient_outlined)))),
                    ]),
                    const SizedBox(height: 10),
                    Text('المعرّف: ${product.id} • الفئة: ${product.category}', style: const TextStyle(color: Colors.white60, fontSize: 10)),
                    const SizedBox(height: 14),
                    Row(children: [
                      Expanded(child: OutlinedButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('إلغاء'))),
                      const SizedBox(width: 8),
                      Expanded(child: FilledButton.icon(onPressed: () => Navigator.pop(dialogContext, true), icon: const Icon(Icons.save_outlined), label: const Text('حفظ وتطبيق'))),
                    ]),
                  ],
                ),
              ),
            ),
          ),
        ) ??
        false;
    if (saved) {
      await controller.updateStoreProductAdmin(
        product,
        price: int.tryParse(price.text) ?? controller.priceFor(product),
        name: name.text,
        description: description.text,
        durationDays: int.tryParse(duration.text) ?? (controller.durationFor(product) ?? 0),
        color1: color1.text,
        color2: color2.text,
      );
      onChanged();
      if (context.mounted) showToast(context, 'تم حفظ جميع خصائص العنصر وتطبيقها مباشرة.');
    }
    price.dispose();
    name.dispose();
    description.dispose();
    duration.dispose();
    color1.dispose();
    color2.dispose();
  }
}

String localizeStoreProductNameV151(StoreProduct product, String lang) {
  if (lang == 'ar') return product.nameAr;
  if (lang == 'en') return product.nameEn;
  final days = product.durationDays ?? 0;
  final number = RegExp(r'\d+').firstMatch(product.nameEn)?.group(0) ?? RegExp(r'\d+').firstMatch(product.id)?.group(0) ?? '';
  final labels = <String, Map<String, String>>{
    'de': {'pasha':'Pasha', 'days':'Tage', 'table':'Premium-Tisch', 'cards':'Kartenrückseite', 'emoji':'Emoji-Paket', 'boost':'XP-Booster', 'names':'Spieler-Glow', 'chat_colors':'Chatfarbe', 'covers':'Profil-Cover', 'themes':'Premium-Theme', 'badges':'Abzeichen', 'effects':'Siegeseffekt'},
    'tr': {'pasha':'Paşa', 'days':'Gün', 'table':'Premium Masa', 'cards':'Kart Arkası', 'emoji':'Emoji Paketi', 'boost':'XP Hızlandırıcı', 'names':'Oyuncu Parıltısı', 'chat_colors':'Sohbet Rengi', 'covers':'Profil Kapağı', 'themes':'Premium Tema', 'badges':'Rozet', 'effects':'Zafer Efekti'},
    'fr': {'pasha':'Pacha', 'days':'jours', 'table':'Table premium', 'cards':'Dos de carte', 'emoji':"Pack d'émojis", 'boost':'Boost XP', 'names':'Halo du joueur', 'chat_colors':'Couleur du chat', 'covers':'Couverture de profil', 'themes':'Thème premium', 'badges':'Badge', 'effects':'Effet de victoire'},
    'es': {'pasha':'Pasha', 'days':'días', 'table':'Mesa prémium', 'cards':'Dorso de carta', 'emoji':'Paquete de emojis', 'boost':'Potenciador XP', 'names':'Brillo del jugador', 'chat_colors':'Color del chat', 'covers':'Portada de perfil', 'themes':'Tema prémium', 'badges':'Insignia', 'effects':'Efecto de victoria'},
  };
  final map = labels[lang];
  if (map == null) return product.nameEn;
  if (product.category == 'pasha') return '${map['pasha']} $days ${map['days']}';
  final base = map[product.category] ?? product.nameEn;
  return number.isEmpty ? base : '$base $number';
}

String localizeStoreProductDescriptionV151(StoreProduct product, String lang) {
  if (lang == 'ar') return product.descriptionAr;
  if (lang == 'en') return product.descriptionEn;
  const descriptions = <String, Map<String, String>>{
    'de': {
      'pasha':'Aktiviert Pasha-Abzeichen, Raumsteuerung, Wettbewerbe und zusätzlichen XP-Bonus.',
      'tables':'Eine hochwertige Tischgestaltung mit direkter Vorschau und Aktivierung im Spiel.',
      'cards':'Ein Kartenrücken, der sofort in der Hand und auf dem Spieltisch angewendet wird.',
      'emoji':'Ein Reaktionspaket für Spiel- und Freundeschats, einschließlich sanfter Animationen.',
      'boost':'Erhöht die verdienten Erfahrungspunkte während der angegebenen Laufzeit.',
      'names':'Farbe und leuchtender Rahmen für Spielername und Profilbild.',
      'chat_colors':'Eine deutlich sichtbare Farbe für Nachrichten im Spiel- und Freundeschat.',
      'covers':'Eine exklusive Profilabdeckung mit Live-Vorschau.',
      'themes':'Wendet Farben, Hintergründe, Karten und Schaltflächen auf die gesamte App an.',
      'badges':'Ein Sammlerabzeichen für Profil und Spielraum.',
      'effects':'Ein dezenter animierter Effekt für Sieg und Auftritt.',
    },
    'tr': {
      'pasha':'Paşa rozeti, oda kontrolleri, yarışmalar ve ek XP avantajını etkinleştirir.',
      'tables':'Canlı önizlemeli, oyun içinde doğrudan etkinleşen premium masa tasarımı.',
      'cards':'Elde ve oyun masasında anında uygulanan özel kart arkası.',
      'emoji':'Oyun ve arkadaş sohbetlerinde kullanılabilen, bazıları animasyonlu tepki paketi.',
      'boost':'Belirlenen süre boyunca kazanılan deneyim puanlarını artırır.',
      'names':'Oyuncu adı ve profil resmi için renkli, parlak çerçeve.',
      'chat_colors':'Oyun ve arkadaş sohbeti mesajları için seçkin yazı rengi.',
      'covers':'Canlı önizlemeli özel profil kapağı.',
      'themes':'Renkleri, arka planları, kartları ve düğmeleri tüm uygulamaya uygular.',
      'badges':'Profil ve oyun odası için koleksiyon rozeti.',
      'effects':'Zafer ve giriş için hafif animasyonlu efekt.',
    },
    'fr': {
      'pasha':'Active le badge Pacha, les contrôles de salle, les compétitions et un bonus XP.',
      'tables':'Une table premium avec aperçu direct et activation immédiate en partie.',
      'cards':'Un dos de carte appliqué instantanément à la main et à la table.',
      'emoji':"Un pack de réactions pour les chats de partie et d'amis, avec animations douces.",
      'boost':"Augmente l'expérience gagnée pendant la durée indiquée.",
      'names':'Une couleur et un halo lumineux pour le nom et la photo du joueur.',
      'chat_colors':'Une couleur lisible et distinctive pour les messages de chat.',
      'covers':'Une couverture de profil exclusive avec aperçu en direct.',
      'themes':"Applique les couleurs, arrière-plans, cartes et boutons à toute l'application.",
      'badges':'Un badge de collection visible sur le profil et dans la salle.',
      'effects':'Un effet animé discret pour les victoires et les entrées.',
    },
    'es': {
      'pasha':'Activa la insignia Pasha, controles de sala, competiciones y bonificación de XP.',
      'tables':'Una mesa prémium con vista previa y activación inmediata dentro de la partida.',
      'cards':'Un dorso de carta aplicado al instante en la mano y la mesa.',
      'emoji':'Un paquete de reacciones para chats de partida y amigos, con animaciones suaves.',
      'boost':'Aumenta la experiencia obtenida durante el periodo indicado.',
      'names':'Color y marco luminoso para el nombre y la foto del jugador.',
      'chat_colors':'Un color claro y distintivo para los mensajes del chat.',
      'covers':'Una portada de perfil exclusiva con vista previa en vivo.',
      'themes':'Aplica colores, fondos, tarjetas y botones a toda la aplicación.',
      'badges':'Una insignia coleccionable para el perfil y la sala.',
      'effects':'Un efecto animado sutil para victorias y entradas.',
    },
  };
  return descriptions[lang]?[product.category] ?? product.descriptionEn;
}

class PashaStatCardV151 extends StatelessWidget {
  final AppController controller;
  final VoidCallback onTap;
  const PashaStatCardV151({super.key, required this.controller, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(18),
      child: PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Row(
            children: [
              PashaHatV173(controller: controller, width: 44, height: 31),
              const SizedBox(width: 7),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text(L.t(controller.localeCode, 'vip'), maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 9, color: Colors.white60)),
                    const SizedBox(height: 3),
                    Text('${controller.vipDays} ${L.t(controller.localeCode, 'days')}', maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w900, color: Color(0xffffcf67))),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

List<Color>? coverColorsForV151(AppController controller, String coverId) {
  final product = storeProductById(coverId);
  if (product == null) return null;
  return <Color>[controller.color1For(product), controller.color2For(product)];
}

String authTextV151(String lang, String key) {
  const values = <String, Map<String, String>>{
    'ar': {
      'tagline':'منصة الألعاب الورقية الاجتماعية', 'newAccount':'إنشاء حساب جديد', 'login':'تسجيل الدخول',
      'registerSubtitle':'أنشئ ملفك وابدأ اللعب مجاناً', 'loginSubtitle':'ادخل إلى حسابك وتابع تقدمك',
      'username':'اسم المستخدم', 'userOrEmail':'اسم المستخدم أو البريد الإلكتروني', 'email':'البريد الإلكتروني', 'password':'كلمة المرور',
      'create':'إنشاء الحساب', 'secure':'دخول آمن', 'chooseDemo':'اختيار حساب لاعب جاهز',
      'fallback':'يفتح الحساب المحلي تلقائياً عندما لا يكون خادم Laravel متاحاً.', 'orVia':'أو المتابعة عبر',
      'guest':'الدخول كضيف', 'providerNote':'الدخول المحلي والضيف يعملان دون خادم. تسجيل Google وApple وFacebook يحتاج مفاتيح الخدمة قبل النشر.',
      'haveAccount':'لديك حساب؟ سجّل الدخول', 'noAccount':'ليس لديك حساب؟ أنشئ حساباً',
    },
    'en': {
      'tagline':'Social card games platform', 'newAccount':'Create a new account', 'login':'Sign in',
      'registerSubtitle':'Create your profile and start playing free', 'loginSubtitle':'Access your account and continue your progress',
      'username':'Username', 'userOrEmail':'Username or email', 'email':'Email', 'password':'Password',
      'create':'Create account', 'secure':'Secure sign in', 'chooseDemo':'Choose a ready player account',
      'fallback':'A local account opens automatically when the Laravel server is unavailable.', 'orVia':'or continue with',
      'guest':'Continue as guest', 'providerNote':'Local and guest access work without a server. Google, Apple and Facebook require provider credentials before release.',
      'haveAccount':'Already have an account? Sign in', 'noAccount':'No account yet? Create one',
    },
    'de': {
      'tagline':'Soziale Kartenspiel-Plattform', 'newAccount':'Neues Konto erstellen', 'login':'Anmelden',
      'registerSubtitle':'Profil erstellen und kostenlos spielen', 'loginSubtitle':'Konto öffnen und Fortschritt fortsetzen',
      'username':'Benutzername', 'userOrEmail':'Benutzername oder E-Mail', 'email':'E-Mail', 'password':'Passwort',
      'create':'Konto erstellen', 'secure':'Sicher anmelden', 'chooseDemo':'Fertiges Spielerkonto wählen',
      'fallback':'Wenn Laravel nicht erreichbar ist, wird automatisch das lokale Konto geöffnet.', 'orVia':'oder weiter mit',
      'guest':'Als Gast fortfahren', 'providerNote':'Lokale und Gastkonten funktionieren ohne Server. Google, Apple und Facebook benötigen Zugangsdaten.',
      'haveAccount':'Konto vorhanden? Anmelden', 'noAccount':'Noch kein Konto? Erstellen',
    },
    'tr': {
      'tagline':'Sosyal kart oyunları platformu', 'newAccount':'Yeni hesap oluştur', 'login':'Giriş yap',
      'registerSubtitle':'Profilini oluştur ve ücretsiz oynamaya başla', 'loginSubtitle':'Hesabına gir ve ilerlemene devam et',
      'username':'Kullanıcı adı', 'userOrEmail':'Kullanıcı adı veya e-posta', 'email':'E-posta', 'password':'Şifre',
      'create':'Hesap oluştur', 'secure':'Güvenli giriş', 'chooseDemo':'Hazır oyuncu hesabı seç',
      'fallback':'Laravel sunucusu yoksa yerel hesap otomatik açılır.', 'orVia':'veya şununla devam et',
      'guest':'Misafir olarak devam et', 'providerNote':'Yerel ve misafir girişleri sunucusuz çalışır. Google, Apple ve Facebook için sağlayıcı anahtarları gerekir.',
      'haveAccount':'Hesabın var mı? Giriş yap', 'noAccount':'Hesabın yok mu? Oluştur',
    },
    'fr': {
      'tagline':'Plateforme sociale de jeux de cartes', 'newAccount':'Créer un nouveau compte', 'login':'Connexion',
      'registerSubtitle':'Créez votre profil et jouez gratuitement', 'loginSubtitle':'Accédez à votre compte et poursuivez votre progression',
      'username':"Nom d'utilisateur", 'userOrEmail':"Nom d'utilisateur ou e-mail", 'email':'E-mail', 'password':'Mot de passe',
      'create':'Créer le compte', 'secure':'Connexion sécurisée', 'chooseDemo':'Choisir un compte joueur prêt',
      'fallback':'Le compte local s’ouvre automatiquement si Laravel est indisponible.', 'orVia':'ou continuer avec',
      'guest':'Continuer comme invité', 'providerNote':'Les accès local et invité fonctionnent sans serveur. Google, Apple et Facebook exigent leurs identifiants.',
      'haveAccount':'Vous avez un compte ? Connexion', 'noAccount':'Pas encore de compte ? Créez-en un',
    },
    'es': {
      'tagline':'Plataforma social de juegos de cartas', 'newAccount':'Crear una cuenta nueva', 'login':'Iniciar sesión',
      'registerSubtitle':'Crea tu perfil y empieza a jugar gratis', 'loginSubtitle':'Accede a tu cuenta y continúa tu progreso',
      'username':'Usuario', 'userOrEmail':'Usuario o correo', 'email':'Correo', 'password':'Contraseña',
      'create':'Crear cuenta', 'secure':'Inicio seguro', 'chooseDemo':'Elegir una cuenta de jugador',
      'fallback':'La cuenta local se abre automáticamente si Laravel no está disponible.', 'orVia':'o continuar con',
      'guest':'Continuar como invitado', 'providerNote':'El acceso local y de invitado funciona sin servidor. Google, Apple y Facebook requieren credenciales.',
      'haveAccount':'¿Ya tienes cuenta? Inicia sesión', 'noAccount':'¿No tienes cuenta? Crea una',
    },
  };
  return values[lang]?[key] ?? values['en']?[key] ?? key;
}
