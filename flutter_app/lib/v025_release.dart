part of 'main.dart';

// Warqna V0.2.5: challenge journey, persistent appearance controls,
// level rewards and the local counterpart of the server absence policy.

Map<String, dynamic>? decodeJsonMapV025(String? raw) {
  if (raw == null || raw.trim().isEmpty) return null;
  try {
    final value = jsonDecode(raw);
    return value is Map ? Map<String, dynamic>.from(value) : null;
  } catch (_) {
    return null;
  }
}

const Map<String, Map<String, String>> _v025Text = {
  'ar': {
    'quickAppearance': 'التحكم السريع', 'appearanceHint': 'اللغة والثيم والخط متاحة في كل شاشة',
    'theme': 'الثيم', 'language': 'اللغة', 'font': 'الخط', 'fontSize': 'حجم الخط', 'portrait': 'طولي', 'landscape': 'عرضي',
    'challengeJourney': 'مسار التحدي', 'classicChallenges': 'التحديات اليومية', 'chooseGame': 'اختر اللعبة',
    'chooseStages': 'عدد المراحل', 'attempts': 'المحاولات', 'stage': 'المرحلة', 'startJourney': 'ابدأ المسار',
    'continueJourney': 'العب المرحلة الحالية', 'opponent': 'المنافس', 'completed': 'مكتمل', 'failed': 'انتهت المحاولات',
    'activeJourney': 'مسار فعال', 'journeyRule': 'تبقى اللعبة المختارة ثابتة حتى نهاية المسار. الخسارة تخصم محاولة واحدة.',
    'reward': 'الجائزة', 'claimed': 'مستلمة', 'locked': 'مقفلة', 'won': 'فوز', 'lost': 'خسارة',
    'levelUp': 'ترقية مستوى', 'reachedLevel': 'وصلت إلى المستوى', 'levelRewards': 'مكافآت المستويات',
    'absenceEjected': 'تم إخراجك مؤقتًا بعد 3 لفات غياب، ويمكنك العودة إلى الغرفة.',
    'adminOnly': 'المصمم الشامل متاح للمدير الرئيسي Adnan فقط.', 'sendTokens': 'إرسال توكنز', 'tokenAmount':'كمية التوكنز', 'sendNow':'إرسال الآن',
    'friendRequest': 'إرسال طلب صداقة', 'actionDone': 'تم تنفيذ العملية بنجاح.', 'actionFailed': 'تعذر تنفيذ العملية.',
    'adTest': 'إعلان تجريبي', 'testMode': 'وضع الاختبار الآمن مفعل', 'preview': 'معاينة كاملة',
    'all': 'الكل', 'inventory': 'مقتنياتي', 'pasha': 'الباشا', 'tickets': 'التذاكر', 'themes': 'الثيمات',
    'tables': 'الطاولات', 'cards': 'ظهر الورق', 'emoji': 'الإيموجي', 'boosters': 'المسرعات',
    'playerColors': 'ألوان اللاعب', 'chatColors': 'ألوان الكتابة', 'badges': 'الشارات', 'effects': 'المؤثرات', 'covers': 'الأغلفة',
    'activeGame': 'اللعبة النشطة', 'dailyReward': 'المكافأة اليومية', 'watchAd': 'شاهد إعلانًا تجريبيًا',
    'adRewardDesc': '50 توكن + 15 XP بعد إكمال الإعلان', 'remainingToday': 'المتبقي اليوم', 'loginStreak': 'استمرارية الدخول',
    'claimReward': 'استلام', 'watch': 'مشاهدة', 'levelRewardRoad': 'مسار مكافآت المستويات 1–100',
    'levelRewardHint': 'كل انتقال لمستوى جديد يمنح توكنز وجائزة إضافية محفوظة مرة واحدة فقط.', 'level': 'المستوى',
    'tokensWord': 'توكن', 'banAccount': 'حظر الحساب', 'unbanAccount': 'إلغاء الحظر',
  },
  'en': {
    'quickAppearance': 'Quick appearance', 'appearanceHint': 'Language, theme and font controls are available on every screen',
    'theme': 'Theme', 'language': 'Language', 'font': 'Font', 'fontSize': 'Font size', 'portrait': 'Portrait', 'landscape': 'Landscape',
    'challengeJourney': 'Challenge Journey', 'classicChallenges': 'Daily challenges', 'chooseGame': 'Choose game',
    'chooseStages': 'Stages', 'attempts': 'Attempts', 'stage': 'Stage', 'startJourney': 'Start journey',
    'continueJourney': 'Play current stage', 'opponent': 'Opponent', 'completed': 'Completed', 'failed': 'No attempts left',
    'activeJourney': 'Active journey', 'journeyRule': 'Your selected game remains fixed for the full journey. A loss costs one attempt.',
    'reward': 'Reward', 'claimed': 'Claimed', 'locked': 'Locked', 'won': 'Win', 'lost': 'Loss',
    'levelUp': 'Level up', 'reachedLevel': 'You reached level', 'levelRewards': 'Level rewards',
    'absenceEjected': 'You were temporarily removed after 3 inactive turns. You may rejoin the room.',
    'adminOnly': 'The comprehensive designer is available only to the primary Adnan administrator.', 'sendTokens': 'Send tokens', 'tokenAmount':'Token amount', 'sendNow':'Send now',
    'friendRequest': 'Send friend request', 'actionDone': 'Action completed successfully.', 'actionFailed': 'Action could not be completed.',
    'adTest': 'Test ad', 'testMode': 'Safe test mode is enabled', 'preview': 'Full preview',
    'all': 'All', 'inventory': 'Owned', 'pasha': 'Pasha', 'tickets': 'Tickets', 'themes': 'Themes',
    'tables': 'Tables', 'cards': 'Card backs', 'emoji': 'Emoji', 'boosters': 'Boosters',
    'playerColors': 'Player colors', 'chatColors': 'Text colors', 'badges': 'Badges', 'effects': 'Effects', 'covers': 'Covers',
    'activeGame': 'Active game', 'dailyReward': 'Daily reward', 'watchAd': 'Watch a test ad',
    'adRewardDesc': '50 tokens + 15 XP after completing the ad', 'remainingToday': 'Remaining today', 'loginStreak': 'Login streak',
    'claimReward': 'Claim', 'watch': 'Watch', 'levelRewardRoad': 'Level reward road 1–100',
    'levelRewardHint': 'Every new level grants tokens plus one extra reward, once per level.', 'level': 'Level',
    'tokensWord': 'tokens', 'banAccount': 'Ban account', 'unbanAccount': 'Unban account',
  },
  'de': {
    'quickAppearance': 'Schnelldesign', 'appearanceHint': 'Sprache, Design und Schrift sind auf jeder Seite verfügbar',
    'theme': 'Design', 'language': 'Sprache', 'font': 'Schrift', 'fontSize': 'Schriftgröße', 'portrait': 'Hochformat', 'landscape': 'Querformat',
    'challengeJourney': 'Herausforderungsweg', 'classicChallenges': 'Tägliche Aufgaben', 'chooseGame': 'Spiel wählen',
    'chooseStages': 'Etappen', 'attempts': 'Versuche', 'stage': 'Etappe', 'startJourney': 'Weg starten',
    'continueJourney': 'Aktuelle Etappe spielen', 'opponent': 'Gegner', 'completed': 'Abgeschlossen', 'failed': 'Keine Versuche mehr',
    'activeJourney': 'Aktiver Weg', 'journeyRule': 'Das gewählte Spiel bleibt bis zum Ende fest. Eine Niederlage kostet einen Versuch.',
    'reward': 'Belohnung', 'claimed': 'Erhalten', 'locked': 'Gesperrt', 'won': 'Sieg', 'lost': 'Niederlage',
    'levelUp': 'Levelaufstieg', 'reachedLevel': 'Du hast Level erreicht', 'levelRewards': 'Levelbelohnungen',
    'absenceEjected': 'Nach 3 inaktiven Runden wurdest du vorübergehend entfernt. Du kannst zurückkehren.',
    'adminOnly': 'Der vollständige Designer ist nur für den Hauptadministrator Adnan verfügbar.', 'sendTokens': 'Token senden', 'tokenAmount':'Token-Menge', 'sendNow':'Jetzt senden',
    'friendRequest': 'Freundschaftsanfrage senden', 'actionDone': 'Aktion erfolgreich.', 'actionFailed': 'Aktion fehlgeschlagen.',
    'adTest': 'Testanzeige', 'testMode': 'Sicherer Testmodus aktiv', 'preview': 'Vollständige Vorschau',
    'all':'Alle','inventory':'Besitz','pasha':'Pascha','tickets':'Tickets','themes':'Designs','tables':'Tische','cards':'Kartenrückseiten','emoji':'Emoji','boosters':'Booster','playerColors':'Spielerfarben','chatColors':'Textfarben','badges':'Abzeichen','effects':'Effekte','covers':'Cover',
    'activeGame':'Aktives Spiel','dailyReward':'Tägliche Belohnung','watchAd':'Testanzeige ansehen','adRewardDesc':'50 Token + 15 XP nach vollständiger Anzeige','remainingToday':'Heute übrig','loginStreak':'Login-Serie','claimReward':'Abholen','watch':'Ansehen','levelRewardRoad':'Level-Belohnungsweg 1–100','levelRewardHint':'Jedes neue Level gewährt einmalig Token und eine Zusatzbelohnung.','level':'Level','tokensWord':'Token','banAccount':'Konto sperren','unbanAccount':'Sperre aufheben',
  },
  'tr': {
    'quickAppearance': 'Hızlı görünüm', 'appearanceHint': 'Dil, tema ve yazı tipi her ekranda kullanılabilir',
    'theme': 'Tema', 'language': 'Dil', 'font': 'Yazı tipi', 'fontSize': 'Yazı boyutu', 'portrait': 'Dikey', 'landscape': 'Yatay',
    'challengeJourney': 'Meydan Okuma Yolu', 'classicChallenges': 'Günlük görevler', 'chooseGame': 'Oyun seç',
    'chooseStages': 'Aşamalar', 'attempts': 'Haklar', 'stage': 'Aşama', 'startJourney': 'Yolu başlat',
    'continueJourney': 'Mevcut aşamayı oyna', 'opponent': 'Rakip', 'completed': 'Tamamlandı', 'failed': 'Hak kalmadı',
    'activeJourney': 'Aktif yol', 'journeyRule': 'Seçilen oyun yol bitene kadar değişmez. Kayıp bir hak götürür.',
    'reward': 'Ödül', 'claimed': 'Alındı', 'locked': 'Kilitli', 'won': 'Kazandın', 'lost': 'Kaybettin',
    'levelUp': 'Seviye atladın', 'reachedLevel': 'Ulaştığın seviye', 'levelRewards': 'Seviye ödülleri',
    'absenceEjected': '3 pasif turdan sonra geçici olarak çıkarıldın. Odaya tekrar girebilirsin.',
    'adminOnly': 'Kapsamlı tasarımcı yalnızca ana yönetici Adnan içindir.', 'sendTokens': 'Jeton gönder', 'tokenAmount':'Jeton miktarı', 'sendNow':'Şimdi gönder',
    'friendRequest': 'Arkadaşlık isteği gönder', 'actionDone': 'İşlem tamamlandı.', 'actionFailed': 'İşlem tamamlanamadı.',
    'adTest': 'Test reklamı', 'testMode': 'Güvenli test modu etkin', 'preview': 'Tam önizleme',
    'all':'Tümü','inventory':'Sahip olduklarım','pasha':'Paşa','tickets':'Biletler','themes':'Temalar','tables':'Masalar','cards':'Kart arkaları','emoji':'Emoji','boosters':'Hızlandırıcılar','playerColors':'Oyuncu renkleri','chatColors':'Yazı renkleri','badges':'Rozetler','effects':'Efektler','covers':'Kapaklar',
    'activeGame':'Aktif oyun','dailyReward':'Günlük ödül','watchAd':'Test reklamı izle','adRewardDesc':'Reklam tamamlanınca 50 jeton + 15 XP','remainingToday':'Bugün kalan','loginStreak':'Giriş serisi','claimReward':'Al','watch':'İzle','levelRewardRoad':'1–100 seviye ödül yolu','levelRewardHint':'Her yeni seviye bir kez jeton ve ek ödül verir.','level':'Seviye','tokensWord':'jeton','banAccount':'Hesabı engelle','unbanAccount':'Engeli kaldır',
  },
  'fr': {
    'quickAppearance': 'Apparence rapide', 'appearanceHint': 'Langue, thème et police disponibles sur chaque écran',
    'theme': 'Thème', 'language': 'Langue', 'font': 'Police', 'fontSize': 'Taille du texte', 'portrait': 'Portrait', 'landscape': 'Paysage',
    'challengeJourney': 'Parcours défi', 'classicChallenges': 'Défis quotidiens', 'chooseGame': 'Choisir le jeu',
    'chooseStages': 'Étapes', 'attempts': 'Essais', 'stage': 'Étape', 'startJourney': 'Démarrer',
    'continueJourney': "Jouer l'étape", 'opponent': 'Adversaire', 'completed': 'Terminé', 'failed': "Plus d'essais",
    'activeJourney': 'Parcours actif', 'journeyRule': 'Le jeu choisi reste fixe. Une défaite retire un essai.',
    'reward': 'Récompense', 'claimed': 'Reçue', 'locked': 'Verrouillée', 'won': 'Victoire', 'lost': 'Défaite',
    'levelUp': 'Niveau supérieur', 'reachedLevel': 'Niveau atteint', 'levelRewards': 'Récompenses de niveau',
    'absenceEjected': 'Retrait temporaire après 3 tours inactifs. Vous pouvez rejoindre la salle.',
    'adminOnly': 'Le designer complet est réservé à l’administrateur principal Adnan.', 'sendTokens': 'Envoyer des jetons', 'tokenAmount':'Montant de jetons', 'sendNow':'Envoyer',
    'friendRequest': "Envoyer une demande d'ami", 'actionDone': 'Action effectuée.', 'actionFailed': "Échec de l'action.",
    'adTest': 'Pub test', 'testMode': 'Mode test sécurisé actif', 'preview': 'Aperçu complet',
    'all':'Tout','inventory':'Possédés','pasha':'Pacha','tickets':'Billets','themes':'Thèmes','tables':'Tables','cards':'Dos des cartes','emoji':'Emoji','boosters':'Boosters','playerColors':'Couleurs joueur','chatColors':'Couleurs texte','badges':'Badges','effects':'Effets','covers':'Couvertures',
    'activeGame':'Partie active','dailyReward':'Récompense quotidienne','watchAd':'Voir une pub test','adRewardDesc':'50 jetons + 15 XP après la publicité','remainingToday':'Restant aujourd’hui','loginStreak':'Série de connexion','claimReward':'Récupérer','watch':'Voir','levelRewardRoad':'Parcours de niveaux 1–100','levelRewardHint':'Chaque nouveau niveau accorde une fois des jetons et une récompense supplémentaire.','level':'Niveau','tokensWord':'jetons','banAccount':'Bloquer le compte','unbanAccount':'Débloquer le compte',
  },
  'es': {
    'quickAppearance': 'Apariencia rápida', 'appearanceHint': 'Idioma, tema y fuente disponibles en todas las pantallas',
    'theme': 'Tema', 'language': 'Idioma', 'font': 'Fuente', 'fontSize': 'Tamaño de texto', 'portrait': 'Vertical', 'landscape': 'Horizontal',
    'challengeJourney': 'Ruta de desafíos', 'classicChallenges': 'Desafíos diarios', 'chooseGame': 'Elegir juego',
    'chooseStages': 'Etapas', 'attempts': 'Intentos', 'stage': 'Etapa', 'startJourney': 'Iniciar ruta',
    'continueJourney': 'Jugar etapa actual', 'opponent': 'Rival', 'completed': 'Completado', 'failed': 'Sin intentos',
    'activeJourney': 'Ruta activa', 'journeyRule': 'El juego elegido se mantiene. Una derrota consume un intento.',
    'reward': 'Premio', 'claimed': 'Recibido', 'locked': 'Bloqueado', 'won': 'Victoria', 'lost': 'Derrota',
    'levelUp': 'Subida de nivel', 'reachedLevel': 'Alcanzaste el nivel', 'levelRewards': 'Premios de nivel',
    'absenceEjected': 'Fuiste retirado temporalmente tras 3 turnos inactivos. Puedes volver a la sala.',
    'adminOnly': 'El diseñador completo es exclusivo del administrador principal Adnan.', 'sendTokens': 'Enviar fichas', 'tokenAmount':'Cantidad de fichas', 'sendNow':'Enviar ahora',
    'friendRequest': 'Enviar solicitud de amistad', 'actionDone': 'Acción completada.', 'actionFailed': 'No se pudo completar.',
    'adTest': 'Anuncio de prueba', 'testMode': 'Modo de prueba seguro activo', 'preview': 'Vista previa completa',
    'all':'Todo','inventory':'Mis artículos','pasha':'Pasha','tickets':'Entradas','themes':'Temas','tables':'Mesas','cards':'Reversos','emoji':'Emoji','boosters':'Aceleradores','playerColors':'Colores de jugador','chatColors':'Colores de texto','badges':'Insignias','effects':'Efectos','covers':'Portadas',
    'activeGame':'Partida activa','dailyReward':'Recompensa diaria','watchAd':'Ver anuncio de prueba','adRewardDesc':'50 fichas + 15 XP al completar el anuncio','remainingToday':'Restantes hoy','loginStreak':'Racha de acceso','claimReward':'Reclamar','watch':'Ver','levelRewardRoad':'Ruta de niveles 1–100','levelRewardHint':'Cada nuevo nivel concede una vez fichas y una recompensa adicional.','level':'Nivel','tokensWord':'fichas','banAccount':'Bloquear cuenta','unbanAccount':'Desbloquear cuenta',
  },
 };

