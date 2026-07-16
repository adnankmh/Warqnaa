class RoomLaunchOptions {
  final String roomName;
  final bool voiceEnabled;
  final String visibility;
  final String? password;
  final int turnSeconds;
  final String? roomCode;
  final int minLevel;
  final bool allowOwnerKick;
  final int playerCount;
  final int botCount;
  final List<int> inviteeIds;

  const RoomLaunchOptions({
    this.roomName = 'غرفة ورقنا',
    this.voiceEnabled = false,
    this.visibility = 'public',
    this.password,
    this.turnSeconds = 10,
    this.roomCode,
    this.minLevel = 1,
    this.allowOwnerKick = true,
    this.playerCount = 4,
    this.botCount = 3,
    this.inviteeIds = const <int>[],
  });

  bool get joiningExisting => roomCode != null && roomCode!.trim().isNotEmpty;

  RoomLaunchOptions copyWith({
    String? roomName,
    bool? voiceEnabled,
    String? visibility,
    String? password,
    int? turnSeconds,
    String? roomCode,
    int? minLevel,
    bool? allowOwnerKick,
    int? playerCount,
    int? botCount,
    List<int>? inviteeIds,
  }) {
    return RoomLaunchOptions(
      roomName: roomName ?? this.roomName,
      voiceEnabled: voiceEnabled ?? this.voiceEnabled,
      visibility: visibility ?? this.visibility,
      password: password ?? this.password,
      turnSeconds: turnSeconds ?? this.turnSeconds,
      roomCode: roomCode ?? this.roomCode,
      minLevel: minLevel ?? this.minLevel,
      allowOwnerKick: allowOwnerKick ?? this.allowOwnerKick,
      playerCount: playerCount ?? this.playerCount,
      botCount: botCount ?? this.botCount,
      inviteeIds: inviteeIds ?? this.inviteeIds,
    );
  }
}
