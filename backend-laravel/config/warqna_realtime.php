<?php
return [
 'mode'=>env('WARQNA_REALTIME_MODE','polling'), // polling | reverb | soketi
 'heartbeat_seconds'=>(int)env('WARQNA_HEARTBEAT_SECONDS',30),
 'room_poll_seconds'=>(int)env('WARQNA_ROOM_POLL_SECONDS',2),
 'chat_poll_seconds'=>(int)env('WARQNA_CHAT_POLL_SECONDS',3),
 'websocket'=>[
  'host'=>env('WARQNA_WS_HOST','127.0.0.1'),
  'port'=>(int)env('WARQNA_WS_PORT',8080),
  'scheme'=>env('WARQNA_WS_SCHEME','ws'),
 ],
 'channels'=>[
  'room'=>'private-room.{code}',
  'chat'=>'private-chat.{id}',
  'notifications'=>'private-user.{id}',
 ],
];
