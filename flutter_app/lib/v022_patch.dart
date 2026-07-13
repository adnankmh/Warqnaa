part of 'main.dart';

/// V0.2.2 focuses on economy consistency, cleaner navigation, configurable
/// home games, original red Pasha branding, and delegated administration.
const Set<String> freeThemeCodesV022 = <String>{
  'dark', 'light', 'blue', 'sky', 'green', 'light_green', 'gold', 'purple', 'light_pink',
};

String themeCategoryV022(StoreProduct product) {
  final value = (product.value ?? product.id).toLowerCase();
  if (freeThemeCodesV022.contains(value)) return 'included';
  if (value.contains('forest') || value.contains('desert') || value.contains('ice') || value.contains('aurora')) return 'nature';
  if (value.contains('gold') || value.contains('classic') || value.contains('obsidian')) return 'luxury';
  return 'premium';
}

class StoreSubcategoryTabsV022 extends StatelessWidget {
  final String selected;
  final ValueChanged<String> onChanged;
  const StoreSubcategoryTabsV022({super.key, required this.selected, required this.onChanged});

  @override
  Widget build(BuildContext context) => PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(10),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('تصنيفات الثيمات', style: TextStyle(fontWeight: FontWeight.w900)),
            const SizedBox(height: 8),
            Wrap(
              spacing: 7,
              runSpacing: 7,
              children: const <(String, String)>[
                ('all', 'الكل'),
                ('included', 'مضمنة'),
                ('premium', 'مميزة'),
                ('luxury', 'فاخرة'),
                ('nature', 'طبيعة'),
              ].map((entry) => ChoiceChip(
                    label: Text(entry.$2, style: const TextStyle(fontWeight: FontWeight.w900)),
                    selected: selected == entry.$1,
                    onSelected: (_) => onChanged(entry.$1),
                  )).toList(),
            ),
          ]),
        ),
      );
}

GameInfo? gameByIdV022(String id) {
  for (final game in gamesCatalog) { if (game.id == id) return game; }
  return null;
}

List<GameInfo> homeGamesV022(AppController controller) {
  final selected = controller.homeGameIdsV022
      .map(gameByIdV022)
      .whereType<GameInfo>()
      .take(4)
      .toList();
  return selected.isEmpty ? gamesCatalog.take(1).toList() : selected;
}

Future<void> showHomeGamePickerV022(BuildContext context, AppController controller) async {
  final selected = controller.homeGameIdsV022.toSet();
  await showPremiumSheet(
    context,
    child: StatefulBuilder(builder: (context, setLocalState) {
      return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        const Text('ألعاب الصفحة الرئيسية', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900)),
        const SizedBox(height: 5),
        const Text('اختر من لعبة واحدة إلى أربع ألعاب. يتم تطبيق الاختيار فوراً.', style: TextStyle(color: Colors.white60)),
        const SizedBox(height: 12),
        ...gamesCatalog.map((game) => CheckboxListTile(
              value: selected.contains(game.id),
              secondary: Text(game.icon, style: const TextStyle(fontSize: 26)),
              title: Text(L.t(controller.localeCode, game.id), style: const TextStyle(fontWeight: FontWeight.w900)),
              subtitle: Text(selected.contains(game.id) ? 'ظاهرة في الرئيسية' : 'غير ظاهرة', style: const TextStyle(fontSize: 10)),
              onChanged: (value) {
                if (value == true && selected.length >= 4) {
                  showToast(context, 'يمكن اختيار أربع ألعاب كحد أقصى.');
                  return;
                }
                if (value == false && selected.length <= 1) {
                  showToast(context, 'يجب إبقاء لعبة واحدة على الأقل.');
                  return;
                }
                setLocalState(() => value == true ? selected.add(game.id) : selected.remove(game.id));
              },
            )),
        const SizedBox(height: 10),
        FilledButton.icon(
          onPressed: () async {
            controller.homeGameIdsV022
              ..clear()
              ..addAll(selected.take(4));
            await controller.persistHomeGamesV022();
            if (context.mounted) Navigator.pop(context);
          },
          icon: const Icon(Icons.check_circle_outline),
          label: const Text('اعتماد الألعاب'),
        ),
      ]);
    }),
  );
}

