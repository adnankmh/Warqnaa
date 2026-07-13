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
            'syrian_tarneeb' => self::entry('طرنيب سوري','Syrian Tarneeb','global_syrian_tarneeb_final_v1',4,4,true,13,52,['bid','pass','choose_trump','play_card'],
                'طرنيب شراكة لأربعة لاعبين مع مزايدة 7–13، دوران عكسي، وإلزام اتباع النوع.',
                'Four-player partnership Tarneeb with 7–13 bidding, counter-clockwise turns and mandatory follow-suit.'),
            'tarneeb_400' => self::entry('طرنيب 400','Tarneeb 400','global_tarneeb_400_final_v1',4,4,true,13,52,['bid','pass','play_card'],
                'لعبة شراكة هدفها الوصول إلى 400 نقطة، والكبة هي الطرنيب الثابت. يحتسب الطلب واللمّات وفق نظام 400.',
                'A partnership game played to 400 points with Hearts as fixed trump and 400-style bidding/scoring.'),
            'trix' => self::entry('تركس','Trix','global_trix_final_v1',4,4,false,13,52,['choose_contract','play_card'],
                'أربع ممالك لكل لاعب، ويختار صاحب المملكة واحدة من خمس طلبات: شيخ الكبة، بنات، ديناري، لطوش أو تركس. الفوز للأعلى نقاطاً بعد اكتمال الممالك.',
                'Each player owns a kingdom and selects one of five contracts. The highest total score after all kingdoms wins.'),
            'trix_partner' => self::entry('تركس شراكة','Partnership Trix','global_trix_partner_final_v1',4,4,true,13,52,['choose_contract','play_card'],
                'قواعد تركس مع فريقين متقابلين، وتجمع نقاط الشريكين معاً.',
                'Trix rules played by two opposing partnerships; partners combine their scores.'),
            'trix_complex' => self::entry('تركس كمبلكس','Trix Complex','global_trix_complex_final_v1',4,4,false,13,52,['choose_contract','play_card'],
                'يجمع الكمبلكس عقوبات شيخ الكبة والبنات والديناري واللطوش في طلب واحد، مع طلب تركس منفصل حسب النمط.',
                'Complex combines King of Hearts, Queens, Diamonds and Tricks penalties into one contract, with Trix handled separately.'),
            'hand' => self::entry('هاند','Hand','global_saudi_hand_final_v1',2,5,false,14,106,['draw_deck','draw_discard','meld_batch','meld','lay_off','discard'],
                'تستخدم مجموعتان مع جوكرين. يسحب اللاعب ثم ينزل مجموعات أو يركّب، ويجب أن يرمي ورقة قبل انتهاء دوره. تنتهي الجولة عند نفاد يد لاعب.',
                'Uses two decks plus jokers. Draw, open with one or several legal melds totaling at least 51, optionally lay off, then discard; the round ends when a player empties their hand.'),
            'hand_partner' => self::entry('هاند شراكة','Partnership Hand','global_hand_partnership_final_v1',4,4,true,14,106,['draw_deck','draw_discard','meld_batch','meld','lay_off','discard'],
                'هاند بنظام فريقين متقابلين، مع افتتاح 51 عبر مجموعة واحدة أو عدة مجموعات، ثم التركيب والرمي واحتساب الفريق كوحدة واحدة.',
                'Hand played by two partnerships with an atomic 51-point opening, legal sets/runs, lay-offs and combined team scoring.'),
            'saudi_hand' => self::entry('هاند سعودي','Saudi Hand','global_saudi_hand_final_v1',2,5,false,14,106,['draw_deck','draw_discard','meld_batch','meld','lay_off','discard'],
                'هاند سعودي لعدد 2–5 لاعبين: سحب، افتتاح 51 عبر عدة مجموعات، تركيب على المجموعات، ثم رمي، وتحتسب الأوراق المتبقية.',
                'Saudi Hand for 2–5 players: draw, complete an atomic 51-point opening, meld/lay off, discard, and score remaining cards.'),
            'banakil' => self::entry('بناكل','Banakil','global_banakil_final_v2',4,4,true,18,106,['draw_deck','draw_discard','meld_batch','meld','lay_off','discard'],
                'بناكل شراكة لأربعة لاعبين باستخدام مجموعتين مع الجوكر والاثنين البري؛ 18 ورقة لكل لاعب، وافتتاح 51 يمكن جمعه من عدة مجموعات، ثم التركيب والرمي.',
                'Four-player partnership Banakil using two decks, jokers and wild twos; each player receives 18 cards and may combine several melds for the 51-point opening before laying off and discarding.'),
            'baloot' => self::entry('بلوت','Baloot','global_baloot_final_v1',4,4,true,8,32,['bid','pass','choose_trump','play_card'],
                'أربعة لاعبين بفريقين، 32 ورقة، شراء صن أو حكم ثم لعب اللمّات مع ترتيب وقيم مختلفة بين الصن والحكم.',
                'Four players in partnerships using 32 cards; bidding selects Sun or Hokm, each with its own rank and scoring order.'),
            'basra' => self::entry('باصرة','Basra','universal_basra_rules',2,2,false,4,52,['play_card','capture'],
                'أربعة أوراق لكل لاعب وأربع على الأرض. يلتقط اللاعب الورقة المماثلة أو مجموعاً يساوي قيمة ورقته، وللولد و7 الديناري أحكام خاصة.',
                'Four cards per player and four on the table. Capture equal ranks or combinations matching the played value; Jacks and 7♦ have special powers.'),
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
