import 'dart:math' as math;

enum TarneebPhase { bidding, chooseTrump, playing, roundEnd, gameOver }

class TarneebCard {
  final String rank;
  final String suit;

  const TarneebCard(this.rank, this.suit);

  String get code => '${rank}_$suit';
  String get symbol => switch (suit) {
        'C' => '♣',
        'D' => '♦',
        'S' => '♠',
        'H' => '♥',
        _ => suit,
      };
  String get label => '$rank$symbol';
  bool get isRed => suit == 'D' || suit == 'H';

  int get power => switch (rank) {
        'A' => 14,
        'K' => 13,
        'Q' => 12,
        'J' => 11,
        '10' => 10,
        '9' => 9,
        '8' => 8,
        '7' => 7,
        '6' => 6,
        '5' => 5,
        '4' => 4,
        '3' => 3,
        _ => 2,
      };

  static TarneebCard fromCode(String code) {
    final parts = code.split('_');
    if (parts.length == 2) return TarneebCard(parts[0], parts[1]);
    final suit = code.characters.last;
    final rank = code.substring(0, code.length - suit.length);
    return TarneebCard(rank, switch (suit) {'♣' => 'C', '♦' => 'D', '♠' => 'S', '♥' => 'H', _ => suit});
  }
}

class TarneebPlay {
  final int seat;
  final TarneebCard card;
  const TarneebPlay(this.seat, this.card);
}

class TarneebBid {
  final int seat;
  final int? amount;
  const TarneebBid(this.seat, this.amount);
}

class TarneebLocalEngine {
  static const ranks = ['A', 'K', 'Q', 'J', '10', '9', '8', '7', '6', '5', '4', '3', '2'];
  static const suits = ['C', 'D', 'S', 'H'];

  final int targetScore;
  final math.Random _random;
  final List<String> playerNames;

  TarneebPhase phase = TarneebPhase.bidding;
  int dealerSeat = 3;
  int currentSeat = 0;
  int round = 0;
  int? highestBid;
  int? bidWinnerSeat;
  String? trump;
  final Set<int> passedSeats = <int>{};
  final List<TarneebBid> bids = <TarneebBid>[];
  final List<List<TarneebCard>> hands = List.generate(4, (_) => <TarneebCard>[]);
  final List<TarneebPlay> trick = <TarneebPlay>[];
  final List<List<TarneebPlay>> completedTricks = <List<TarneebPlay>>[];
  final List<int> scores = [0, 0];
  final List<int> roundTricks = [0, 0];
  final List<String> messages = <String>[];
  int? winnerTeam;

  TarneebLocalEngine({
    this.targetScore = 41,
    math.Random? random,
    List<String>? playerNames,
  })  : _random = random ?? math.Random.secure(),
        playerNames = playerNames ?? const ['أحمد', 'عاصم', 'ليلى', 'جميل'] {
    startNextRound();
  }

  int teamOf(int seat) => seat.isEven ? 0 : 1;
  bool get isHumanTurn => currentSeat == 0 && phase != TarneebPhase.gameOver;
  List<TarneebCard> get humanHand => List.unmodifiable(hands[0]);

  void startNextRound() {
    round += 1;
    dealerSeat = (dealerSeat + 1) % 4;
    currentSeat = (dealerSeat + 1) % 4;
    phase = TarneebPhase.bidding;
    highestBid = null;
    bidWinnerSeat = null;
    trump = null;
    passedSeats.clear();
    bids.clear();
    trick.clear();
    completedTricks.clear();
    roundTricks[0] = 0;
    roundTricks[1] = 0;
    for (final hand in hands) hand.clear();

    final deck = <TarneebCard>[
      for (final suit in suits)
        for (final rank in ranks) TarneebCard(rank, suit),
    ]..shuffle(_random);

    for (var i = 0; i < 52; i++) {
      hands[i % 4].add(deck[i]);
    }
    for (final hand in hands) _sortHand(hand);
    messages.add('بدأت الجولة $round: تم توزيع 13 ورقة فريدة لكل لاعب.');
  }

  bool canBid(int seat, int? amount) {
    if (phase != TarneebPhase.bidding || seat != currentSeat || passedSeats.contains(seat)) return false;
    if (amount == null) return true;
    return amount >= 7 && amount <= 13 && amount > (highestBid ?? 6);
  }