const Map<String, Map<String, String>> _v025UiText = {
  'ar': {
    'storeLevelProgress':'تقدم المستوى {level}','xpNeeded':'تحتاج {points} نقطة XP للمستوى التالي',
    'roundPoints':'نقاط الجولات','tournamentPoints':'نقاط المسابقات','clubPoints':'نقاط النادي',
    'multiplierHint':'المضاعف الحالي ×{value} • المسابقات أعلى من اللعب العادي • المواسم المدعومة حتى ×3',
    'premiumItems':'{count} عنصر فاخر','storeSummary':'{tables} طاولة • {cards} ظهر ورق • شراء بتأكيد ومعاينة مباشرة',
    'ownedItems':'{count} مملوك','storeSearch':'ابحث في الطاولات والثيمات والبطاقات والعناصر…',
    'tableCollections':'مجموعات الطاولات','newTables1':'الجديدة 1–10','newTables2':'الجديدة 11–20','newTables3':'الجديدة 21–30','newTables4':'الجديدة 31–40',
    'royalCollection':'مجموعة V173 الملكية','showcaseCollection':'حيوانات وسيارات V173','legacyTables':'الطاولات السابقة',
    'classification':'التصنيف','beginner':'مبتدئ','professional':'محترف','legendary':'أسطوري',
    'price':'السعر','status':'الحالة','owned':'مملوك','renewable':'قابل للتجديد','available':'متاح','category':'الفئة','duration':'المدة','expiry':'الصلاحية',
    'activateItem':'تفعيل العنصر','confirmPurchase':'تأكيد الشراء','purchaseQuestion':'هل تريد شراء {name} مقابل {price} توكن؟ لن يتم الخصم إلا بعد التأكيد.',
    'cancel':'إلغاء','yesBuy':'نعم، شراء','purchaseDone':'تم الشراء وإضافة العنصر إلى مقتنياتك.','purchaseFailed':'تعذر إكمال الشراء.',
    'itemActivated':'تم تفعيل {name}.','testChatMessage':'رسالة تجريبية بلون الدردشة المختار','themePreview':'معاينة الثيم',
    'pashaPreview':'تحكم بالغرفة • شارة خاصة • XP إضافي','playerRole':'المستوى {level} • {role}','player':'لاعب','storeDebitNotice':'لا تُخصم التوكنز من اللعب أو الغرف. الخصم يتم داخل المتجر فقط وبعد رسالة تأكيد واضحة.',
  },
  'en': {
    'storeLevelProgress':'Level {level} progress','xpNeeded':'You need {points} XP for the next level',
    'roundPoints':'Round points','tournamentPoints':'Tournament points','clubPoints':'Club points',
    'multiplierHint':'Current multiplier ×{value} • competitions award more than normal play • sponsored seasons up to ×3',
    'premiumItems':'{count} premium items','storeSummary':'{tables} tables • {cards} card backs • confirmed purchase with live preview',
    'ownedItems':'{count} owned','storeSearch':'Search tables, themes, card backs and items…',
    'tableCollections':'Table collections','newTables1':'New 1–10','newTables2':'New 11–20','newTables3':'New 21–30','newTables4':'New 31–40',
    'royalCollection':'V173 Royal collection','showcaseCollection':'V173 animals and vehicles','legacyTables':'Previous tables',
    'classification':'Tier','beginner':'Beginner','professional':'Pro','legendary':'Legendary',
    'price':'Price','status':'Status','owned':'Owned','renewable':'Renewable','available':'Available','category':'Tier','duration':'Duration','expiry':'Expires',
    'activateItem':'Activate item','confirmPurchase':'Confirm purchase','purchaseQuestion':'Buy {name} for {price} tokens? No tokens are deducted until you confirm.',
    'cancel':'Cancel','yesBuy':'Yes, buy','purchaseDone':'Purchased and added to your inventory.','purchaseFailed':'Purchase could not be completed.',
    'itemActivated':'Activated {name}.','testChatMessage':'Preview message in the selected chat color','themePreview':'Theme preview',
    'pashaPreview':'Room controls • special badge • bonus XP','playerRole':'Level {level} • {role}','player':'Player','storeDebitNotice':'Tokens are never deducted during games or rooms. Store purchases require an explicit confirmation.',
  },
  'de': {
    'storeLevelProgress':'Fortschritt Level {level}','xpNeeded':'Noch {points} XP bis zum nächsten Level','roundPoints':'Rundenpunkte','tournamentPoints':'Turnierpunkte','clubPoints':'Clubpunkte',
    'multiplierHint':'Aktueller Multiplikator ×{value} • Wettbewerbe belohnen stärker • gesponserte Saisons bis ×3','premiumItems':'{count} Premium-Artikel','storeSummary':'{tables} Tische • {cards} Kartenrückseiten • bestätigter Kauf mit Live-Vorschau','ownedItems':'{count} im Besitz','storeSearch':'Tische, Designs, Kartenrückseiten und Artikel suchen…',
    'tableCollections':'Tischkollektionen','newTables1':'Neu 1–10','newTables2':'Neu 11–20','newTables3':'Neu 21–30','newTables4':'Neu 31–40','royalCollection':'V173 Royal','showcaseCollection':'V173 Tiere und Fahrzeuge','legacyTables':'Frühere Tische','classification':'Klasse','beginner':'Anfänger','professional':'Profi','legendary':'Legendär',
    'price':'Preis','status':'Status','owned':'Im Besitz','renewable':'Verlängerbar','available':'Verfügbar','category':'Klasse','duration':'Dauer','expiry':'Ablauf','activateItem':'Artikel aktivieren','confirmPurchase':'Kauf bestätigen','purchaseQuestion':'{name} für {price} Token kaufen? Erst nach Bestätigung wird abgebucht.','cancel':'Abbrechen','yesBuy':'Ja, kaufen','purchaseDone':'Gekauft und zum Inventar hinzugefügt.','purchaseFailed':'Kauf fehlgeschlagen.','itemActivated':'{name} aktiviert.','testChatMessage':'Vorschaunachricht in der gewählten Textfarbe','themePreview':'Designvorschau','pashaPreview':'Raumkontrolle • Sonderabzeichen • Bonus-XP','playerRole':'Level {level} • {role}','player':'Spieler','storeDebitNotice':'Token werden nie in Spielen oder Räumen abgezogen. Käufe im Shop benötigen eine klare Bestätigung.',
  },
  'tr': {
    'storeLevelProgress':'Seviye {level} ilerlemesi','xpNeeded':'Sonraki seviye için {points} XP gerekiyor','roundPoints':'Tur puanı','tournamentPoints':'Turnuva puanı','clubPoints':'Kulüp puanı','multiplierHint':'Mevcut çarpan ×{value} • yarışmalar normal oyundan daha fazla verir • sponsorlu sezonlar ×3’e kadar','premiumItems':'{count} premium öğe','storeSummary':'{tables} masa • {cards} kart arkası • onaylı satın alma ve canlı önizleme','ownedItems':'{count} sahip olunan','storeSearch':'Masa, tema, kart arkası ve öğe ara…','tableCollections':'Masa koleksiyonları','newTables1':'Yeni 1–10','newTables2':'Yeni 11–20','newTables3':'Yeni 21–30','newTables4':'Yeni 31–40','royalCollection':'V173 Kraliyet','showcaseCollection':'V173 hayvanlar ve araçlar','legacyTables':'Önceki masalar','classification':'Sınıf','beginner':'Başlangıç','professional':'Profesyonel','legendary':'Efsanevi','price':'Fiyat','status':'Durum','owned':'Sahip','renewable':'Yenilenebilir','available':'Mevcut','category':'Sınıf','duration':'Süre','expiry':'Bitiş','activateItem':'Öğeyi etkinleştir','confirmPurchase':'Satın almayı onayla','purchaseQuestion':'{name}, {price} jetona alınsın mı? Onaylamadan kesinti yapılmaz.','cancel':'İptal','yesBuy':'Evet, satın al','purchaseDone':'Satın alındı ve envantere eklendi.','purchaseFailed':'Satın alma tamamlanamadı.','itemActivated':'{name} etkinleştirildi.','testChatMessage':'Seçilen sohbet renginde önizleme mesajı','themePreview':'Tema önizlemesi','pashaPreview':'Oda kontrolü • özel rozet • ek XP','playerRole':'Seviye {level} • {role}','player':'Oyuncu','storeDebitNotice':'Jetonlar oyunlarda veya odalarda kesilmez. Mağaza satın alımları açık onay gerektirir.',
  },
  'fr': {
    'storeLevelProgress':'Progression niveau {level}','xpNeeded':'Il faut encore {points} XP pour le niveau suivant','roundPoints':'Points de manche','tournamentPoints':'Points de tournoi','clubPoints':'Points du club','multiplierHint':'Multiplicateur actuel ×{value} • les compétitions rapportent davantage • saisons sponsorisées jusqu’à ×3','premiumItems':'{count} articles premium','storeSummary':'{tables} tables • {cards} dos de cartes • achat confirmé avec aperçu direct','ownedItems':'{count} possédés','storeSearch':'Rechercher tables, thèmes, dos de cartes et articles…','tableCollections':'Collections de tables','newTables1':'Nouvelles 1–10','newTables2':'Nouvelles 11–20','newTables3':'Nouvelles 21–30','newTables4':'Nouvelles 31–40','royalCollection':'Collection royale V173','showcaseCollection':'Animaux et véhicules V173','legacyTables':'Tables précédentes','classification':'Catégorie','beginner':'Débutant','professional':'Pro','legendary':'Légendaire','price':'Prix','status':'État','owned':'Possédé','renewable':'Renouvelable','available':'Disponible','category':'Catégorie','duration':'Durée','expiry':'Expiration','activateItem':'Activer l’article','confirmPurchase':'Confirmer l’achat','purchaseQuestion':'Acheter {name} pour {price} jetons ? Aucun débit avant confirmation.','cancel':'Annuler','yesBuy':'Oui, acheter','purchaseDone':'Achat ajouté à votre inventaire.','purchaseFailed':'Impossible de finaliser l’achat.','itemActivated':'{name} activé.','testChatMessage':'Message aperçu dans la couleur choisie','themePreview':'Aperçu du thème','pashaPreview':'Contrôle de salle • badge spécial • XP bonus','playerRole':'Niveau {level} • {role}','player':'Joueur','storeDebitNotice':'Aucun jeton n’est déduit pendant les parties ou dans les salles. Tout achat requiert une confirmation explicite.',
  },
  'es': {
    'storeLevelProgress':'Progreso del nivel {level}','xpNeeded':'Necesitas {points} XP para el siguiente nivel','roundPoints':'Puntos de ronda','tournamentPoints':'Puntos de torneo','clubPoints':'Puntos del club','multiplierHint':'Multiplicador actual ×{value} • las competiciones otorgan más • temporadas patrocinadas hasta ×3','premiumItems':'{count} artículos premium','storeSummary':'{tables} mesas • {cards} reversos • compra confirmada con vista previa','ownedItems':'{count} en propiedad','storeSearch':'Buscar mesas, temas, reversos y artículos…','tableCollections':'Colecciones de mesas','newTables1':'Nuevas 1–10','newTables2':'Nuevas 11–20','newTables3':'Nuevas 21–30','newTables4':'Nuevas 31–40','royalCollection':'Colección real V173','showcaseCollection':'Animales y vehículos V173','legacyTables':'Mesas anteriores','classification':'Categoría','beginner':'Principiante','professional':'Profesional','legendary':'Legendario','price':'Precio','status':'Estado','owned':'En propiedad','renewable':'Renovable','available':'Disponible','category':'Categoría','duration':'Duración','expiry':'Caducidad','activateItem':'Activar artículo','confirmPurchase':'Confirmar compra','purchaseQuestion':'¿Comprar {name} por {price} fichas? No se descontará nada hasta confirmar.','cancel':'Cancelar','yesBuy':'Sí, comprar','purchaseDone':'Compra añadida al inventario.','purchaseFailed':'No se pudo completar la compra.','itemActivated':'{name} activado.','testChatMessage':'Mensaje de prueba con el color elegido','themePreview':'Vista previa del tema','pashaPreview':'Control de sala • insignia especial • XP extra','playerRole':'Nivel {level} • {role}','player':'Jugador','storeDebitNotice':'Nunca se descuentan fichas durante partidas o salas. Las compras requieren una confirmación explícita.',
  },
};

