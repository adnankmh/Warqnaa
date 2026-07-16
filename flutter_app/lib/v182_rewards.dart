part of 'main.dart';

const List<Map<String, dynamic>> luckyWheelSegmentsV182 = <Map<String, dynamic>>[
  <String, dynamic>{'key':'ticket_200','label_ar':'تذكرة 200','label_en':'Ticket 200','icon':'🎟️','weight':18,'color':'#5b21b6','reward':<String,dynamic>{'type':'ticket','value':'200','duration_hours':0,'rarity':'common','icon':'🎟️','label_ar':'تذكرة مسابقة 200'}},
  <String, dynamic>{'key':'tokens_150','label_ar':'150 توكن','label_en':'150 Tokens','icon':'🪙','weight':20,'color':'#047857','reward':<String,dynamic>{'type':'tokens','value':'150','duration_hours':0,'rarity':'common','icon':'🪙','label_ar':'150 توكن مجاني'}},
  <String, dynamic>{'key':'writing_color','label_ar':'لون كتابة','label_en':'Writing Color','icon':'✍️','weight':10,'color':'#0891b2','reward':<String,dynamic>{'type':'writing_color','value':'#22d3ee','duration_hours':24,'rarity':'rare','icon':'✍️','label_ar':'لون كتابة لمدة يوم','store_item_key':'lucky_wheel_chat_cyan_v182'}},
  <String, dynamic>{'key':'player_color','label_ar':'لون لاعب','label_en':'Player Color','icon':'🎨','weight':10,'color':'#ca8a04','reward':<String,dynamic>{'type':'player_color','value':'#facc15','duration_hours':24,'rarity':'rare','icon':'🎨','label_ar':'لون لاعب لمدة يوم','store_item_key':'lucky_wheel_name_gold_v182'}},
  <String, dynamic>{'key':'tokens_250','label_ar':'250 توكن','label_en':'250 Tokens','icon':'🪙','weight':14,'color':'#15803d','reward':<String,dynamic>{'type':'tokens','value':'250','duration_hours':0,'rarity':'common','icon':'🪙','label_ar':'250 توكن مجاني'}},
  <String, dynamic>{'key':'ticket_500','label_ar':'تذكرة 500','label_en':'Ticket 500','icon':'🎟️','weight':10,'color':'#7c3aed','reward':<String,dynamic>{'type':'ticket','value':'500','duration_hours':0,'rarity':'rare','icon':'🎟️','label_ar':'تذكرة مسابقة 500'}},
  <String, dynamic>{'key':'pasha_day','label_ar':'يوم باشا','label_en':'Pasha Day','icon':'👑','weight':5,'color':'#dc2626','reward':<String,dynamic>{'type':'pasha_day','value':'1','duration_hours':24,'rarity':'legendary','icon':'👑','label_ar':'يوم باشا','store_item_key':'lucky_wheel_pasha_day_v182'}},
  <String, dynamic>{'key':'royal_box','label_ar':'غلاف ملكي','label_en':'Royal Cover','icon':'🎁','weight':4,'color':'#be123c','reward':<String,dynamic>{'type':'profile_cover','value':'cover_v02_royal','duration_hours':72,'rarity':'epic','icon':'🖼️','label_ar':'غلاف شخصي ملكي لمدة 3 أيام','store_item_key':'lucky_wheel_royal_cover_v182'}},
  <String, dynamic>{'key':'tokens_500','label_ar':'500 توكن','label_en':'500 Tokens','icon':'💰','weight':6,'color':'#0f766e','reward':<String,dynamic>{'type':'tokens','value':'500','duration_hours':0,'rarity':'epic','icon':'💰','label_ar':'500 توكن مجاني'}},
  <String, dynamic>{'key':'ticket_1000','label_ar':'تذكرة 1000','label_en':'Ticket 1000','icon':'🏆','weight':3,'color':'#92400e','reward':<String,dynamic>{'type':'ticket','value':'1000','duration_hours':0,'rarity':'legendary','icon':'🏆','label_ar':'تذكرة مسابقة 1000'}},
];