extension AppControllerV022 on AppController {
  Future<void> persistHomeGamesV022() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList('homeGameIdsV022', homeGameIdsV022.take(4).toList());
    refreshUi();
  }

  Future<void> loadHomeGamesV022() async {
    final prefs = await SharedPreferences.getInstance();
    final stored = prefs.getStringList('homeGameIdsV022') ?? const <String>[];
    final valid = stored.where((id) => gamesCatalog.any((game) => game.id == id)).take(4).toList();
    if (valid.isNotEmpty) {
      homeGameIdsV022
        ..clear()
        ..addAll(valid);
    }
  }
}

class ClubIdentityV022 extends StatelessWidget {
  final String? name;
  final String? logo;
  final int level;
  const ClubIdentityV022({super.key, required this.name, required this.logo, this.level = 1});

  @override
  Widget build(BuildContext context) {
    if (name == null || name!.trim().isEmpty) return const SizedBox.shrink();
    return PremiumPanel(
      child: Padding(
        padding: const EdgeInsets.all(11),
        child: Row(children: [
          CircleAvatar(
            radius: 24,
            backgroundColor: Theme.of(context).colorScheme.primary.withValues(alpha: .18),
            backgroundImage: logo != null && logo!.startsWith('http') ? NetworkImage(logo!) : null,
            child: logo == null || !logo!.startsWith('http') ? Text(logo?.isNotEmpty == true ? logo! : '🛡️', style: const TextStyle(fontSize: 24)) : null,
          ),
          const SizedBox(width: 10),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Text('النادي الحالي', style: TextStyle(color: Colors.white54, fontSize: 9)),
            Text(name!, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)),
            Text('مستوى النادي $level', style: const TextStyle(color: Colors.white60, fontSize: 10)),
          ])),
          const Icon(Icons.verified_rounded, color: Colors.amber),
        ]),
      ),
    );
  }
}

Future<void> showLeaderboardV022(BuildContext context, AppController controller) async {
  final entries = <LocalFriend>[
    LocalFriend(6, 'ياسر', 'Yasser', online: true, activity: 'المركز الأول', level: 64, xp: 18420, xpNext: xpNeededForLevel(64), gamesPlayed: 1870, wins: 1210, badge: 'LEGEND'),
    LocalFriend(3, 'ليلى', 'Layla', online: true, activity: 'المركز الثاني', level: 61, xp: 17980, xpNext: xpNeededForLevel(61), gamesPlayed: 1640, wins: 1044, badge: 'PRO'),
    LocalFriend(2, 'سامر', 'Samer', online: true, activity: 'المركز الثالث', level: 58, xp: 16800, xpNext: xpNeededForLevel(58), gamesPlayed: 1530, wins: 930, badge: 'PRO'),
    LocalFriend(9, 'أحمد', 'Ahmad', online: false, activity: 'المركز الرابع', level: 55, xp: 15120, xpNext: xpNeededForLevel(55), gamesPlayed: 1420, wins: 810),
  ];
  await showPremiumSheet(
    context,
    child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      Row(children: [
        Container(width: 58, height: 58, alignment: Alignment.center, decoration: BoxDecoration(shape: BoxShape.circle, color: Colors.amber.withValues(alpha: .15)), child: const Text('🏆', style: TextStyle(fontSize: 31))),
        const SizedBox(width: 10),
        const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text('لوحة الصدارة العالمية', style: TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
          Text('موسمية • أسبوعية • أندية • كل لعبة', style: TextStyle(color: Colors.white60, fontSize: 10)),
        ])),
      ]),
      const SizedBox(height: 12),
      SegmentedButton<String>(
        segments: const [
          ButtonSegment(value: 'global', label: Text('العالمية'), icon: Icon(Icons.public)),
          ButtonSegment(value: 'weekly', label: Text('الأسبوعية'), icon: Icon(Icons.calendar_view_week)),
          ButtonSegment(value: 'clubs', label: Text('الأندية'), icon: Icon(Icons.shield_outlined)),
        ],
        selected: const {'global'},
        onSelectionChanged: (_) {},
      ),
      const SizedBox(height: 14),
      Row(crossAxisAlignment: CrossAxisAlignment.end, children: [
        Expanded(child: _PodiumV022(friend: entries[1], rank: 2, height: 112, controller: controller)),
        const SizedBox(width: 7),
        Expanded(child: _PodiumV022(friend: entries[0], rank: 1, height: 148, controller: controller)),
        const SizedBox(width: 7),
        Expanded(child: _PodiumV022(friend: entries[2], rank: 3, height: 96, controller: controller)),
      ]),
      const SizedBox(height: 12),
      ...entries.skip(3).map((friend) => Padding(
            padding: const EdgeInsets.only(bottom: 8),
            child: PremiumListTile(
              onTap: () => showPublicPlayerProfileV170(context, controller, friend),
              icon: '${entries.indexOf(friend) + 1}',
              title: friend.name,
              subtitle: '${formatNumber(friend.xp)} XP • المستوى ${friend.level}',
              action: FilledButton.tonal(onPressed: () => showPublicPlayerProfileV170(context, controller, friend), child: const Text('البروفايل')),
            ),
          )),
    ]),
  );
}

