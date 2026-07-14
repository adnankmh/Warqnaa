part of 'main.dart';

const bool warqnaOnlineOnlyV173 = false;

class PashaStyleV173 {
  final String key;
  final String nameAr;
  final String nameEn;
  final String primaryHex;
  final String darkHex;
  final String asset;
  const PashaStyleV173(this.key, this.nameAr, this.nameEn, this.primaryHex, this.darkHex, this.asset);
}

const List<PashaStyleV173> pashaStylesV173 = <PashaStyleV173>[
  PashaStyleV173('yellow', 'أصفر', 'Yellow', '#f6c915', '#9b6d00', 'assets/images/pasha/v173/pasha_yellow.png'),
  PashaStyleV173('red', 'أحمر', 'Red', '#b70822', '#6f0012', 'assets/images/pasha/v173/pasha_red.png'),
  PashaStyleV173('blue', 'أزرق', 'Blue', '#1e55b5', '#0b2a70', 'assets/images/pasha/v173/pasha_blue.png'),
  PashaStyleV173('green', 'أخضر', 'Green', '#006b58', '#00382f', 'assets/images/pasha/v173/pasha_green.png'),
  PashaStyleV173('purple', 'بنفسجي', 'Purple', '#7b318b', '#45164e', 'assets/images/pasha/v173/pasha_purple.png'),
  PashaStyleV173('bronze', 'برونزي', 'Bronze', '#b57b38', '#68401d', 'assets/images/pasha/v173/pasha_bronze.png'),
  PashaStyleV173('gold', 'ذهبي', 'Gold', '#d79d31', '#7b4e0c', 'assets/images/pasha/v173/pasha_gold.png'),
  PashaStyleV173('orange', 'برتقالي', 'Orange', '#ff6515', '#a32d00', 'assets/images/pasha/v173/pasha_orange.png'),
  PashaStyleV173('pink', 'وردي', 'Pink', '#e42f8a', '#8a104a', 'assets/images/pasha/v173/pasha_pink.png'),
  PashaStyleV173('silver', 'فضي', 'Silver', '#c9ccd2', '#737883', 'assets/images/pasha/v173/pasha_silver.png'),
  PashaStyleV173('platinum', 'بلاتيني', 'Platinum', '#e0e4e9', '#8d96a3', 'assets/images/pasha/v173/pasha_platinum.png'),
  PashaStyleV173('navy', 'كحلي', 'Navy', '#16265e', '#091333', 'assets/images/pasha/v173/pasha_navy.png'),
  PashaStyleV173('white', 'أبيض', 'White', '#f2f3f5', '#aeb4bd', 'assets/images/pasha/v173/pasha_white.png'),
];

PashaStyleV173 pashaStyleV173(String key) => pashaStylesV173.firstWhere(
  (style) => style.key == (key == 'black' ? 'red' : key),
  orElse: () => pashaStylesV173[1],
);

const List<int> competitionTicketValuesV173 = <int>[50,100,200,500,1000,2000,4000,5000,8000,10000,20000,30000,50000,100000];

