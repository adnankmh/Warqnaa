/// Seat-neutral post-shuffle balancing for local/offline card sessions.
///
/// Cards are shuffled before this method is called. Only bounded swaps are
/// allowed, so the full deck, uniqueness and hand sizes remain unchanged.
class FairDealBalancer {
  static const Map<String, int> _rankValues = <String, int>{
    '2': 2, '3': 3, '4': 4, '5': 5, '6': 6, '7': 7,
    '8': 8, '9': 9, '10': 10, 'J': 11, 'Q': 12, 'K': 13, 'A': 14,
    'JOKER': 17, 'JOKER1': 17, 'JOKER2': 17, 'JOKER_R': 17, 'JOKER_B': 17,
  };

  static List<List<String>> balanceCodes(List<List<String>> source, {String mode = 'trick'}) {
    final hands = source.map((hand) => List<String>.from(hand)).toList();
    if (hands.length < 2 || hands.any((hand) => hand.isEmpty)) return hands;
    final handSize = hands.map((hand) => hand.length).reduce((a, b) => a < b ? a : b);
    final premiumQuota = handSize >= 13 ? 2 : handSize >= 7 ? 1 : 0;
    final maxSpread = mode == 'rummy'
        ? (handSize * 2.0).round().clamp(18, 80)
        : mode == 'baloot'
            ? 16
            : (handSize * 1.75).round().clamp(16, 70);

    for (var step = 0; step < 96; step++) {
      final metrics = hands.map((hand) => _metrics(hand, mode)).toList();
      var weak = 0;
      var strong = 0;
      for (var i = 1; i < hands.length; i++) {
        if (metrics[i].score < metrics[weak].score) weak = i;
        if (metrics[i].score > metrics[strong].score) strong = i;
      }
      final spread = metrics[strong].score - metrics[weak].score;
      final needsPremium = metrics[weak].premium < premiumQuota;
      if (!needsPremium && spread <= maxSpread) break;

      final swap = _bestSwap(
        hands[strong],
        hands[weak],
        metrics[strong],
        metrics[weak],
        premiumQuota,
        mode,
      );
      if (swap == null) break;
      final value = hands[strong][swap.$1];
      hands[strong][swap.$1] = hands[weak][swap.$2];
      hands[weak][swap.$2] = value;
    }
    return hands;
  }

  static (int, int)? _bestSwap(
    List<String> donor,
    List<String> receiver,
    _HandMetrics donorMetrics,
    _HandMetrics receiverMetrics,
    int quota,
    String mode,
  ) {
    final beforeGap = (donorMetrics.score - receiverMetrics.score).abs();
    (int, int)? best;
    var bestGain = 0;
    for (var di = 0; di < donor.length; di++) {
      if (_isPremium(donor[di]) && donorMetrics.premium <= quota) continue;
      for (var ri = 0; ri < receiver.length; ri++) {
        if (_cardScore(donor[di], mode) <= _cardScore(receiver[ri], mode)) continue;
        final donorCopy = List<String>.from(donor)..[di] = receiver[ri];
        final receiverCopy = List<String>.from(receiver)..[ri] = donor[di];
        final nextDonor = _metrics(donorCopy, mode);
        final nextReceiver = _metrics(receiverCopy, mode);
        if (nextDonor.premium < quota) continue;
        final afterGap = (nextDonor.score - nextReceiver.score).abs();
        final premiumGain = (nextReceiver.premium - receiverMetrics.premium) * 12;
        final gain = beforeGap - afterGap + premiumGain;
        if (gain > bestGain) {
          bestGain = gain;
          best = (di, ri);
        }
      }
    }
    return best;
  }

  static _HandMetrics _metrics(List<String> hand, String mode) {
    var score = 0;
    var premium = 0;
    final rankCounts = <String, int>{};
    final suitRanks = <String, List<int>>{};
    for (final card in hand) {
      score += _cardScore(card, mode);
      if (_isPremium(card)) premium++;
      final parts = _parts(card);
      rankCounts[parts.$1] = (rankCounts[parts.$1] ?? 0) + 1;
      if (parts.$2.isNotEmpty) suitRanks.putIfAbsent(parts.$2, () => <int>[]).add(_rankValue(parts.$1));
    }
    if (mode == 'rummy') {
      for (final count in rankCounts.values) {
        if (count >= 2) score += (count - 1) * 4;
      }
      for (final values in suitRanks.values) {
        final ranks = values.toSet().toList()..sort();
        for (var i = 1; i < ranks.length; i++) {
          if (ranks[i] - ranks[i - 1] == 1) score += 3;
        }
      }
    }
    return _HandMetrics(score, premium);
  }

  static int _cardScore(String card, String mode) {
    final rank = _parts(card).$1;
    final value = _rankValue(rank);
    if (rank.startsWith('JOKER')) return 22;
    if (mode == 'baloot') {
      return switch (rank) {
        'J' => 18,
        '9' => 17,
        'A' => 16,
        '10' => 15,
        'K' => 13,
        'Q' => 12,
        _ => value,
      };
    }
    return value + (value >= 11 ? 2 : 0);
  }

  static bool _isPremium(String card) {
    final rank = _parts(card).$1;
    return const <String>{'A', 'K', 'Q', 'J', 'JOKER', 'JOKER1', 'JOKER2', 'JOKER_R', 'JOKER_B'}.contains(rank);
  }

  static (String, String) _parts(String card) {
    final normalized = card.trim().toUpperCase();
    if (normalized.startsWith('JOKER')) return (normalized, 'JOKER');
    final split = normalized.split(RegExp(r'[_\-\s]+'));
    if (split.length >= 2) return (split.first, split[1]);
    final match = RegExp(r'^(10|[2-9AJQK])([CDSH])$').firstMatch(normalized);
    if (match != null) return (match.group(1)!, match.group(2)!);
    return (normalized, '');
  }

  static int _rankValue(String rank) => _rankValues[rank] ?? 0;
}

class _HandMetrics {
  final int score;
  final int premium;
  const _HandMetrics(this.score, this.premium);
}
