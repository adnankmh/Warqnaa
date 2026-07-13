part of 'main.dart';

/// Warqna V0.3 premium interaction layer.
/// New user-facing strings are localized in every language shipped by the app.
const Map<String, Map<String, String>> v03Translations = <String, Map<String, String>>{
  'ar': {
    'challengeRoad': 'طريق التحدي',
    'chooseGame': 'اختر اللعبة',
    'chooseStages': 'اختر عدد المراحل',
    'startRoad': 'ابدأ طريق التحدي',
    'continueRoad': 'العب المرحلة الحالية',
    'lives': 'المحاولات',
    'stage': 'المرحلة',
    'opponent': 'المنافس',
    'globalSettings': 'الإعدادات السريعة',
    'theme': 'الثيم',
    'language': 'اللغة',
    'font': 'الخط',
    'portraitDefault': 'الوضع الطولي الافتراضي',
    'noCodeDesigner': 'المصمم الشامل بدون كود',
    'levelRewards': 'مكافآت المستويات',
    'winToAdvance': 'افز في المباراة للانتقال إلى المرحلة التالية. الخسارة تخصم محاولة واحدة.',
    'fiveLives': 'تبدأ بخمس محاولات، وتبقى اللعبة المختارة ثابتة طوال المسار.',
  },
  'en': {
    'challengeRoad': 'Challenge Road', 'chooseGame': 'Choose game', 'chooseStages': 'Choose stages',
    'startRoad': 'Start challenge road', 'continueRoad': 'Play current stage', 'lives': 'Lives',
    'stage': 'Stage', 'opponent': 'Opponent', 'globalSettings': 'Quick settings', 'theme': 'Theme',
    'language': 'Language', 'font': 'Font', 'portraitDefault': 'Portrait by default',
    'noCodeDesigner': 'No-code universal designer', 'levelRewards': 'Level rewards',
    'winToAdvance': 'Win the match to advance. A loss costs one life.',
    'fiveLives': 'You start with five lives and keep the same selected game for the whole road.',
  },
  'de': {
    'challengeRoad': 'Herausforderungsweg', 'chooseGame': 'Spiel wählen', 'chooseStages': 'Stufen wählen',
    'startRoad': 'Herausforderung starten', 'continueRoad': 'Aktuelle Stufe spielen', 'lives': 'Versuche',
    'stage': 'Stufe', 'opponent': 'Gegner', 'globalSettings': 'Schnelleinstellungen', 'theme': 'Design',
    'language': 'Sprache', 'font': 'Schrift', 'portraitDefault': 'Hochformat als Standard',
    'noCodeDesigner': 'Universeller No-Code-Designer', 'levelRewards': 'Level-Belohnungen',
    'winToAdvance': 'Gewinne, um weiterzukommen. Eine Niederlage kostet einen Versuch.',
    'fiveLives': 'Du startest mit fünf Versuchen und behältst dasselbe Spiel.',
  },
  'tr': {
    'challengeRoad': 'Meydan Okuma Yolu', 'chooseGame': 'Oyun seç', 'chooseStages': 'Aşama seç',
    'startRoad': 'Yolu başlat', 'continueRoad': 'Mevcut aşamayı oyna', 'lives': 'Haklar',
    'stage': 'Aşama', 'opponent': 'Rakip', 'globalSettings': 'Hızlı ayarlar', 'theme': 'Tema',
    'language': 'Dil', 'font': 'Yazı', 'portraitDefault': 'Varsayılan dikey görünüm',
    'noCodeDesigner': 'Kodsuz kapsamlı tasarımcı', 'levelRewards': 'Seviye ödülleri',
    'winToAdvance': 'İlerlemek için maçı kazan. Kayıp bir hakkı azaltır.',
    'fiveLives': 'Beş hakla başlarsın ve yol boyunca aynı oyunu kullanırsın.',
  },
  'fr': {
    'challengeRoad': 'Parcours défi', 'chooseGame': 'Choisir le jeu', 'chooseStages': 'Choisir les étapes',
    'startRoad': 'Démarrer le parcours', 'continueRoad': 'Jouer l’étape actuelle', 'lives': 'Essais',
    'stage': 'Étape', 'opponent': 'Adversaire', 'globalSettings': 'Réglages rapides', 'theme': 'Thème',
    'language': 'Langue', 'font': 'Police', 'portraitDefault': 'Portrait par défaut',
    'noCodeDesigner': 'Concepteur universel sans code', 'levelRewards': 'Récompenses de niveau',
    'winToAdvance': 'Gagnez pour avancer. Une défaite coûte un essai.',
    'fiveLives': 'Vous commencez avec cinq essais et gardez le même jeu.',
  },
  'es': {
    'challengeRoad': 'Camino de desafíos', 'chooseGame': 'Elegir juego', 'chooseStages': 'Elegir etapas',
    'startRoad': 'Iniciar camino', 'continueRoad': 'Jugar etapa actual', 'lives': 'Intentos',
    'stage': 'Etapa', 'opponent': 'Rival', 'globalSettings': 'Ajustes rápidos', 'theme': 'Tema',
    'language': 'Idioma', 'font': 'Fuente', 'portraitDefault': 'Vertical por defecto',
    'noCodeDesigner': 'Diseñador universal sin código', 'levelRewards': 'Premios de nivel',
    'winToAdvance': 'Gana para avanzar. Una derrota consume un intento.',
    'fiveLives': 'Empiezas con cinco intentos y mantienes el mismo juego.',
  },
};

