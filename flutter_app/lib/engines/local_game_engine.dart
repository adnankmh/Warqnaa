import 'dart:math';

/// Offline/local fallback engine used by the Flutter Web/PWA build.
///
/// The authoritative online mode remains the Laravel engine. This local engine
/// keeps every curated card game playable when the app is hosted on GitHub
/// Pages or opened without a backend connection.
class LocalGameSession {
  LocalGameSession({required this.gameId, required this.humanName, int? seed})
      : _random = Random(seed) {
    _setup();
  }

  final String gameId;
  final String humanName;
  final Random _random;

  static const _standardRanks = <String>[
    '2',
    '3',
    '4',
    '5',
    '6',
    '7',
    '8',
    '9',
    '10',
    'J',
    'Q',
    'K',
    'A',
  ];
  static const _balootRanks = <String>['7', '8', '9', '10', 'J', 'Q', 'K', 'A'];
  static const _suits = <String>['C', 'D', 'S', 'H'];
  static const _botNames = <String>['سامر', 'ليلى', 'جميل'];

  final List<List<String>> _hands = <List<String>>[];
  final List<String> _deck = <String>[];
  final List<String> _discard = <String>[];
  final List<List<String>> _melds = <List<String>>[];
  final List<String> _table = <String>[];
  final Map<int, String> _trick = <int, String>{};
  final Map<int, int> _tricksWon = <int, int>{0: 0, 1: 0, 2: 0, 3: 0};
  final Map<int, int> _scores = <int, int>{0: 0, 1: 0, 2: 0, 3: 0};
  final Map<int, List<String>> _captured = <int, List<String>>{0: <String>[], 1: <String>[]};
  final Map<int, int> _basras = <int, int>{0: 0, 1: 0};
  final List<String> _messages = <String>[];

  String phase = 'playing';
  String enginePhase = 'playing';
  String? trump;
  String? contract;
  int currentSeat = 0;
  int highestBid = 6;
  int? bidWinner;
  int round = 1;
  bool gameOver = false;
  String? winnerKey;
  int _lastCaptureSeat = 0;

  bool get _isRummy =>
      gameId == 'hand' ||
      gameId == 'hand_partner' ||
      gameId == 'saudi_hand' ||
      gameId == 'banakil';

  bool get _isBasra => gameId == 'basra';

  bool get _isTrix => gameId == 'trix' || gameId == 'trix_partner' || gameId == 'trix_complex';

  bool get _isTrixPartner => gameId == 'trix_partner';

  bool get _isHandPartner => gameId == 'hand_partner';

  bool get _isSyrianTarneeb => gameId == 'syrian_tarneeb';

  bool get _isTarneeb400 => gameId == 'tarneeb_400';

  bool get _isTarneebVariant => gameId == 'syrian_tarneeb' || gameId == 'tarneeb_400';

  bool get _isBaloot => gameId == 'baloot';

  int get playerCount => _isBasra ? 2 : 4;

  void _setup() {
    _hands.clear();
    _deck.clear();
    _discard.clear();
    _melds.clear();
    _table.clear();
    _trick.clear();
    _messages.clear();
    _tricksWon.updateAll((_, __) => 0);
    _scores.updateAll((_, __) => 0);
    _captured[0] = <String>[];
    _captured[1] = <String>[];
    _basras[0] = 0;
    _basras[1] = 0;
    currentSeat = 0;
    highestBid = 6;
    bidWinner = null;
    trump = null;
    contract = null;
    gameOver = false;
    winnerKey = null;
    round = 1;

    if (_isRummy) {
      _setupRummy();
    } else if (_isBasra) {
      _setupBasra();
    } else {
      _setupTrickGame();
    }
  }

  List<String> _makeDeck({bool baloot = false, int copies = 1, bool jokers = false}) {
    final ranks = baloot ? _balootRanks : _standardRanks;
    final cards = <String>[];
    for (var copy = 0; copy < copies; copy++) {
      for (final suit in _suits) {
        for (final rank in ranks) {
          cards.add('$rank$suit');
        }
      }
    }
    if (jokers) {
      cards.add('JOKER1');
      cards.add('JOKER2');
    }
    cards.shuffle(_random);
    return cards;
  }

  void _setupTrickGame() {
    _deck.addAll(_makeDeck(baloot: _isBaloot));
    final handSize = _isBaloot ? 8 : 13;
    for (var seat = 0; seat < 4; seat++) {
      _hands.add(<String>[]);
    }
    for (var card = 0; card < handSize; card++) {
      for (var seat = 0; seat < 4; seat++) {
        _hands[seat].add(_deck.removeLast());
      }
    }
    for (final hand in _hands) {
      _sortHand(hand);
    }

    if (_isTarneebVariant) {
      phase = 'bidding';
      enginePhase = 'bidding';
      _messages.add('ابدأ المزايدة من 7 حتى 13.');
    } else if (_isTrix) {
      phase = 'choose_contract';
      enginePhase = 'choose_contract';
      _messages.add('اختر عقد الجولة ثم ابدأ اللعب.');
    } else if (_isBaloot) {
      phase = 'choose_contract';
      enginePhase = 'choose_contract';
      _messages.add('اختر صن أو حكم.');
    } else {
      phase = 'playing';
      enginePhase = 'playing';
    }
  }