String _todayV182() {
  final now = DateTime.now();
  return '${now.year.toString().padLeft(4,'0')}-${now.month.toString().padLeft(2,'0')}-${now.day.toString().padLeft(2,'0')}';
}

extension WarqnaV182Controller on AppController {
  bool get luckyWheelFreeAvailableV182 => luckyWheelLastFreeDateV182 != _todayV182();
  int get luckyWheelTokenSpinsRemainingV182 {
    if (luckyWheelSpinDateV182 != _todayV182()) return 5;
    return (5 - luckyWheelTokenSpinsV182).clamp(0, 5).toInt();
  }

  Future<Map<String, dynamic>> spinLuckyWheelV182({String source = 'free'}) async {
    if (source != 'free' && source != 'tokens') throw StateError('طريقة التدوير غير صالحة.');
    final today = _todayV182();
    if (luckyWheelSpinDateV182 != today) {
      luckyWheelSpinDateV182 = today;
      luckyWheelTokenSpinsV182 = 0;
    }
    if (source == 'free' && !luckyWheelFreeAvailableV182) throw StateError('استخدمت التدويرة المجانية اليوم.');
    if (source == 'tokens' && luckyWheelTokenSpinsRemainingV182 <= 0) throw StateError('وصلت إلى الحد اليومي للتدوير بالتوكنز.');

    Map<String, dynamic> result;
    if (serverConnected && api.token?.isNotEmpty == true) {
      result = await api.spinLuckyWheelV182(source);
      final rewardRaw = result['reward'];
      if (rewardRaw is Map) _applyPrizeRewardV02(Map<String, dynamic>.from(rewardRaw), result);
      final center = result['center'];
      if (center is Map) {
        if (center['free_available'] == false) luckyWheelLastFreeDateV182 = today;
        luckyWheelTokenSpinsV182 = int.tryParse(center['token_spins_today']?.toString() ?? '') ?? luckyWheelTokenSpinsV182;
      }
    } else {
      if (source == 'tokens') {
        if (coins < BigInt.from(100)) throw StateError('رصيد التوكنز غير كافٍ.');
        coins -= BigInt.from(100);
        if (!isPrimaryAdmin) adminRevenueTokensV182 += BigInt.from(100);
        transactions.insert(0, const TokenTransaction('تدوير دولاب الحظ', -100, 'الآن'));
        luckyWheelTokenSpinsV182 += 1;
      } else {
        luckyWheelLastFreeDateV182 = today;
      }
      final random = math.Random(DateTime.now().microsecondsSinceEpoch);
      final total = luckyWheelSegmentsV182.fold<int>(0, (sum, item) => sum + (item['weight'] as int));
      var pick = random.nextInt(total) + 1;
      var index = 0;
      for (var i = 0; i < luckyWheelSegmentsV182.length; i++) {
        pick -= luckyWheelSegmentsV182[i]['weight'] as int;
        if (pick <= 0) { index = i; break; }
      }
      final segment = Map<String, dynamic>.from(luckyWheelSegmentsV182[index]);
      final reward = Map<String, dynamic>.from(segment['reward'] as Map);
      final hours = int.tryParse(reward['duration_hours']?.toString() ?? '') ?? 0;
      if (hours > 0) reward['expires_at'] = DateTime.now().add(Duration(hours: hours)).toIso8601String();
      _applyPrizeRewardV02(reward, const <String, dynamic>{});
      result = <String, dynamic>{'segment_index':index,'segment':segment,'reward':reward};
    }
    lastLuckyWheelRewardV182 = result['reward'] is Map ? Map<String, dynamic>.from(result['reward'] as Map) : null;
    notices.insert(0, AppNotice('🎡','دولاب الحظ',lastLuckyWheelRewardV182?['label_ar']?.toString() ?? 'تمت إضافة الجائزة إلى مقتنياتك.'));
    await _save();
    refreshUi();
    return result;
  }