String v03Text(String locale, String key) => v03Translations[locale]?[key] ?? v03Translations['en']![key] ?? key;


const Map<String, Map<String, String>> v03ThemeLabels = <String, Map<String, String>>{
  'ar': {'dark':'غامق','light':'فاتح','blue':'أزرق','sky':'أزرق سماوي','green':'أخضر','light_green':'أخضر فاتح','gold':'ذهبي','purple':'بنفسجي','light_pink':'وردي فاتح'},
  'en': {'dark':'Dark','light':'Light','blue':'Blue','sky':'Sky blue','green':'Green','light_green':'Light green','gold':'Gold','purple':'Purple','light_pink':'Light pink'},
  'de': {'dark':'Dunkel','light':'Hell','blue':'Blau','sky':'Himmelblau','green':'Grün','light_green':'Hellgrün','gold':'Gold','purple':'Violett','light_pink':'Hellrosa'},
  'tr': {'dark':'Koyu','light':'Açık','blue':'Mavi','sky':'Gök mavisi','green':'Yeşil','light_green':'Açık yeşil','gold':'Altın','purple':'Mor','light_pink':'Açık pembe'},
  'fr': {'dark':'Sombre','light':'Clair','blue':'Bleu','sky':'Bleu ciel','green':'Vert','light_green':'Vert clair','gold':'Or','purple':'Violet','light_pink':'Rose clair'},
  'es': {'dark':'Oscuro','light':'Claro','blue':'Azul','sky':'Azul cielo','green':'Verde','light_green':'Verde claro','gold':'Dorado','purple':'Morado','light_pink':'Rosa claro'},
};

String v03ThemeLabel(String locale,String theme)=>v03ThemeLabels[locale]?[theme]??v03ThemeLabels['en']?[theme]??theme;
String v03MatchmakingLabel(String locale)=>const {'ar':'بحث عن منافس','en':'Matchmaking','de':'Gegnersuche','tr':'Rakip aranıyor','fr':'Recherche d’adversaire','es':'Buscando rival'}[locale]??'Matchmaking';