  void _setupRummy() {
    _deck.addAll(_makeDeck(copies: 2, jokers: true));
    for (var seat = 0; seat < 4; seat++) {
      _hands.add(<String>[]);
    }
    for (var card = 0; card < 14; card++) {
      for (var seat = 0; seat < 4; seat++) {
        _hands[seat].add(_deck.removeLast());
      }
    }
    for (final hand in _hands) {
      _sortHand(hand);
    }
    _discard.add(_deck.removeLast());
    phase = 'draw';
    enginePhase = 'draw';
    _messages.add('اسحب من الرزمة أو المكشوف، نزّل المجموعات، ثم ارمِ ورقة.');
  }

  void _setupBasra() {
    _deck.addAll(_makeDeck());
    _hands.add(<String>[]);
    _hands.add(<String>[]);
    _dealBasraHands();
    for (var i = 0; i < 4; i++) {
      _table.add(_deck.removeLast());
    }
    phase = 'playing';
    enginePhase = 'playing';
    _messages.add('التقط الورق المطابق أو مجموعة تساوي قيمة ورقتك.');
  }

  void _dealBasraHands() {
    for (var card = 0; card < 4 && _deck.length >= playerCount; card++) {
      for (var seat = 0; seat < 2; seat++) {
        _hands[seat].add(_deck.removeLast());
      }
    }
    _sortHand(_hands[0]);
    _sortHand(_hands[1]);
  }

  Map<String, dynamic> room() {
    return <String, dynamic>{
      'code': 'LOCAL-${gameId.toUpperCase()}',
      'local': true,
      'players': List<Map<String, dynamic>>.generate(playerCount, (index) {
        final name = index == 0 ? humanName : _botNames[index - 1];
        return <String, dynamic>{
          'key': index == 0 ? 'user:0' : 'bot:$index',
          'name': name,
          'bot': index != 0,
          'seat': index,
          'score': _scores[index] ?? 0,
        };
      }),
      'state': _publicState(),
    };
  }

  Map<String, dynamic> _publicState() {
    final state = <String, dynamic>{
      'hand': List<String>.from(_hands.isEmpty ? const <String>[] : _hands[0]),
      'legal_cards': currentSeat == 0 ? _legalCardsFor(0) : <String>[],
      'available_actions': currentSeat == 0 ? _availableActions() : <Map<String, dynamic>>[],
      'phase': phase,
      'engine_phase': enginePhase,
      'trump': trump,
      'contract': contract,
      'current_player': currentSeat == 0 ? 'user:0' : 'bot:$currentSeat',
      'trick': <String, String>{
        for (final entry in _trick.entries)
          (entry.key == 0 ? 'user:0' : 'bot:${entry.key}'): entry.value,
      },
      'table': List<String>.from(_table),
      'messages': List<String>.from(_messages.take(30)),
      'scores': <String, int>{
        for (var seat = 0; seat < playerCount; seat++)
          (seat == 0 ? 'user:0' : 'bot:$seat'): _scores[seat] ?? 0,
      },
      'tricks': <String, int>{
        for (var seat = 0; seat < playerCount; seat++)
          (seat == 0 ? 'user:0' : 'bot:$seat'): _tricksWon[seat] ?? 0,
      },
      'round': round,
      'game_over': gameOver,
      'winner': winnerKey,
      'deck_count': _deck.length,
      'discard_top': _discard.isEmpty ? null : _discard.last,
      'melds': _melds.map((e) => List<String>.from(e)).toList(),
      'basras': <String, int>{'user:0': _basras[0] ?? 0, 'bot:1': _basras[1] ?? 0},
      'captured_count': <String, int>{'user:0': _captured[0]?.length ?? 0, 'bot:1': _captured[1]?.length ?? 0},
    };
    if (_isRummy && _discard.isNotEmpty) {
      state['table'] = <String>[..._melds.expand((meld) => meld), _discard.last];
    }
    return state;
  }