  Future<void> exitActiveGameV182() async {
    final code = activeRoomCode;
    if (serverConnected && code != null && code.isNotEmpty) {
      try { await api.leaveGame(code); } catch (_) {}
    }
    final gameId = activeGame;
    if (gameId != null) {
      await recordGameExit(gameId);
    } else {
      leaveGame();
    }
  }

  void ensureDesignerOfflineSeedV182() {
    if (designerOfflineEntitiesV182.isNotEmpty) return;
    var id = -1;
    for (final entry in const <(String,String)>[
      ('system','app_settings'),('game','game_catalog'),('store','store_catalog'),('prize_box','daily_boxes'),
      ('lucky_wheel','lucky_wheel'),('group','club_permissions'),('theme','themes'),('translation','translations'),
    ]) {
      designerOfflineEntitiesV182.add(<String,dynamic>{
        'id':id--,'entity_type':entry.$1,'key':entry.$2,'locale':'all','sort_order':0,'active':true,'revision':1,
        'payload':<String,dynamic>{'enabled':true,'source':'offline_admin_v182'},
      });
    }
  }

  Future<void> upsertDesignerOfflineEntityV182({
    required String entityType, required String key, required String locale,
    required int sortOrder, required bool active, required Map<String, dynamic> payload,
  }) async {
    if (!isPrimaryAdmin) throw StateError('هذه الميزة متاحة لحساب Adnan فقط.');
    ensureDesignerOfflineSeedV182();
    final index = designerOfflineEntitiesV182.indexWhere((item) => item['entity_type'] == entityType && item['key'] == key);
    final previous = index >= 0 ? designerOfflineEntitiesV182[index] : null;
    final item = <String,dynamic>{
      'id':previous?['id'] ?? -(DateTime.now().millisecondsSinceEpoch),
      'entity_type':entityType,'key':key,'locale':locale,'sort_order':sortOrder,'active':active,
      'revision':(int.tryParse(previous?['revision']?.toString() ?? '') ?? 0)+1,'payload':payload,
      'pending_sync':!serverConnected,
    };
    if (index >= 0) { designerOfflineEntitiesV182[index] = item; } else { designerOfflineEntitiesV182.add(item); }
    await _save();
    refreshUi();
  }

  Future<void> deleteDesignerOfflineEntityV182(Map<String, dynamic> entity) async {
    if (!isPrimaryAdmin) throw StateError('هذه الميزة متاحة لحساب Adnan فقط.');
    designerOfflineEntitiesV182.removeWhere((item) =>
        item['id']?.toString() == entity['id']?.toString() ||
        (item['entity_type']?.toString() == entity['entity_type']?.toString() &&
         item['key']?.toString() == entity['key']?.toString()));
    await _save();
    refreshUi();
  }

  Future<void> syncDesignerOfflineEntitiesV182() async {
    if (!isPrimaryAdmin || !serverConnected || api.token?.isNotEmpty != true) return;
    for (final entity in List<Map<String,dynamic>>.from(designerOfflineEntitiesV182)) {
      try {
        await api.upsertAdminDesignerEntityV173(
          entityType:entity['entity_type']?.toString() ?? 'system',
          key:entity['key']?.toString() ?? 'item',
          locale:entity['locale']?.toString() ?? 'all',
          sortOrder:int.tryParse(entity['sort_order']?.toString() ?? '') ?? 0,
          active:entity['active'] != false,
          payload:entity['payload'] is Map ? Map<String,dynamic>.from(entity['payload'] as Map) : <String,dynamic>{},
        );
        entity['pending_sync'] = false;
      } catch (_) { entity['pending_sync'] = true; }
    }
    await _save();
  }

  void ensureClubManagementV182() {
    if (clubActivityV182.isEmpty) {
      clubActivityV182.add(<String,dynamic>{'icon':'🛡️','title':'تم تفعيل مركز إدارة النادي','time':'الآن'});
    }
    for (final friend in friends) {
      clubPermissionsV182.putIfAbsent('${friend.id}', () => <String>['member']);
    }
  }