String v03RewardLabel(String locale,Map<String,dynamic> item){
  final type=item['type']?.toString()??'tokens';
  final amount=int.tryParse(item['amount']?.toString()??'')??0;
  final days=(amount/24).ceil().clamp(1,99);
  switch(locale){
    case 'ar': return switch(type){'pasha_days'=>'$amount أيام باشا','prize_box'=>'صندوق جوائز أسطوري','table_days'=>'طاولة أسطورية لمدة $amount أيام','ticket'=>'تذكرة مسابقة $amount','name_color'=>'لون لاعب لمدة $days يوم','writing_color'=>'لون كتابة لمدة $days يوم',_=>'$amount توكن'};
    case 'de': return switch(type){'pasha_days'=>'$amount Pascha-Tage','prize_box'=>'Legendäre Belohnungskiste','table_days'=>'Legendärer Tisch für $amount Tage','ticket'=>'Wettkampfticket $amount','name_color'=>'Spielerfarbe für $days Tage','writing_color'=>'Schreibfarbe für $days Tage',_=>'$amount Token'};
    case 'tr': return switch(type){'pasha_days'=>'$amount Paşa günü','prize_box'=>'Efsane ödül sandığı','table_days'=>'$amount günlük efsane masa','ticket'=>'$amount yarışma bileti','name_color'=>'$days günlük oyuncu rengi','writing_color'=>'$days günlük yazı rengi',_=>'$amount jeton'};
    case 'fr': return switch(type){'pasha_days'=>'$amount jours Pacha','prize_box'=>'Coffre légendaire','table_days'=>'Table légendaire pendant $amount jours','ticket'=>'Ticket de compétition $amount','name_color'=>'Couleur du joueur pendant $days jours','writing_color'=>'Couleur du texte pendant $days jours',_=>'$amount jetons'};
    case 'es': return switch(type){'pasha_days'=>'$amount días de Pachá','prize_box'=>'Cofre legendario','table_days'=>'Mesa legendaria durante $amount días','ticket'=>'Entrada de competición $amount','name_color'=>'Color del jugador durante $days días','writing_color'=>'Color de texto durante $days días',_=>'$amount fichas'};
    default: return switch(type){'pasha_days'=>'$amount Pasha days','prize_box'=>'Legendary prize box','table_days'=>'Legendary table for $amount days','ticket'=>'$amount competition ticket','name_color'=>'Player color for $days days','writing_color'=>'Writing color for $days days',_=>'$amount tokens'};
  }
}

class Warqna3DButtonV03 extends StatelessWidget {
  final Widget child;
  final VoidCallback? onPressed;
  final IconData? icon;
  final Color? color;
  final EdgeInsetsGeometry padding;
  const Warqna3DButtonV03({super.key, required this.child, this.onPressed, this.icon, this.color, this.padding = const EdgeInsets.symmetric(horizontal: 18, vertical: 13)});

  @override
  Widget build(BuildContext context) {
    final base = color ?? Theme.of(context).colorScheme.primary;
    return AnimatedOpacity(
      duration: const Duration(milliseconds: 160),
      opacity: onPressed == null ? .48 : 1,
      child: DecoratedBox(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Color.lerp(base, Colors.white, .18)!, base, Color.lerp(base, Colors.black, .42)!]),
          border: Border.all(color: Color.lerp(base, Colors.white, .48)!, width: 1.2),
          boxShadow: [BoxShadow(color: base.withValues(alpha: .36), blurRadius: 16, offset: const Offset(0, 7)), const BoxShadow(color: Colors.black54, blurRadius: 3, offset: Offset(0, 4))],
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: onPressed,
            borderRadius: BorderRadius.circular(16),
            child: Padding(
              padding: padding,
              child: Row(mainAxisSize: MainAxisSize.min, mainAxisAlignment: MainAxisAlignment.center, children: [
                if (icon != null) ...[Icon(icon, size: 19), const SizedBox(width: 8)],
                DefaultTextStyle.merge(style: const TextStyle(fontWeight: FontWeight.w900, letterSpacing: .1), child: child),
              ]),
            ),
          ),
        ),
      ),
    );
  }
}

class GlobalSettingsDockV03 extends StatelessWidget {
  final AppController controller;
  const GlobalSettingsDockV03({super.key, required this.controller});

