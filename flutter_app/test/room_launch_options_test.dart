import 'package:flutter_test/flutter_test.dart';
import 'package:warqna_mobile/models/room_launch_options.dart';

void main() {
  test('normal room is the safe default', () {
    const options = RoomLaunchOptions();
    expect(options.voiceEnabled, isFalse);
    expect(options.visibility, 'public');
    expect(options.turnSeconds, 10);
    expect(options.joiningExisting, isFalse);
  });

  test('voice room and join-by-code options are preserved', () {
    const options = RoomLaunchOptions(
      roomName: 'Voice League',
      voiceEnabled: true,
      visibility: 'private',
      password: '1234',
      turnSeconds: 7,
      roomCode: 'ABCD12',
    );

    expect(options.voiceEnabled, isTrue);
    expect(options.joiningExisting, isTrue);
    expect(options.roomCode, 'ABCD12');
    expect(options.password, '1234');
    expect(options.turnSeconds, 7);
  });
}
