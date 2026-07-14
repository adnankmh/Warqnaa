part of 'main.dart';

/// Exact level-transition XP contract imported from XPs.xlsx for levels 1..100.

/// Returns the authoritative XP required to advance from [currentLevel].
/// Shared by controller logic and top-level profile/card helpers.
int xpNeededForLevelV175(int currentLevel) {
  final safe = currentLevel.clamp(1, 200).toInt();
  final exact = xpRequirementsV175[safe];
  if (exact != null) return exact;
  final extra = safe - 100;
  return (xpRequirementsV175[100]! * math.pow(1.12, extra)).round();
}

const Map<int, int> xpRequirementsV175 = <int, int>{
  1: 80,
  2: 98,
  3: 119,
  4: 145,
  5: 177,
  6: 215,
  7: 262,
  8: 319,
  9: 387,
  10: 470,
  11: 570,
  12: 690,
  13: 834,
  14: 1007,
  15: 1213,
  16: 1460,
  17: 1755,
  18: 2105,
  19: 2519,
  20: 3010,
  21: 3588,
  22: 4268,
  23: 5065,
  24: 5996,
  25: 7081,
  26: 8341,
  27: 9798,
  28: 11477,
  29: 13405,
  30: 15610,
  31: 18121,
  32: 20969,
  33: 24185,
  34: 27799,
  35: 31842,
  36: 36343,
  37: 41326,
  38: 46814,
  39: 52825,
  40: 59371,
  41: 66455,
  42: 74074,
  43: 82212,
  44: 90846,
  45: 99936,
  46: 109434,
  47: 119274,
  48: 129379,
  49: 139656,
  50: 150000,
  51: 160627,
  52: 171847,
  53: 183691,
  54: 196194,
  55: 209392,
  56: 223324,
  57: 238035,
  58: 253573,
  59: 269989,
  60: 287341,
  61: 305693,
  62: 325113,
  63: 345677,
  64: 367470,
  65: 390584,
  66: 415120,
  67: 441192,
  68: 468922,
  69: 498450,
  70: 529926,
  71: 563519,
  72: 599416,
  73: 637823,
  74: 678971,
  75: 723116,
  76: 770543,
  77: 821569,
  78: 876548,
  79: 935877,
  80: 1000000,
  81: 1068951,
  82: 1142786,
  83: 1222076,
  84: 1307479,
  85: 1399753,
  86: 1499772,
  87: 1608544,
  88: 1727236,
  89: 1857199,
  90: 2000000,
  91: 2175021,
  92: 2405469,
  93: 2700731,
  94: 3072915,
  95: 3537123,
  96: 4111710,
  97: 4818481,
  98: 5682712,
  99: 6732888,
  100: 8000000,
};

class ChallengeCenterV175 extends StatefulWidget {
  final AppController controller;
  const ChallengeCenterV175({super.key, required this.controller});

  @override
  State<ChallengeCenterV175> createState() => _ChallengeCenterV175State();
}

class _ChallengeCenterV175State extends State<ChallengeCenterV175> {
  bool loading = true;
  String? error;
  String selectedRoadGame = 'tarneeb';
  int selectedRoadStages = 12;
  List<Map<String, dynamic>> challenges = <Map<String, dynamic>>[];

  static const List<Map<String, dynamic>> fallback = <Map<String, dynamic>>[
    {'key':'daily_wins','icon':'🔥','name_ar':'سلسلة النار','description_ar':'حقق 3 انتصارات اليوم من دون انسحاب','cadence':'daily','progress':0,'target':3,'reward_tokens':750,'reward_xp':150,'activated':false,'completed':false,'claimed':false},
    {'key':'clean_play','icon':'🛡️','name_ar':'اللعب النظيف','description_ar':'أكمل 5 مباريات بلا مغادرة أو بلاغ','cadence':'daily','progress':0,'target':5,'reward_tokens':900,'reward_xp':180,'activated':false,'completed':false,'claimed':false},
    {'key':'tarneeb_master','icon':'🂡','name_ar':'سيّد الطرنيب','description_ar':'اربح جولتين بفارق 10 نقاط','cadence':'weekly','progress':0,'target':2,'reward_tokens':1200,'reward_xp':250,'activated':false,'completed':false,'claimed':false},
    {'key':'social','icon':'🤝','name_ar':'تحدي الأصدقاء','description_ar':'العب 3 مباريات مع أصدقاء مختلفين','cadence':'weekly','progress':0,'target':3,'reward_tokens':600,'reward_xp':120,'activated':false,'completed':false,'claimed':false},
    {'key':'club','icon':'👥','name_ar':'قوة المجموعة','description_ar':'اجمع 25 نقطة لمجموعتك خلال أسبوع','cadence':'weekly','progress':0,'target':25,'reward_tokens':2000,'reward_xp':400,'activated':false,'completed':false,'claimed':false},
    {'key':'legend','icon':'🐉','name_ar':'مسار الأسطورة','description_ar':'اربح 10 مباريات مصنفة هذا الموسم','cadence':'seasonal','progress':0,'target':10,'reward_tokens':5000,'reward_xp':1000,'activated':false,'completed':false,'claimed':false},
  ];