  Future<void> updateClubIdentityV182({required String name, required String imageEmoji, required String description}) async {
    final cleanName = name.trim();
    if (cleanName.length < 3) throw StateError('اسم المجموعة يجب أن يحتوي على 3 أحرف على الأقل.');
    activeClub = cleanName;
    clubImageEmojiV182 = imageEmoji.trim().isEmpty ? '🛡️' : imageEmoji.trim();
    clubDescriptionV182 = description.trim().isEmpty ? 'نادي احترافي داخل مجتمع ورقنا.' : description.trim();
    clubActivityV182.insert(0, <String, dynamic>{'icon':'🖼️','title':'تم تحديث هوية المجموعة وصورتها','time':'الآن'});
    await _save();
    refreshUi();
  }
  Future<void> updateClubPermissionV182(int playerId, String permission, bool enabled) async {
    if (!isPrimaryAdmin && activeClub == null) return;
    final key = '$playerId';
    final permissions = clubPermissionsV182.putIfAbsent(key, () => <String>['member']);
    if (enabled) { if (!permissions.contains(permission)) permissions.add(permission); }
    else { permissions.remove(permission); }
    clubActivityV182.insert(0,<String,dynamic>{'icon':'🔐','title':'تم ${enabled ? 'منح' : 'إزالة'} صلاحية $permission للاعب رقم $playerId','time':'الآن'});
    await _save();
    refreshUi();
  }
}

class LuckyWheelHomeCardV182 extends StatelessWidget {
  final AppController controller;
  const LuckyWheelHomeCardV182({super.key, required this.controller});
  @override
  Widget build(BuildContext context) {
    final palette = AppPalette.fromCode(controller.themeCode);
    return InkWell(
      onTap: () => Navigator.of(context).push(MaterialPageRoute<void>(builder: (_) => LuckyWheelPageV182(controller: controller))),
      borderRadius: BorderRadius.circular(25),
      child: Container(
        padding: const EdgeInsets.all(15),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(25),
          gradient: const LinearGradient(colors:<Color>[Color(0xff3b0764),Color(0xff7c2d12),Color(0xff0f766e)]),
          border: Border.all(color: const Color(0xffffd166).withValues(alpha:.7)),
          boxShadow:<BoxShadow>[BoxShadow(color:const Color(0xffa855f7).withValues(alpha:.18),blurRadius:22,spreadRadius:1)],
        ),
        child: Row(children:<Widget>[
          Container(width:82,height:82,decoration:BoxDecoration(shape:BoxShape.circle,gradient:SweepGradient(colors:luckyWheelSegmentsV182.map((e)=>colorFromHex(e['color'].toString())).toList()),border:Border.all(color:Colors.amberAccent,width:3)),child:const Center(child:Text('🎡',style:TextStyle(fontSize:38)))),
          const SizedBox(width:13),
          Expanded(child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:<Widget>[
            const Text('دولاب الحظ الملكي',style:TextStyle(fontWeight:FontWeight.w900,fontSize:18,color:Colors.white)),
            const SizedBox(height:4),
            const Text('6 جوائز فاخرة • توقف سينمائي خلال 4 ثوانٍ • الجائزة تذهب لمكانها الصحيح',style:TextStyle(color:Colors.white70,fontSize:10,height:1.45)),
            const SizedBox(height:8),
            Row(children:<Widget>[
              Icon(controller.luckyWheelFreeAvailableV182 ? Icons.card_giftcard_rounded : Icons.schedule_rounded,size:17,color:Colors.amberAccent),
              const SizedBox(width:5),
              Text(controller.luckyWheelFreeAvailableV182 ? 'تدويرة مجانية جاهزة' : 'التدويرة المجانية غداً',style:const TextStyle(color:Colors.amberAccent,fontWeight:FontWeight.w900,fontSize:11)),
            ]),
          ])),
          Icon(Icons.arrow_forward_ios_rounded,color:palette.gold,size:18),
        ]),
      ),
    );
  }
}

