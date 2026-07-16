<?php

namespace App\Services\GameEngine;

/**
 * Canonical registry used by the mobile API, admin audit and client UI.
 * Rules are original summaries of common public-domain game mechanics.
 */
final class EngineRegistry
{
    /** @return array<string,array<string,mixed>> */
    public static function all(): array
    {
        return [
            'tarneeb' => self::entry('طرنيب','Tarneeb','tarneeb_standalone_v2',4,4,true,13,52,['bid','pass','choose_trump','play_card'],
                'توزّع 13 ورقة لكل لاعب. المزايدة من 7 إلى 13، وصاحب أعلى طلب يختار الطرنيب. يجب اتباع النوع إن وُجد، وتفوز أعلى ورقة من النوع المتصدر ما لم تُلعب ورقة طرنيب.',
                'Each player receives 13 cards. Bids run from 7 to 13; the highest bidder chooses trump. Players must follow suit when possible.'),
            'tarneeb_41' => self::entry('طرنيب 41','Tarneeb 41','tarneeb_standalone_v2',4,4,true,13,52,['bid','pass','choose_trump','play_card'],
                'طرنيب كامل بهدف 41 نقطة مع مزايدة 7–13 وإلزام اتباع النوع.',
                'Full Tarneeb played to 41 points with 7–13 bidding and mandatory follow-suit.'),
            'tarneeb_61' => self::entry('طرنيب 61','Tarneeb 61','tarneeb_standalone_v2',4,4,true,13,52,['bid','pass','choose_trump','play_card'],
                'طرنيب كامل بهدف 61 نقطة للمباريات الطويلة.',
                'Full Tarneeb played to 61 points for longer matches.'),
            'syrian_tarneeb' => self::entry('طرنيب سوري','Syrian Tarneeb','global_syrian_tarneeb_v185',4,4,true,13,52,['bid','play_card'],
                'يطلب كل لاعب مرة واحدة من 2 إلى 13، وتعاد الجولة إذا كان مجموع الطلبات أقل من 11. يحدد الطرنيب من الورقة الأخيرة المكشوفة ويحسب الطلب لكل لاعب بصورة مستقلة داخل فريقه.',
                'Each player declares once from 2 to 13; the deal repeats if total declarations are below 11. Trump is derived from the exposed last card and scoring is individual inside the partnership.'),
            'tarneeb_400' => self::entry('طرنيب 400','Tarneeb 400','global_tarneeb_400_final_v1',4,4,true,13,52,['bid','pass','play_card'],
                'لعبة شراكة لبنانية بنظام 400: الكبة هي الطرنيب الثابت، ويحسب طلب كل لاعب بصورة مستقلة، والفوز عند تحقق شرط 41 داخل الفريق.',
                'Lebanese 400-style partnership play with Hearts as fixed trump, individual declarations and the 41-point team victory condition.'),
            'trix' => self::entry('تركس','Trix','global_trix_final_v1',4,4,false,13,52,['choose_contract','play_card'],
                'أربع ممالك لكل لاعب، ويختار صاحب المملكة واحدة من خمس طلبات: شيخ الكبة، بنات، ديناري، لطوش أو تركس. الفوز للأعلى نقاطاً بعد اكتمال الممالك.',
                'Each player owns a kingdom and selects one of five contracts. The highest total score after all kingdoms wins.'),
            'trix_partner' => self::entry('تركس شراكة','Partnership Trix','global_trix_partner_final_v1',4,4,true,13,52,['choose_contract','play_card'],
                'قواعد تركس مع فريقين متقابلين، وتجمع نقاط الشريكين معاً.',
                'Trix rules played by two opposing partnerships; partners combine their scores.'),
            'trix_complex' => self::entry('تركس كمبلكس','Trix Complex','global_trix_complex_final_v1',4,4,false,13,52,['choose_contract','play_card'],
                'يجمع الكمبلكس عقوبات شيخ الكبة والبنات والديناري واللطوش في طلب واحد، مع طلب تركس منفصل حسب النمط.',
                'Complex combines King of Hearts, Queens, Diamonds and Tricks penalties into one contract, with Trix handled separately.'),
            'hand' => self::entry('هاند','Hand','global_saudi_hand_final_v1',2,5,false,14,106,['draw_deck','draw_discard','meld','discard'],
                'تستخدم مجموعتان مع جوكرين. يسحب اللاعب ثم ينزل مجموعات أو يركّب، ويجب أن يرمي ورقة قبل انتهاء دوره. تنتهي الجولة عند نفاد يد لاعب.',
                'Uses two decks plus two jokers. Draw, optionally meld/lay off, then discard; the round ends when a player empties their hand.'),
            'hand_partner' => self::entry('هاند شراكة','Partnership Hand','global_hand_partnership_final_v1',4,4,true,14,106,['draw_deck','draw_discard','meld','discard'],
                'هاند بنظام فريقين متقابلين، مع احتساب نزول ونقاط الفريق كوحدة واحدة.',
                'Hand played by two partnerships, with team meld thresholds and combined scoring.'),
            'saudi_hand' => self::entry('هاند سعودي','Saudi Hand','global_saudi_hand_final_v1',2,5,false,14,106,['draw_deck','draw_discard','meld','discard'],
                'هاند سعودي لعدد 2–5 لاعبين، سحب ثم نزول/تركيب ثم رمي، وتحتسب الأوراق المتبقية على الخاسرين.',
                'Saudi Hand for 2–5 players: draw, meld/lay off, discard, and score remaining cards against losing players.'),
            'banakil' => self::entry('بناكل','Banakil','global_banakil_final_v1',2,4,true,18,106,['draw_deck','draw_discard','meld','layoff','discard','organize'],
                'توزّع 18 ورقة لكل لاعب وورقة إضافية للبادئ. النزول من 3 أوراق على الأقل بلا حد افتتاح، والجوكر و2 أوراق بديلة بضوابط المجموعة.',
                'Deals 18 cards each plus one extra to the starter. Melds require at least three cards with no opening threshold; jokers and twos are controlled wild cards.'),
            'pinochle' => self::entry('بناكل كلاسيك','Classic Banakil','global_banakil_classic_final_v1',2,4,true,18,106,['draw_deck','draw_discard','meld','layoff','discard','organize'],
                'نسخة البناكل الكلاسيكية بهدف 222 نقطة للشراكة أو 150 للمواجهة الفردية.',
                'Classic Banakil to 222 points in partnerships or 150 in one-versus-one play.'),
            'solitaire_multiplayer' => self::entry('سوليتير تنافسي','Competitive Solitaire','global_competitive_solitaire_final_v1',2,4,false,13,52,['draw','play_card','move_card'],
                'سباق سوليتير متزامن؛ يفوز من ينهي أساساته أولاً وفق الحركات القانونية.',
                'Synchronized competitive solitaire; the first player to complete legal foundations wins.'),
            'baloot' => self::entry('بلوت','Baloot','global_baloot_final_v1',4,4,true,8,32,['bid','pass','choose_trump','play_card'],
                'أربعة لاعبين بفريقين، 32 ورقة، شراء صن أو حكم ثم لعب اللمّات مع ترتيب وقيم مختلفة بين الصن والحكم.',
                'Four players in partnerships using 32 cards; bidding selects Sun or Hokm, each with its own rank and scoring order.'),
            'basra' => self::entry('باصرة','Basra','universal_basra_rules',2,2,false,4,52,['play_card','capture'],
                'أربعة أوراق لكل لاعب وأربع على الأرض. يلتقط اللاعب الورقة المماثلة أو مجموعاً يساوي قيمة ورقته، وللولد و7 الديناري أحكام خاصة.',
                'Four cards per player and four on the table. Capture equal ranks or combinations matching the played value; Jacks and 7♦ have special powers.'),
            'domino' => self::entry('دومينو','Domino','dedicated_domino_v142',2,4,false,7,28,['play_tile','draw','pass'],
                'محرك دومينو مخصص بمجموعة 28 حجرًا فريدة والتحقق من الطرف المفتوح والسحب والتمرير.',
                'Dedicated domino engine with 28 unique tiles, open-end validation, drawing and passing.'),
            'backgammon' => self::entry('طاولة الزهر','Backgammon','dedicated_backgammon_v142',2,2,false,15,30,['roll_dice','move'],
                'محرك طاولة زهر يتحقق من اتجاه الحركة، النرد، أولوية دخول الأحجار من البار والإخراج.',
                'Backgammon engine validates movement direction, dice, bar-entry priority and bearing off.'),
            'jackaroo' => self::entry('جاكارو','Jackaroo','dedicated_jackaroo_v142',4,4,true,4,52,['play_card','move_token'],
                'جاكارو بأربعة لاعبين وفريقين مع إخراج الأحجار وتحريكها حسب قيمة الورقة.',
                'Four-player partnership Jackaroo with card-driven marble release and movement.'),
            'chess' => self::entry('شطرنج','Chess','dedicated_chess_v142',2,2,false,16,32,['move'],
                'شطرنج كامل يتحقق من الحركات القانونية والتبييت والأخذ بالتجاوز.',
                'Chess engine validates legal moves, castling and en-passant.'),
        ];
    }

    /** @return array<string,mixed> */
    private static function entry(string $ar,string $en,string $engine,int $min,int $max,bool $partnership,int $hand,int $deck,array $actions,string $rulesAr,string $rulesEn): array
    {
        return compact('engine','min','max','partnership','hand','deck','actions') + [
            'name'=>['ar'=>$ar,'en'=>$en],
            'rules'=>['ar'=>$rulesAr,'en'=>$rulesEn],
            'server_authoritative'=>true,
            'free_play'=>true,
            'fair_shuffle'=>'server_seeded_shuffle_with_unique_deck_validation',
        ];
    }

    /** @return array<string,mixed>|null */
    public static function get(string $key): ?array
    {
        return self::all()[$key] ?? null;
    }
}