  @override
  void initState() { super.initState(); unawaited(_load()); }

  Future<void> _load() async {
    if (!widget.controller.serverConnected) {
      if (mounted) setState(() { challenges = fallback.map(Map<String,dynamic>.from).toList(); loading = false; error = 'وضع محلي فعّال: مسار المراحل والمكافآت محفوظ على هذا الجهاز.'; });
      return;
    }
    try {
      final data = await widget.controller.api.engagementCenterV173();
      final raw = data['challenges'];
      final parsed = raw is List ? raw.whereType<Map>().map((e) => Map<String,dynamic>.from(e)).toList() : <Map<String,dynamic>>[];
      if (mounted) setState(() { challenges = parsed.isEmpty ? fallback.map(Map<String,dynamic>.from).toList() : parsed; loading = false; error = null; });
    } on ApiException catch (e) { if (mounted) setState(() { loading=false; challenges=fallback.map(Map<String,dynamic>.from).toList(); error=e.message; }); }
    catch (_) { if (mounted) setState(() { loading=false; challenges=fallback.map(Map<String,dynamic>.from).toList(); error='تعذر تحديث التحديات الآن.'; }); }
  }

  Future<void> _action(Map<String,dynamic> item, bool claim) async {
    final key=item['key']?.toString() ?? '';
    if (key.isEmpty) return;
    if (!widget.controller.serverConnected) {
      if (claim) {
        showToast(context, 'المكافآت المحلية تُضاف تلقائياً بعد الفوز بكل مرحلة.');
      } else {
        widget.controller.joinChallenge(key);
        if (mounted) setState(() => item['activated'] = true);
        showToast(context, 'تم تفعيل التحدي محلياً.');
      }
      return;
    }
    setState(() => loading=true);
    try {
      final data = claim ? await widget.controller.api.claimChallengeV175(key) : await widget.controller.api.activateChallengeV175(key);
      final message=data['message']?.toString() ?? (claim ? 'تم استلام المكافأة.' : 'تم تفعيل التحدي.');
      if (mounted) showToast(context,message);
      await _load();
      widget.controller.refreshUi();
    } on ApiException catch(e) { if(mounted) { setState(() => loading=false); showToast(context,e.message); } }
    catch (_) { if(mounted) { setState(() => loading=false); showToast(context,'تعذر تنفيذ العملية الآن.'); } }
  }

  void _startRoad() {
    widget.controller.startChallengeRoad(selectedRoadGame, selectedRoadStages);
    setState(() {});
    showToast(context, 'بدأ مسار ${L.t(widget.controller.localeCode, selectedRoadGame)} بـ$selectedRoadStages مرحلة و5 محاولات.');
  }

  void _playRoadStage() {
    final gameId = widget.controller.challengeRoadGame ?? selectedRoadGame;
    final game = gamesCatalog.firstWhere((item) => item.id == gameId, orElse: () => gamesCatalog.first);
    Navigator.pop(context);
    showCreateRoom(context, widget.controller, game);
  }

  String _label(dynamic value) {
    if (value is Map) return value['ar']?.toString() ?? value['en']?.toString() ?? '';
    return value?.toString() ?? '';
  }