String trV025(AppController controller, String key) =>
    _v025Text[controller.localeCode]?[key] ??
    _v025UiText[controller.localeCode]?[key] ??
    _v025Text['en']?[key] ??
    _v025UiText['en']?[key] ??
    key;

String trfV025(AppController controller, String key, Map<String, Object> values) {
  var text = trV025(controller, key);
  for (final entry in values.entries) {
    text = text.replaceAll('{${entry.key}}', '${entry.value}');
  }
  return text;
}

String rewardLabelV025(AppController controller, Map<String, dynamic> reward) {
  if (controller.localeCode == 'ar' && reward['label_ar'] != null) return reward['label_ar'].toString();
  final amount = int.tryParse('${reward['amount'] ?? 1}') ?? 1;
  final type = reward['type']?.toString() ?? '';
  final locale = controller.localeCode;
  const labels = <String, Map<String, String>>{
    'en': {
      'pasha_days':'{n} Pasha day(s)','table_days':'Table for {n} day(s)','prize_box':'Prize box','ticket':'Competition ticket 200',
      'booster_hours':'{n}-hour booster','chat_color_days':'Text color for {n} day(s)','name_color_days':'Player color for {n} day(s)','tokens':'{n} tokens',
    },
    'de': {
      'pasha_days':'{n} Pascha-Tag(e)','table_days':'Tisch für {n} Tag(e)','prize_box':'Preisbox','ticket':'Wettbewerbsticket 200',
      'booster_hours':'Booster für {n} Stunde(n)','chat_color_days':'Textfarbe für {n} Tag(e)','name_color_days':'Spielerfarbe für {n} Tag(e)','tokens':'{n} Token',
    },
    'tr': {
      'pasha_days':'{n} Paşa günü','table_days':'{n} günlük masa','prize_box':'Ödül kutusu','ticket':'200 yarışma bileti',
      'booster_hours':'{n} saatlik hızlandırıcı','chat_color_days':'{n} günlük yazı rengi','name_color_days':'{n} günlük oyuncu rengi','tokens':'{n} jeton',
    },
    'fr': {
      'pasha_days':'{n} jour(s) Pacha','table_days':'Table pendant {n} jour(s)','prize_box':'Coffre de prix','ticket':'Billet de compétition 200',
      'booster_hours':'Booster de {n} heure(s)','chat_color_days':'Couleur de texte pendant {n} jour(s)','name_color_days':'Couleur du joueur pendant {n} jour(s)','tokens':'{n} jetons',
    },
    'es': {
      'pasha_days':'{n} día(s) Pasha','table_days':'Mesa por {n} día(s)','prize_box':'Caja de premios','ticket':'Entrada de competición 200',
      'booster_hours':'Acelerador de {n} hora(s)','chat_color_days':'Color de texto por {n} día(s)','name_color_days':'Color de jugador por {n} día(s)','tokens':'{n} fichas',
    },
  };
  final template = labels[locale]?[type] ?? labels['en']?[type];
  return template?.replaceAll('{n}', '$amount') ?? trV025(controller, 'reward');
}