  List<Map<String, dynamic>> _availableActions() {
    if (gameOver) {
      return <Map<String, dynamic>>[
        <String, dynamic>{'type': 'new_round', 'label': 'إعادة اللعب'},
      ];
    }

    if (_isRummy) {
      if (phase == 'draw') {
        return <Map<String, dynamic>>[
          <String, dynamic>{'type': 'draw_deck'},
          if (_discard.isNotEmpty) <String, dynamic>{'type': 'draw_discard'},
          <String, dynamic>{'type': 'organize'},
        ];
      }
      final actions = <Map<String, dynamic>>[
        for (final card in _hands[0]) <String, dynamic>{'type': 'discard', 'card': card},
        <String, dynamic>{'type': 'organize'},
      ];
      for (final meld in _meldSuggestions(_hands[0])) {
        actions.add(<String, dynamic>{'type': 'meld', 'cards': meld});
      }
      return actions;
    }

    if (_isBasra) {
      return <Map<String, dynamic>>[
        for (final card in _hands[0]) <String, dynamic>{'type': 'play_card', 'card': card},
      ];
    }

    if (phase == 'bidding') {
      return <Map<String, dynamic>>[
        for (var value = max(7, highestBid + 1); value <= 13; value++)
          <String, dynamic>{'type': 'bid', 'amount': value},
        <String, dynamic>{'type': 'pass'},
      ];
    }
    if (phase == 'choose_trump') {
      return <Map<String, dynamic>>[
        for (final suit in _suits) <String, dynamic>{'type': 'choose_trump', 'suit': suit},
      ];
    }
    if (phase == 'choose_contract') {
      if (_isBaloot) {
        return <Map<String, dynamic>>[
          <String, dynamic>{'type': 'choose_contract', 'contract': 'sun'},
          <String, dynamic>{'type': 'choose_contract', 'contract': 'hokm'},
        ];
      }
      final values = gameId == 'trix_complex'
          ? <String>['complex', 'trix']
          : <String>['king_hearts', 'girls', 'diamonds', 'tricks', 'trix'];
      return <Map<String, dynamic>>[
        for (final value in values) <String, dynamic>{'type': 'choose_contract', 'contract': value},
      ];
    }
    return <Map<String, dynamic>>[
      for (final card in _legalCardsFor(0)) <String, dynamic>{'type': 'play_card', 'card': card},
    ];
  }

  Map<String, dynamic> action(String action, Map<String, dynamic>? payload) {
    if (action == 'new_round') {
      _setup();
      return room();
    }
    if (gameOver) {
      return room();
    }
    if (currentSeat != 0) {
      _autoBotsUntilHuman();
    }

    if (_isRummy) {
      _rummyAction(action, payload ?? const <String, dynamic>{});
    } else if (_isBasra) {
      _basraAction(action, payload ?? const <String, dynamic>{});
    } else {
      _trickAction(action, payload ?? const <String, dynamic>{});
    }
    return room();
  }

  Map<String, dynamic> timeout() {
    if (gameOver) {
      _setup();
      return room();
    }
    if (currentSeat != 0) {
      _autoBotsUntilHuman();
      return room();
    }
    if (_isRummy) {
      if (phase == 'draw') {
        _rummyAction('draw_deck', const <String, dynamic>{});
      } else if (_hands[0].isNotEmpty) {
        _rummyAction('discard', <String, dynamic>{'card': _highestCard(_hands[0])});
      }
    } else if (_isBasra) {
      if (_hands[0].isNotEmpty) {
        _basraAction('play_card', <String, dynamic>{'card': _bestBasraCard(0)});
      }
    } else if (phase == 'bidding') {
      _trickAction('bid', <String, dynamic>{'amount': max(7, highestBid + 1)});
    } else if (phase == 'choose_trump') {
      _trickAction('choose_trump', <String, dynamic>{'suit': _bestSuit(_hands[0])});
    } else if (phase == 'choose_contract') {
      _trickAction('choose_contract', <String, dynamic>{'contract': _isBaloot ? 'sun' : (gameId == 'trix_complex' ? 'complex' : 'tricks')});
    } else {
      final legal = _legalCardsFor(0);
      if (legal.isNotEmpty) {
        _trickAction('play_card', <String, dynamic>{'card': legal.first});
      }
    }
    return room();
  }

  void _trickAction(String action, Map<String, dynamic> payload) {
    if (phase == 'bidding') {
      if (action == 'pass') {
        _messages.add('$humanName: سكون');
      } else if (action == 'bid') {
        final amount = int.tryParse(payload['amount']?.toString() ?? '') ?? 0;
        if (amount < max(7, highestBid + 1) || amount > 13) {
          throw StateError('طلب غير قانوني.');
        }
        highestBid = amount;
        bidWinner = 0;
        _messages.add('$humanName طلب $amount');
      } else {
        throw StateError('الحركة غير متاحة في المزايدة.');
      }
      _finishLocalBidding();
      return;
    }

    if (phase == 'choose_contract') {
      if (action != 'choose_contract') {
        throw StateError('اختر العقد أولاً.');
      }
      final value = payload['contract']?.toString() ?? '';
      if (_isBaloot) {
        if (value != 'sun' && value != 'hokm') {
          throw StateError('اختر صن أو حكم.');
        }
        contract = value;
        if (value == 'hokm') {
          phase = 'choose_trump';
          enginePhase = 'choose_trump';
          _messages.add('تم اختيار حكم؛ اختر النوع.');
        } else {
          phase = 'playing';
          enginePhase = 'playing';
          trump = null;
          _messages.add('بدأت جولة الصن.');
        }
      } else {
        contract = value;
        phase = 'playing';
        enginePhase = 'playing';
        _messages.add('العقد: ${_contractLabel(value)}');
      }
      return;
    }

    if (phase == 'choose_trump') {
      if (action != 'choose_trump') {
        throw StateError('اختر نوع الحكم أولاً.');
      }
      final suit = payload['suit']?.toString() ?? '';
      if (!_suits.contains(suit)) {
        throw StateError('نوع غير صحيح.');
      }
      trump = suit;
      phase = 'playing';
      enginePhase = 'playing';
      currentSeat = bidWinner ?? 0;
      _messages.add('الحكم: ${_suitSymbol(suit)}');
      _autoBotsUntilHuman();
      return;
    }

    if (action != 'play_card') {
      throw StateError('اختر ورقة للعب.');
    }
    final card = payload['card']?.toString() ?? '';
    _playTrickCard(0, card);
    _autoBotsUntilHuman();
  }

