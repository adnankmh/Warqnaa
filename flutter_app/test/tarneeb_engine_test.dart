import 'dart:math' as math;

import 'package:flutter_test/flutter_test.dart';
import 'package:warqna_mobile/engines/tarneeb_engine.dart';

void main() {
  group('TarneebLocalEngine', () {
    test('deals exactly 13 unique cards to each of four players', () {
      final engine = TarneebLocalEngine(random: math.Random(142));

      expect(engine.hands, hasLength(4));
      for (final hand in engine.hands) {
        expect(hand, hasLength(13));
      }

      final codes = engine.hands.expand((hand) => hand).map((card) => card.code).toSet();
      expect(codes, hasLength(52));
    });

    test('accepts bids only from 7 through 13 and above current bid', () {
      final engine = TarneebLocalEngine(random: math.Random(142));
      final seat = engine.currentSeat;

      expect(engine.canBid(seat, 6), isFalse);
      expect(engine.canBid(seat, 7), isTrue);
      expect(engine.canBid(seat, 13), isTrue);
      expect(engine.canBid(seat, 14), isFalse);

      engine.bid(seat, 8);
      final next = engine.currentSeat;
      expect(engine.canBid(next, 8), isFalse);
      expect(engine.canBid(next, 9), isTrue);
    });

    test('enforces following the lead suit when the player has it', () {
      final engine = TarneebLocalEngine(random: math.Random(142));
      engine.phase = TarneebPhase.playing;
      engine.currentSeat = 0;
      engine.trump = 'H';
      engine.trick
        ..clear()
        ..add(const TarneebPlay(3, TarneebCard('10', 'C')));
      engine.hands[0]
        ..clear()
        ..addAll(const [
          TarneebCard('A', 'C'),
          TarneebCard('K', 'H'),
          TarneebCard('2', 'S'),
        ]);

      final legal = engine.legalCards(0);
      expect(legal.map((card) => card.code), ['A_C']);
      expect(
        () => engine.playCard(0, const TarneebCard('K', 'H')),
        throwsStateError,
      );
    });

    test('allows any card when the player cannot follow the lead suit', () {
      final engine = TarneebLocalEngine(random: math.Random(142));
      engine.phase = TarneebPhase.playing;
      engine.currentSeat = 0;
      engine.trump = 'H';
      engine.trick
        ..clear()
        ..add(const TarneebPlay(3, TarneebCard('10', 'C')));
      engine.hands[0]
        ..clear()
        ..addAll(const [
          TarneebCard('K', 'H'),
          TarneebCard('2', 'S'),
        ]);

      expect(engine.legalCards(0), hasLength(2));
    });
  });
}