List<Map<String, dynamic>> buildRewardRoadV025(int stages) {
  return List<Map<String, dynamic>>.generate(stages, (index) {
    final stage = index + 1;
    if (stage == stages) return {'type':'pasha_days','amount':3,'icon':'👑','label_ar':'3 أيام باشا'};
    if (stage % 7 == 0) return {'type':'table_days','amount':5,'value':'table_v025_challenge','icon':'🎴','label_ar':'طاولة 5 أيام'};
    if (stage % 6 == 0) return {'type':'prize_box','amount':1,'value':'royal_amethyst','icon':'📦','label_ar':'صندوق ملكي'};
    if (stage % 5 == 0) return {'type':'ticket','amount':1,'value':200,'icon':'🎟️','label_ar':'تذكرة مسابقة 200'};
    if (stage % 4 == 0) return {'type':'booster_hours','amount':4,'icon':'⚡','label_ar':'مسرّع 4 ساعات'};
    if (stage % 3 == 0) return {'type':'chat_color_days','amount':3,'value':'#a78bfa','icon':'✍️','label_ar':'لون كتابة 3 أيام'};
    if (stage % 2 == 0) {
      final tokens = math.min(1000, 150 + stage * 50);
      return {'type':'tokens','amount':tokens,'icon':'🪙','label_ar':'$tokens توكن'};
    }
    return {'type':'name_color_days','amount':2,'value':'#facc15','icon':'🎨','label_ar':'لون لاعب يومان'};
  });
}