List<StoreProduct> buildV173StoreProducts() => <StoreProduct>[
    StoreProduct(id: 'pasha_style_yellow_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا أصفر', nameEn: 'Yellow Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون أصفر: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full yellow Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'yellow', previewColor1: Color(0xfff6c915), previewColor2: Color(0xff9b6d00), imageAsset: 'assets/images/pasha/v173/pasha_yellow.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_red_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا أحمر', nameEn: 'Red Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون أحمر: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full red Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'red', previewColor1: Color(0xffb70822), previewColor2: Color(0xff6f0012), imageAsset: 'assets/images/pasha/v173/pasha_red.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_blue_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا أزرق', nameEn: 'Blue Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون أزرق: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full blue Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'blue', previewColor1: Color(0xff1e55b5), previewColor2: Color(0xff0b2a70), imageAsset: 'assets/images/pasha/v173/pasha_blue.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_green_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا أخضر', nameEn: 'Green Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون أخضر: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full green Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'green', previewColor1: Color(0xff006b58), previewColor2: Color(0xff00382f), imageAsset: 'assets/images/pasha/v173/pasha_green.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_purple_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا بنفسجي', nameEn: 'Purple Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون بنفسجي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full purple Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'purple', previewColor1: Color(0xff7b318b), previewColor2: Color(0xff45164e), imageAsset: 'assets/images/pasha/v173/pasha_purple.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_bronze_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا برونزي', nameEn: 'Bronze Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون برونزي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full bronze Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'bronze', previewColor1: Color(0xffb57b38), previewColor2: Color(0xff68401d), imageAsset: 'assets/images/pasha/v173/pasha_bronze.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_gold_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا ذهبي', nameEn: 'Gold Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون ذهبي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full gold Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'gold', previewColor1: Color(0xffd79d31), previewColor2: Color(0xff7b4e0c), imageAsset: 'assets/images/pasha/v173/pasha_gold.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_orange_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا برتقالي', nameEn: 'Orange Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون برتقالي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full orange Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'orange', previewColor1: Color(0xffff6515), previewColor2: Color(0xffa32d00), imageAsset: 'assets/images/pasha/v173/pasha_orange.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_pink_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا وردي', nameEn: 'Pink Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون وردي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full pink Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'pink', previewColor1: Color(0xffe42f8a), previewColor2: Color(0xff8a104a), imageAsset: 'assets/images/pasha/v173/pasha_pink.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_silver_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا فضي', nameEn: 'Silver Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون فضي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full silver Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'silver', previewColor1: Color(0xffc9ccd2), previewColor2: Color(0xff737883), imageAsset: 'assets/images/pasha/v173/pasha_silver.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_platinum_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا بلاتيني', nameEn: 'Platinum Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون بلاتيني: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full platinum Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'platinum', previewColor1: Color(0xffe0e4e9), previewColor2: Color(0xff8d96a3), imageAsset: 'assets/images/pasha/v173/pasha_platinum.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_navy_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا كحلي', nameEn: 'Navy Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون كحلي: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full navy Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'navy', previewColor1: Color(0xff16265e), previewColor2: Color(0xff091333), imageAsset: 'assets/images/pasha/v173/pasha_navy.png', collection: 'pasha_v173'),
    StoreProduct(id: 'pasha_style_white_v173', category: 'pasha_style', icon: '🎩', nameAr: 'طربوش باشا أبيض', nameEn: 'White Pasha Fez', descriptionAr: 'هوية باشا كاملة باللون أبيض: الطربوش والوهج والاسم والدردشة واللون الرئيسي.', descriptionEn: 'Full white Pasha identity: fez, glow, name, chat and accent color.', price: 6500, value: 'white', previewColor1: Color(0xfff2f3f5), previewColor2: Color(0xffaeb4bd), imageAsset: 'assets/images/pasha/v173/pasha_white.png', collection: 'pasha_v173'),
    StoreProduct(id: 'table_v173_royal_01', category: 'tables', icon: '🃏', nameAr: 'طاولة الزمرد الملكي', nameEn: 'Royal Collection 01 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 12000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_01.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_02', category: 'tables', icon: '🃏', nameAr: 'طاولة المخمل القرمزي', nameEn: 'Royal Collection 02 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 12850, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_02.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_03', category: 'tables', icon: '🃏', nameAr: 'طاولة المحيط الأزرق', nameEn: 'Royal Collection 03 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 13700, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_03.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_04', category: 'tables', icon: '🃏', nameAr: 'طاولة الليل الذهبي', nameEn: 'Royal Collection 04 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 14550, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_04.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_05', category: 'tables', icon: '🃏', nameAr: 'طاولة البنفسج الإمبراطوري', nameEn: 'Royal Collection 05 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 15400, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_05.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_06', category: 'tables', icon: '🃏', nameAr: 'طاولة الغابة العميقة', nameEn: 'Royal Collection 06 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 16250, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_06.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_07', category: 'tables', icon: '🃏', nameAr: 'طاولة البرونز العتيق', nameEn: 'Royal Collection 07 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 17100, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_07.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_08', category: 'tables', icon: '🃏', nameAr: 'طاولة الياقوت الأحمر', nameEn: 'Royal Collection 08 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 17950, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_08.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_09', category: 'tables', icon: '🃏', nameAr: 'طاولة الفيروز الفاخر', nameEn: 'Royal Collection 09 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 18800, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_09.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_10', category: 'tables', icon: '🃏', nameAr: 'طاولة الفضة الملكية', nameEn: 'Royal Collection 10 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 19650, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_10.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_11', category: 'tables', icon: '🃏', nameAr: 'طاولة الأسد الذهبي', nameEn: 'Royal Collection 11 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 20500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_11.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_12', category: 'tables', icon: '🃏', nameAr: 'طاولة النمر الملكي', nameEn: 'Royal Collection 12 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 21350, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_12.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_13', category: 'tables', icon: '🃏', nameAr: 'طاولة الحصان الأسود', nameEn: 'Royal Collection 13 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 22200, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_13.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_14', category: 'tables', icon: '🃏', nameAr: 'طاولة الصقر الجبلي', nameEn: 'Royal Collection 14 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 23050, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_14.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_15', category: 'tables', icon: '🃏', nameAr: 'طاولة الذئب الأزرق', nameEn: 'Royal Collection 15 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 23900, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_15.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_16', category: 'tables', icon: '🃏', nameAr: 'طاولة التنين الناري', nameEn: 'Royal Collection 16 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 24750, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_16.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_17', category: 'tables', icon: '🃏', nameAr: 'طاولة العقاب الفضي', nameEn: 'Royal Collection 17 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 25600, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_17.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_18', category: 'tables', icon: '🃏', nameAr: 'طاولة الفهد الليلي', nameEn: 'Royal Collection 18 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 26450, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_18.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_19', category: 'tables', icon: '🃏', nameAr: 'طاولة القرش الأزرق', nameEn: 'Royal Collection 19 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 27300, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_19.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_20', category: 'tables', icon: '🃏', nameAr: 'طاولة الحوت العميق', nameEn: 'Royal Collection 20 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 28150, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_20.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_21', category: 'tables', icon: '🃏', nameAr: 'طاولة السباق الأسود', nameEn: 'Royal Collection 21 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 29000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_21.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_22', category: 'tables', icon: '🃏', nameAr: 'طاولة السيارة الحمراء', nameEn: 'Royal Collection 22 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 29850, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_22.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_23', category: 'tables', icon: '🃏', nameAr: 'طاولة العضلات الكلاسيكية', nameEn: 'Royal Collection 23 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 30700, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_23.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_24', category: 'tables', icon: '🃏', nameAr: 'طاولة السرعة الزرقاء', nameEn: 'Royal Collection 24 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 31550, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_24.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_25', category: 'tables', icon: '🃏', nameAr: 'طاولة الدراجة الخضراء', nameEn: 'Royal Collection 25 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 32400, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_25.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_26', category: 'tables', icon: '🃏', nameAr: 'طاولة الدراجة النارية', nameEn: 'Royal Collection 26 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 33250, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_26.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_27', category: 'tables', icon: '🃏', nameAr: 'طاولة البومة القمرية', nameEn: 'Royal Collection 27 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 34100, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_27.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_28', category: 'tables', icon: '🃏', nameAr: 'طاولة الذئب الثلجي', nameEn: 'Royal Collection 28 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 34950, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_28.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_29', category: 'tables', icon: '🃏', nameAr: 'طاولة التنين الذهبي', nameEn: 'Royal Collection 29 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 35800, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_29.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_royal_30', category: 'tables', icon: '🃏', nameAr: 'طاولة العنقاء النارية', nameEn: 'Royal Collection 30 Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 36650, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/royal/table_v173_royal_30.jpg', collection: 'v173_royal'),
    StoreProduct(id: 'table_v173_showcase_01', category: 'tables', icon: '🃏', nameAr: 'طاولة الأسد الملكي', nameEn: 'Royal Lion Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 28000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_01.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_02', category: 'tables', icon: '🃏', nameAr: 'طاولة النمر الأبيض', nameEn: 'White Tiger Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 29500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_02.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_03', category: 'tables', icon: '🃏', nameAr: 'طاولة الحصان الأسود', nameEn: 'Black Stallion Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 31000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_03.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_04', category: 'tables', icon: '🃏', nameAr: 'طاولة العقاب الجبلي', nameEn: 'Mountain Eagle Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 32500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_04.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_05', category: 'tables', icon: '🃏', nameAr: 'طاولة النمر الناري', nameEn: 'Fire Tiger Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 34000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_05.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_06', category: 'tables', icon: '🃏', nameAr: 'طاولة القرش الأزرق', nameEn: 'Blue Shark Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 35500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_06.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_07', category: 'tables', icon: '🃏', nameAr: 'طاولة الحوت العميق', nameEn: 'Deep Whale Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 37000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_07.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_08', category: 'tables', icon: '🃏', nameAr: 'طاولة الفهد الأسود', nameEn: 'Black Panther Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 38500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_08.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_09', category: 'tables', icon: '🃏', nameAr: 'طاولة السيارة السوداء', nameEn: 'Black Supercar Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 40000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_09.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_10', category: 'tables', icon: '🃏', nameAr: 'طاولة السيارة الحمراء', nameEn: 'Red Supercar Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 41500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_10.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_11', category: 'tables', icon: '🃏', nameAr: 'طاولة السيارة الكلاسيكية', nameEn: 'Classic Muscle Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 43000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_11.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_12', category: 'tables', icon: '🃏', nameAr: 'طاولة السيارة الزرقاء', nameEn: 'Blue Hypercar Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 44500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_12.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_13', category: 'tables', icon: '🃏', nameAr: 'طاولة الدراجة الخضراء', nameEn: 'Green Superbike Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 46000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_13.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_14', category: 'tables', icon: '🃏', nameAr: 'طاولة الدراجة الحمراء', nameEn: 'Red Superbike Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 47500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_14.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_15', category: 'tables', icon: '🃏', nameAr: 'طاولة البومة القمرية', nameEn: 'Moon Owl Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 49000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_15.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_16', category: 'tables', icon: '🃏', nameAr: 'طاولة الذئب الأبيض', nameEn: 'White Wolf Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 50500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_16.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_17', category: 'tables', icon: '🃏', nameAr: 'طاولة التنين الناري', nameEn: 'Fire Dragon Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 52000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_17.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_18', category: 'tables', icon: '🃏', nameAr: 'طاولة التنين الذهبي', nameEn: 'Golden Dragon Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 53500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_18.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_19', category: 'tables', icon: '🃏', nameAr: 'طاولة الذئب الأزرق', nameEn: 'Blue Wolf Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 55000, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_19.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'table_v173_showcase_20', category: 'tables', icon: '🃏', nameAr: 'طاولة العنقاء النارية', nameEn: 'Fire Phoenix Table', descriptionAr: 'طاولة Full HD من مجموعة V173 الجديدة مع معاينة كاملة داخل غرفة اللعب.', descriptionEn: 'Full-HD V173 table with full in-room preview.', price: 56500, previewColor1: const Color(0xff07111c), previewColor2: const Color(0xffd6aa59), imageAsset: 'assets/images/tables/v173/showcase/table_v173_showcase_20.jpg', collection: 'v173_showcase'),
    StoreProduct(id: 'competition_ticket_50_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 50 توكن', nameEn: '50 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 50 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 50 tokens, sold at a 10% discount.', price: 45, value: '50', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_100_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 100 توكن', nameEn: '100 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 100 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 100 tokens, sold at a 10% discount.', price: 90, value: '100', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_200_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 200 توكن', nameEn: '200 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 200 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 200 tokens, sold at a 10% discount.', price: 180, value: '200', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_500_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 500 توكن', nameEn: '500 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 500 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 500 tokens, sold at a 10% discount.', price: 450, value: '500', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_1000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 1,000 توكن', nameEn: '1,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 1,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 1,000 tokens, sold at a 10% discount.', price: 900, value: '1000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_2000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 2,000 توكن', nameEn: '2,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 2,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 2,000 tokens, sold at a 10% discount.', price: 1800, value: '2000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_4000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 4,000 توكن', nameEn: '4,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 4,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 4,000 tokens, sold at a 10% discount.', price: 3600, value: '4000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_5000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 5,000 توكن', nameEn: '5,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 5,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 5,000 tokens, sold at a 10% discount.', price: 4500, value: '5000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_8000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 8,000 توكن', nameEn: '8,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 8,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 8,000 tokens, sold at a 10% discount.', price: 7200, value: '8000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_10000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 10,000 توكن', nameEn: '10,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 10,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 10,000 tokens, sold at a 10% discount.', price: 9000, value: '10000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_20000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 20,000 توكن', nameEn: '20,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 20,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 20,000 tokens, sold at a 10% discount.', price: 18000, value: '20000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_30000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 30,000 توكن', nameEn: '30,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 30,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 30,000 tokens, sold at a 10% discount.', price: 27000, value: '30000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_50000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 50,000 توكن', nameEn: '50,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 50,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 50,000 tokens, sold at a 10% discount.', price: 45000, value: '50000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
    StoreProduct(id: 'competition_ticket_100000_v173', category: 'competition_ticket', icon: '🎟️', nameAr: 'تذكرة منافسة 100,000 توكن', nameEn: '100,000 Token Competition Ticket', descriptionAr: 'تذكرة دخول للمنافسات حتى قيمة 100,000 توكن. سعر المتجر أقل 10% من قيمتها.', descriptionEn: 'Competition entry ticket worth up to 100,000 tokens, sold at a 10% discount.', price: 90000, value: '100000', previewColor1: const Color(0xff6d28d9), previewColor2: const Color(0xfffacc15), collection: 'tickets_v173'),
];

class PashaHatV173 extends StatelessWidget {
  final AppController controller;
  final double width;
  final double? height;
  final BoxFit fit;
  const PashaHatV173({super.key, required this.controller, this.width = 44, this.height, this.fit = BoxFit.contain});
  @override
  Widget build(BuildContext context) {
    return Image.asset('assets/images/pasha.png', width: width, height: height ?? width * .68, fit: BoxFit.contain, filterQuality: FilterQuality.high, errorBuilder: (_, __, ___) => const Text('🎩'));
  }
}

extension WarqnaV173Controller on AppController {
  PashaStyleV173 get activePashaStyleV173 => pashaStyleV173(selectedPashaStyle);
  int ticketCountV173(int value) => competitionTickets[value] ?? 0;
  int get totalTicketsV173 => competitionTickets.values.fold<int>(0, (sum, value) => sum + value);
  bool get dailyPackAvailableV173 => dailyPackLastOpened != DateTime.now().toIso8601String().substring(0, 10);

  Future<void> startConnectivityMonitorV173() async {
    connectivityTimerV173?.cancel();
    connectivityTimerV173 = Timer.periodic(const Duration(seconds: 20), (_) async {
      if (!isAuthenticated || authToken == null) return;
      try {
        final data = await api.bootstrap();
        _applySession(data);
        if (!serverConnected) { serverConnected = true; refreshUi(); }
      } catch (_) {
        if (serverConnected) { serverConnected = false; refreshUi(); }
      }
    });
  }

  Future<bool> reconnectV173() async {
    if (authToken == null || authToken!.isEmpty) return false;
    api.token = authToken;
    try {
      final data = await api.bootstrap();
      _applySession(data);
      isAuthenticated = true;
      serverConnected = true;
      await _save();
      refreshUi();
      return true;
    } catch (_) {
      serverConnected = false;
      refreshUi();
      return false;
    }
  }

  Future<String?> openDailyPackV173() async {
    if (!serverConnected) return 'يلزم اتصال فعّال بالإنترنت لفتح الحزمة.';
    if (!dailyPackAvailableV173) return 'تم فتح حزمة اليوم مسبقاً.';
    try {
      final data = await api.openDailyPackV173();
      final reward = data['reward'];
      final map = reward is Map ? Map<String, dynamic>.from(reward) : <String, dynamic>{};
      dailyPackLastOpened = DateTime.now().toIso8601String().substring(0, 10);
      dailyPackReward = map['label_ar']?.toString() ?? data['message']?.toString() ?? 'هدية يومية';
      applyDailyPackRewardV176(map, data);
      final wallet = data['wallet'];
      if (wallet is Map) coins = BigInt.tryParse(wallet['tokens']?.toString() ?? '') ?? coins;
      await _save();
      refreshUi();
      return null;
    } on ApiException catch (e) { return e.message; } catch (_) { return 'تعذر فتح الحزمة الآن.'; }
  }

  Future<String?> joinCompetitionV173(String id, int fee) async {
    if (!serverConnected) return 'المنافسات تعمل عبر الإنترنت فقط.';
    try {
      final data = await api.joinCompetitionV173(id, fee);
      activeCompetition = id;
      final tickets = data['tickets'];
      if (tickets is Map) {
        competitionTickets
          ..clear()
          ..addAll(tickets.map((key, value) => MapEntry(int.tryParse(key.toString()) ?? 0, int.tryParse(value.toString()) ?? 0)));
      }
      championRankPointsV173 = int.tryParse(data['rank_points']?.toString() ?? '') ?? championRankPointsV173;
      await _save();
      refreshUi();
      return null;
    } on ApiException catch (e) { return e.message; } catch (_) { return 'تعذر التسجيل في المنافسة.'; }
  }
}

class OnlineRequiredScreenV173 extends StatefulWidget {
  final AppController controller;
  const OnlineRequiredScreenV173({super.key, required this.controller});
  @override State<OnlineRequiredScreenV173> createState() => _OnlineRequiredScreenV173State();
}
class _OnlineRequiredScreenV173State extends State<OnlineRequiredScreenV173> {
  bool busy = false;
  @override Widget build(BuildContext context) => Scaffold(
    body: Center(child: ConstrainedBox(constraints: const BoxConstraints(maxWidth: 520), child: Padding(
      padding: const EdgeInsets.all(24),
      child: PremiumPanel(child: Padding(padding: const EdgeInsets.all(24), child: Column(mainAxisSize: MainAxisSize.min, children: [
        const Icon(Icons.wifi_off_rounded, size: 76, color: Colors.orangeAccent),
        const SizedBox(height: 14),
        const Text('وضع الأوفلاين مفعل', textAlign: TextAlign.center, style: TextStyle(fontSize: 24, fontWeight: FontWeight.w900)),
        const SizedBox(height: 9),
        const Text('يمكنك متابعة اللعب محلياً دون إنترنت، بينما تحتاج المزامنة والمنافسات والمتجر الشبكي إلى اتصال بالخادم.', textAlign: TextAlign.center, style: TextStyle(color: Colors.white70, height: 1.6)),
        const SizedBox(height: 18),
        FilledButton.icon(onPressed: busy ? null : () async { setState(() => busy = true); final ok = await widget.controller.reconnectV173(); if (!mounted) return; setState(() => busy = false); if (!ok) showToast(this.context, 'الخادم غير متاح حالياً. يمكنك متابعة اللعب أوفلاين.'); }, icon: busy ? const SizedBox(width:18,height:18,child:CircularProgressIndicator(strokeWidth:2)) : const Icon(Icons.refresh_rounded), label: const Text('إعادة الاتصال')),
        TextButton.icon(onPressed: () => widget.controller.logout(), icon: const Icon(Icons.logout), label: const Text('العودة لتسجيل الدخول')),
      ]))),
    ))),
  );
}

void showPashaStyleSelectorV173(BuildContext context, AppController controller) {
  showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    const Text('ألوان طربوش الباشا', style: TextStyle(fontSize: 23, fontWeight: FontWeight.w900)),
    const SizedBox(height: 5),
    const Text('اختيار اللون يطبّق هوية اللون على الطربوش والوهج والاسم والدردشة واللون الرئيسي للتطبيق.', style: TextStyle(color: Colors.white60, height: 1.5)),
    const SizedBox(height: 12),
    GridView.builder(shrinkWrap: true, physics: const NeverScrollableScrollPhysics(), itemCount: pashaStylesV173.length, gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, childAspectRatio: 1.18, crossAxisSpacing: 8, mainAxisSpacing: 8), itemBuilder: (_, i) {
      final style = pashaStylesV173[i];
      final selected = controller.selectedPashaStyle == style.key;
      final product = storeProductById('pasha_style_${style.key}_v173');
      final owned = product != null && controller.isOwnedActiveV176(product.id);
      return InkWell(onTap: () {
        if (product == null) return;
        if (owned || controller.isAdmin) { controller.activateProduct(product); showToast(context, 'تم اعتماد الطربوش ${style.nameAr}.'); } else { showProductPreview(context, controller, product); }
      }, borderRadius: BorderRadius.circular(18), child: Container(
        padding: const EdgeInsets.all(8),
        decoration: BoxDecoration(borderRadius: BorderRadius.circular(18), gradient: LinearGradient(colors: [colorFromHex(style.darkHex), colorFromHex(style.primaryHex).withValues(alpha:.55)]), border: Border.all(color: selected ? Colors.white : Colors.white24, width: selected ? 3 : 1)),
        child: Column(children: [Expanded(child: Image.asset(style.asset, fit: BoxFit.contain)), Text(style.nameAr, style: const TextStyle(fontWeight: FontWeight.w900)), Text(selected ? 'مفعّل' : owned ? 'اختيار' : '🪙 6,500', style: const TextStyle(fontSize: 10, color: Colors.white70))]),
      ));
    }),
  ]));
}