  void _finishLocalBidding() {
    final biddingOrder = _isSyrianTarneeb ? <int>[3, 2, 1] : <int>[1, 2, 3];
    for (final seat in biddingOrder) {
      final strength = _handStrength(_hands[seat]);
      final suggested = 7 + (strength ~/ 55);
      if (suggested > highestBid && suggested <= 10 && _random.nextDouble() > .25) {
        highestBid = suggested;
        bidWinner = seat;
        _messages.add('${_botNames[seat - 1]} طلب $suggested');
      } else {
        _messages.add('${_botNames[seat - 1]}: سكون');
      }
    }
    bidWinner ??= _strongestSeat();
    if (highestBid < 7) {
      highestBid = 7;
    }
    if (_isTarneeb400) {
      trump = 'H';
      phase = 'playing';
      enginePhase = 'playing';
      currentSeat = bidWinner ?? 0;
      _messages.add('الكبة ♥ هي الحكم الثابت في طرنيب 400.');
      _autoBotsUntilHuman();
    } else if (bidWinner == 0) {
      phase = 'choose_trump';
      enginePhase = 'choose_trump';
      currentSeat = 0;
    } else {
      trump = _bestSuit(_hands[bidWinner!]);
      phase = 'playing';
      enginePhase = 'playing';
      currentSeat = bidWinner!;
      _messages.add('${_botNames[bidWinner! - 1]} اختار ${_suitSymbol(trump!)}');
      _autoBotsUntilHuman();
    }
  }

  void _playTrickCard(int seat, String card) {
    if (phase != 'playing' || currentSeat != seat) {
      throw StateError('ليس دور هذا اللاعب.');
    }
    final legal = _legalCardsFor(seat);
    if (!legal.contains(card)) {
      throw StateError('يجب اتباع النوع المتصدر عند توفره.');
    }
    _hands[seat].remove(card);
    _trick[seat] = card;
    _messages.add('${_seatName(seat)} رمى ${_prettyCard(card)}');

    if (_trick.length == 4) {
      final winner = _trickWinner();
      _tricksWon[winner] = (_tricksWon[winner] ?? 0) + 1;
      _scoreTrixTrick(winner);
      _messages.add('${_seatName(winner)} أخذ اللمّة');
      _trick.clear();
      currentSeat = winner;
      if (_hands.every((hand) => hand.isEmpty)) {
        _finishTrickRound();
      }
    } else {
      currentSeat = _nextTrickSeat(seat);
    }
  }

  void _autoBotsUntilHuman() {
    var guard = 0;
    while (!gameOver && currentSeat != 0 && guard < 80) {
      if (_isRummy) {
        _runRummyBot(currentSeat);
      } else if (_isBasra) {
        _playBasraCard(currentSeat, _bestBasraCard(currentSeat));
      } else if (phase == 'playing') {
        final card = _bestBotTrickCard(currentSeat);
        _playTrickCard(currentSeat, card);
      } else if (phase == 'choose_trump') {
        trump = _bestSuit(_hands[currentSeat]);
        phase = 'playing';
        enginePhase = 'playing';
      } else {
        break;
      }
      guard++;
    }
  }

  List<String> _legalCardsFor(int seat) {
    if (gameOver || seat >= _hands.length) {
      return <String>[];
    }
    if (_isRummy) {
      return phase == 'discard' && currentSeat == seat ? List<String>.from(_hands[seat]) : <String>[];
    }
    if (_isBasra) {
      return currentSeat == seat ? List<String>.from(_hands[seat]) : <String>[];
    }
    if (phase != 'playing' || currentSeat != seat) {
      return <String>[];
    }
    if (_trick.isEmpty) {
      return List<String>.from(_hands[seat]);
    }
    final leadSuit = _cardSuit(_trick.values.first);
    final sameSuit = _hands[seat].where((card) => _cardSuit(card) == leadSuit).toList();
    return sameSuit.isEmpty ? List<String>.from(_hands[seat]) : sameSuit;
  }