Map<String, dynamic> levelRewardForV025(int level) {
  late Map<String, dynamic> base;
  if (level % 25 == 0) {
    base = <String,dynamic>{'type':'pasha_days','amount':3,'icon':'👑','label_ar':'3 أيام باشا'};
  } else if (level % 20 == 0) {
    base = <String,dynamic>{'type':'prize_box','amount':1,'value':'diamond_phoenix','icon':'🎁','label_ar':'صندوق ألماسي'};
  } else if (level % 15 == 0) {
    base = <String,dynamic>{'type':'table_days','amount':7,'value':'table_v025_level_royal','icon':'🎴','label_ar':'طاولة ملكية 7 أيام'};
  } else if (level % 10 == 0) {
    base = <String,dynamic>{'type':'pasha_days','amount':1,'icon':'👑','label_ar':'يوم باشا'};
  } else if (level % 7 == 0) {
    base = <String,dynamic>{'type':'ticket','amount':1,'value':200,'icon':'🎟️','label_ar':'تذكرة مسابقة 200'};
  } else if (level % 5 == 0) {
    base = <String,dynamic>{'type':'prize_box','amount':1,'value':'royal_amethyst','icon':'📦','label_ar':'صندوق إضافي'};
  } else if (level % 4 == 0) {
    base = <String,dynamic>{'type':'booster_hours','amount':6,'icon':'⚡','label_ar':'مسرّع نقاط 6 ساعات'};
  } else if (level % 3 == 0) {
    base = <String,dynamic>{'type':'chat_color_days','amount':3,'value':'#22d3ee','icon':'✍️','label_ar':'لون كتابة 3 أيام'};
  } else {
    base = <String,dynamic>{'type':'name_color_days','amount':2,'value':'#facc15','icon':'🎨','label_ar':'لون لاعب يومان'};
  }
  return {...base, 'level': level, 'tokens': math.min(1000, 75 + level * 15)};
}

extension WarqnaV025Controller on AppController {
  bool get isPrimaryAdminV025 => isAdmin && username.trim().toLowerCase() == 'adnan';

  Future<int> recordAbsenceEjectionV025(String gameId) async {
    final count = (absenceEjectionCountsV025[gameId] ?? 0) + 1;
    absenceEjectionCountsV025[gameId] = count;
    activeGame = null;
    activeRoomCode = null;
    awayMode = false;
    notices.insert(0, AppNotice('⏳', trV025(this, 'activeGame'), trV025(this, 'absenceEjected')));
    await _save();
    refreshUi();
    return count;
  }

  Map<String, dynamic> grantLevelRewardLocalV025(int targetLevel) {
    final reward = levelRewardForV025(targetLevel);
    if (locallyGrantedLevelRewardsV025.add(targetLevel)) {
      coins += BigInt.from(int.tryParse('${reward['tokens']}') ?? 0);
      _applyRewardV025(reward, source: 'level:$targetLevel');
      pendingLevelRewardsV025.insert(0, reward);
      unawaited(_save());
    }
    return reward;
  }

  void absorbServerLevelRewardsV025(dynamic raw) {
    if (raw is! List) return;
    for (final item in raw.whereType<Map>()) {
      final reward = Map<String, dynamic>.from(item);
      final rewardLevel = int.tryParse('${reward['level'] ?? 0}') ?? 0;
      if (rewardLevel > 0) locallyGrantedLevelRewardsV025.add(rewardLevel);
      pendingLevelRewardsV025.insert(0, reward);
    }
  }

  Future<void> refreshAccountSnapshotV025() async {
    if (!serverConnected || api.token == null) return;
    try {
      final account = await api.bootstrap();
      _applySession(account);
      await _save();
      refreshUi();
    } catch (_) {}
  }

  void _applyRewardV025(Map<String, dynamic> reward, {required String source}) {
    final amount = int.tryParse('${reward['amount'] ?? 1}') ?? 1;
    final now = DateTime.now();
    switch (reward['type']?.toString()) {
      case 'tokens':
        coins += BigInt.from(math.min(1000, math.max(1, amount)));
        break;
      case 'pasha_days':
        vipDays += amount;
        break;
      case 'ticket':
        competitionTickets[200] = (competitionTickets[200] ?? 0) + amount;
        break;
      case 'booster_hours':
        activeXpMultiplier = math.max(activeXpMultiplier, 2.0);
        boosterExpiresAtV173 = (boosterExpiresAtV173 != null && boosterExpiresAtV173!.isAfter(now) ? boosterExpiresAtV173! : now).add(Duration(hours: amount));
        break;
      case 'chat_color_days':
        selectedChatColor = reward['value']?.toString() ?? '#a78bfa';
        chatColorExpiresAt = now.add(Duration(days: amount));
        break;
      case 'name_color_days':
        selectedNameColor = reward['value']?.toString() ?? '#facc15';
        nameColorExpiresAt = now.add(Duration(days: amount));
        break;
      case 'table_days':
        selectedTable = reward['value']?.toString() ?? 'table_v025_challenge';
        temporaryTableExpiresAtV173 = now.add(Duration(days: amount));
        break;
      case 'prize_box':
        prizeBoxesV02.insert(0, <String,dynamic>{
          'id': -now.microsecondsSinceEpoch,
          'box_key': reward['value']?.toString() ?? 'royal_amethyst',
          'source_type': source,
          'status': 'available',
          'awarded_date': now.toIso8601String().substring(0, 10),
        });
        break;
    }
  }