Future<void> showDailyPackV173(BuildContext context, AppController controller) => showDailyPackV176(context, controller);

void showCompetitionsV173(BuildContext context, AppController controller) {
  const competitions = <(String,String,String,int,int,int,String)>[
    ('champions','🏆','بطولة الأبطال',2000,64,4,'طرنيب'),
    ('weekend','⚡','كأس نهاية الأسبوع',1000,32,3,'اختيار اللعبة'),
    ('elite','👑','دوري النخبة',5000,128,4,'طرنيب وتركس'),
    ('clubs_war','🛡️','حرب المجموعات',10000,64,4,'فرق 2 ضد 2'),
    ('quick','🚀','المواجهة السريعة',500,16,2,'زمن 8 ثوانٍ'),
    ('legend','🐉','كأس الأساطير',20000,256,5,'إقصاء عالمي'),
  ];
  showPremiumSheet(context, child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    Row(children: [const Expanded(child: Text('مركز المنافسات V173', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w900))), Chip(label: Text('🎟️ ${controller.totalTicketsV173}'))]),
    Text('نقاط التصنيف: ${controller.championRankPointsV173} • دخول بالتذكرة أو التوكنز • مقاعد عشوائية • دردشة خاصة • بدء تلقائي', style: const TextStyle(color: Colors.white60, height: 1.5)),
    const SizedBox(height: 10),
    ...competitions.map((entry) => Padding(padding: const EdgeInsets.only(bottom: 9), child: PremiumPanel(child: Padding(padding: const EdgeInsets.all(12), child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
      Row(children: [Text(entry.$2, style: const TextStyle(fontSize: 32)), const SizedBox(width: 9), Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text(entry.$3, style: const TextStyle(fontWeight: FontWeight.w900, fontSize: 15)), Text('${entry.$7} • ${entry.$5} لاعب • ${entry.$6} جولات', style: const TextStyle(color: Colors.white60, fontSize: 10))])), Text('🪙 ${formatNumber(entry.$4)}', style: const TextStyle(color: Colors.amber, fontWeight: FontWeight.w900))]),
      const SizedBox(height: 8),
      Row(children: [Expanded(child: Text('الجائزة حتى ${formatNumber(entry.$4 * entry.$5 ~/ 2)} توكن', style: const TextStyle(color: Colors.lightGreenAccent, fontSize: 10))), FilledButton(onPressed: () async { final error = await controller.joinCompetitionV173(entry.$1, entry.$4); if (context.mounted) showToast(context, error ?? 'تم تسجيلك في ${entry.$3}.'); }, child: const Text('دخول'))]),
    ]))))),
    const SizedBox(height: 4),
    FilledButton.tonalIcon(onPressed: () { Navigator.pop(context); showChallengesV173(context, controller); }, icon: const Icon(Icons.bolt_rounded), label: const Text('فتح التحديات الفورية')),
  ]));
}