  void bid(int seat, int? amount) {
    if (!canBid(seat, amount)) throw StateError('طلب غير قانوني');
    bids.add(TarneebBid(seat, amount));
    if (amount == null) {
      passedSeats.add(seat);
      messages.add('${playerNames[seat]}: سكون');
    } else {
      highestBid = amount;
      bidWinnerSeat = seat;
      messages.add('${playerNames[seat]} طلب $amount');
    }

    if (highestBid == null && passedSeats.length == 4) {
      messages.add('مرّر الجميع؛ أعيد توزيع الورق دون نقاط.');
      dealerSeat = (dealerSeat + 3) % 4;
      startNextRound();
      return;
    }

    final active = List.generate(4, (i) => i).where((i) => !passedSeats.contains(i)).toList();
    if (highestBid != null && active.length == 1 && active.first == bidWinnerSeat) {
      phase = TarneebPhase.chooseTrump;
      currentSeat = bidWinnerSeat!;
      messages.add('${playerNames[currentSeat]} يختار نوع الطرنيب.');
      return;
    }

    currentSeat = _nextBidSeat(currentSeat);
  }

  void chooseTrump(int seat, String suit) {
    if (phase != TarneebPhase.chooseTrump || seat != bidWinnerSeat || !suits.contains(suit)) {
      throw StateError('اختيار طرنيب غير قانوني');
    }
    trump = suit;
    phase = TarneebPhase.playing;
    currentSeat = seat;
    messages.add('${playerNames[seat]} اختار ${suitName(suit)} طرنيباً.');
  }

  List<TarneebCard> legalCards(int seat) {
    if (phase != TarneebPhase.playing || seat != currentSeat) return const [];
    final hand = hands[seat];
    if (trick.isEmpty) return List.unmodifiable(hand);
    final leadSuit = trick.first.card.suit;
    final following = hand.where((card) => card.suit == leadSuit).toList();
    return List.unmodifiable(following.isNotEmpty ? following : hand);
  }

  void playCard(int seat, TarneebCard card) {
    if (phase != TarneebPhase.playing || seat != currentSeat) throw StateError('ليس دور هذا اللاعب');
    final handIndex = hands[seat].indexWhere((c) => c.code == card.code);
    if (handIndex < 0) throw StateError('الورقة ليست في يد اللاعب');
    if (!legalCards(seat).any((c) => c.code == card.code)) throw StateError('يجب اتباع نوع الورقة المتصدرة');

    final played = hands[seat].removeAt(handIndex);
    trick.add(TarneebPlay(seat, played));
    messages.add('${playerNames[seat]} رمى ${played.label}');

    if (trick.length < 4) {
      currentSeat = (currentSeat + 1) % 4;
      return;
    }

    final winner = _trickWinner(trick);
    final captured = List<TarneebPlay>.from(trick);
    completedTricks.add(captured);
    trick.clear();
    roundTricks[teamOf(winner)] += 1;
    currentSeat = winner;
    messages.add('${playerNames[winner]} فاز باللّمّة.');

    if (hands.every((hand) => hand.isEmpty)) _finishRound();
  }

  void autoActCurrentSeat() {
    if (phase == TarneebPhase.bidding) {
      final amount = _botBid(currentSeat);
      bid(currentSeat, amount);
      return;
    }
    if (phase == TarneebPhase.chooseTrump) {
      chooseTrump(currentSeat, _bestTrump(currentSeat));
      return;
    }
    if (phase == TarneebPhase.playing) {
      playCard(currentSeat, _botCard(currentSeat));
    }
  }

  void autoPlayBots({int guard = 60}) {
    var steps = 0;
    while (currentSeat != 0 && phase != TarneebPhase.roundEnd && phase != TarneebPhase.gameOver && steps < guard) {
      autoActCurrentSeat();
      steps += 1;
    }
  }

  int? _botBid(int seat) {
    final hand = hands[seat];
    final suitScores = <String, double>{for (final suit in suits) suit: 0};
    for (final card in hand) {
      final high = switch (card.rank) {'A' => 2.0, 'K' => 1.4, 'Q' => .9, 'J' => .5, _ => 0.0};
      suitScores[card.suit] = suitScores[card.suit]! + high + .18;
    }
    final best = suitScores.values.reduce(math.max);
    var estimate = 6 + (best / 2.1).floor();
    estimate += hand.where((c) => c.rank == 'A').length ~/ 2;
    estimate = estimate.clamp(7, 10).toInt();
    final minimum = (highestBid ?? 6) + 1;
    if (estimate < minimum || minimum > 13) return null;
    if (_random.nextDouble() < .12 && estimate < 10) return null;
    return math.min(estimate, 13).toInt();
  }