  Future<Map<String, dynamic>?> syncChallengeJourneyV025() async {
    if (!serverConnected || api.token == null) return activeChallengeJourneyV025;
    try {
      final data = await api.challengeJourneyV025();
      final run = data['run'];
      activeChallengeJourneyV025 = run is Map ? Map<String, dynamic>.from(run) : null;
      if (activeChallengeJourneyV025 == null || activeChallengeJourneyV025?['status']?.toString() != 'active') {
        activeChallenge = null;
      } else {
        activeChallenge = 'journey_v025';
      }
      await _save();
      refreshUi();
    } catch (_) {}
    return activeChallengeJourneyV025;
  }

  Future<String?> startChallengeJourneyV025(String gameKey, int stages) async {
    if (![10, 12, 15].contains(stages)) return 'Invalid stage count.';
    try {
      if (serverConnected && api.token != null) {
        final data = await api.startChallengeJourneyV025(gameKey, stages);
        final run = data['run'];
        if (run is! Map) return trV025(this, 'actionFailed');
        activeChallengeJourneyV025 = Map<String, dynamic>.from(run);
      } else {
        final names = botProfiles.map((e) => e.name(localeCode)).toList();
        names.shuffle(math.Random.secure());
        activeChallengeJourneyV025 = <String,dynamic>{
          'id': 'local-${DateTime.now().millisecondsSinceEpoch}', 'game_key': gameKey,
          'stages_total': stages, 'current_stage': 1, 'attempts_left': 5, 'status': 'active',
          'opponent': {'user_id': null, 'name': names.firstOrNull ?? 'Adnan'},
          'stage_rewards': buildRewardRoadV025(stages), 'claimed_stages': <int>[], 'local_mode': true,
        };
      }
      activeChallenge = 'journey_v025';
      await _save();
      refreshUi();
      return null;
    } on ApiException catch (e) {
      return e.message;
    } catch (_) {
      return trV025(this, 'actionFailed');
    }
  }

  Future<Map<String, dynamic>?> recordChallengeJourneyResultV025({required bool won, required String gameId, String? resultKey}) async {
    final run = activeChallengeJourneyV025;
    if (run == null || run['status']?.toString() != 'active' || run['game_key']?.toString() != gameId) return null;
    final stage = int.tryParse('${run['current_stage'] ?? 1}') ?? 1;
    final stablePart = (resultKey == null || resultKey.trim().isEmpty)
        ? 'stage-$stage-${won ? 'win' : 'loss'}'
        : resultKey.trim();
    final safeResultPart = stablePart.replaceAll(RegExp(r'[^a-zA-Z0-9:._-]'), '-');
    final clientResultId = 'v025:${run['id']}:$gameId:$stage:${safeResultPart.length > 72 ? safeResultPart.substring(0, 72) : safeResultPart}';
    if (serverConnected && api.token != null && run['local_mode'] != true) {
      try {
        final data = await api.recordChallengeJourneyResultV025(won, clientResultId, gameId);
        final updated = data['run'];
        if (updated is Map) {
          activeChallengeJourneyV025 = Map<String, dynamic>.from(updated);
          final granted = activeChallengeJourneyV025!['reward_granted'];
          if (granted is Map) {
            notices.insert(0, AppNotice('🏆', trV025(this, 'reward'), rewardLabelV025(this, Map<String,dynamic>.from(granted))));
            await refreshAccountSnapshotV025();
          }
        }
      } catch (_) {
        return null;
      }
    } else {
      final total = int.tryParse('${run['stages_total'] ?? 10}') ?? 10;
      final claimed = List<int>.from((run['claimed_stages'] as List? ?? const []).map((e) => int.tryParse('$e') ?? 0));
      if (won) {
        final road = (run['stage_rewards'] as List? ?? const []).whereType<Map>().map((e) => Map<String,dynamic>.from(e)).toList();
        if (!claimed.contains(stage) && stage - 1 < road.length) {
          final reward = road[stage - 1];
          _applyRewardV025(reward, source: 'challenge:${run['id']}:$stage');
          claimed.add(stage);
          notices.insert(0, AppNotice('🏆', trV025(this, 'reward'), '${reward['icon'] ?? '🎁'} ${rewardLabelV025(this, reward)}'));
        }
        if (stage >= total) {
          run['status'] = 'completed';
        } else {
          run['current_stage'] = stage + 1;
          run['opponent'] = {'user_id': null, 'name': randomBotNameV025(localeCode)};
        }
      } else {
        final attempts = math.max(0, (int.tryParse('${run['attempts_left'] ?? 5}') ?? 5) - 1);
        run['attempts_left'] = attempts;
        if (attempts == 0) run['status'] = 'failed';
        run['opponent'] = {'user_id': null, 'name': randomBotNameV025(localeCode)};
      }
      run['claimed_stages'] = claimed;
      run['last_result'] = won ? 'win' : 'loss';
      activeChallengeJourneyV025 = Map<String,dynamic>.from(run);
    }
    if (activeChallengeJourneyV025?['status']?.toString() != 'active') activeChallenge = null;
    await _save();
    refreshUi();
    return activeChallengeJourneyV025;
  }
}

String randomBotNameV025(String locale) {
  final names = botProfiles.map((e) => e.name(locale)).toList();
  if (names.isEmpty) return 'Adnan';
  return names[math.Random.secure().nextInt(names.length)];
}

class GlobalAppearanceOverlayV025 extends StatelessWidget {
  final AppController controller;
  final Widget child;
  const GlobalAppearanceOverlayV025({super.key, required this.controller, required this.child});

  @override
  Widget build(BuildContext context) {
    if (!controller.ready) return child;
    return Stack(children: [
      Positioned.fill(child: child),
      PositionedDirectional(
        start: 6,
        top: MediaQuery.paddingOf(context).top + 58,
        child: SafeArea(
          child: Material(
            color: Colors.transparent,
            child: Tooltip(
              message: trV025(controller, 'quickAppearance'),
              child: InkWell(
                borderRadius: BorderRadius.circular(18),
                onTap: () => showAppearanceDockV025(context, controller),
                child: Container(
                  width: 42, height: 42,
                  decoration: BoxDecoration(
                    gradient: LinearGradient(colors: [Theme.of(context).colorScheme.primary.withValues(alpha: .95), Theme.of(context).colorScheme.surface]),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: Colors.white24),
                    boxShadow: const [BoxShadow(color: Colors.black45, blurRadius: 12, offset: Offset(0, 4))],
                  ),
                  child: const Icon(Icons.tune_rounded, size: 21),
                ),
              ),
            ),
          ),
        ),
      ),
    ]);
  }
}

