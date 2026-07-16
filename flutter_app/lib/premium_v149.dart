import 'dart:math' as math;
import 'dart:typed_data';
import 'dart:ui' as ui;

import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';

/// Visual and UX primitives introduced in Warqna v149.
/// These widgets are dependency-light so they work on Web, Android and iOS.

enum BotDifficulty { easy, normal, pro, master }

class BotProfile {
  final String id;
  final String nameAr;
  final String nameEn;
  final int seed;
  final int level;
  final BotDifficulty difficulty;
  final Color primary;
  final Color secondary;
  final String styleAr;
  final String styleEn;

  const BotProfile({
    required this.id,
    required this.nameAr,
    required this.nameEn,
    required this.seed,
    required this.level,
    required this.difficulty,
    required this.primary,
    required this.secondary,
    required this.styleAr,
    required this.styleEn,
  });

  String name(String locale) => locale == 'ar' ? nameAr : nameEn;
  String style(String locale) => locale == 'ar' ? styleAr : styleEn;
}

const botProfiles = <BotProfile>[
  BotProfile(id: 'asem', nameAr: 'عاصم', nameEn: 'Asem', seed: 11, level: 72, difficulty: BotDifficulty.pro, primary: Color(0xff2563eb), secondary: Color(0xff38bdf8), styleAr: 'تكتيكي متوازن', styleEn: 'Balanced tactician'),
  BotProfile(id: 'jameel', nameAr: 'جميل', nameEn: 'Jameel', seed: 23, level: 68, difficulty: BotDifficulty.pro, primary: Color(0xff0f766e), secondary: Color(0xff34d399), styleAr: 'دفاعي وصبور', styleEn: 'Patient defender'),
  BotProfile(id: 'layla_ai', nameAr: 'ليلى', nameEn: 'Layla', seed: 37, level: 76, difficulty: BotDifficulty.master, primary: Color(0xff7c3aed), secondary: Color(0xffd8b4fe), styleAr: 'قراءة متقدمة', styleEn: 'Advanced reader'),
  BotProfile(id: 'samer_ai', nameAr: 'سامر', nameEn: 'Samer', seed: 41, level: 64, difficulty: BotDifficulty.pro, primary: Color(0xffb45309), secondary: Color(0xfffacc15), styleAr: 'هجومي ذكي', styleEn: 'Smart aggressor'),
  BotProfile(id: 'nour_ai', nameAr: 'نور', nameEn: 'Nour', seed: 53, level: 70, difficulty: BotDifficulty.pro, primary: Color(0xffbe185d), secondary: Color(0xfffb7185), styleAr: 'إدارة أوراق دقيقة', styleEn: 'Precise hand control'),
  BotProfile(id: 'basel_ai', nameAr: 'باسل', nameEn: 'Basel', seed: 67, level: 81, difficulty: BotDifficulty.master, primary: Color(0xff991b1b), secondary: Color(0xfff87171), styleAr: 'ضغط محسوب', styleEn: 'Calculated pressure'),
  BotProfile(id: 'omar_ai', nameAr: 'عمر', nameEn: 'Omar', seed: 79, level: 74, difficulty: BotDifficulty.pro, primary: Color(0xff1d4ed8), secondary: Color(0xff93c5fd), styleAr: 'شريك موثوق', styleEn: 'Reliable partner'),
  BotProfile(id: 'sara_ai', nameAr: 'سارة', nameEn: 'Sara', seed: 89, level: 78, difficulty: BotDifficulty.master, primary: Color(0xff9333ea), secondary: Color(0xfff0abfc), styleAr: 'مناورة احترافية', styleEn: 'Professional maneuvering'),
];

class Bot3DAvatar extends StatelessWidget {
  final BotProfile profile;
  final double size;
  final bool online;
  final bool showLevel;

  const Bot3DAvatar({
    super.key,
    required this.profile,
    this.size = 48,
    this.online = true,
    this.showLevel = false,
  });

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      width: size,
      height: size,
      child: Stack(
        clipBehavior: Clip.none,
        children: [
          Positioned.fill(
            child: DecoratedBox(
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                gradient: LinearGradient(
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight,
                  colors: [profile.secondary, profile.primary, const Color(0xff07111c)],
                ),
                border: Border.all(color: profile.secondary.withValues(alpha: .9), width: 2.2),
                boxShadow: [
                  BoxShadow(color: profile.primary.withValues(alpha: .55), blurRadius: size * .3, spreadRadius: 1),
                  const BoxShadow(color: Colors.black54, blurRadius: 8, offset: Offset(0, 5)),
                ],
              ),
              child: ClipOval(child: CustomPaint(painter: _BotFacePainter(profile.seed))),
            ),
          ),
          if (online)
            Positioned(
              right: 1,
              bottom: 1,
              child: Container(
                width: size * .21,
                height: size * .21,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: const Color(0xff22c55e),
                  border: Border.all(color: const Color(0xff07111c), width: 2),
                ),
              ),
            ),
          if (showLevel)
            Positioned(
              left: -3,
              bottom: -4,
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                decoration: BoxDecoration(
                  color: const Color(0xff07111c),
                  borderRadius: BorderRadius.circular(7),
                  border: Border.all(color: profile.secondary.withValues(alpha: .8)),
                ),
                child: Text('LV.${profile.level}', style: TextStyle(fontSize: size * .13, fontWeight: FontWeight.w900)),
              ),
            ),
        ],
      ),
    );
  }
}