void showChallengesV173(BuildContext context, AppController controller) {
  showChallengesV175(context, controller);
}

class GroupCommandCenterV173 extends StatelessWidget {
  final AppController controller;
  const GroupCommandCenterV173({super.key, required this.controller});
  @override Widget build(BuildContext context) => PremiumPanel(child: Padding(padding: const EdgeInsets.all(13), child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
    Row(children: [const Text('🛡️', style: TextStyle(fontSize: 31)), const SizedBox(width: 8), const Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [Text('مركز قيادة المجموعة', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 16)), Text('موسم • خزينة • حرب مجموعات • صلاحيات • سجل كامل', style: TextStyle(color: Colors.white60, fontSize: 9))])), Chip(label: Text('LV.${controller.level}'))]),
    const SizedBox(height: 9),
    Row(children: [Expanded(child: _MiniStatV173('🏆','${controller.clubPoints}','نقاط الموسم')), const SizedBox(width:6), Expanded(child: _MiniStatV173('🔥','${controller.challengeStreakV173}','سلسلة')), const SizedBox(width:6), const Expanded(child: _MiniStatV173('🎟️','3','منافسات مجانية'))]),
    const SizedBox(height: 9),
    Wrap(spacing: 6, runSpacing: 6, children: [
      FilledButton.tonalIcon(onPressed: () => showCompetitionsV173(context, controller), icon: const Icon(Icons.emoji_events_outlined), label: const Text('منافسات المجموعة')),
      FilledButton.tonalIcon(onPressed: () => showChallengesV173(context, controller), icon: const Icon(Icons.bolt), label: const Text('مهام الأعضاء')),
      FilledButton.tonalIcon(onPressed: () => showToast(context, 'سجل المجموعة: الانضمام، الترقية، الجوائز، التحويلات والمخالفات.'), icon: const Icon(Icons.history), label: const Text('السجل')),
      FilledButton.tonalIcon(onPressed: () => showToast(context, 'الصلاحيات: قائد، نائب، منظم منافسات، مشرف دردشة، عضو.'), icon: const Icon(Icons.admin_panel_settings_outlined), label: const Text('الصلاحيات')),
    ]),
  ])));
}
class _MiniStatV173 extends StatelessWidget { final String icon,value,label; const _MiniStatV173(this.icon,this.value,this.label); @override Widget build(BuildContext context)=>Container(padding:const EdgeInsets.all(8),decoration:BoxDecoration(color:Colors.white.withValues(alpha:.05),borderRadius:BorderRadius.circular(13)),child:Column(children:[Text(icon),Text(value,style:const TextStyle(fontWeight:FontWeight.w900)),Text(label,style:const TextStyle(fontSize:8,color:Colors.white54))])); }

