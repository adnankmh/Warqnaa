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

  test('Hand family deals 14 cards and requires a draw', () {
    final game = LocalGameSession(gameId: 'hand', humanName: 'Adnan', seed: 11);
    final state = Map<String, dynamic>.from(game.room()['state'] as Map);
    expect((state['hand'] as List).length, 14);
    expect(state['phase'], 'draw');
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


}