class _BotFacePainter extends CustomPainter {
  final int seed;
  const _BotFacePainter(this.seed);

  @override
  void paint(Canvas canvas, Size size) {
    final random = math.Random(seed);
    final center = Offset(size.width / 2, size.height / 2);
    final skinChoices = <Color>[
      const Color(0xffffd4ad),
      const Color(0xffe7b48a),
      const Color(0xffc9865f),
      const Color(0xff8f5a3c),
    ];
    final skin = skinChoices[seed % skinChoices.length];
    final faceRect = Rect.fromCenter(center: Offset(center.dx, center.dy * 1.02), width: size.width * .57, height: size.height * .64);

    final neckPaint = Paint()..color = skin;
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromCenter(center: Offset(center.dx, size.height * .82), width: size.width * .22, height: size.height * .24),
        Radius.circular(size.width * .08),
      ),
      neckPaint,
    );

    final shirt = Paint()
      ..shader = const LinearGradient(colors: [Color(0xffe2e8f0), Color(0xff64748b)]).createShader(Rect.fromLTWH(0, size.height * .72, size.width, size.height * .28));
    final shirtPath = Path()
      ..moveTo(size.width * .16, size.height)
      ..quadraticBezierTo(size.width * .22, size.height * .76, size.width * .43, size.height * .75)
      ..lineTo(size.width * .57, size.height * .75)
      ..quadraticBezierTo(size.width * .78, size.height * .76, size.width * .84, size.height)
      ..close();
    canvas.drawPath(shirtPath, shirt);

    final facePaint = Paint()
      ..shader = RadialGradient(
        center: const Alignment(-.25, -.3),
        colors: [Color.lerp(skin, Colors.white, .22)!, skin, Color.lerp(skin, Colors.black, .16)!],
      ).createShader(faceRect);
    canvas.drawRRect(RRect.fromRectAndRadius(faceRect, Radius.circular(size.width * .24)), facePaint);

    final hairColor = [const Color(0xff1f2937), const Color(0xff3f2b1d), const Color(0xff111827), const Color(0xff6b3f24)][seed % 4];
    final hairPaint = Paint()..color = hairColor;
    final hairPath = Path()
      ..moveTo(size.width * .22, size.height * .37)
      ..quadraticBezierTo(size.width * .20, size.height * .08, size.width * .52, size.height * .10)
      ..quadraticBezierTo(size.width * .82, size.height * .10, size.width * .79, size.height * .42)
      ..quadraticBezierTo(size.width * .67, size.height * .27, size.width * .52, size.height * .27)
      ..quadraticBezierTo(size.width * .36, size.height * .25, size.width * .22, size.height * .37)
      ..close();
    canvas.drawPath(hairPath, hairPaint);

    final eyePaint = Paint()..color = const Color(0xff111827);
    final eyeY = size.height * .47;
    final eyeDistance = size.width * .13;
    final eyeRadius = size.width * .025;
    canvas.drawCircle(Offset(center.dx - eyeDistance, eyeY), eyeRadius, eyePaint);
    canvas.drawCircle(Offset(center.dx + eyeDistance, eyeY), eyeRadius, eyePaint);

    final highlight = Paint()..color = Colors.white.withValues(alpha: .85);
    canvas.drawCircle(Offset(center.dx - eyeDistance - 1, eyeY - 1), eyeRadius * .35, highlight);
    canvas.drawCircle(Offset(center.dx + eyeDistance - 1, eyeY - 1), eyeRadius * .35, highlight);

    final browPaint = Paint()
      ..color = hairColor
      ..strokeWidth = math.max(1.2, size.width * .025)
      ..strokeCap = StrokeCap.round;
    final browTilt = (random.nextDouble() - .5) * size.height * .04;
    canvas.drawLine(Offset(center.dx - eyeDistance - size.width * .05, eyeY - size.height * .07), Offset(center.dx - eyeDistance + size.width * .05, eyeY - size.height * .07 + browTilt), browPaint);
    canvas.drawLine(Offset(center.dx + eyeDistance - size.width * .05, eyeY - size.height * .07 - browTilt), Offset(center.dx + eyeDistance + size.width * .05, eyeY - size.height * .07), browPaint);

    final nosePaint = Paint()
      ..color = Color.lerp(skin, Colors.black, .22)!
      ..strokeWidth = math.max(1.0, size.width * .018)
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;
    canvas.drawLine(Offset(center.dx, size.height * .50), Offset(center.dx - size.width * .018, size.height * .61), nosePaint);

    final mouthPaint = Paint()
      ..color = const Color(0xff7f1d1d)
      ..strokeWidth = math.max(1.3, size.width * .023)
      ..style = PaintingStyle.stroke
      ..strokeCap = StrokeCap.round;
    canvas.drawArc(Rect.fromCenter(center: Offset(center.dx, size.height * .67), width: size.width * .18, height: size.height * .08), .12, math.pi - .24, false, mouthPaint);

    if (seed.isEven) {
      final beard = Paint()..color = hairColor.withValues(alpha: .74);
      final beardPath = Path()
        ..moveTo(size.width * .30, size.height * .63)
        ..quadraticBezierTo(size.width * .34, size.height * .83, size.width * .50, size.height * .86)
        ..quadraticBezierTo(size.width * .66, size.height * .83, size.width * .70, size.height * .63)
        ..quadraticBezierTo(size.width * .61, size.height * .76, size.width * .50, size.height * .78)
        ..quadraticBezierTo(size.width * .39, size.height * .76, size.width * .30, size.height * .63)
        ..close();
      canvas.drawPath(beardPath, beard);
    }
  }

  @override
  bool shouldRepaint(covariant _BotFacePainter oldDelegate) => oldDelegate.seed != seed;
}