  String _bestTrump(int seat) {
    final values = <String, double>{for (final suit in suits) suit: 0};
    for (final card in hands[seat]) {
      values[card.suit] = values[card.suit]! + 1 + (card.power >= 11 ? (card.power - 10) * .75 : 0);
    }
    return values.entries.reduce((a, b) => a.value >= b.value ? a : b).key;
  }

  TarneebCard _botCard(int seat) {
    final legal = [...legalCards(seat)]..sort((a, b) => a.power.compareTo(b.power));
    if (trick.isEmpty) {
      final aces = legal.where((c) => c.rank == 'A').toList();
      return aces.isNotEmpty && _random.nextDouble() < .65 ? aces.first : legal.first;
    }

    final currentWinner = _trickWinner(trick);
    final partnerWinning = teamOf(currentWinner) == teamOf(seat);
    if (partnerWinning) return legal.first;

    final winning = legal.where((candidate) {
      final test = [...trick, TarneebPlay(seat, candidate)];
      return _trickWinner(test) == seat;
    }).toList();
    if (winning.isNotEmpty) return winning.first;
    return legal.first;
  }

  int _trickWinner(List<TarneebPlay> cards) {
    final leadSuit = cards.first.card.suit;
    TarneebPlay winner = cards.first;
    for (final play in cards.skip(1)) {
      final candidateTrump = play.card.suit == trump;
      final winnerTrump = winner.card.suit == trump;
      if (candidateTrump && !winnerTrump) {
        winner = play;
      } else if (candidateTrump == winnerTrump && play.card.suit == winner.card.suit && play.card.power > winner.card.power) {
        winner = play;
      } else if (!winnerTrump && play.card.suit == leadSuit && winner.card.suit != leadSuit) {
        winner = play;
      }
    }
    return winner.seat;
  }

  void _finishRound() {
    final biddingSeat = bidWinnerSeat!;
    final biddingTeam = teamOf(biddingSeat);
    final otherTeam = 1 - biddingTeam;
    final contract = highestBid!;
    if (roundTricks[biddingTeam] >= contract) {
      scores[biddingTeam] += roundTricks[biddingTeam];
      scores[otherTeam] += roundTricks[otherTeam];
      messages.add('نجح فريق ${teamName(biddingTeam)} بالطلب $contract.');
    } else {
      scores[biddingTeam] -= contract;
      scores[otherTeam] += roundTricks[otherTeam];
      messages.add('فشل فريق ${teamName(biddingTeam)} بالطلب $contract وخُصمت قيمة الطلب.');
    }

    if (scores[0] >= targetScore || scores[1] >= targetScore) {
      winnerTeam = scores[0] == scores[1] ? (roundTricks[0] >= roundTricks[1] ? 0 : 1) : (scores[0] > scores[1] ? 0 : 1);
      phase = TarneebPhase.gameOver;
      messages.add('انتهت المباراة: فاز فريق ${teamName(winnerTeam!)}.');
    } else {
      phase = TarneebPhase.roundEnd;
      messages.add('انتهت الجولة. النتيجة ${scores[0]} - ${scores[1]}.');
    }
  }

  int _nextBidSeat(int from) {
    var seat = from;
    for (var i = 0; i < 4; i++) {
      seat = (seat + 1) % 4;
      if (!passedSeats.contains(seat)) return seat;
    }
    return bidWinnerSeat ?? 0;
  }

  void _sortHand(List<TarneebCard> hand) {
    hand.sort((a, b) {
      final suitCompare = suits.indexOf(a.suit).compareTo(suits.indexOf(b.suit));
      if (suitCompare != 0) return suitCompare;
      return b.power.compareTo(a.power);
    });
  }

  String suitName(String suit) => switch (suit) {'C' => 'الشجرة', 'D' => 'الديناري', 'S' => 'البستوني', 'H' => 'الكبة', _ => suit};
  String teamName(int team) => team == 0 ? 'نحن' : 'هم';
}

extension on String {
  Iterable<String> get characters sync* {
    for (var i = 0; i < length; i++) yield this[i];
  }
}