Future<void> showAppearanceDockV025(BuildContext context, AppController controller) {
  return showModalBottomSheet<void>(
    context: context, useSafeArea: true, showDragHandle: true, isScrollControlled: true,
    builder: (context) => StatefulBuilder(builder: (context, setSheetState) {
      void refresh() => setSheetState(() {});
      return SingleChildScrollView(
        padding: const EdgeInsets.fromLTRB(16, 0, 16, 24),
        child: Column(crossAxisAlignment: CrossAxisAlignment.stretch, children: [
          Text(trV025(controller, 'quickAppearance'), style: const TextStyle(fontSize: 21, fontWeight: FontWeight.w900)),
          Text(trV025(controller, 'appearanceHint'), style: const TextStyle(color: Colors.white60, fontSize: 11)),
          const SizedBox(height: 14),
          Row(children: [
            Expanded(child: DropdownButtonFormField<String>(
              value: controller.localeCode,
              decoration: InputDecoration(labelText: trV025(controller, 'language')),
              items: const [('ar','العربية'),('en','English'),('de','Deutsch'),('tr','Türkçe'),('fr','Français'),('es','Español')]
                  .map((e) => DropdownMenuItem(value: e.$1, child: Text(e.$2))).toList(),
              onChanged: (value) { if (value != null) { controller.changeLocale(value); refresh(); } },
            )),
            const SizedBox(width: 10),
            Expanded(child: DropdownButtonFormField<String>(
              value: controller.uiFontFamily,
              decoration: InputDecoration(labelText: trV025(controller, 'font')),
              items: const ['Roboto','Arial','serif','monospace'].map((e) => DropdownMenuItem(value:e, child:Text(e))).toList(),
              onChanged: (value) { if (value != null) { controller.changeFontFamily(value); refresh(); } },
            )),
          ]),
          const SizedBox(height: 12),
          Text('${trV025(controller, 'fontSize')}: ${controller.uiFontScale.toStringAsFixed(2)}×', style: const TextStyle(fontWeight: FontWeight.w800)),
          Row(children: [
            IconButton.filledTonal(onPressed: () { controller.updateNoCodeDesign(fontScale: math.max(.85, controller.uiFontScale - .05)); refresh(); }, icon: const Icon(Icons.remove)),
            Expanded(child: Slider(min:.85, max:1.35, divisions:20, value:controller.uiFontScale.clamp(.85,1.35).toDouble(), onChanged:(v){ controller.updateNoCodeDesign(fontScale:v); refresh(); })),
            IconButton.filled(onPressed: () { controller.updateNoCodeDesign(fontScale: math.min(1.35, controller.uiFontScale + .05)); refresh(); }, icon: const Icon(Icons.add)),
          ]),
          const SizedBox(height: 8),
          Text(trV025(controller, 'theme'), style: const TextStyle(fontWeight: FontWeight.w900)),
          const SizedBox(height: 7),
          Wrap(spacing: 7, runSpacing: 7, children: const ['dark','royal','emerald','midnight','classic','ocean','purple','sunset','carbon','gold','forest','ruby','ice']
              .map((theme) => ChoiceChip(label: Text(theme.toUpperCase(), style: const TextStyle(fontSize: 10)), selected: controller.themeCode == theme, onSelected: (_) { controller.changeTheme(theme); refresh(); })).toList()),
          const SizedBox(height: 10),
          SegmentedButton<bool>(
            segments: [
              ButtonSegment(value:false, icon:const Icon(Icons.stay_current_portrait), label:Text(trV025(controller,'portrait'))),
              ButtonSegment(value:true, icon:const Icon(Icons.stay_current_landscape), label:Text(trV025(controller,'landscape'))),
            ],
            selected: {controller.landscapeMode},
            onSelectionChanged: (value) async { await controller.setLandscapeMode(value.first); refresh(); },
          ),
        ]),
      );
    }),
  );
}

class Premium3DButtonV025 extends StatefulWidget {
  final VoidCallback? onPressed;
  final Widget child;
  final IconData? icon;
  final bool compact;
  const Premium3DButtonV025({super.key, required this.onPressed, required this.child, this.icon, this.compact = false});
  @override State<Premium3DButtonV025> createState() => _Premium3DButtonV025State();
}

class _Premium3DButtonV025State extends State<Premium3DButtonV025> {
  bool pressed = false;
  @override Widget build(BuildContext context) {
    final active = widget.onPressed != null;
    return GestureDetector(
      onTapDown: active ? (_) => setState(() => pressed = true) : null,
      onTapCancel: active ? () => setState(() => pressed = false) : null,
      onTapUp: active ? (_) { setState(() => pressed = false); widget.onPressed?.call(); } : null,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 90),
        transform: Matrix4.translationValues(0, pressed ? 4 : 0, 0),
        padding: EdgeInsets.symmetric(horizontal: widget.compact ? 11 : 16, vertical: widget.compact ? 9 : 13),
        decoration: BoxDecoration(
          gradient: LinearGradient(begin:Alignment.topCenter,end:Alignment.bottomCenter,colors: active
              ? [Theme.of(context).colorScheme.primary.withValues(alpha:.98), Theme.of(context).colorScheme.primary.withValues(alpha:.58)]
              : [Colors.white12, Colors.white10]),
          borderRadius: BorderRadius.circular(16), border: Border.all(color:Colors.white30),
          boxShadow: pressed ? const [] : [BoxShadow(color: Theme.of(context).colorScheme.primary.withValues(alpha:.3), blurRadius:10, offset:const Offset(0,5)), const BoxShadow(color:Colors.black54, blurRadius:4, offset:Offset(0,5))],
        ),
        child: DefaultTextStyle(style: TextStyle(color:active?Colors.black87:Colors.white38,fontWeight:FontWeight.w900), child: Row(mainAxisSize:MainAxisSize.min,mainAxisAlignment:MainAxisAlignment.center,children:[if(widget.icon!=null)...[Icon(widget.icon,size:19,color:active?Colors.black87:Colors.white38),const SizedBox(width:7)],widget.child])),
      ),
    );
  }
}

void showChallengeHubV025(BuildContext context, AppController controller) {
  showModalBottomSheet<void>(
    context: context, isScrollControlled: true, useSafeArea: true, showDragHandle: true,
    builder: (_) => FractionallySizedBox(heightFactor: .94, child: ChallengeHubV025(controller: controller)),
  );
}

class ChallengeHubV025 extends StatelessWidget {
  final AppController controller;
  const ChallengeHubV025({super.key, required this.controller});
  @override Widget build(BuildContext context) => DefaultTabController(
    length: 2,
    child: Column(children: [
      Padding(padding:const EdgeInsets.symmetric(horizontal:14), child:Row(children:[const Text('🏆',style:TextStyle(fontSize:28)),const SizedBox(width:8),Expanded(child:Text(trV025(controller,'challengeJourney'),style:const TextStyle(fontSize:21,fontWeight:FontWeight.w900)))])),
      TabBar(tabs:[Tab(text:trV025(controller,'challengeJourney')),Tab(text:trV025(controller,'classicChallenges'))]),
      Expanded(child:TabBarView(children:[ChallengeJourneyV025(controller:controller),SingleChildScrollView(padding:const EdgeInsets.all(14),child:ChallengeCenterV175(controller:controller))])),
    ]),
  );
}

class ChallengeJourneyV025 extends StatefulWidget {
  final AppController controller;
  const ChallengeJourneyV025({super.key, required this.controller});
  @override State<ChallengeJourneyV025> createState() => _ChallengeJourneyV025State();
}

class _ChallengeJourneyV025State extends State<ChallengeJourneyV025> {
  String game = 'tarneeb';
  int stages = 10;
  bool busy = false;

  @override void initState() {
    super.initState();
    final run = widget.controller.activeChallengeJourneyV025;
    game = run?['game_key']?.toString() ?? game;
    stages = int.tryParse('${run?['stages_total'] ?? stages}') ?? stages;
    WidgetsBinding.instance.addPostFrameCallback((_) async {
      await widget.controller.syncChallengeJourneyV025();
      if (!mounted) return;
      final synced = widget.controller.activeChallengeJourneyV025;
      setState(() {
        game = synced?['game_key']?.toString() ?? game;
        stages = int.tryParse('${synced?['stages_total'] ?? stages}') ?? stages;
      });
    });
  }

  Future<void> _start() async {
    setState(() => busy = true);
    final error = await widget.controller.startChallengeJourneyV025(game, stages);
    if (!mounted) return;
    setState(() => busy = false);
    if (error != null) showToast(context, error);
  }