class UniversalDesignerV173 extends StatelessWidget {
  final AppController controller;
  const UniversalDesignerV173({super.key, required this.controller});

  @override
  Widget build(BuildContext context) => PremiumPanel(
        child: Padding(
          padding: const EdgeInsets.all(13),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Text('المصمم الشامل V0.2 — للمدير فقط', style: TextStyle(fontWeight: FontWeight.w900, fontSize: 17)),
              const SizedBox(height: 4),
              const Text(
                'إدارة وإضافة وتعديل وحذف جميع مكونات التطبيق: الألعاب والطاولات والمتجر والاقتصاد والمستويات وصناديق الجوائز والتحديات والمنافسات والمجموعات والإعلانات والإشعارات والصفحات والتنقل والترجمات والثيمات والأمان من قاعدة البيانات.',
                style: TextStyle(color: Colors.white60, height: 1.5, fontSize: 10),
              ),
              const SizedBox(height: 9),
              Wrap(
                spacing: 7,
                runSpacing: 7,
                children: [
                  for (final entry in const <(String, String, IconData)>[
                    ('الطاولات', 'table', Icons.table_restaurant),
                    ('التذاكر', 'competition_ticket', Icons.confirmation_number),
                    ('صناديق الجوائز', 'prize_box', Icons.redeem),
                    ('التحديات', 'challenge', Icons.bolt),
                    ('المنافسات', 'competition', Icons.emoji_events),
                    ('المجموعات', 'group', Icons.groups),
                    ('الإعلانات', 'ads', Icons.ads_click),
                    ('النصوص والترجمات', 'translation', Icons.translate),
                    ('الثيمات', 'theme', Icons.palette),
                    ('الألعاب', 'game', Icons.style),
                    ('المتجر', 'store', Icons.storefront),
                    ('اقتصاد اللعبة', 'economy', Icons.account_balance_wallet),
                    ('نقاط المستويات', 'level_xp', Icons.stairs),
                    ('الصفحات', 'page', Icons.web),
                    ('التنقل', 'navigation', Icons.alt_route),
                    ('الإشعارات', 'notification', Icons.notifications_active),
                    ('الأمان', 'security', Icons.security),
                    ('مفاتيح الميزات', 'feature_flag', Icons.toggle_on),
                    ('إعدادات النظام', 'system', Icons.settings_suggest),
                  ])
                    OutlinedButton.icon(
                      onPressed: controller.serverConnected
                          ? () => Navigator.of(context).push(
                                MaterialPageRoute<void>(
                                  builder: (_) => DesignerEntityManagerV173(
                                    controller: controller,
                                    initialEntityType: entry.$2,
                                    title: entry.$1,
                                  ),
                                ),
                              )
                          : null,
                      icon: Icon(entry.$3, size: 18),
                      label: Text(entry.$1),
                    ),
                ],
              ),
              const SizedBox(height: 8),
              FilledButton.icon(
                onPressed: controller.serverConnected
                    ? () => Navigator.of(context).push(
                          MaterialPageRoute<void>(
                            builder: (_) => DesignerEntityManagerV173(
                              controller: controller,
                              initialEntityType: 'all',
                              title: 'كل مكونات التطبيق',
                            ),
                          ),
                        )
                    : null,
                icon: const Icon(Icons.cloud_sync),
                label: const Text('فتح الإدارة الكاملة والمزامنة'),
              ),
            ],
          ),
        ),
      );
}

