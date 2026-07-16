import 'package:flutter_test/flutter_test.dart';
import 'package:warqna_mobile/engines/local_game_engine.dart';

void main() {
  test('Syrian Tarneeb deals 13 cards and starts bidding', () {
    final game = LocalGameSession(gameId: 'syrian_tarneeb', humanName: 'Adnan', seed: 7);
    final room = game.room();
    final state = Map<String, dynamic>.from(room['state'] as Map);
    expect((state['hand'] as List).length, 13);
    expect(state['phase'], 'bidding');
    expect((state['available_actions'] as List).isNotEmpty, isTrue);
  });

  test('Hand family starts with 15 cards, discards once, then draws normally', () {
    for (final id in <String>['hand', 'hand_partner', 'saudi_hand']) {
      final game = LocalGameSession(gameId: id, humanName: 'Adnan', seed: 11);
      final initial = Map<String, dynamic>.from(game.room()['state'] as Map);
      expect((initial['hand'] as List).length, 15, reason: id);
      expect(initial['phase'], 'discard', reason: id);
      final initialActions = initial['available_actions'] as List;
      expect(
        initialActions.any((action) => action is Map && action['type'] == 'discard'),
        isTrue,
        reason: id,
      );

      final afterStarterDiscard = Map<String, dynamic>.from(game.timeout()['state'] as Map);
      expect((afterStarterDiscard['hand'] as List).length, 14, reason: id);
      expect(afterStarterDiscard['phase'], 'draw', reason: id);
    }
  });

  test('Banakil starts with 19 cards, discards once, then returns with 18', () {
    final game = LocalGameSession(gameId: 'banakil', humanName: 'Adnan', seed: 12);
    final initial = Map<String, dynamic>.from(game.room()['state'] as Map);
    expect((initial['hand'] as List).length, 19);
    expect(initial['phase'], 'discard');

    final afterStarterDiscard = Map<String, dynamic>.from(game.timeout()['state'] as Map);
    expect((afterStarterDiscard['hand'] as List).length, 18);
    expect(afterStarterDiscard['phase'], 'draw');
  });

  test('Basra deals four cards to the player and four to table', () {
    final game = LocalGameSession(gameId: 'basra', humanName: 'Adnan', seed: 15);
    final state = Map<String, dynamic>.from(game.room()['state'] as Map);
    expect((state['hand'] as List).length, 4);
    expect((state['table'] as List).length, 4);
  });

  test('Trix starts with contract selection', () {
    final game = LocalGameSession(gameId: 'trix', humanName: 'Adnan', seed: 19);
    final state = Map<String, dynamic>.from(game.room()['state'] as Map);
    expect((state['hand'] as List).length, 13);
    expect(state['phase'], 'choose_contract');
  });

  test('Baloot deals eight cards and offers sun or hokm', () {
    final game = LocalGameSession(gameId: 'baloot', humanName: 'Adnan', seed: 23);
    final state = Map<String, dynamic>.from(game.room()['state'] as Map);
    expect((state['hand'] as List).length, 8);
    final actions = state['available_actions'] as List;
    expect(actions.length, 2);
  });

  test('all curated non-Tarneeb engines initialize and accept a safe timeout', () {
    const ids = <String>[
      'syrian_tarneeb',
      'tarneeb_400',
      'trix',
      'trix_partner',
      'trix_complex',
      'hand',
      'hand_partner',
      'saudi_hand',
      'banakil',
      'baloot',
      'basra',
    ];
    for (var index = 0; index < ids.length; index++) {
      final game = LocalGameSession(gameId: ids[index], humanName: 'Tester', seed: 100 + index);
      final before = Map<String, dynamic>.from(game.room()['state'] as Map);
      expect(before['hand'], isA<List>());
      expect((before['hand'] as List).isNotEmpty, isTrue, reason: ids[index]);
      final after = Map<String, dynamic>.from(game.timeout()['state'] as Map);
      expect(after['phase'], isNotNull, reason: ids[index]);
      expect(after['messages'], isA<List>(), reason: ids[index]);
    }
  });
  test('Tarneeb 400 uses Hearts as the fixed trump after bidding', () {
    final game = LocalGameSession(gameId: 'tarneeb_400', humanName: 'Adnan', seed: 31);
    final state = Map<String, dynamic>.from(game.timeout()['state'] as Map);
    expect(state['trump'], 'H');
    expect(state['phase'], 'playing');
  });


  test('all AI difficulty levels initialize safely for every curated engine', () {
    const ids = <String>[
      'syrian_tarneeb', 'tarneeb_400', 'trix', 'trix_partner', 'trix_complex',
      'hand', 'hand_partner', 'saudi_hand', 'banakil', 'baloot', 'basra',
    ];
    const difficulties = <String>['easy', 'normal', 'pro', 'master'];
    for (final difficulty in difficulties) {
      for (var index = 0; index < ids.length; index++) {
        final game = LocalGameSession(gameId: ids[index], humanName: 'Tester', difficulty: difficulty, seed: 1490 + index);
        final state = Map<String, dynamic>.from(game.room()['state'] as Map);
        expect(state['hand'], isA<List>(), reason: '${ids[index]} / $difficulty');
        expect((state['hand'] as List).isNotEmpty, isTrue, reason: '${ids[index]} / $difficulty');
      }
    }
  });

}