  @override
  Widget build(BuildContext context) {
    if (loading && challenges.isEmpty) return const Padding(padding: EdgeInsets.all(36), child: Center(child:CircularProgressIndicator()));
    return Column(crossAxisAlignment: CrossAxisAlignment.stretch, children:[
      Row(children:[const Expanded(child:Text('مركز التحديات الاحترافي',style:TextStyle(fontSize:22,fontWeight:FontWeight.w900))),Chip(label:Text('🔥 ${widget.controller.challengeStreakV173}'))]),
      const Text('اختر اللعبة وطول المسار. كل خسارة تستهلك محاولة، وكل فوز يفتح مرحلة ومكافأة حتى الجائزة الختامية.',style:TextStyle(color:Colors.white60,height:1.5)),
      const SizedBox(height:10),
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(13),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
        Row(children:[const Text('🛤️',style:TextStyle(fontSize:34)),const SizedBox(width:9),Expanded(child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:[const Text('مسار المراحل',style:TextStyle(fontSize:16,fontWeight:FontWeight.w900)),Text(widget.controller.challengeRoadGame==null?'ابدأ مساراً جديداً':'${L.t(widget.controller.localeCode,widget.controller.challengeRoadGame!)} • المرحلة ${widget.controller.challengeRoadStage + (widget.controller.challengeRoadCompleted?0:1)} من ${widget.controller.challengeRoadTotal}',style:const TextStyle(color:Colors.white60,fontSize:10))])),Text('❤️ ${widget.controller.challengeRoadAttempts}/5',style:const TextStyle(fontWeight:FontWeight.w900,color:Colors.redAccent))]),
        const SizedBox(height:10),
        if(widget.controller.challengeRoadGame==null || widget.controller.challengeRoadCompleted || widget.controller.challengeRoadAttempts==0) ...[
          DropdownButtonFormField<String>(initialValue:selectedRoadGame,isExpanded:true,decoration:const InputDecoration(labelText:'اللعبة'),items:gamesCatalog.map((game)=>DropdownMenuItem(value:game.id,child:Text('${game.icon} ${L.t(widget.controller.localeCode,game.id)}'))).toList(),onChanged:(value){if(value!=null)setState(()=>selectedRoadGame=value);}),
          const SizedBox(height:8),
          SegmentedButton<int>(segments:const [ButtonSegment(value:10,label:Text('10 مراحل')),ButtonSegment(value:12,label:Text('12 مرحلة')),ButtonSegment(value:15,label:Text('15 مرحلة'))],selected:<int>{selectedRoadStages},onSelectionChanged:(value)=>setState(()=>selectedRoadStages=value.first)),
          const SizedBox(height:9),
          FilledButton.icon(onPressed:_startRoad,icon:const Icon(Icons.flag_rounded),label:const Text('بدء المسار بخمس محاولات')),
        ] else ...[
          ClipRRect(borderRadius:BorderRadius.circular(99),child:LinearProgressIndicator(value:(widget.controller.challengeRoadStage/widget.controller.challengeRoadTotal).clamp(0.0,1.0).toDouble(),minHeight:11)),
          const SizedBox(height:7),
          Text('مكافأة المرحلة القادمة: 🪙 ${formatNumber(widget.controller.challengeRoadRewardForStage(widget.controller.challengeRoadStage+1))}',style:const TextStyle(color:Colors.amberAccent,fontWeight:FontWeight.w800)),
          const SizedBox(height:8),
          Row(children:[Expanded(child:FilledButton.icon(onPressed:_playRoadStage,icon:const Icon(Icons.play_arrow_rounded),label:const Text('ابدأ المرحلة'))),const SizedBox(width:7),OutlinedButton(onPressed:(){widget.controller.resetChallengeRoad();setState((){});},child:const Text('إعادة'))]),
        ],
      ]))),
      if(error!=null) Padding(padding:const EdgeInsets.only(top:8),child:Container(padding:const EdgeInsets.all(10),decoration:BoxDecoration(color:Colors.orange.withValues(alpha:.12),borderRadius:BorderRadius.circular(14),border:Border.all(color:Colors.orangeAccent.withValues(alpha:.35))),child:Text(error!,style:const TextStyle(fontSize:11)))),
      const SizedBox(height:12),
      ...challenges.map((item){
        final progress=int.tryParse(item['progress']?.toString() ?? '') ?? 0;
        final target=math.max(1,int.tryParse(item['target']?.toString() ?? '') ?? 1).toInt();
        final activated=item['activated']==true || progress>0;
        final completed=item['completed']==true || progress>=target;
        final claimed=item['claimed']==true;
        final cadence=item['cadence']?.toString() ?? 'daily';
        final icon=item['icon']?.toString() ?? (cadence=='daily'?'⚡':cadence=='weekly'?'🏆':'🐉');
        final name=item['name_ar']?.toString() ?? _label(item['name']);
        final description=item['description_ar']?.toString() ?? _label(item['description']);
        final tokens=int.tryParse(item['reward_tokens']?.toString() ?? '') ?? 0;
        final rewardXp=int.tryParse(item['reward_xp']?.toString() ?? '') ?? 0;
        return Padding(padding:const EdgeInsets.only(bottom:10),child:PremiumPanel(child:Padding(padding:const EdgeInsets.all(13),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
          Row(children:[Text(icon,style:const TextStyle(fontSize:34)),const SizedBox(width:10),Expanded(child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:[Text(name,style:const TextStyle(fontSize:15,fontWeight:FontWeight.w900)),Text(description,style:const TextStyle(color:Colors.white60,fontSize:10,height:1.4))])),Chip(label:Text(cadence=='daily'?'يومي':cadence=='weekly'?'أسبوعي':'موسمي'))]),
          const SizedBox(height:9),
          Row(children:[Expanded(child:ClipRRect(borderRadius:BorderRadius.circular(99),child:LinearProgressIndicator(value:(progress/target).clamp(0.0,1.0).toDouble(),minHeight:9))),const SizedBox(width:8),Text('$progress / $target',style:const TextStyle(fontWeight:FontWeight.w900))]),
          const SizedBox(height:8),
          Row(children:[Expanded(child:Text('🪙 ${formatNumber(tokens)}  •  ⭐ ${formatNumber(rewardXp)} XP',style:const TextStyle(color:Colors.amberAccent,fontWeight:FontWeight.w800,fontSize:11))),FilledButton.tonal(onPressed:claimed||loading?null:() => _action(item,completed),child:Text(claimed ? 'تم الاستلام' : completed ? 'استلام' : activated ? 'متابعة' : 'تفعيل'))]),
        ]))));
      }),
      if(loading) const Padding(padding:EdgeInsets.only(top:4),child:LinearProgressIndicator()),
    ]);
  }
}

void showChallengesV175(BuildContext context, AppController controller) {
  showPremiumSheet(context, child: ChallengeCenterV175(controller: controller));
}
