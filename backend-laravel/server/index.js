const http = require('http');
const { Server } = require('socket.io');
const server = http.createServer();
const io = new Server(server,{cors:{origin:'*'}});
const rooms = new Map();
const suits=['hearts','diamonds','spades','clubs'];
const ranks=['A','K','Q','J','10','9','8','7','6','5','4','3','2'];
const botNames=['سارة','رنا','ليان','نور','مريم','ياسمين','علي','خالد','هيثم','يزن','كريم','رامي','باسل','تيم','مازن'];
function shuffle(a){for(let i=a.length-1;i>0;i--){const j=Math.floor(Math.random()*(i+1));[a[i],a[j]]=[a[j],a[i]]}return a}
function deck(){const d=[]; for(const s of suits) for(const r of ranks)d.push(`${r}_${s}`); return shuffle(d)}
function makeRoom(code){const d=deck(); return {code,players:[],hands:{},turn:null,trick:[],phase:'waiting',createdAt:Date.now(),deck:d}}
function publicState(st,socketId){return {phase:st.phase,players:st.players,turn:st.turn,hand:st.hands[socketId]||[],trick:st.trick}}
function nextHuman(st){const ids=st.players.filter(p=>!p.bot).map(p=>p.id); if(!ids.length)return null; const i=Math.max(0,ids.indexOf(st.turn)); return ids[(i+1)%ids.length]}
io.on('connection',socket=>{
 socket.on('join_room',({room,name})=>{socket.join(room); if(!rooms.has(room)) rooms.set(room,makeRoom(room)); const st=rooms.get(room); if(!st.players.find(p=>p.id===socket.id)){st.players.push({id:socket.id,name:name||'لاعب',bot:false}); st.hands[socket.id]=st.deck.splice(0,13);} if(!st.turn) st.turn=socket.id; socket.emit('state',publicState(st,socket.id)); io.to(room).emit('table_action',{action:'join',player:name||'لاعب'});});
 socket.on('add_bot',({room})=>{const st=rooms.get(room); if(!st||st.players.length>=6)return; const id='BOT_'+Math.random().toString(36).slice(2,8); const name=botNames[Math.floor(Math.random()*botNames.length)]; st.players.push({id,name,bot:true}); st.hands[id]=st.deck.splice(0,13); io.to(room).emit('table_action',{action:'bot_join',player:name});});
 socket.on('play_card',({room,card})=>{const st=rooms.get(room); if(!st||!st.hands[socket.id])return; const i=st.hands[socket.id].indexOf(card); if(i<0)return; st.hands[socket.id].splice(i,1); st.trick.push({player:socket.id,card}); st.turn=nextHuman(st); io.to(room).emit('table_action',{player:socket.id,card,action:'play_card'}); socket.emit('state',publicState(st,socket.id));});
 socket.on('chat',({room,message})=>io.to(room).emit('chat',{from:socket.id,message:String(message||'').slice(0,500)}));
 socket.on('chat_message',(msg)=>{const room=msg&&msg.room; if(room) socket.to(room).emit('chat_message',{name:String(msg.name||'لاعب').slice(0,40),body:String(msg.body||'').slice(0,500),color:String(msg.color||'#fff').slice(0,20),emoji:!!msg.emoji});});

 // v96 WebRTC voice signaling for voice rooms. Run with: npm run socket
 socket.on('voice_join',({room,name})=>{ if(!room)return; socket.join('voice:'+room); socket.data.voiceRoom=room; socket.data.voiceName=String(name||'لاعب').slice(0,40); socket.to('voice:'+room).emit('voice_peer',{id:socket.id,name:socket.data.voiceName}); });
 socket.on('voice_ready',({room})=>{ if(!room)return; socket.to('voice:'+room).emit('voice_peer',{id:socket.id,name:socket.data.voiceName||'لاعب'}); });
 socket.on('voice_offer',({room,to,offer})=>{ if(room&&to) io.to(to).emit('voice_offer',{from:socket.id,offer}); });
 socket.on('voice_answer',({room,to,answer})=>{ if(room&&to) io.to(to).emit('voice_answer',{from:socket.id,answer}); });
 socket.on('voice_ice',({room,to,candidate})=>{ if(room&&to) io.to(to).emit('voice_ice',{from:socket.id,candidate}); });
 socket.on('voice_leave',({room})=>{ if(room){ socket.to('voice:'+room).emit('voice_leave',{id:socket.id}); socket.leave('voice:'+room); } });

 socket.on('disconnect',()=>{ if(socket.data.voiceRoom) socket.to('voice:'+socket.data.voiceRoom).emit('voice_leave',{id:socket.id}); for(const st of rooms.values()){const p=st.players.find(x=>x.id===socket.id); if(p){p.connected=false;}}});
});
server.listen(process.env.SOCKET_PORT||4000,()=>console.log('Warqna realtime server on '+(process.env.SOCKET_PORT||4000)));