class LuckyWheelPageV182 extends StatefulWidget {
  final AppController controller;
  const LuckyWheelPageV182({super.key, required this.controller});
  @override State<LuckyWheelPageV182> createState()=>_LuckyWheelPageV182State();
}

class _LuckyWheelPageV182State extends State<LuckyWheelPageV182> with SingleTickerProviderStateMixin {
  late final AnimationController animationController;
  Animation<double>? animation;
  double angle = 0;
  bool spinning = false;
  String? error;

  @override void initState(){ super.initState(); animationController=AnimationController(vsync:this,duration:const Duration(seconds:4))..addListener((){ if(mounted && animation!=null)setState(()=>angle=animation!.value); }); }
  @override void dispose(){ animationController.dispose(); super.dispose(); }

  Future<void> _spin(String source) async {
    if(spinning)return;
    setState((){spinning=true;error=null;});
    try{
      final result=await widget.controller.spinLuckyWheelV182(source:source);
      final index=int.tryParse(result['segment_index']?.toString() ?? '') ?? 0;
      final sweep=(math.pi*2)/luckyWheelSegmentsV182.length;
      final target=angle + math.pi*2*7 + (math.pi*2 - ((index*sweep)+(sweep/2))) - (angle % (math.pi*2));
      animation=Tween<double>(begin:angle,end:target).animate(CurvedAnimation(parent:animationController,curve:Curves.easeOutQuart));
      await animationController.forward(from:0);
      if(!mounted)return;
      final reward=result['reward'] is Map ? Map<String,dynamic>.from(result['reward'] as Map) : <String,dynamic>{};
      await showDialog<void>(context:context,barrierDismissible:false,builder:(dialogContext)=>AlertDialog(
        icon:const Text('🎉',style:TextStyle(fontSize:50)),title:const Text('مبروك!',textAlign:TextAlign.center),
        content:Column(mainAxisSize:MainAxisSize.min,children:<Widget>[
          Text(reward['icon']?.toString() ?? '🎁',style:const TextStyle(fontSize:58)),const SizedBox(height:8),
          Text(reward['label_ar']?.toString() ?? prizeRewardLabelV02(widget.controller.localeCode,reward),textAlign:TextAlign.center,style:const TextStyle(fontSize:19,fontWeight:FontWeight.w900)),
          const SizedBox(height:7),const Text('تمت إضافة الجائزة تلقائيًا إلى رصيدك أو القسم الصحيح داخل المتجر.',textAlign:TextAlign.center,style:TextStyle(fontSize:11,height:1.5)),
        ]),actions:<Widget>[FilledButton(onPressed:()=>Navigator.pop(dialogContext),child:const Text('رائع'))],
      ));
    }catch(e){ if(mounted)setState(()=>error=e.toString().replaceFirst('Bad state: ','')); }
    if(mounted)setState(()=>spinning=false);
  }