  Future<void> _open(BuildContext context) async {
    await showModalBottomSheet<void>(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (sheetContext) => SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: PremiumPanel(
            child: StatefulBuilder(builder: (context, setLocalState) => Padding(
              padding: const EdgeInsets.all(16),
              child: Column(mainAxisSize: MainAxisSize.min, crossAxisAlignment: CrossAxisAlignment.stretch, children: [
                Row(children: [Icon(Icons.tune_rounded, color: Theme.of(context).colorScheme.primary), const SizedBox(width: 8), Expanded(child: Text(v03Text(controller.localeCode, 'globalSettings'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))), IconButton(onPressed: () => Navigator.pop(context), icon: const Icon(Icons.close))]),
                const SizedBox(height: 10),
                Text(v03Text(controller.localeCode, 'language'), style: const TextStyle(fontWeight: FontWeight.w900)),
                const SizedBox(height: 6),
                Wrap(spacing: 7, runSpacing: 7, children: const [('ar','العربية'),('en','English'),('de','Deutsch'),('tr','Türkçe'),('fr','Français'),('es','Español')].map((item) => ChoiceChip(label: Text(item.$2), selected: controller.localeCode == item.$1, onSelected: (_) { controller.changeLocale(item.$1); setLocalState(() {}); })).toList()),
                const SizedBox(height: 12),
                Text(v03Text(controller.localeCode, 'theme'), style: const TextStyle(fontWeight: FontWeight.w900)),
                const SizedBox(height: 6),
                Wrap(spacing: 7, runSpacing: 7, children: const ['dark','light','blue','sky','green','light_green','gold','purple','light_pink'].map((theme) => ChoiceChip(label: Text(v03ThemeLabel(controller.localeCode,theme)), selected: controller.themeCode == theme, onSelected: (_) { controller.changeTheme(theme); setLocalState(() {}); })).toList()),
                const SizedBox(height: 12),
                Row(children: [Expanded(child: Text('${v03Text(controller.localeCode, 'font')} ${(controller.uiFontScale * 100).round()}%', style: const TextStyle(fontWeight: FontWeight.w900))), IconButton.filledTonal(onPressed: () { controller.adjustFontScale(-.06); setLocalState(() {}); }, icon: const Text('A−')), const SizedBox(width: 5), IconButton.filled(onPressed: () { controller.adjustFontScale(.06); setLocalState(() {}); }, icon: const Text('A+'))]),
                const SizedBox(height: 8),
                SwitchListTile.adaptive(contentPadding: EdgeInsets.zero, value: !controller.landscapeMode, onChanged: (value) async { await controller.setLandscapeMode(!value); setLocalState(() {}); }, title: Text(v03Text(controller.localeCode, 'portraitDefault'), style: const TextStyle(fontWeight: FontWeight.w900))),
              ]),
            )),
          ),
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) => SafeArea(
        child: Align(
          alignment: controller.localeCode == 'ar' ? Alignment.bottomLeft : Alignment.bottomRight,
          child: Padding(
            padding: const EdgeInsets.only(bottom: 92, left: 7, right: 7),
            child: Material(
              color: Colors.black.withValues(alpha: .50),
              shape: const CircleBorder(),
              elevation: 8,
              child: IconButton(tooltip: v03Text(controller.localeCode, 'globalSettings'), onPressed: () => _open(context), icon: const Icon(Icons.tune_rounded, size: 20)),
            ),
          ),
        ),
      );
}

class ChallengeRoadPageV03 extends StatefulWidget {
  final AppController controller;
  const ChallengeRoadPageV03({super.key, required this.controller});
  @override State<ChallengeRoadPageV03> createState() => _ChallengeRoadPageV03State();
}

class _ChallengeRoadPageV03State extends State<ChallengeRoadPageV03> {
  bool loading = true;
  Map<String, dynamic>? run;
  String selectedGame = 'tarneeb';
  int stages = 12;
  String? error;

  @override void initState() { super.initState(); unawaited(_load()); }

  Future<void> _load() async {
    if (!widget.controller.serverConnected) { if (mounted) setState(() { loading=false; error=L.t(widget.controller.localeCode,'serverUnavailable'); }); return; }
    try {
      final data=await widget.controller.api.engagementCenterV173();
      final road=data['challenge_road'];
      final active=road is Map ? road['active_run'] : null;
      if(mounted) setState(() { run=active is Map?Map<String,dynamic>.from(active):null; loading=false; error=null; });
      widget.controller.challengeRoadV03=run;
    } catch(e) { if(mounted)setState(() {loading=false;error=friendlyErrorMessage(e,widget.controller.localeCode);}); }
  }