  int _trickWinner() {
    final leadSuit = _cardSuit(_trick.values.first);
    var winner = _trick.keys.first;
    var best = _trick.values.first;
    for (final entry in _trick.entries.skip(1)) {
      if (_beats(entry.value, best, leadSuit)) {
        winner = entry.key;
        best = entry.value;
      }
    }
    return winner;
  }

  bool _beats(String challenger, String current, String leadSuit) {
    final challengerSuit = _cardSuit(challenger);
    final currentSuit = _cardSuit(current);
    if (trump != null) {
      if (challengerSuit == trump && currentSuit != trump) {
        return true;
      }
      if (challengerSuit != trump && currentSuit == trump) {
        return false;
      }
    }
    if (challengerSuit == currentSuit) {
      return _rankPower(challenger, trumpSuit: challengerSuit == trump) >
          _rankPower(current, trumpSuit: currentSuit == trump);
    }
    return challengerSuit == leadSuit && currentSuit != leadSuit;
  }

  int _rankPower(String card, {bool trumpSuit = false}) {
    final rank = _cardRank(card);
    if (_isBaloot) {
      final order = trumpSuit && contract == 'hokm'
          ? <String>['7', '8', 'Q', 'K', '10', 'A', '9', 'J']
          : <String>['7', '8', '9', 'J', 'Q', 'K', '10', 'A'];
      return order.indexOf(rank);
    }
    return _standardRanks.indexOf(rank);
  }

  String _bestBotTrickCard(int seat) {
    final legal = _legalCardsFor(seat);
    if (legal.isEmpty) {
      return _hands[seat].first;
    }
    if (_trick.isEmpty) {
      legal.sort((a, b) => _rankPower(b).compareTo(_rankPower(a)));
      return legal.first;
    }
    final winning = legal.where((card) {
      final lead = _cardSuit(_trick.values.first);
      var bestCard = _trick.values.first;
      for (final value in _trick.values.skip(1)) {
        if (_beats(value, bestCard, lead)) {
          bestCard = value;
        }
      }
      return _beats(card, bestCard, lead);
    }).toList();
    if (winning.isNotEmpty) {
      winning.sort((a, b) => _rankPower(a, trumpSuit: _cardSuit(a) == trump)
          .compareTo(_rankPower(b, trumpSuit: _cardSuit(b) == trump)));
      return winning.first;
    }
    legal.sort((a, b) => _rankPower(a).compareTo(_rankPower(b)));
    return legal.first;
  }

  int _nextTrickSeat(int seat) {
    if (_isSyrianTarneeb) {
      return (seat + 3) % 4;
    }
    return (seat + 1) % 4;
  }

  void _scoreTrixTrick(int winner) {
    if (!_isTrix) {
      return;
    }
    var delta = 0;
    final cards = _trick.values.toList();
    if (contract == 'king_hearts' || contract == 'complex') {
      if (cards.contains('KH')) {
        delta -= 75;
      }
    }
    if (contract == 'girls' || contract == 'complex') {
      delta -= cards.where((card) => _cardRank(card) == 'Q').length * 25;
    }
    if (contract == 'diamonds' || contract == 'complex') {
      delta -= cards.where((card) => _cardSuit(card) == 'D').length * 10;
    }
    if (contract == 'tricks' || contract == 'complex') {
      delta -= 15;
    }
    if (contract == 'trix') {
      delta += 10;
    }
    _scores[winner] = (_scores[winner] ?? 0) + delta;
  }

  void _finishTrickRound() {
    gameOver = true;
    phase = 'finished';
    enginePhase = 'finished';
    if (_isTarneebVariant) {
      final teamA = (_tricksWon[0] ?? 0) + (_tricksWon[2] ?? 0);
      final teamB = (_tricksWon[1] ?? 0) + (_tricksWon[3] ?? 0);
      final bidderTeamA = (bidWinner ?? 0).isEven;
      final bidderTricks = bidderTeamA ? teamA : teamB;
      if (bidderTricks >= highestBid) {
        if (bidderTeamA) {
          _scores[0] = teamA;
          _scores[2] = teamA;
        } else {
          _scores[1] = teamB;
          _scores[3] = teamB;
        }
      } else {
        if (bidderTeamA) {
          _scores[0] = -highestBid;
          _scores[2] = -highestBid;
        } else {
          _scores[1] = -highestBid;
          _scores[3] = -highestBid;
        }
      }
      final humanTeamWon = teamA >= teamB;
      winnerKey = humanTeamWon ? 'user:0' : 'bot:1';
      _messages.add('النتيجة: فريقك $teamA مقابل $teamB.');
    } else if (_isTrix) {
      if (_isTrixPartner) {
        final teamA = (_scores[0] ?? 0) + (_scores[2] ?? 0);
        final teamB = (_scores[1] ?? 0) + (_scores[3] ?? 0);
        _scores[0] = teamA;
        _scores[2] = teamA;
        _scores[1] = teamB;
        _scores[3] = teamB;
        winnerKey = teamA >= teamB ? 'user:0' : 'bot:1';
        _messages.add('انتهى عقد الشراكة: فريقك $teamA مقابل $teamB.');
      } else {
        final best = _scores.entries.reduce((a, b) => a.value >= b.value ? a : b);
        winnerKey = best.key == 0 ? 'user:0' : 'bot:${best.key}';
        _messages.add('انتهى العقد. الأعلى نقاطاً هو ${_seatName(best.key)}.');
      }
    } else {
      final teamA = (_tricksWon[0] ?? 0) + (_tricksWon[2] ?? 0);
      final teamB = (_tricksWon[1] ?? 0) + (_tricksWon[3] ?? 0);
      winnerKey = teamA >= teamB ? 'user:0' : 'bot:1';
      _scores[0] = teamA;
      _scores[2] = teamA;
      _scores[1] = teamB;
      _scores[3] = teamB;
      _messages.add('انتهت الجولة: فريقك $teamA مقابل $teamB.');
    }
  }