  @override Widget build(BuildContext context){
    final size=math.min(MediaQuery.sizeOf(context).width-34,430.0).toDouble();
    return Scaffold(appBar:AppBar(title:const Text('دولاب الحظ الملكي')),body:ListView(padding:const EdgeInsets.all(16),children:<Widget>[
      const Text('أدر الدولاب واربح جوائز حقيقية داخل اللعبة',textAlign:TextAlign.center,style:TextStyle(fontWeight:FontWeight.w900,fontSize:18)),
      const SizedBox(height:6),const Text('النتيجة معتمدة من الخادم عند الاتصال، وتظهر بعد دوران سلس مدته 4 ثوانٍ. تم توسيع الدولاب إلى 10 خانات مع مؤشر أكبر وواجهة أوضح.',textAlign:TextAlign.center,style:TextStyle(fontSize:11,height:1.5)),
      const SizedBox(height:18),
      Center(child:SizedBox(width:size,height:size+76,child:Stack(alignment:Alignment.center,children:<Widget>[
        Positioned(
          top:4,
          child:Container(
            width:38,
            height:94,
            decoration:BoxDecoration(
              gradient:LinearGradient(begin:Alignment.topCenter,end:Alignment.bottomCenter,colors:<Color>[Colors.amber.shade200,const Color(0xffd97706),const Color(0xff7c2d12)]),
              borderRadius:BorderRadius.circular(28),
              border:Border.all(color:Colors.white,width:2.6),
              boxShadow:const <BoxShadow>[BoxShadow(blurRadius:20,color:Colors.black54,offset:Offset(0,8))],
            ),
            child:Align(
              alignment:Alignment.bottomCenter,
              child:Transform.translate(
                offset: const Offset(0, 18),
                child:SizedBox(width:28,height:26,child:CustomPaint(painter:_LuckyWheelPointerPainterV182())),
              ),
            ),
          ),
        ),
        Positioned(top:58,child:Transform.rotate(angle:angle,child:CustomPaint(size:Size.square(size-20),painter:LuckyWheelPainterV182()))),
        Positioned(top:58+(size-20)/2-34,child:Container(width:68,height:68,decoration:BoxDecoration(shape:BoxShape.circle,gradient:const LinearGradient(colors:<Color>[Color(0xffffe08a),Color(0xffa16207)]),border:Border.all(color:Colors.white,width:3),boxShadow:const <BoxShadow>[BoxShadow(blurRadius:16,color:Colors.black54)]),child:const Center(child:Text('W',style:TextStyle(fontWeight:FontWeight.w900,fontSize:26,color:Color(0xff3b2304))))),),
      ]))),
      if(error!=null)...<Widget>[const SizedBox(height:10),Text(error!,textAlign:TextAlign.center,style:const TextStyle(color:Colors.redAccent,fontWeight:FontWeight.w800))],
      const SizedBox(height:16),
      FilledButton.icon(onPressed:spinning || !widget.controller.luckyWheelFreeAvailableV182 ? null : ()=>_spin('free'),icon:spinning?const SizedBox(width:20,height:20,child:CircularProgressIndicator(strokeWidth:2)):const Icon(Icons.casino_rounded),label:Text(widget.controller.luckyWheelFreeAvailableV182?'تدوير مجاني الآن':'تم استخدام التدويرة المجانية'),style:FilledButton.styleFrom(minimumSize:const Size.fromHeight(55))),
      const SizedBox(height:9),
      OutlinedButton.icon(onPressed:spinning || widget.controller.luckyWheelTokenSpinsRemainingV182<=0 ? null : ()=>_spin('tokens'),icon:const Text('🪙'),label:Text('تدوير مقابل 100 توكن • متبقي ${widget.controller.luckyWheelTokenSpinsRemainingV182}/5'),style:OutlinedButton.styleFrom(minimumSize:const Size.fromHeight(52))),
      const SizedBox(height:18),
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(13),child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:<Widget>[
        const Text('جوائز الدولاب',style:TextStyle(fontWeight:FontWeight.w900,fontSize:16)),const SizedBox(height:9),
        for(final segment in luckyWheelSegmentsV182) ListTile(dense:true,leading:CircleAvatar(backgroundColor:colorFromHex(segment['color'].toString()),child:Text(segment['icon'].toString())),title:Text(segment['label_ar'].toString(),style:const TextStyle(fontWeight:FontWeight.w800)),subtitle:Text(segment['label_en'].toString())),
      ]))),
    ]));
  }
}