class _PodiumV022 extends StatelessWidget {
  final LocalFriend friend;
  final int rank;
  final double height;
  final AppController controller;
  const _PodiumV022({required this.friend, required this.rank, required this.height, required this.controller});

  @override
  Widget build(BuildContext context) => InkWell(
        onTap: () => showPublicPlayerProfileV170(context, controller, friend),
        borderRadius: BorderRadius.circular(20),
        child: Container(
          height: height,
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(20),
            gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.amber.withValues(alpha: rank == 1 ? .32 : .15), Colors.white.withValues(alpha: .035)]),
            border: Border.all(color: rank == 1 ? Colors.amber : Colors.white12),
          ),
          child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
            Text(rank == 1 ? '🥇' : rank == 2 ? '🥈' : '🥉', style: const TextStyle(fontSize: 25)),
            Text(friend.name, maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontWeight: FontWeight.w900)),
            Text('LV.${friend.level}', style: const TextStyle(color: Colors.white60, fontSize: 10)),
          ]),
        ),
      );
}

const Map<String, String> adminPermissionLabelsV022 = <String, String>{
  'store.manage': 'إدارة المتجر',
  'games.manage': 'إدارة الألعاب',
  'clubs.manage': 'إدارة الأندية',
  'competitions.manage': 'إدارة المنافسات',
  'designer.manage': 'المصمم الشامل',
  'users.moderate': 'إدارة اللاعبين',
  'reports.manage': 'البلاغات',
  'features.manage': 'ميزات المنصة',
};

class AdminDelegationsV022 extends StatefulWidget {
  final AppController controller;
  const AdminDelegationsV022({super.key, required this.controller});

  @override
  State<AdminDelegationsV022> createState() => _AdminDelegationsV022State();
}

class _AdminDelegationsV022State extends State<AdminDelegationsV022> {
  final Map<int, Set<String>> selected = <int, Set<String>>{};
  bool saving = false;