  void _rummyAction(String action, Map<String, dynamic> payload) {
    if (action == 'organize') {
      _sortHand(_hands[0]);
      return;
    }
    if (phase == 'draw') {
      if (action == 'draw_deck') {
        _ensureDeckForRummy();
        _hands[0].add(_deck.removeLast());
      } else if (action == 'draw_discard' && _discard.isNotEmpty) {
        _hands[0].add(_discard.removeLast());
      } else {
        throw StateError('يجب السحب أولاً.');
      }
      phase = 'discard';
      enginePhase = 'discard';
      _sortHand(_hands[0]);
      return;
    }
    if (action == 'meld') {
      final cards = (payload['cards'] as List?)?.map((e) => e.toString()).toList() ?? <String>[];
      if (!_isValidMeld(cards) || !_containsAll(_hands[0], cards)) {
        throw StateError('المجموعة المختارة غير قانونية.');
      }
      for (final card in cards) {
        _hands[0].remove(card);
      }
      _melds.add(cards);
      _messages.add('$humanName نزّل مجموعة من ${cards.length} أوراق.');
      if (_hands[0].isEmpty) {
        _finishRummy(0);
      }
      return;
    }
    if (action != 'discard') {
      throw StateError('اختر ورقة للرمي.');
    }
    final card = payload['card']?.toString() ?? '';
    if (!_hands[0].remove(card)) {
      throw StateError('الورقة غير موجودة في يدك.');
    }
    _discard.add(card);
    _messages.add('$humanName رمى ${_prettyCard(card)}');
    if (_hands[0].isEmpty) {
      _finishRummy(0);
      return;
    }
    currentSeat = 1;
    phase = 'draw';
    enginePhase = 'draw';
    _autoBotsUntilHuman();
  }

  void _runRummyBot(int seat) {
    _ensureDeckForRummy();
    if (_deck.isNotEmpty) {
      _hands[seat].add(_deck.removeLast());
    }
    final suggestions = _meldSuggestions(_hands[seat]);
    if (suggestions.isNotEmpty && _random.nextDouble() > .2) {
      final meld = suggestions.first;
      for (final card in meld) {
        _hands[seat].remove(card);
      }
      _melds.add(meld);
      _messages.add('${_seatName(seat)} نزّل مجموعة.');
    }
    if (_hands[seat].isEmpty) {
      _finishRummy(seat);
      return;
    }
    final discard = _highestCard(_hands[seat]);
    _hands[seat].remove(discard);
    _discard.add(discard);
    _messages.add('${_seatName(seat)} رمى ${_prettyCard(discard)}');
    if (_hands[seat].isEmpty) {
      _finishRummy(seat);
      return;
    }
    currentSeat = (seat + 1) % 4;
    phase = 'draw';
    enginePhase = 'draw';
  }

  void _finishRummy(int winner) {
    gameOver = true;
    phase = 'finished';
    enginePhase = 'finished';
    winnerKey = winner == 0 ? 'user:0' : 'bot:$winner';
    for (var seat = 0; seat < 4; seat++) {
      _scores[seat] = -_hands[seat].fold<int>(0, (sum, card) => sum + _cardPoints(card));
    }
    _scores[winner] = _scores.values.map((e) => e.abs()).fold<int>(0, (a, b) => a + b);
    if (_isHandPartner) {
      final partner = (winner + 2) % 4;
      _scores[partner] = _scores[winner];
      winnerKey = winner.isEven ? 'user:0' : 'bot:1';
      _messages.add('فريق ${_seatName(winner)} أنهى اليد وفاز بالجولة.');
    } else {
      _messages.add('${_seatName(winner)} أنهى يده وفاز بالجولة.');
    }
  }

  void _ensureDeckForRummy() {
    if (_deck.isNotEmpty) {
      return;
    }
    if (_discard.length <= 1) {
      return;
    }
    final top = _discard.removeLast();
    _deck.addAll(_discard);
    _deck.shuffle(_random);
    _discard
      ..clear()
      ..add(top);
  }