class LuckyWheelPainterV182 extends CustomPainter {
  @override void paint(Canvas canvas,Size size){
    final center=Offset(size.width/2,size.height/2);
    final radius=size.width/2;
    final sweep=(math.pi*2)/luckyWheelSegmentsV182.length;
    final fill=Paint()..style=PaintingStyle.fill;
    final border=Paint()..style=PaintingStyle.stroke..strokeWidth=4..color=const Color(0xffffd166);
    final divider=Paint()..style=PaintingStyle.stroke..strokeWidth=2.4..color=Colors.white.withValues(alpha:.72);
    final rim=Paint()..style=PaintingStyle.stroke..strokeWidth=12..color=const Color(0x33ffffff);
    canvas.drawCircle(center,radius,Paint()..color=const Color(0xff1f2937));
    for(var i=0;i<luckyWheelSegmentsV182.length;i++){
      final start=-math.pi/2+i*sweep;
      fill.color=colorFromHex(luckyWheelSegmentsV182[i]['color'].toString());
      canvas.drawArc(Rect.fromCircle(center:center,radius:radius-4),start,sweep,true,fill);
      canvas.save();
      canvas.translate(center.dx,center.dy);
      canvas.rotate(start+sweep/2);
      final segment=luckyWheelSegmentsV182[i];
      final painter=TextPainter(
        textDirection:TextDirection.rtl,
        textAlign:TextAlign.center,
        text:TextSpan(
          text:'${segment['icon']}\n${segment['label_ar']}',
          style:const TextStyle(color:Colors.white,fontSize:10.2,fontWeight:FontWeight.w900,height:1.18,shadows:<Shadow>[Shadow(color:Colors.black54,blurRadius:4)]),
        ),
      )..layout(maxWidth:radius*.44);
      painter.paint(canvas,Offset(radius*.47,-painter.height/2));
      canvas.drawLine(Offset.zero, Offset(radius-10,0), divider);
      canvas.restore();
    }
    canvas.drawCircle(center,radius-2,border);
    canvas.drawCircle(center,radius-10,rim);
    canvas.drawCircle(center,radius*.14,Paint()..color=const Color(0xfffbbf24));
  }
  @override bool shouldRepaint(covariant CustomPainter oldDelegate)=>false;
}

class _LuckyWheelPointerPainterV182 extends CustomPainter {
  @override void paint(Canvas canvas, Size size) {
    final path=Path()
      ..moveTo(size.width/2, size.height)
      ..lineTo(0,0)
      ..lineTo(size.width,0)
      ..close();
    canvas.drawPath(path, Paint()..shader=const LinearGradient(begin:Alignment.topCenter,end:Alignment.bottomCenter,colors:<Color>[Color(0xfffff4c2),Color(0xfff59e0b),Color(0xff7c2d12)]).createShader(Offset.zero & size));
    canvas.drawPath(path, Paint()..style=PaintingStyle.stroke..strokeWidth=2..color=Colors.white.withValues(alpha:.9));
  }
  @override bool shouldRepaint(covariant CustomPainter oldDelegate)=>false;
}

class ClubManagementPageV182 extends StatefulWidget {
  final AppController controller;
  const ClubManagementPageV182({super.key,required this.controller});
  @override State<ClubManagementPageV182> createState()=>_ClubManagementPageV182State();
}
class _ClubManagementPageV182State extends State<ClubManagementPageV182>{
  late final TextEditingController nameController;
  late final TextEditingController imageController;
  late final TextEditingController descriptionController;

  @override
  void initState(){
    super.initState();
    widget.controller.ensureClubManagementV182();
    nameController=TextEditingController(text:widget.controller.activeClub ?? 'نادي ورقنا الملكي');
    imageController=TextEditingController(text:widget.controller.clubImageEmojiV182);
    descriptionController=TextEditingController(text:widget.controller.clubDescriptionV182);
  }

  @override
  void dispose(){
    nameController.dispose();
    imageController.dispose();
    descriptionController.dispose();
    super.dispose();
  }

  Future<void> _saveIdentity() async {
    try{
      await widget.controller.updateClubIdentityV182(
        name:nameController.text,
        imageEmoji:imageController.text,
        description:descriptionController.text,
      );
      if(mounted){setState((){});showToast(context,'تم حفظ صورة المجموعة وبياناتها.');}
    }catch(error){if(mounted)showToast(context,error.toString().replaceFirst('Bad state: ',''));}
  }