class ReactionItem {
  final String id;
  final String emoji;
  final String category;
  final String labelAr;
  final String labelEn;
  final bool animated;

  const ReactionItem(this.id, this.emoji, this.category, this.labelAr, this.labelEn, {this.animated = false});
  String label(String locale) => locale == 'ar' ? labelAr : labelEn;
}

const reactionCatalog = <ReactionItem>[
  ReactionItem('like', '👍', 'friendly', 'ممتاز', 'Great'),
  ReactionItem('clap', '👏', 'friendly', 'تصفيق', 'Applause', animated: true),
  ReactionItem('wave', '👋', 'friendly', 'مرحباً', 'Hello', animated: true),
  ReactionItem('heart', '❤️', 'friendly', 'محبة', 'Love', animated: true),
  ReactionItem('respect', '🫡', 'friendly', 'احترام', 'Respect'),
  ReactionItem('handshake', '🤝', 'friendly', 'لعب نظيف', 'Fair play'),
  ReactionItem('coffee', '☕', 'friendly', 'استراحة', 'Break'),
  ReactionItem('flower', '🌹', 'friendly', 'وردة', 'Rose'),
  ReactionItem('laugh', '😂', 'fun', 'ضحك', 'Laugh', animated: true),
  ReactionItem('rofl', '🤣', 'fun', 'مضحك جداً', 'ROFL', animated: true),
  ReactionItem('cool', '😎', 'fun', 'رائع', 'Cool'),
  ReactionItem('wink', '😉', 'fun', 'غمزة', 'Wink'),
  ReactionItem('thinking', '🤔', 'fun', 'أفكر', 'Thinking'),
  ReactionItem('shock', '😱', 'fun', 'مفاجأة', 'Shock', animated: true),
  ReactionItem('oops', '🙈', 'fun', 'أوبس', 'Oops'),
  ReactionItem('sleep', '😴', 'fun', 'انتظار', 'Waiting'),
  ReactionItem('fire', '🔥', 'power', 'ناري', 'Fire', animated: true),
  ReactionItem('bolt', '⚡', 'power', 'سريع', 'Fast', animated: true),
  ReactionItem('rocket', '🚀', 'power', 'انطلاق', 'Launch', animated: true),
  ReactionItem('target', '🎯', 'power', 'إصابة', 'Target'),
  ReactionItem('muscle', '💪', 'power', 'قوة', 'Power'),
  ReactionItem('brain', '🧠', 'power', 'ذكاء', 'Smart'),
  ReactionItem('shield', '🛡️', 'power', 'دفاع', 'Defense'),
  ReactionItem('dragon', '🐉', 'power', 'تنين', 'Dragon', animated: true),
  ReactionItem('crown', '👑', 'victory', 'الملك', 'King', animated: true),
  ReactionItem('cup', '🏆', 'victory', 'البطل', 'Champion', animated: true),
  ReactionItem('medal', '🥇', 'victory', 'المركز الأول', 'First place'),
  ReactionItem('diamond', '💎', 'victory', 'ألماسي', 'Diamond', animated: true),
  ReactionItem('party', '🥳', 'victory', 'احتفال', 'Party', animated: true),
  ReactionItem('confetti', '🎉', 'victory', 'مبروك', 'Congrats', animated: true),
  ReactionItem('star', '⭐', 'victory', 'نجم', 'Star', animated: true),
  ReactionItem('hundred', '💯', 'victory', 'مئة بالمئة', 'Perfect'),
  ReactionItem('angry', '😡', 'mood', 'غاضب', 'Angry', animated: true),
  ReactionItem('sad', '😢', 'mood', 'حزين', 'Sad'),
  ReactionItem('cry', '😭', 'mood', 'بكاء', 'Crying', animated: true),
  ReactionItem('nervous', '😬', 'mood', 'متوتر', 'Nervous'),
  ReactionItem('confused', '😵‍💫', 'mood', 'محتار', 'Confused', animated: true),
  ReactionItem('silent', '🤐', 'mood', 'صامت', 'Silent'),
  ReactionItem('pasha', '👑', 'pasha', 'باشا', 'Pasha', animated: true),
  ReactionItem('lion', '🦁', 'pasha', 'الأسد', 'Lion', animated: true),
  ReactionItem('eagle', '🦅', 'pasha', 'النسر', 'Eagle', animated: true),
  ReactionItem('palace', '🏰', 'pasha', 'القصر', 'Palace'),
  ReactionItem('gem', '🔱', 'pasha', 'ملكي', 'Royal', animated: true),
  ReactionItem('cards', '🃏', 'pasha', 'ورقنا', 'Warqna', animated: true),
  ReactionItem('crescent', '🌙', 'pasha', 'هلال', 'Crescent', animated: true),
  ReactionItem('sparkles', '✨', 'pasha', 'بريق', 'Sparkles', animated: true),
  ReactionItem('check', '✅', 'friendly', 'تمام', 'Done', animated: true),
  ReactionItem('magic', '🪄', 'victory', 'لمسة سحرية', 'Magic move', animated: true),
  ReactionItem('smile_hearts', '🥰', 'friendly', 'محبة كبيرة', 'Lots of love', animated: true),
  ReactionItem('salute', '🤠', 'friendly', 'تحية', 'Salute'),
  ReactionItem('tears_joy', '🥹', 'mood', 'تأثر', 'Touched', animated: true),
  ReactionItem('exploding', '🤯', 'fun', 'مذهل', 'Mind blown', animated: true),
  ReactionItem('ghost', '👻', 'fun', 'مفاجأة', 'Boo', animated: true),
  ReactionItem('tornado', '🌪️', 'power', 'عاصفة', 'Tornado', animated: true),
  ReactionItem('meteor', '☄️', 'power', 'نيزك', 'Meteor', animated: true),
  ReactionItem('gold_medal', '🏅', 'victory', 'وسام', 'Medal', animated: true),
  ReactionItem('drum', '🥁', 'victory', 'طبول الفوز', 'Victory drum', animated: true),
  ReactionItem('rage', '🤬', 'mood', 'غضب', 'Rage', animated: true),
  ReactionItem('broken_heart', '💔', 'mood', 'قلب مكسور', 'Broken heart', animated: true),
  ReactionItem('red_hat', '🧢', 'pasha', 'طربوش الباشا', 'Pasha hat', animated: true),
  ReactionItem('palestine', '🇵🇸', 'friendly', 'فلسطين', 'Palestine', animated: true),
  ReactionItem('prayer', '🤲', 'friendly', 'دعاء', 'Prayer', animated: true),
  ReactionItem('sun', '☀️', 'friendly', 'صباح الخير', 'Good morning', animated: true),
  ReactionItem('moon_face', '🌙', 'friendly', 'مساء الخير', 'Good evening', animated: true),
  ReactionItem('cat_laugh', '😹', 'fun', 'ضحك القط', 'Cat laugh', animated: true),
  ReactionItem('monkey', '🙊', 'fun', 'لن أقول', 'Secret', animated: true),
  ReactionItem('alien', '👽', 'fun', 'فضائي', 'Alien', animated: true),
  ReactionItem('clown', '🤡', 'fun', 'مقلب', 'Joke', animated: true),
  ReactionItem('volcano', '🌋', 'power', 'بركان', 'Volcano', animated: true),
  ReactionItem('lion_roar', '🦁', 'power', 'زئير', 'Roar', animated: true),
  ReactionItem('eagle_fly', '🦅', 'power', 'تحليق', 'Soar', animated: true),
  ReactionItem('boxing', '🥊', 'power', 'تحدي', 'Challenge', animated: true),
  ReactionItem('fireworks', '🎆', 'victory', 'ألعاب نارية', 'Fireworks', animated: true),
  ReactionItem('champagne', '🍾', 'victory', 'احتفال', 'Celebrate', animated: true),
  ReactionItem('royal_crown', '♛', 'victory', 'تاج ملكي', 'Royal crown', animated: true),
  ReactionItem('trophy_gold', '🏆', 'victory', 'الكأس الذهبي', 'Gold trophy', animated: true),
  ReactionItem('steam', '😤', 'mood', 'إصرار', 'Determined', animated: true),
  ReactionItem('pleading', '🥺', 'mood', 'رجاء', 'Please', animated: true),
  ReactionItem('melting', '🫠', 'mood', 'ذبت', 'Melting', animated: true),
  ReactionItem('scream', '😱', 'mood', 'صدمة', 'Scream', animated: true),
  ReactionItem('throne', '🪑', 'pasha', 'العرش', 'Throne', animated: true),
  ReactionItem('sultan', '🕌', 'pasha', 'قصر السلطان', 'Sultan palace', animated: true),
  ReactionItem('falcon', '🦅', 'pasha', 'صقر الباشا', 'Pasha falcon', animated: true),
  ReactionItem('gold_key', '🗝️', 'pasha', 'مفتاح القصر', 'Palace key', animated: true),
  ReactionItem('olive_branch', '🫒', 'friendly', 'غصن زيتون', 'Olive branch', animated: true),
  ReactionItem('peace_dove', '🕊️', 'friendly', 'سلام', 'Peace', animated: true),
  ReactionItem('victory_hand', '✌️', 'friendly', 'نصر', 'Victory', animated: true),
  ReactionItem('arabic_coffee', '☕', 'friendly', 'تفضل قهوة', 'Coffee', animated: true),
  ReactionItem('card_ace', '🂡', 'power', 'آس قوي', 'Power ace', animated: true),
  ReactionItem('wild_card', '🃏', 'power', 'ورقة رابحة', 'Wild card', animated: true),
  ReactionItem('lightning_combo', '🌩️', 'power', 'ضربة برق', 'Lightning combo', animated: true),
  ReactionItem('black_panther', '🐈‍⬛', 'power', 'فهد أسود', 'Black panther', animated: true),
  ReactionItem('wolf_howl', '🐺', 'power', 'عواء الذئب', 'Wolf howl', animated: true),
  ReactionItem('shark', '🦈', 'power', 'قرش', 'Shark', animated: true),
  ReactionItem('scorpion', '🦂', 'power', 'عقرب', 'Scorpion', animated: true),
  ReactionItem('space_ship', '🛸', 'fun', 'مركبة فضائية', 'Spaceship', animated: true),
  ReactionItem('planet', '🪐', 'fun', 'كوكب', 'Planet', animated: true),
  ReactionItem('shooting_star', '🌠', 'fun', 'شهاب', 'Shooting star', animated: true),
  ReactionItem('party_face', '🥸', 'fun', 'تنكر', 'Disguise', animated: true),
  ReactionItem('tada', '🎊', 'victory', 'احتفال كبير', 'Grand celebration', animated: true),
  ReactionItem('podium', '🥇', 'victory', 'منصة الأبطال', 'Podium', animated: true),
  ReactionItem('gem_burst', '💠', 'victory', 'جوهرة', 'Gem burst', animated: true),
  ReactionItem('royal_scepter', '⚜️', 'pasha', 'صولجان', 'Royal scepter', animated: true),
  ReactionItem('desert_palace', '🕌', 'pasha', 'قصر عربي', 'Arabian palace', animated: true),
  ReactionItem('red_heart_fire', '❤️‍🔥', 'mood', 'قلب مشتعل', 'Heart on fire', animated: true),
  ReactionItem('cold_face', '🥶', 'mood', 'تجمّدت', 'Frozen', animated: true),
  ReactionItem('hot_face', '🥵', 'mood', 'الجو حار', 'Too hot', animated: true),
  ReactionItem('facepalm', '🤦', 'mood', 'يا ساتر', 'Facepalm', animated: true),
  ReactionItem('shrug', '🤷', 'mood', 'لا أعرف', 'I do not know', animated: true),
  ReactionItem('eyes', '👀', 'fun', 'أراقب', 'Watching', animated: true),
  ReactionItem('alarm', '⏰', 'fun', 'الوقت', 'Time', animated: true),
  ReactionItem('dice', '🎲', 'fun', 'حظ', 'Luck', animated: true),
  ReactionItem('gold_starburst', '🌟', 'victory', 'نجم ذهبي', 'Golden star', animated: true),
  ReactionItem('champion_belt', '🥋', 'victory', 'حزام البطل', 'Champion belt', animated: true),
  ReactionItem('royal_lion', '🦁', 'pasha', 'أسد ملكي', 'Royal lion', animated: true),
  ReactionItem('falcon_dive', '🦅', 'pasha', 'انقضاض الصقر', 'Falcon dive', animated: true),
];