  void _play() {
    final selected = gamesCatalog.firstWhere((e) => e.id == game, orElse: () => gamesCatalog.first);
    Navigator.of(context).pop();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final rootContext = warqnaNavigatorKey.currentContext;
      if (rootContext != null) showGameLobby(rootContext, widget.controller, selected);
    });
  }

  @override Widget build(BuildContext context) {
    final run = widget.controller.activeChallengeJourneyV025;
    final active = run != null && run['status']?.toString() == 'active';
    final total = int.tryParse('${run?['stages_total'] ?? stages}') ?? stages;
    final current = int.tryParse('${run?['current_stage'] ?? 1}') ?? 1;
    final attempts = int.tryParse('${run?['attempts_left'] ?? 5}') ?? 5;
    final claimed = (run?['claimed_stages'] as List? ?? const []).map((e)=>int.tryParse('$e')??0).toSet();
    final road = active
        ? (run?['stage_rewards'] as List? ?? const []).whereType<Map>().map((e)=>Map<String,dynamic>.from(e)).toList()
        : buildRewardRoadV025(stages);
    final status = run?['status']?.toString();
    final opponent = (run?['opponent'] is Map ? (run!['opponent'] as Map)['name'] : null)?.toString() ?? '—';
    return ListView(padding:const EdgeInsets.all(14),children:[
      PremiumPanel(child:Padding(padding:const EdgeInsets.all(14),child:Column(crossAxisAlignment:CrossAxisAlignment.stretch,children:[
        Text(trV025(widget.controller,'journeyRule'),style:const TextStyle(color:Colors.white70,height:1.5)),const SizedBox(height:12),
        DropdownButtonFormField<String>(value:game,decoration:InputDecoration(labelText:trV025(widget.controller,'chooseGame')),items:gamesCatalog.map((e)=>DropdownMenuItem(value:e.id,child:Text('${e.icon} ${L.t(widget.controller.localeCode,e.id)}'))).toList(),onChanged:active?null:(v)=>setState(()=>game=v??game)),
        const SizedBox(height:10),
        SegmentedButton<int>(segments:[10,12,15].map((v)=>ButtonSegment(value:v,label:Text('$v'))).toList(),selected:{stages},onSelectionChanged:active?null:(v)=>setState(()=>stages=v.first)),
        const SizedBox(height:12),
        if(active)...[
          Row(children:[Expanded(child:Text('${trV025(widget.controller,'stage')} $current / $total',style:const TextStyle(fontSize:17,fontWeight:FontWeight.w900))),Text(List.filled(attempts,'❤️').join(),style:const TextStyle(fontSize:18))]),
          const SizedBox(height:8),ClipRRect(borderRadius:BorderRadius.circular(99),child:LinearProgressIndicator(value:((current-1)/total).clamp(0.0,1.0).toDouble(),minHeight:11)),
          const SizedBox(height:10),ListTile(contentPadding:EdgeInsets.zero,leading:const CircleAvatar(child:Icon(Icons.person)),title:Text('${trV025(widget.controller,'opponent')}: $opponent'),subtitle:Text(L.t(widget.controller.localeCode,game))),
        ] else if(status=='completed') Center(child:Text('🏆 ${trV025(widget.controller,'completed')}',style:const TextStyle(fontSize:20,fontWeight:FontWeight.w900,color:Colors.lightGreenAccent)))
          else if(status=='failed') Center(child:Text('💔 ${trV025(widget.controller,'failed')}',style:const TextStyle(fontSize:20,fontWeight:FontWeight.w900,color:Colors.redAccent))),
        const SizedBox(height:12),
        Premium3DButtonV025(onPressed:busy?null:(active?_play:_start),icon:active?Icons.play_arrow_rounded:Icons.flag_rounded,child:Text(busy?'…':trV025(widget.controller,active?'continueJourney':'startJourney'))),
      ]))),
      const SizedBox(height:12),
      Text('${trV025(widget.controller,'reward')} • $total',style:const TextStyle(fontSize:16,fontWeight:FontWeight.w900)),const SizedBox(height:8),
      ...List.generate(road.length, (i) {
        final stage = i + 1;
        final reward = road[i];
        final isClaimed = claimed.contains(stage);
        final isCurrent = active && stage == current;
        return Padding(
          padding: const EdgeInsets.only(bottom: 7),
          child: PremiumPanel(
            child: ListTile(
              leading: CircleAvatar(
                backgroundColor: isClaimed
                    ? Colors.green.withValues(alpha: .22)
                    : isCurrent
                        ? Theme.of(context).colorScheme.primary.withValues(alpha: .25)
                        : Colors.white10,
                child: Text(reward['icon']?.toString() ?? '🎁'),
              ),
              title: Text(
                '${trV025(widget.controller, 'stage')} $stage • ${rewardLabelV025(widget.controller, reward)}',
                style: TextStyle(
                  fontWeight: FontWeight.w800,
                  color: isCurrent ? Theme.of(context).colorScheme.primary : null,
                ),
              ),
              trailing: Icon(
                isClaimed ? Icons.check_circle : isCurrent ? Icons.play_circle_fill : Icons.lock_outline,
                color: isClaimed
                    ? Colors.lightGreenAccent
                    : isCurrent
                        ? Theme.of(context).colorScheme.primary
                        : Colors.white24,
              ),
            ),
          ),
        );
      }),
    ]);
  }
}


void showLevelRewardsV025(BuildContext context, AppController controller) {
  showModalBottomSheet<void>(
    context: context,
    isScrollControlled: true,
    useSafeArea: true,
    showDragHandle: true,
    builder: (_) => FractionallySizedBox(
      heightFactor: .94,
      child: Column(
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(16, 0, 16, 10),
            child: Row(
              children: [
                const Text('🎁', style: TextStyle(fontSize: 30)),
                const SizedBox(width: 9),
                Expanded(child: Text(trV025(controller, 'levelRewardRoad'), style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w900))),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Text(trV025(controller, 'levelRewardHint'), textAlign: TextAlign.center, style: const TextStyle(color: Colors.white70, height: 1.45)),
          ),
          const SizedBox(height: 10),
          Expanded(
            child: ListView.builder(
              padding: const EdgeInsets.fromLTRB(14, 0, 14, 22),
              itemCount: 99,
              itemBuilder: (_, index) {
                final targetLevel = index + 2;
                final reward = levelRewardForV025(targetLevel);
                final claimed = controller.locallyGrantedLevelRewardsV025.contains(targetLevel);
                final current = targetLevel == controller.level + 1;
                final tokens = int.tryParse('${reward['tokens'] ?? 0}') ?? 0;
                return Padding(
                  padding: const EdgeInsets.only(bottom: 7),
                  child: PremiumPanel(
                    child: ListTile(
                      leading: CircleAvatar(
                        backgroundColor: claimed ? Colors.green.withValues(alpha: .2) : current ? Theme.of(context).colorScheme.primary.withValues(alpha: .22) : Colors.white10,
                        child: Text('$targetLevel', style: const TextStyle(fontWeight: FontWeight.w900)),
                      ),
                      title: Text('${reward['icon'] ?? '🎁'} ${rewardLabelV025(controller, reward)}', style: const TextStyle(fontWeight: FontWeight.w800)),
                      subtitle: Text('+$tokens ${trV025(controller, 'tokensWord')}'),
                      trailing: Icon(claimed ? Icons.check_circle : current ? Icons.lock_open_rounded : Icons.lock_outline, color: claimed ? Colors.lightGreenAccent : current ? Theme.of(context).colorScheme.primary : Colors.white24),
                    ),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    ),
  );
}

class AdminDesignerGateV025 extends StatelessWidget {
  final AppController controller;
  final Widget child;
  const AdminDesignerGateV025({super.key, required this.controller, required this.child});
  @override Widget build(BuildContext context) => controller.isPrimaryAdminV025 ? child : Center(child:Padding(padding:const EdgeInsets.all(24),child:PremiumPanel(child:Padding(padding:const EdgeInsets.all(24),child:Column(mainAxisSize:MainAxisSize.min,children:[const Icon(Icons.admin_panel_settings,size:58,color:Colors.amber),const SizedBox(height:12),Text(trV025(controller,'adminOnly'),textAlign:TextAlign.center,style:const TextStyle(fontWeight:FontWeight.w800,height:1.5))])))));
}