  List<List<String>> _meldSuggestions(List<String> hand) {
    final suggestions = <List<String>>[];
    final byRank = <String, List<String>>{};
    for (final card in hand.where((card) => !card.startsWith('JOKER'))) {
      byRank.putIfAbsent(_cardRank(card), () => <String>[]).add(card);
    }
    for (final cards in byRank.values) {
      if (cards.length >= 3) {
        suggestions.add(cards.take(4).toList());
      }
    }
    for (final suit in _suits) {
      final cards = hand.where((card) => _cardSuit(card) == suit).toList()
        ..sort((a, b) => _standardRanks.indexOf(_cardRank(a)).compareTo(_standardRanks.indexOf(_cardRank(b))));
      var run = <String>[];
      var previous = -2;
      for (final card in cards) {
        final value = _standardRanks.indexOf(_cardRank(card));
        if (value == previous + 1) {
          run.add(card);
        } else if (value != previous) {
          if (run.length >= 3) {
            suggestions.add(List<String>.from(run));
          }
          run = <String>[card];
        }
        previous = value;
      }
      if (run.length >= 3) {
        suggestions.add(List<String>.from(run));
      }
    }
    return suggestions;
  }

  bool _isValidMeld(List<String> cards) {
    if (cards.length < 3) {
      return false;
    }
    final natural = cards.where((card) => !card.startsWith('JOKER')).toList();
    if (natural.length < 2) {
      return false;
    }
    final sameRank = natural.every((card) => _cardRank(card) == _cardRank(natural.first));
    if (sameRank) {
      return true;
    }
    final sameSuit = natural.every((card) => _cardSuit(card) == _cardSuit(natural.first));
    if (!sameSuit) {
      return false;
    }
    final values = natural.map((card) => _standardRanks.indexOf(_cardRank(card))).toList()..sort();
    for (var i = 1; i < values.length; i++) {
      if (values[i] - values[i - 1] > 1 + cards.where((card) => card.startsWith('JOKER')).length) {
        return false;
      }
    }
    return true;
  }

  void _basraAction(String action, Map<String, dynamic> payload) {
    if (action != 'play_card') {
      throw StateError('اختر ورقة للعب.');
    }
    final card = payload['card']?.toString() ?? '';
    _playBasraCard(0, card);
    _autoBotsUntilHuman();
  }

  void _playBasraCard(int seat, String card) {
    if (currentSeat != seat || !_hands[seat].remove(card)) {
      throw StateError('الحركة غير قانونية.');
    }
    final captured = _basraCapture(card);
    if (captured.isEmpty) {
      _table.add(card);
      _messages.add('${_seatName(seat)} وضع ${_prettyCard(card)}');
    } else {
      final clearsTable = captured.length == _table.length;
      for (final item in captured) {
        _table.remove(item);
      }
      _captured[seat]!.addAll(captured);
      _captured[seat]!.add(card);
      _lastCaptureSeat = seat;
      if (clearsTable && _cardRank(card) != 'J') {
        _basras[seat] = (_basras[seat] ?? 0) + (card == '7D' ? 2 : 1);
      }
      _messages.add('${_seatName(seat)} التقط ${captured.length} ورقة${clearsTable ? ' وسجّل باصرة' : ''}.');
    }
    currentSeat = (seat + 1) % 2;
    if (_hands[0].isEmpty && _hands[1].isEmpty) {
      if (_deck.isNotEmpty) {
        _dealBasraHands();
      } else {
        _finishBasra();
      }
    }
  }

  List<String> _basraCapture(String card) {
    if (_table.isEmpty) {
      return <String>[];
    }
    if (_cardRank(card) == 'J' || card == '7D') {
      return List<String>.from(_table);
    }
    final same = _table.where((item) => _cardRank(item) == _cardRank(card)).toList();
    if (same.isNotEmpty) {
      return same;
    }
    final target = _numericValue(card);
    if (target <= 10) {
      final subset = _findSubset(_table.where((item) => _numericValue(item) <= 10).toList(), target);
      if (subset.isNotEmpty) {
        return subset;
      }
    }
    return <String>[];
  }

  List<String> _findSubset(List<String> cards, int target) {
    final limit = min(cards.length, 12);
    for (var mask = 1; mask < (1 << limit); mask++) {
      var sum = 0;
      final picked = <String>[];
      for (var i = 0; i < limit; i++) {
        if ((mask & (1 << i)) != 0) {
          sum += _numericValue(cards[i]);
          picked.add(cards[i]);
        }
      }
      if (sum == target) {
        return picked;
      }
    }
    return <String>[];
  }

  String _bestBasraCard(int seat) {
    for (final card in _hands[seat]) {
      if (_basraCapture(card).isNotEmpty) {
        return card;
      }
    }
    final cards = List<String>.from(_hands[seat]);
    cards.sort((a, b) => _numericValue(a).compareTo(_numericValue(b)));
    return cards.first;
  }