class DesignerEntityManagerV173 extends StatefulWidget {
  final AppController controller;
  final String initialEntityType;
  final String title;
  const DesignerEntityManagerV173({
    super.key,
    required this.controller,
    required this.initialEntityType,
    required this.title,
  });

  @override
  State<DesignerEntityManagerV173> createState() => _DesignerEntityManagerV173State();
}

class _DesignerEntityManagerV173State extends State<DesignerEntityManagerV173> {
  bool loading = true;
  String? error;
  List<Map<String, dynamic>> entities = <Map<String, dynamic>>[];

  @override
  void initState() {
    super.initState();
    unawaited(_load());
  }

  Future<void> _load() async {
    if (!widget.controller.serverConnected) {
      if (mounted) setState(() { loading = false; error = 'لا يوجد اتصال بالخادم.'; });
      return;
    }
    if (mounted) setState(() { loading = true; error = null; });
    try {
      final result = await widget.controller.api.adminDesignerEntitiesV173();
      final raw = result['entities'];
      final list = raw is List
          ? raw.whereType<Map>().map((e) => Map<String, dynamic>.from(e)).toList()
          : <Map<String, dynamic>>[];
      if (!mounted) return;
      setState(() {
        entities = list;
        loading = false;
      });
    } catch (e) {
      if (!mounted) return;
      setState(() { loading = false; error = e.toString(); });
    }
  }