  Future<void> _start() async {
    setState(() => loading=true);
    try {
      final data=await widget.controller.api.startChallengeRoadV03(selectedGame,stages);
      final raw=data['run'];
      if(raw is Map){run=Map<String,dynamic>.from(raw);widget.controller.challengeRoadV03=run;}
      if(mounted){setState(() => loading=false);showToast(context,data['message']?.toString()??v03Text(widget.controller.localeCode,'startRoad'));}
    } catch(e) {if(mounted){setState(() => loading=false);showToast(context,friendlyErrorMessage(e,widget.controller.localeCode));}}
  }

  Future<void> _play() async {
    final active=run;if(active==null)return;
    final gameKey=active['game_key']?.toString()??selectedGame;
    final game=gameByIdV022(gameKey) ?? gamesCatalog.firstWhere((item)=>item.id=='tarneeb',orElse:()=>gamesCatalog.first);
    await openGameRoom(context,widget.controller,game,options:RoomLaunchOptions(roomName:'${v03Text(widget.controller.localeCode,'challengeRoad')} • ${active['stage'] ?? 1}',visibility:'public'));
  }

  @override Widget build(BuildContext context) {
    final active=run;
    return Scaffold(
      appBar: AppBar(title: Text(v03Text(widget.controller.localeCode,'challengeRoad')), actions:[IconButton(onPressed:_load,icon:const Icon(Icons.refresh))]),
      body: PremiumBackground(child: SafeArea(child: ListView(padding:const EdgeInsets.all(14),children:[
        PremiumPanel(child:Padding(padding:const EdgeInsets.all(16),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
          Row(children:[Container(width:56,height:56,alignment:Alignment.center,decoration:BoxDecoration(shape:BoxShape.circle,gradient:const LinearGradient(colors:[Color(0xffffcf67),Color(0xff8b3f05)]),boxShadow:const[BoxShadow(color:Colors.amber,blurRadius:18)]),child:const Text('🏆',style:TextStyle(fontSize:30))),const SizedBox(width:12),Expanded(child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:[Text(v03Text(widget.controller.localeCode,'challengeRoad'),style:const TextStyle(fontSize:22,fontWeight:FontWeight.w900)),Text(v03Text(widget.controller.localeCode,'fiveLives'),style:const TextStyle(color:Colors.white60,height:1.4))]))]),
          if(error!=null)...[const SizedBox(height:10),Text(error!,style:const TextStyle(color:Colors.orangeAccent))],
        ]))),
        const SizedBox(height:12),
        if(loading) const Center(child:Padding(padding:EdgeInsets.all(32),child:CircularProgressIndicator())) else if(active==null) ...[
          PremiumPanel(child:Padding(padding:const EdgeInsets.all(16),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
            Text(v03Text(widget.controller.localeCode,'chooseGame'),style:const TextStyle(fontWeight:FontWeight.w900,fontSize:16)),const SizedBox(height:8),
            DropdownButtonFormField<String>(initialValue:selectedGame,items:gamesCatalog.map((game)=>DropdownMenuItem(value:game.id,child:Text('${game.icon} ${L.t(widget.controller.localeCode,game.id)}'))).toList(),onChanged:(value)=>setState(()=>selectedGame=value??selectedGame)),
            const SizedBox(height:12),Text(v03Text(widget.controller.localeCode,'chooseStages'),style:const TextStyle(fontWeight:FontWeight.w900,fontSize:16)),const SizedBox(height:8),
            SegmentedButton<int>(segments:const[ButtonSegment(value:10,label:Text('10')),ButtonSegment(value:12,label:Text('12')),ButtonSegment(value:15,label:Text('15'))],selected:<int>{stages},onSelectionChanged:(value)=>setState(()=>stages=value.first)),
            const SizedBox(height:16),Warqna3DButtonV03(icon:Icons.flag_rounded,onPressed:_start,child:Text(v03Text(widget.controller.localeCode,'startRoad'))),
          ]))),
        ] else ...[
          _ChallengeRunCardV03(controller:widget.controller,run:active,onPlay:_play),
        ],
      ]))),
    );
  }
}