  void _finishBasra() {
    if (_table.isNotEmpty) {
      _captured[_lastCaptureSeat]!.addAll(_table);
      _table.clear();
    }
    for (var seat = 0; seat < 2; seat++) {
      final cards = _captured[seat]!;
      var score = _basras[seat] ?? 0;
      score += cards.where((card) => _cardRank(card) == 'A').length;
      score += cards.contains('7D') ? 1 : 0;
      score += cards.contains('10D') ? 2 : 0;
      _scores[seat] = score;
    }
    if (_captured[0]!.length > _captured[1]!.length) {
      _scores[0] = (_scores[0] ?? 0) + 3;
    } else if (_captured[1]!.length > _captured[0]!.length) {
      _scores[1] = (_scores[1] ?? 0) + 3;
    }
    final diamonds0 = _captured[0]!.where((card) => _cardSuit(card) == 'D').length;
    final diamonds1 = _captured[1]!.where((card) => _cardSuit(card) == 'D').length;
    if (diamonds0 > diamonds1) {
      _scores[0] = (_scores[0] ?? 0) + 1;
    } else if (diamonds1 > diamonds0) {
      _scores[1] = (_scores[1] ?? 0) + 1;
    }
    gameOver = true;
    phase = 'finished';
    enginePhase = 'finished';
    winnerKey = (_scores[0] ?? 0) >= (_scores[1] ?? 0) ? 'user:0' : 'bot:1';
    _messages.add('انتهت الجولة: أنت ${_scores[0]} — الخصم ${_scores[1]}.');
  }

  void _sortHand(List<String> hand) {
    hand.sort((a, b) {
      final suit = _cardSuit(a).compareTo(_cardSuit(b));
      if (suit != 0) {
        return suit;
      }
      return _cardPoints(a).compareTo(_cardPoints(b));
    });
  }

  bool _containsAll(List<String> source, List<String> values) {
    final copy = List<String>.from(source);
    for (final value in values) {
      if (!copy.remove(value)) {
        return false;
      }
    }
    return true;
  }

  int _handStrength(List<String> hand) => hand.fold<int>(0, (sum, card) => sum + _cardPoints(card));

  int _strongestSeat() {
    var bestSeat = 0;
    var best = -1;
    for (var seat = 0; seat < 4; seat++) {
      final value = _handStrength(_hands[seat]);
      if (value > best) {
        best = value;
        bestSeat = seat;
      }
    }
    return bestSeat;
  }

  String _bestSuit(List<String> hand) {
    final totals = <String, int>{for (final suit in _suits) suit: 0};
    for (final card in hand) {
      if (!card.startsWith('JOKER')) {
        totals[_cardSuit(card)] = (totals[_cardSuit(card)] ?? 0) + _cardPoints(card);
      }
    }
    return totals.entries.reduce((a, b) => a.value >= b.value ? a : b).key;
  }

  String _highestCard(List<String> hand) {
    final copy = List<String>.from(hand);
    copy.sort((a, b) => _cardPoints(b).compareTo(_cardPoints(a)));
    return copy.first;
  }

  int _cardPoints(String card) {
    if (card.startsWith('JOKER')) {
      return 25;
    }
    final rank = _cardRank(card);
    if (rank == 'A') return 15;
    if (rank == 'K' || rank == 'Q' || rank == 'J' || rank == '10') return 10;
    return int.tryParse(rank) ?? 0;
  }

  int _numericValue(String card) {
    final rank = _cardRank(card);
    if (rank == 'A') return 1;
    if (rank == 'J') return 11;
    if (rank == 'Q') return 12;
    if (rank == 'K') return 13;
    return int.tryParse(rank) ?? 0;
  }

  String _cardRank(String card) {
    if (card.startsWith('JOKER')) {
      return 'JOKER';
    }
    return card.substring(0, card.length - 1);
  }

  String _cardSuit(String card) {
    if (card.startsWith('JOKER')) {
      return 'X';
    }
    return card.substring(card.length - 1);
  }

  String _seatName(int seat) => seat == 0 ? humanName : _botNames[seat - 1];

  String _suitSymbol(String suit) => switch (suit) {
        'C' => '♣',
        'D' => '♦',
        'S' => '♠',
        'H' => '♥',
        _ => suit,
      };

  String _prettyCard(String card) {
    if (card.startsWith('JOKER')) {
      return 'جوكر';
    }
    return '${_cardRank(card)}${_suitSymbol(_cardSuit(card))}';
  }

  String _contractLabel(String value) => switch (value) {
        'king_hearts' => 'شيخ الكبة',
        'girls' => 'البنات',
        'diamonds' => 'الديناري',
        'tricks' => 'اللطوش',
        'trix' => 'تركس',
        'complex' => 'كمبلكس',
        'sun' => 'صن',
        'hokm' => 'حكم',
        _ => value,
      };
}