  List<Map<String, dynamic>> get filtered {
    if (widget.initialEntityType == 'all') return entities;
    return entities.where((e) => e['entity_type']?.toString() == widget.initialEntityType).toList();
  }

  Future<void> _edit([Map<String, dynamic>? existing]) async {
    final type = TextEditingController(text: existing?['entity_type']?.toString() ?? (widget.initialEntityType == 'all' ? 'system' : widget.initialEntityType));
    final key = TextEditingController(text: existing?['key']?.toString() ?? 'new_item_${DateTime.now().millisecondsSinceEpoch}');
    final locale = TextEditingController(text: existing?['locale']?.toString() ?? 'all');
    final order = TextEditingController(text: existing?['sort_order']?.toString() ?? '0');
    final rawPayload = existing?['payload'];
    final payload = TextEditingController(
      text: const JsonEncoder.withIndent('  ').convert(rawPayload is Map ? rawPayload : <String, dynamic>{'name': <String, String>{'ar': 'عنصر جديد', 'en': 'New item'}, 'enabled': true}),
    );
    var active = existing?['active'] != false;
    final saved = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setLocalState) => AlertDialog(
          title: Text(existing == null ? 'إضافة عنصر جديد' : 'تعديل ${existing['key']}'),
          content: SizedBox(
            width: 620,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  TextField(controller: type, enabled: existing == null, decoration: const InputDecoration(labelText: 'نوع العنصر بالإنجليزية')),
                  TextField(controller: key, enabled: existing == null, decoration: const InputDecoration(labelText: 'المفتاح الفريد')),
                  Row(children: [
                    Expanded(child: TextField(controller: locale, decoration: const InputDecoration(labelText: 'اللغة'))),
                    const SizedBox(width: 10),
                    Expanded(child: TextField(controller: order, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'الترتيب'))),
                  ]),
                  SwitchListTile(value: active, onChanged: (v) => setLocalState(() => active = v), title: const Text('مفعّل ومنشور')),
                  TextField(
                    controller: payload,
                    minLines: 10,
                    maxLines: 18,
                    style: const TextStyle(fontFamily: 'monospace', fontSize: 12),
                    decoration: const InputDecoration(labelText: 'Payload JSON', alignLabelWithHint: true, border: OutlineInputBorder()),
                  ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(onPressed: () => Navigator.pop(dialogContext, false), child: const Text('إلغاء')),
            FilledButton(
              onPressed: () async {
                try {
                  final decoded = jsonDecode(payload.text);
                  if (decoded is! Map) throw const FormatException('يجب أن يكون JSON Object.');
                  await widget.controller.api.upsertAdminDesignerEntityV173(
                    entityType: type.text.trim(),
                    key: key.text.trim(),
                    locale: locale.text.trim().isEmpty ? 'all' : locale.text.trim(),
                    sortOrder: int.tryParse(order.text) ?? 0,
                    active: active,
                    payload: Map<String, dynamic>.from(decoded),
                  );
                  if (dialogContext.mounted) Navigator.pop(dialogContext, true);
                } catch (e) {
                  if (dialogContext.mounted) {
                    ScaffoldMessenger.of(dialogContext).showSnackBar(SnackBar(content: Text('تعذر الحفظ: $e')));
                  }
                }
              },
              child: const Text('حفظ ونشر'),
            ),
          ],
        ),
      ),
    );
    type.dispose(); key.dispose(); locale.dispose(); order.dispose(); payload.dispose();
    if (saved == true) {
      await _load();
      if (mounted) showToast(context, 'تم حفظ العنصر ومزامنته مع الخادم.');
    }
  }

  Future<void> _delete(Map<String, dynamic> entity) async {
    final id = int.tryParse(entity['id']?.toString() ?? '');
    if (id == null) return;
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد الحذف'),
        content: Text('حذف ${entity['entity_type']} / ${entity['key']} نهائياً؟'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context, false), child: const Text('إلغاء')),
          FilledButton(onPressed: () => Navigator.pop(context, true), child: const Text('حذف')),
        ],
      ),
    );
    if (confirmed != true) return;
    try {
      await widget.controller.api.deleteAdminDesignerEntityV173(id);
      await _load();
      if (mounted) showToast(context, 'تم حذف العنصر.');
    } catch (e) {
      if (mounted) showToast(context, 'تعذر الحذف: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    final list = filtered;
    return Scaffold(
      appBar: AppBar(
        title: Text('المصمم الشامل — ${widget.title}'),
        actions: [IconButton(onPressed: _load, icon: const Icon(Icons.refresh))],
      ),
      floatingActionButton: FloatingActionButton.extended(onPressed: () => _edit(), icon: const Icon(Icons.add), label: const Text('إضافة')),
      body: loading
          ? const Center(child: CircularProgressIndicator())
          : error != null
              ? Center(child: Padding(padding: const EdgeInsets.all(24), child: Text('تعذر تحميل المصمم: $error', textAlign: TextAlign.center)))
              : list.isEmpty
                  ? const Center(child: Text('لا توجد عناصر في هذا القسم بعد.'))
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: ListView.separated(
                        padding: const EdgeInsets.fromLTRB(12, 12, 12, 90),
                        itemCount: list.length,
                        separatorBuilder: (_, __) => const SizedBox(height: 9),
                        itemBuilder: (context, index) {
                          final entity = list[index];
                          final payload = entity['payload'];
                          return Card(
                            child: ListTile(
                              leading: Icon(entity['active'] == false ? Icons.visibility_off : Icons.cloud_done),
                              title: Text('${entity['entity_type']} / ${entity['key']}', style: const TextStyle(fontWeight: FontWeight.w800)),
                              subtitle: Text('اللغة: ${entity['locale']} • المراجعة: ${entity['revision']}\n${jsonEncode(payload)}', maxLines: 3, overflow: TextOverflow.ellipsis),
                              isThreeLine: true,
                              onTap: () => _edit(entity),
                              trailing: IconButton(onPressed: () => _delete(entity), icon: const Icon(Icons.delete_outline)),
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}