  @override
  Widget build(BuildContext context) => ListView(
        padding: const EdgeInsets.all(12),
        children: [
          const _AdminInfo(text: 'المدير Adnan يملك كل الصلاحيات. يمكن تفويض أي لاعب بصلاحية واحدة أو عدة صلاحيات، ويمكن منح الصلاحية نفسها لأكثر من لاعب.'),
          const SizedBox(height: 10),
          ...widget.controller.friends.map((friend) {
            final permissions = selected.putIfAbsent(friend.id, () => <String>{});
            return Padding(
              padding: const EdgeInsets.only(bottom: 10),
              child: PremiumPanel(
                child: ExpansionTile(
                  leading: CircleAvatar(child: Text(friend.name.characters.first)),
                  title: Text(friend.name, style: const TextStyle(fontWeight: FontWeight.w900)),
                  subtitle: Text('@${friend.username} • ${permissions.length} صلاحيات'),
                  children: [
                    Padding(
                      padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
                      child: Column(children: [
                        Wrap(spacing: 6, runSpacing: 6, children: adminPermissionLabelsV022.entries.map((entry) => FilterChip(
                          selected: permissions.contains(entry.key),
                          label: Text(entry.value, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w800)),
                          onSelected: (value) => setState(() => value ? permissions.add(entry.key) : permissions.remove(entry.key)),
                        )).toList()),
                        const SizedBox(height: 10),
                        Row(children: [
                          Expanded(child: FilledButton.icon(
                            onPressed: saving ? null : () => _save(friend.id, permissions),
                            icon: const Icon(Icons.security),
                            label: const Text('حفظ الصلاحيات'),
                          )),
                          const SizedBox(width: 7),
                          IconButton.filledTonal(onPressed: saving ? null : () => _remove(friend.id), icon: const Icon(Icons.delete_outline)),
                        ]),
                      ]),
                    ),
                  ],
                ),
              ),
            );
          }),
        ],
      );

  Future<void> _save(int userId, Set<String> permissions) async {
    setState(() => saving = true);
    try {
      if (widget.controller.serverConnected) await widget.controller.api.updateAdminDelegationV022(userId, permissions.toList());
      if (mounted) showToast(context, 'تم حفظ الصلاحيات.');
    } catch (error) {
      if (mounted) showToast(context, friendlyErrorMessage(error, widget.controller.localeCode));
    } finally {
      if (mounted) setState(() => saving = false);
    }
  }

  Future<void> _remove(int userId) async {
    setState(() => saving = true);
    try {
      if (widget.controller.serverConnected) await widget.controller.api.removeAdminDelegationV022(userId);
      if (mounted) setState(() => selected[userId]?.clear());
      if (mounted) showToast(context, 'تم إلغاء التفويض.');
    } catch (error) {
      if (mounted) showToast(context, friendlyErrorMessage(error, widget.controller.localeCode));
    } finally {
      if (mounted) setState(() => saving = false);
    }
  }
}


String _firstCharacterV022(String? value) {
  final normalized = value?.trim() ?? '';
  return normalized.isEmpty ? '?' : normalized.characters.first;
}

const Map<String, String> clubPermissionLabelsV022 = <String, String>{
  'manage_members': 'إدارة الأعضاء',
  'accept_members': 'قبول الطلبات',
  'kick_members': 'إخراج الأعضاء',
  'promote_members': 'ترقية الأعضاء',
  'manage_roles': 'إدارة الأدوار',
  'create_tournaments': 'إنشاء منافسات',
  'manage_tournaments': 'إدارة المنافسات',
  'create_challenges': 'إنشاء تحديات',
  'manage_challenges': 'إدارة التحديات',
  'manage_chat': 'إدارة الدردشة',
  'create_announcements': 'نشر الإعلانات',
  'manage_club_profile': 'تعديل ملف النادي',
  'view_audit_log': 'عرض سجل النادي',
  'manage_treasury': 'إدارة الخزينة',
};

Future<void> showClubManagementV022(BuildContext context, AppController controller) {
  return showPremiumSheet(
    context,
    child: SizedBox(
      height: MediaQuery.sizeOf(context).height * .78,
      child: ClubManagementV022(controller: controller),
    ),
  );
}

class ClubManagementV022 extends StatefulWidget {
  final AppController controller;
  const ClubManagementV022({super.key, required this.controller});

  @override
  State<ClubManagementV022> createState() => _ClubManagementV022State();
}