  @override Widget build(BuildContext context){
    final c=widget.controller;
    final muted=Theme.of(context).colorScheme.onSurfaceVariant;
    return Scaffold(appBar:AppBar(title:const Text('إدارة المجموعة والصلاحيات')),body:ListView(padding:const EdgeInsets.all(13),children:<Widget>[
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(13),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:<Widget>[
        Row(children:<Widget>[
          CircleAvatar(radius:28,child:Text(c.clubImageEmojiV182,style:const TextStyle(fontSize:28))),
          const SizedBox(width:10),
          Expanded(child:Column(crossAxisAlignment:CrossAxisAlignment.start,children:<Widget>[
            Text(c.activeClub ?? 'نادي ورقنا الملكي',style:const TextStyle(fontWeight:FontWeight.w900,fontSize:17)),
            Text(c.clubDescriptionV182,style:TextStyle(color:muted,fontSize:10,height:1.4)),
          ])),
        ]),
        const SizedBox(height:12),
        TextField(controller:nameController,decoration:const InputDecoration(labelText:'اسم المجموعة')),
        const SizedBox(height:8),
        Row(children:<Widget>[
          SizedBox(width:105,child:TextField(controller:imageController,textAlign:TextAlign.center,decoration:const InputDecoration(labelText:'الصورة/الرمز'))),
          const SizedBox(width:8),
          Expanded(child:TextField(controller:descriptionController,maxLines:2,decoration:const InputDecoration(labelText:'وصف المجموعة'))),
        ]),
        const SizedBox(height:8),
        FilledButton.icon(onPressed:_saveIdentity,icon:const Icon(Icons.save_rounded),label:const Text('حفظ هوية المجموعة')),
      ]))),
      const SizedBox(height:10),
      Row(children:<Widget>[
        const Expanded(child:Text('الأعضاء والصلاحيات',style:TextStyle(fontSize:17,fontWeight:FontWeight.w900))),
        Chip(avatar:const Icon(Icons.groups_rounded,size:17),label:Text('${c.friends.length} عضو')),
      ]),
      for(final player in c.friends) Card(child:ExpansionTile(
        leading:GlowAvatar(text:player.avatar ?? '👤',size:44,color:colorFromHex(player.nameColor)),
        title:PlayerIdentityTapV021(controller:c,userId:player.id,name:player.name,username:player.username,avatar:player.avatar,level:player.level,countryCode:player.countryCode,online:player.online,activity:player.activity,pashaDays:player.pashaDays,gamesPlayed:player.gamesPlayed,wins:player.wins,xp:player.xp,xpNext:player.xpNext,roundPoints:player.roundPoints,tournamentPoints:player.tournamentPoints,clubPoints:player.clubPoints,nameColor:player.nameColor,badge:player.badge,child:Text(player.name,style:const TextStyle(fontWeight:FontWeight.w900))),
        subtitle:Text(player.activity),
        children:<Widget>[
          for(final permission in const <String>['نائب المدير','تنظيم المنافسات','إدارة الأعضاء','إدارة الدردشة','نشر الإعلانات'])
            CheckboxListTile(
              value:c.clubPermissionsV182['${player.id}']?.contains(permission) ?? false,
              title:Text(permission),
              onChanged:(value)async{await c.updateClubPermissionV182(player.id,permission,value==true);if(mounted)setState((){});},
            ),
        ],
      )),
      const SizedBox(height:12),
      const Text('سجل المجموعة',style:TextStyle(fontSize:17,fontWeight:FontWeight.w900)),
      for(final entry in c.clubActivityV182) ListTile(
        leading:CircleAvatar(child:Text(entry['icon']?.toString() ?? '•')),
        title:Text(entry['title']?.toString() ?? ''),
        trailing:Text(entry['time']?.toString() ?? '',style:TextStyle(fontSize:10,color:muted)),
      ),
    ]));
  }
}