class ReactionDock extends StatefulWidget {
  final String locale;
  final ValueChanged<ReactionItem> onSelected;
  final Set<String>? unlockedCategories;

  const ReactionDock({super.key, required this.locale, required this.onSelected, this.unlockedCategories});

  @override
  State<ReactionDock> createState() => _ReactionDockState();
}

class _ReactionDockState extends State<ReactionDock> {
  String category = 'friendly';

  @override
  Widget build(BuildContext context) {
    final rtl = widget.locale == 'ar';
    const labelsAr = <String, String>{'friendly': 'ودية', 'fun': 'مرحة', 'power': 'قوية', 'victory': 'فوز', 'mood': 'مشاعر', 'pasha': 'باشا'};
    const labelsEn = <String, String>{'friendly': 'Friendly', 'fun': 'Fun', 'power': 'Power', 'victory': 'Victory', 'mood': 'Mood', 'pasha': 'Pasha'};
    final allowed = widget.unlockedCategories ?? labelsAr.keys.toSet();
    final items = reactionCatalog.where((item) => item.category == category).toList();
    return Directionality(
      textDirection: rtl ? TextDirection.rtl : TextDirection.ltr,
      child: Material(
        color: Colors.transparent,
        child: Container(
          constraints: const BoxConstraints(maxHeight: 330),
          padding: const EdgeInsets.all(10),
          decoration: BoxDecoration(
            color: const Color(0xff0b1724).withValues(alpha: .97),
            borderRadius: BorderRadius.circular(20),
            border: Border.all(color: Colors.white.withValues(alpha: .10)),
            boxShadow: const [BoxShadow(color: Colors.black54, blurRadius: 24, offset: Offset(0, 12))],
          ),
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              SizedBox(
                height: 38,
                child: ListView(
                  scrollDirection: Axis.horizontal,
                  children: labelsAr.keys.map((key) {
                    final unlocked = allowed.contains(key);
                    return Padding(
                      padding: const EdgeInsetsDirectional.only(end: 5),
                      child: ChoiceChip(
                        selected: category == key,
                        onSelected: unlocked ? (_) => setState(() => category = key) : null,
                        avatar: unlocked ? null : const Icon(Icons.lock_outline, size: 14),
                        label: Text((rtl ? labelsAr : labelsEn)[key]!, style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w900)),
                      ),
                    );
                  }).toList(),
                ),
              ),
              const SizedBox(height: 8),
              Flexible(
                child: GridView.builder(
                  shrinkWrap: true,
                  gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 4, childAspectRatio: .95, crossAxisSpacing: 7, mainAxisSpacing: 7),
                  itemCount: items.length,
                  itemBuilder: (context, index) {
                    final item = items[index];
                    return InkWell(
                      onTap: () => widget.onSelected(item),
                      borderRadius: BorderRadius.circular(14),
                      child: Container(
                        padding: const EdgeInsets.all(5),
                        decoration: BoxDecoration(color: Colors.white.withValues(alpha: .055), borderRadius: BorderRadius.circular(14), border: Border.all(color: Colors.white.withValues(alpha: .07))),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            item.animated ? _AnimatedReaction(item.emoji, size: 42) : Text(item.emoji, style: const TextStyle(fontSize: 42)),
                            const SizedBox(height: 2),
                            Text(item.label(widget.locale), maxLines: 1, overflow: TextOverflow.ellipsis, style: const TextStyle(fontSize: 8, color: Colors.white70, fontWeight: FontWeight.w800)),
                          ],
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _AnimatedReaction extends StatefulWidget {
  final String emoji;
  final double size;
  const _AnimatedReaction(this.emoji, {this.size = 38});

  @override
  State<_AnimatedReaction> createState() => _AnimatedReactionState();
}

class _AnimatedReactionState extends State<_AnimatedReaction> with SingleTickerProviderStateMixin {
  late final AnimationController controller;
  late final Animation<double> pulse;

  @override
  void initState() {
    super.initState();
    controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1100))..repeat(reverse: true);
    pulse = Tween(begin: .9, end: 1.12).animate(CurvedAnimation(parent: controller, curve: Curves.easeInOutBack));
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => ScaleTransition(scale: pulse, child: RotationTransition(turns: Tween(begin: -.015, end: .015).animate(controller), child: Text(widget.emoji, style: TextStyle(fontSize: widget.size))));
}

class CoverStyle {
  final String id;
  final String nameAr;
  final String nameEn;
  final List<Color> colors;
  final IconData icon;

  const CoverStyle(this.id, this.nameAr, this.nameEn, this.colors, this.icon);
  String name(String locale) => locale == 'ar' ? nameAr : nameEn;
}

const coverStyles = <CoverStyle>[
  CoverStyle('cover_royal_gold', 'الذهب الملكي', 'Royal Gold', [Color(0xff4b2d08), Color(0xffd39b2a), Color(0xff0b1724)], Icons.workspace_premium_rounded),
  CoverStyle('cover_midnight', 'منتصف الليل', 'Midnight', [Color(0xff020617), Color(0xff1d4ed8), Color(0xff0f172a)], Icons.nights_stay_rounded),
  CoverStyle('cover_emerald', 'زمرد القصر', 'Palace Emerald', [Color(0xff022c22), Color(0xff10b981), Color(0xff07111c)], Icons.diamond_rounded),
  CoverStyle('cover_crimson', 'القرمزي', 'Crimson', [Color(0xff450a0a), Color(0xffef4444), Color(0xff170409)], Icons.local_fire_department_rounded),
  CoverStyle('cover_aurora', 'الشفق القطبي', 'Aurora', [Color(0xff042f2e), Color(0xff22d3ee), Color(0xff7c3aed)], Icons.auto_awesome_rounded),
  CoverStyle('cover_sapphire', 'الياقوت الأزرق', 'Sapphire', [Color(0xff172554), Color(0xff3b82f6), Color(0xff0f172a)], Icons.water_drop_rounded),
  CoverStyle('cover_rose', 'روز غولد', 'Rose Gold', [Color(0xff4c0519), Color(0xfffb7185), Color(0xfff9a8d4)], Icons.favorite_rounded),
  CoverStyle('cover_desert', 'رمال الصحراء', 'Desert Sand', [Color(0xff422006), Color(0xffd97706), Color(0xffffe0a3)], Icons.wb_sunny_rounded),
  CoverStyle('cover_obsidian', 'الأوبسيديان', 'Obsidian', [Color(0xff030712), Color(0xff374151), Color(0xff111827)], Icons.blur_on_rounded),
  CoverStyle('cover_pasha', 'قصر الباشا', 'Pasha Palace', [Color(0xff3f0a0a), Color(0xffb91c1c), Color(0xfff59e0b)], Icons.account_balance_rounded),
  CoverStyle('cover_cosmic', 'المجرة', 'Cosmic', [Color(0xff12033a), Color(0xff7c3aed), Color(0xff06b6d4)], Icons.public_rounded),
  CoverStyle('cover_elite', 'النخبة البيضاء', 'White Elite', [Color(0xff334155), Color(0xffe2e8f0), Color(0xff64748b)], Icons.shield_rounded),
  CoverStyle('cover_phoenix', 'العنقاء الذهبية', 'Golden Phoenix', [Color(0xff3b0a08), Color(0xfff97316), Color(0xffffd166)], Icons.local_fire_department_rounded),
  CoverStyle('cover_ocean', 'موج المحيط', 'Ocean Wave', [Color(0xff021b36), Color(0xff0369a1), Color(0xff22d3ee)], Icons.waves_rounded),
  CoverStyle('cover_neon', 'مدينة النيون', 'Neon City', [Color(0xff090217), Color(0xff7c3aed), Color(0xffec4899)], Icons.location_city_rounded),
  CoverStyle('cover_forest', 'الغابة الملكية', 'Royal Forest', [Color(0xff032018), Color(0xff15803d), Color(0xff84cc16)], Icons.forest_rounded),
  CoverStyle('cover_sunset', 'غروب فاخر', 'Luxury Sunset', [Color(0xff431407), Color(0xffea580c), Color(0xfffbbf24)], Icons.wb_twilight_rounded),
  CoverStyle('cover_ice', 'الكريستال الجليدي', 'Ice Crystal', [Color(0xff082f49), Color(0xff38bdf8), Color(0xffe0f2fe)], Icons.ac_unit_rounded),
  CoverStyle('cover_tiger', 'هيبة النمر', 'Tiger Prestige', [Color(0xff1c1005), Color(0xffb45309), Color(0xffffd166)], Icons.pets_rounded),
  CoverStyle('cover_eagle', 'جناح النسر', 'Eagle Wing', [Color(0xff0f172a), Color(0xff475569), Color(0xfff8fafc)], Icons.flight_rounded),
  CoverStyle('cover_lava', 'حمم أسطورية', 'Legendary Lava', [Color(0xff260303), Color(0xff991b1b), Color(0xffff6b00)], Icons.volcano_rounded),
  CoverStyle('cover_pearl', 'لؤلؤة القصر', 'Palace Pearl', [Color(0xff312e3b), Color(0xffc4b5fd), Color(0xfffffbeb)], Icons.diamond_rounded),
];

CoverStyle coverById(String id) => coverStyles.firstWhere((item) => item.id == id, orElse: () => coverStyles.first);

class ProfileCover extends StatelessWidget {
  final String coverId;
  final double height;
  final Widget? child;
  final bool animated;
  final List<Color>? colors;

  const ProfileCover({super.key, required this.coverId, this.height = 150, this.child, this.animated = true, this.colors});

  @override
  Widget build(BuildContext context) {
    final cover = coverById(coverId);
    return ClipRRect(
      borderRadius: BorderRadius.circular(24),
      child: SizedBox(
        height: height,
        width: double.infinity,
        child: Stack(
          fit: StackFit.expand,
          children: [
            DecoratedBox(decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: colors ?? cover.colors))),
            if (animated) const AmbientTableFX(density: 8, subtle: true),
            Positioned(right: 18, top: 12, child: Icon(cover.icon, size: height * .55, color: Colors.white.withValues(alpha: .10))),
            Positioned.fill(child: DecoratedBox(decoration: BoxDecoration(gradient: LinearGradient(begin: Alignment.topCenter, end: Alignment.bottomCenter, colors: [Colors.transparent, Colors.black.withValues(alpha: .58)])))),
            if (child != null) child!,
          ],
        ),
      ),
    );
  }
}

class AmbientTableFX extends StatefulWidget {
  final int density;
  final bool subtle;
  const AmbientTableFX({super.key, this.density = 10, this.subtle = false});

  @override
  State<AmbientTableFX> createState() => _AmbientTableFXState();
}

class _AmbientTableFXState extends State<AmbientTableFX> with SingleTickerProviderStateMixin {
  late final AnimationController controller;

  @override
  void initState() {
    super.initState();
    controller = AnimationController(vsync: this, duration: const Duration(seconds: 9))..repeat();
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => IgnorePointer(
        child: AnimatedBuilder(
          animation: controller,
          builder: (context, _) => CustomPaint(painter: _AmbientPainter(controller.value, widget.density, widget.subtle)),
        ),
      );
}

class _AmbientPainter extends CustomPainter {
  final double progress;
  final int density;
  final bool subtle;
  const _AmbientPainter(this.progress, this.density, this.subtle);

  @override
  void paint(Canvas canvas, Size size) {
    for (var i = 0; i < density; i++) {
      final seed = i * 97.0;
      final t = (progress + i / density) % 1;
      final x = ((math.sin(seed) + 1) / 2) * size.width;
      final y = size.height * (1.08 - t * 1.15);
      final radius = (subtle ? 1.2 : 2.2) + (i % 3) * .65;
      final opacity = ((math.sin(t * math.pi)).clamp(0, 1) * (subtle ? .18 : .34)).toDouble();
      final paint = Paint()
        ..color = (i.isEven ? const Color(0xffffd166) : const Color(0xff67e8f9)).withValues(alpha: opacity)
        ..maskFilter = const MaskFilter.blur(BlurStyle.normal, 4);
      canvas.drawCircle(Offset(x, y), radius, paint);
    }
  }

  @override
  bool shouldRepaint(covariant _AmbientPainter oldDelegate) => oldDelegate.progress != progress || oldDelegate.density != density || oldDelegate.subtle != subtle;
}

class AvatarCropDialog extends StatefulWidget {
  final Uint8List bytes;
  final String title;
  const AvatarCropDialog({super.key, required this.bytes, this.title = 'معاينة وقص الصورة'});

  @override
  State<AvatarCropDialog> createState() => _AvatarCropDialogState();
}

class _AvatarCropDialogState extends State<AvatarCropDialog> {
  final boundaryKey = GlobalKey();
  final transform = TransformationController();
  ui.Image? decoded;
  bool exporting = false;

  @override
  void initState() {
    super.initState();
    ui.decodeImageFromList(widget.bytes, (image) {
      if (mounted) setState(() => decoded = image);
    });
  }

  @override
  void dispose() {
    transform.dispose();
    decoded?.dispose();
    super.dispose();
  }

  Future<void> export() async {
    setState(() => exporting = true);
    try {
      await Future<void>.delayed(const Duration(milliseconds: 30));
      final boundary = boundaryKey.currentContext?.findRenderObject() as RenderRepaintBoundary?;
      if (boundary == null) return;
      final image = await boundary.toImage(pixelRatio: 2.0);
      final bytes = await image.toByteData(format: ui.ImageByteFormat.png);
      image.dispose();
      if (!mounted || bytes == null) return;
      Navigator.pop(context, bytes.buffer.asUint8List());
    } finally {
      if (mounted) setState(() => exporting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final image = decoded;
    const viewport = 290.0;
    double imageWidth = viewport;
    double imageHeight = viewport;
    if (image != null) {
      final ratio = image.width / image.height;
      if (ratio >= 1) {
        imageHeight = viewport;
        imageWidth = viewport * ratio;
      } else {
        imageWidth = viewport;
        imageHeight = viewport / ratio;
      }
    }
    return AlertDialog(
      title: Text(widget.title, style: const TextStyle(fontWeight: FontWeight.w900)),
      content: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Text('اسحب الصورة وقرّب أو بعّد بإصبعين، ثم اعتمد المعاينة.', style: TextStyle(fontSize: 11, color: Colors.white60)),
          const SizedBox(height: 12),
          Center(
            child: RepaintBoundary(
              key: boundaryKey,
              child: ClipRRect(
                borderRadius: BorderRadius.circular(30),
                child: Container(
                  width: viewport,
                  height: viewport,
                  color: const Color(0xff0a1420),
                  child: InteractiveViewer(
                    transformationController: transform,
                    minScale: 1,
                    maxScale: 5,
                    boundaryMargin: const EdgeInsets.all(180),
                    constrained: false,
                    child: SizedBox(width: imageWidth, height: imageHeight, child: Image.memory(widget.bytes, fit: BoxFit.contain)),
                  ),
                ),
              ),
            ),
          ),
          const SizedBox(height: 9),
          TextButton.icon(onPressed: () => transform.value = Matrix4.identity(), icon: const Icon(Icons.restart_alt), label: const Text('إعادة الضبط')),
        ],
      ),
      actions: [
        TextButton(onPressed: exporting ? null : () => Navigator.pop(context), child: const Text('إلغاء')),
        FilledButton.icon(onPressed: exporting ? null : export, icon: exporting ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2)) : const Icon(Icons.crop_rounded), label: const Text('اعتماد الصورة')),
      ],
    );
  }
}

class FloatingReaction extends StatefulWidget {
  final ReactionItem reaction;
  final VoidCallback? onCompleted;
  const FloatingReaction({super.key, required this.reaction, this.onCompleted});

  @override
  State<FloatingReaction> createState() => _FloatingReactionState();
}

class _FloatingReactionState extends State<FloatingReaction> with SingleTickerProviderStateMixin {
  late final AnimationController controller;
  late final Animation<double> scale;
  late final Animation<double> fade;
  late final Animation<Offset> slide;
  late final Animation<double> rotation;

  @override
  void initState() {
    super.initState();
    controller = AnimationController(vsync: this, duration: const Duration(milliseconds: 1700));
    scale = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: .2, end: 1.18).chain(CurveTween(curve: Curves.easeOutBack)), weight: 40),
      TweenSequenceItem(tween: Tween(begin: 1.18, end: 1.0), weight: 25),
      TweenSequenceItem(tween: Tween(begin: 1.0, end: .75), weight: 35),
    ]).animate(controller);
    fade = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: 0, end: 1), weight: 20),
      TweenSequenceItem(tween: ConstantTween(1), weight: 55),
      TweenSequenceItem(tween: Tween(begin: 1, end: 0), weight: 25),
    ]).animate(controller);
    slide = Tween(begin: const Offset(0, .20), end: const Offset(0, -.38)).animate(CurvedAnimation(parent: controller, curve: Curves.easeOutCubic));
    rotation = TweenSequence<double>([
      TweenSequenceItem(tween: Tween(begin: -.12, end: .10).chain(CurveTween(curve: Curves.easeOutBack)), weight: 45),
      TweenSequenceItem(tween: Tween(begin: .10, end: -.04), weight: 25),
      TweenSequenceItem(tween: Tween(begin: -.04, end: 0), weight: 30),
    ]).animate(controller);
    controller.forward().whenComplete(() => widget.onCompleted?.call());
  }

  @override
  void dispose() {
    controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => FadeTransition(
        opacity: fade,
        child: SlideTransition(
          position: slide,
          child: ScaleTransition(
            scale: scale,
            child: AnimatedBuilder(
              animation: controller,
              builder: (context, child) => Transform(
                alignment: Alignment.center,
                transform: Matrix4.identity()
                  ..setEntry(3, 2, .0014)
                  ..rotateY(rotation.value)
                  ..rotateZ(rotation.value * .42),
                child: child,
              ),
              child: Container(
                padding: const EdgeInsets.symmetric(horizontal: 22, vertical: 15),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(begin: Alignment.topLeft, end: Alignment.bottomRight, colors: [Color(0xee20364d), Color(0xee07111c), Color(0xee341d4d)]),
                  borderRadius: BorderRadius.circular(26),
                  border: Border.all(color: const Color(0xffffd166).withValues(alpha: .55), width: 1.5),
                  boxShadow: const [
                    BoxShadow(color: Color(0x99000000), blurRadius: 26, offset: Offset(0, 12)),
                    BoxShadow(color: Color(0x555de7ff), blurRadius: 24, spreadRadius: 1),
                    BoxShadow(color: Color(0x44ffd166), blurRadius: 18, spreadRadius: 1),
                  ],
                ),
                child: Stack(alignment: Alignment.center, children: [
                  Text(widget.reaction.emoji, style: TextStyle(fontSize: 70, foreground: Paint()..color = const Color(0x22ffffff))),
                  Text(widget.reaction.emoji, style: const TextStyle(fontSize: 58, shadows: [Shadow(color: Color(0xaa000000), blurRadius: 9, offset: Offset(0, 5)), Shadow(color: Color(0x88ffffff), blurRadius: 10)])),
                ]),
              ),
            ),
          ),
        ),
      );
}