class _ClubManagementV022State extends State<ClubManagementV022> {
  bool loading = true;
  String? error;
  Map<String, dynamic>? club;
  List<Map<String, dynamic>> logs = <Map<String, dynamic>>[];
  final Map<int, Set<String>> permissions = <int, Set<String>>{};
  final Map<int, String> roles = <int, String>{};
  final Set<int> saving = <int>{};

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      loading = true;
      error = null;
    });
    try {
      if (!widget.controller.serverConnected) {
        throw const ApiException('إدارة النادي تحتاج اتصالاً بالخادم.');
      }
      final response = await widget.controller.api.myClubV022();
      final rawClub = response['club'];
      if (rawClub is Map) {
        club = Map<String, dynamic>.from(rawClub);
        final members = club?['members'];
        if (members is List) {
          for (final raw in members.whereType<Map>()) {
            final member = Map<String, dynamic>.from(raw);
            final membershipId = int.tryParse(member['membership_id']?.toString() ?? '') ?? 0;
            roles[membershipId] = member['role']?.toString() ?? 'member';
            permissions[membershipId] = _permissionSet(member['permissions']);
          }
        }
        final clubId = int.tryParse(club?['id']?.toString() ?? '') ?? 0;
        if (clubId > 0) {
          try {
            final activity = await widget.controller.api.clubActivityV022(clubId);
            final rawLogs = activity['logs'];
            if (rawLogs is List) {
              logs = rawLogs.whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList();
            }
          } catch (_) {
            // The audit tab is intentionally hidden when the member does not
            // have permission; member management remains available.
            logs = <Map<String, dynamic>>[];
          }
        }
      }
    } catch (exception) {
      error = friendlyErrorMessage(exception, widget.controller.localeCode);
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Set<String> _permissionSet(dynamic raw) {
    if (raw is List) return raw.map((value) => value.toString()).toSet();
    if (raw is Map) {
      return raw.entries.where((entry) => entry.value == true || entry.value == 1).map((entry) => entry.key.toString()).toSet();
    }
    return <String>{};
  }

  @override
  Widget build(BuildContext context) {
    if (loading) return const Center(child: CircularProgressIndicator());
    if (error != null) {
      return Center(child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.cloud_off_rounded, size: 44, color: Colors.orangeAccent),
        const SizedBox(height: 9),
        Text(error!, textAlign: TextAlign.center),
        const SizedBox(height: 9),
        FilledButton.icon(onPressed: _load, icon: const Icon(Icons.refresh), label: const Text('إعادة المحاولة')),
      ]));
    }
    if (club == null) {
      return const Center(child: Text('انضم إلى نادٍ أولاً حتى تظهر لوحة إدارته.'));
    }
    final members = (club?['members'] is List)
        ? (club!['members'] as List).whereType<Map>().map((item) => Map<String, dynamic>.from(item)).toList()
        : <Map<String, dynamic>>[];
    return DefaultTabController(
      length: 2,
      child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
        Row(children: [
          CircleAvatar(radius: 25, child: Text((club?['logo']?.toString().isNotEmpty == true) ? club!['logo'].toString() : '🛡️', style: const TextStyle(fontSize: 23))),
          const SizedBox(width: 9),
          Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(club?['name']?.toString() ?? 'النادي', style: const TextStyle(fontSize: 19, fontWeight: FontWeight.w900)),
            Text('${members.length}/${club?['capacity'] ?? 50} عضو • LV.${club?['level'] ?? 1}', style: const TextStyle(color: Colors.white60, fontSize: 10)),
          ])),
          IconButton.filledTonal(onPressed: _load, icon: const Icon(Icons.refresh_rounded)),
        ]),
        const SizedBox(height: 10),
        const TabBar(tabs: [
          Tab(icon: Icon(Icons.manage_accounts_rounded), text: 'الأعضاء والصلاحيات'),
          Tab(icon: Icon(Icons.history_rounded), text: 'سجل النادي'),
        ]),
        const SizedBox(height: 8),
        Expanded(child: TabBarView(children: [
          ListView(children: members.map(_memberCard).toList()),
          _activityList(),
        ])),
      ]),
    );
  }

  Widget _memberCard(Map<String, dynamic> member) {
    final membershipId = int.tryParse(member['membership_id']?.toString() ?? '') ?? 0;
    final role = roles[membershipId] ?? member['role']?.toString() ?? 'member';
    final selected = permissions.putIfAbsent(membershipId, () => _permissionSet(member['permissions']));
    final owner = role == 'owner';
    return Padding(
      padding: const EdgeInsets.only(bottom: 8),
      child: PremiumPanel(
        child: ExpansionTile(
          leading: CircleAvatar(child: Text((member['avatar']?.toString().isNotEmpty == true) ? member['avatar'].toString() : _firstCharacterV022(member['name']?.toString()))),
          title: Text(member['name']?.toString() ?? member['username']?.toString() ?? 'عضو', style: const TextStyle(fontWeight: FontWeight.w900)),
          subtitle: Text('@${member['username'] ?? ''} • ${_roleLabel(role)} • ${selected.length} صلاحيات'),
          trailing: owner ? const Icon(Icons.workspace_premium_rounded, color: Colors.amber) : null,
          children: owner ? const [Padding(padding: EdgeInsets.all(12), child: Text('مالك النادي يملك جميع الصلاحيات ولا يمكن تعديل دوره.'))] : [
            Padding(
              padding: const EdgeInsets.fromLTRB(12, 0, 12, 12),
              child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                DropdownButtonFormField<String>(
                  initialValue: role,
                  decoration: const InputDecoration(labelText: 'الدور'),
                  items: const [
                    DropdownMenuItem(value: 'member', child: Text('عضو')),
                    DropdownMenuItem(value: 'moderator', child: Text('مشرف بصلاحيات مخصصة')),
                  ],
                  onChanged: (value) => setState(() {
                    roles[membershipId] = value ?? 'member';
                    if (roles[membershipId] == 'member') selected.clear();
                  }),
                ),
                if ((roles[membershipId] ?? role) == 'moderator') ...[
                  const SizedBox(height: 9),
                  Wrap(spacing: 6, runSpacing: 6, children: clubPermissionLabelsV022.entries.map((entry) => FilterChip(
                    selected: selected.contains(entry.key),
                    label: Text(entry.value, style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800)),
                    onSelected: (value) => setState(() => value ? selected.add(entry.key) : selected.remove(entry.key)),
                  )).toList()),
                ],
                const SizedBox(height: 10),
                FilledButton.icon(
                  onPressed: saving.contains(membershipId) ? null : () => _saveMember(membershipId),
                  icon: saving.contains(membershipId)
                      ? const SizedBox(width: 17, height: 17, child: CircularProgressIndicator(strokeWidth: 2))
                      : const Icon(Icons.save_outlined),
                  label: const Text('حفظ الدور والصلاحيات'),
                ),
              ]),
            ),
          ],
        ),
      ),
    );
  }

  String _roleLabel(String role) => role == 'owner' ? 'المالك' : role == 'moderator' ? 'مشرف' : 'عضو';

  Future<void> _saveMember(int membershipId) async {
    final clubId = int.tryParse(club?['id']?.toString() ?? '') ?? 0;
    setState(() => saving.add(membershipId));
    try {
      await widget.controller.api.updateClubMemberV022(
        clubId: clubId,
        membershipId: membershipId,
        role: roles[membershipId] ?? 'member',
        permissions: permissions[membershipId]?.toList() ?? const <String>[],
      );
      if (mounted) showToast(context, 'تم حفظ صلاحيات العضو وتسجيل العملية في سجل النادي.');
      await _load();
    } catch (exception) {
      if (mounted) showToast(context, friendlyErrorMessage(exception, widget.controller.localeCode));
    } finally {
      if (mounted) setState(() => saving.remove(membershipId));
    }
  }

  Widget _activityList() {
    if (logs.isEmpty) {
      return const _EmptyState(icon: Icons.history_toggle_off_rounded, title: 'لا توجد سجلات متاحة أو لا تملك صلاحية عرضها');
    }
    return ListView.builder(
      itemCount: logs.length,
      itemBuilder: (context, index) {
        final log = logs[index];
        final actor = log['actor'];
        final actorName = actor is Map ? (actor['profile'] is Map ? (actor['profile']['display_name'] ?? actor['username']) : actor['username']) : null;
        return Padding(
          padding: const EdgeInsets.only(bottom: 7),
          child: PremiumListTile(
            icon: _activityIcon(log['category']?.toString()),
            title: log['description']?.toString() ?? log['message']?.toString() ?? log['action']?.toString() ?? 'نشاط النادي',
            subtitle: '${actorName ?? 'النظام'} • ${log['created_at'] ?? ''}',
          ),
        );
      },
    );
  }

  String _activityIcon(String? category) => switch (category) {
        'members' => '👥',
        'competitions' => '🏆',
        'challenges' => '🎯',
        'games' => '🎴',
        'treasury' => '🪙',
        _ => '🛡️',
      };
}