class _ChallengeRunCardV03 extends StatelessWidget {
  final AppController controller; final Map<String,dynamic> run; final VoidCallback onPlay;
  const _ChallengeRunCardV03({required this.controller,required this.run,required this.onPlay});
  @override Widget build(BuildContext context){
    final stage=int.tryParse(run['stage']?.toString()??'')??1;
    final total=int.tryParse(run['stage_count']?.toString()??'')??12;
    final lives=int.tryParse(run['lives']?.toString()??'')??5;
    final road=run['reward_road'] is List ? run['reward_road'] as List : const[];
    final opponent=run['opponent'] is Map ? Map<String,dynamic>.from(run['opponent'] as Map) : <String,dynamic>{};
    return Column(children:[
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(16),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
        Row(children:[Expanded(child:Text('${v03Text(controller.localeCode,'stage')} $stage / $total',style:const TextStyle(fontSize:21,fontWeight:FontWeight.w900))),Text(List.filled(lives,'❤️').join(' '),style:const TextStyle(fontSize:18))]),
        const SizedBox(height:8),ClipRRect(borderRadius:BorderRadius.circular(99),child:LinearProgressIndicator(value:((stage-1)/total).clamp(0,1).toDouble(),minHeight:12)),
        const SizedBox(height:12),Text('${L.t(controller.localeCode,run['game_key']?.toString()??'tarneeb')} • ${v03Text(controller.localeCode,'opponent')}: ${opponent['display_name']??opponent['username']??v03MatchmakingLabel(controller.localeCode)}',style:const TextStyle(fontWeight:FontWeight.w800)),
        const SizedBox(height:5),Text(v03Text(controller.localeCode,'winToAdvance'),style:const TextStyle(color:Colors.white60,height:1.45)),
        const SizedBox(height:14),Warqna3DButtonV03(icon:Icons.play_arrow_rounded,onPressed:run['status']=='active'?onPlay:null,child:Text(v03Text(controller.localeCode,'continueRoad'))),
      ]))),
      const SizedBox(height:12),
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(12),child:Wrap(spacing:8,runSpacing:8,children:road.whereType<Map>().map((raw){final item=Map<String,dynamic>.from(raw);final number=int.tryParse(item['stage']?.toString()??'')??0;final claimed=(run['claimed_stages'] is List)&&((run['claimed_stages'] as List).map((e)=>int.tryParse(e.toString())??-1).contains(number));return Container(width:76,padding:const EdgeInsets.all(8),decoration:BoxDecoration(borderRadius:BorderRadius.circular(14),color:claimed?Colors.green.withValues(alpha:.16):number==stage?Theme.of(context).colorScheme.primary.withValues(alpha:.18):Colors.white.withValues(alpha:.05),border:Border.all(color:number==stage?Theme.of(context).colorScheme.primary:Colors.white10)),child:Column(mainAxisSize:MainAxisSize.min,children:[Text('${item['icon']??'🎁'}',style:const TextStyle(fontSize:23)),Text('$number',style:const TextStyle(fontWeight:FontWeight.w900)),Text(v03RewardLabel(controller.localeCode,item),textAlign:TextAlign.center,maxLines:2,overflow:TextOverflow.ellipsis,style:const TextStyle(fontSize:8,color:Colors.white60))]));}).toList()))),
    ]);
  }
}

void openChallengeRoadV03(BuildContext context,AppController controller){Navigator.push(context,MaterialPageRoute(builder:(_)=>ChallengeRoadPageV03(controller:controller)));}

extension AppControllerV03 on AppController {
  Future<void> reportChallengeRoadResultV03(bool won,{String? roomCode}) async {
    final active=challengeRoadV03;
    final runId=int.tryParse(active?['id']?.toString()??'');
    if(runId==null||!serverConnected)return;
    try{
      final data=await api.reportChallengeRoadV03(runId,won,roomCode:roomCode);
      final raw=data['run'];
      if(raw is Map)challengeRoadV03=Map<String,dynamic>.from(raw);
      notices.insert(0,AppNotice(won?'🏆':'❤️',v03Text(localeCode,'challengeRoad'),data['message']?.toString()??''));
      refreshUi();
    }catch(_){ }
  }
}
