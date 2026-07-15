// Warqna v79 stabilized UI/game/chat/store helpers.
(function(){
  'use strict';
  window.CSRF = window.CSRF || document.querySelector('meta[name="csrf-token"]')?.content || '';
  const $ = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));
  window.escapeHtml = function(s){return String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));};

  window.WarqnaSound=(function(){let enabled=(localStorage.soundEnabled??document.body?.dataset?.sound??'1')!=='0',ctx=null;function ac(){if(!ctx)ctx=new(window.AudioContext||window.webkitAudioContext)();return ctx}function icon(){let b=$('#soundToggle');if(b)b.textContent=enabled?'🔊':'🔇'}function tone(f=440,d=.08,t='sine',g=.04,delay=0){if(!enabled)return;try{let c=ac(),o=c.createOscillator(),gn=c.createGain(),st=c.currentTime+delay;o.type=t;o.frequency.value=f;gn.gain.value=.0001;o.connect(gn);gn.connect(c.destination);gn.gain.exponentialRampToValueAtTime(g,st+.01);gn.gain.exponentialRampToValueAtTime(.0001,st+d);o.start(st);o.stop(st+d+.03)}catch(e){}}function noise(d=.06,g=.025){if(!enabled)return;try{let c=ac(),buf=c.createBuffer(1,Math.max(1,c.sampleRate*d),c.sampleRate),data=buf.getChannelData(0);for(let i=0;i<data.length;i++)data[i]=(Math.random()*2-1)*(1-i/data.length);let s=c.createBufferSource(),gn=c.createGain();s.buffer=buf;gn.gain.value=g;s.connect(gn);gn.connect(c.destination);s.start()}catch(e){}}function play(k){if(k==='card'){noise();tone(820,.045,'triangle',.03,.01)}else if(k==='draw'){tone(360,.06,'sawtooth',.028);tone(520,.06,'triangle',.022,.04)}else if(k==='discard'){noise(.075,.035);tone(650,.045,'square',.018,.03)}else if(k==='bid'){tone(500,.055,'sine',.032);tone(700,.04,'sine',.024,.05)}else if(k==='win'){tone(523,.09,'triangle',.045);tone(659,.1,'triangle',.045,.09);tone(784,.14,'triangle',.045,.19)}else if(k==='lose'){tone(330,.1,'sine',.035);tone(247,.16,'sine',.032,.11)}else if(k==='notify'){tone(880,.07,'sine',.04);tone(1174,.09,'sine',.03,.07)}else if(k==='message'){tone(740,.045,'triangle',.025);tone(980,.045,'triangle',.018,.045)}else if(k==='shop'){tone(660,.06,'triangle',.03);tone(990,.08,'triangle',.028,.07);tone(1320,.08,'triangle',.022,.15)}else if(k==='ui'){tone(420,.035,'sine',.018)}}return{play,ui:()=>play('ui'),message:()=>play('message'),notify:()=>play('notify'),shop:()=>play('shop'),toggle(){enabled=!enabled;localStorage.soundEnabled=enabled?'1':'0';icon();play('ui')},enabled:()=>enabled,setIcon:icon}})();

  let fontScale=parseFloat(localStorage.fontScale||'1');
  function applyFont(){document.documentElement.style.fontSize=(16*fontScale)+'px'}
  window.changeFont=function(d){fontScale=Math.max(.95,Math.min(1.35,fontScale+d*.025));localStorage.fontScale=fontScale;applyFont();}; applyFont();

  function baseDialog(){let d=$('#confirmDialog');if(!d){d=document.createElement('div');d.id='confirmDialog';d.className='confirm-dialog hidden';document.body.appendChild(d);d.addEventListener('click',e=>{if(e.target===d)d.classList.add('hidden')});}return d;}
  window.showNotice=function(message){let d=baseDialog();let safe=escapeHtml(message);d.innerHTML=`<div class="confirm-card notice-card"><button class="modal-x" type="button" onclick=&quot;document.getElementById('confirmDialog').classList.add('hidden')&quot;>×</button><div class="notice-body">${safe}</div></div>`;d.classList.remove('hidden');};
  window.showRichNotice=function(html){let d=baseDialog();d.innerHTML=`<div class="confirm-card notice-card"><button class="modal-x" type="button" onclick=&quot;document.getElementById('confirmDialog').classList.add('hidden')&quot;>×</button><div class="notice-body">${String(html||'')}</div></div>`;d.classList.remove('hidden');};
  window.showConfirm=function(message,onYes){let d=baseDialog();let safe=String(message).startsWith('<div')?String(message):escapeHtml(message);d.innerHTML=`<div class="confirm-card"><button class="modal-x" type="button" onclick=&quot;document.getElementById('confirmDialog').classList.add('hidden')&quot;>×</button><h3>تأكيد العملية</h3><p>${safe}</p><div class="confirm-actions"><button class="primary" id="confirmYes" type="button">نعم</button><button id="confirmNo" type="button">لا</button></div><small>يمكن الإغلاق بالضغط خارج النافذة أو Esc.</small></div>`;d.classList.remove('hidden');$('#confirmYes',d).onclick=()=>{d.classList.add('hidden'); if(typeof onYes==='function')onYes();};$('#confirmNo',d).onclick=()=>d.classList.add('hidden');};

  
  window.setSiteTheme=function(theme){ if(!theme)return; document.body.className=document.body.className.replace(/theme-[^\s]+/g,'').trim()+' theme-'+theme; document.body.dataset.theme=theme; localStorage.siteTheme=theme; WarqnaSound?.ui?.(); };
  function updateClock(){let el=document.getElementById('siteClock'); if(!el)return; let d=new Date(); el.textContent=d.toLocaleDateString('ar-EG',{weekday:'short',year:'numeric',month:'2-digit',day:'2-digit'})+' • '+d.toLocaleTimeString('ar-EG',{hour:'2-digit',minute:'2-digit'});}
  setInterval(updateClock,30000); document.addEventListener('DOMContentLoaded',()=>{ if(localStorage.siteTheme) setSiteTheme(localStorage.siteTheme); updateClock(); });

  window.toggleTopPanel=function(id){$$('.top-panel,.games-curtain').forEach(p=>{if(p.id!==id)p.classList.add('hidden')});let p=document.getElementById(id);if(p)p.classList.toggle('hidden');WarqnaSound.ui();};
  document.addEventListener('keydown',e=>{if(e.key==='Escape'){$$('#confirmDialog,.top-panel,.games-curtain,#tablePreview,#profileModal').forEach(p=>p.classList.add('hidden'));}});
  document.addEventListener('click',e=>{if(!e.target.closest('#profileModal')&&!e.target.closest('[onclick^="openProfile"]')&&!e.target.closest('[data-profile-id]'))$('#profileModal')?.classList.add('hidden');if(!e.target.closest('.top-panel')&&!e.target.closest('.games-curtain')&&!e.target.closest('.nav-drop-btn')&&!e.target.closest('.quick-icons')&&!e.target.closest('.top-icons'))$$('.top-panel,.games-curtain').forEach(p=>p.classList.add('hidden'));if(e.target.id==='tablePreview')e.target.classList.add('hidden');});

  function headers(json=true){return Object.assign({'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}, json?{'Content-Type':'application/json'}:{});}
  async function postForm(form){let r=await fetch(form.getAttribute('action'),{method:(form.method||'POST').toUpperCase(),headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});let ct=r.headers.get('content-type')||'';let data=ct.includes('json')?await r.json():{ok:r.ok,message:r.ok?'تم تنفيذ العملية':'تعذر تنفيذ العملية'}; if(data.url){window.location.href=data.url;} return data;}

  document.addEventListener('submit',function(e){let f=e.target;if(!f.matches('form[data-confirm],form[data-ajax-start],form[data-ajax-soft],form[data-ajax-profile-action],form[action*="/store/"],form[action*="/inventory/"],form[action*="/wallet/transfer"],form[action*="/friends/"]'))return;e.preventDefault();let run=async()=>{try{if(f.dataset.ajaxStart){await startGameAjax(f);return}let j=await postForm(f);showNotice(j.message||'تم تنفيذ العملية.');if(j.profile){document.querySelectorAll('[data-my-country-name]').forEach(el=>el.textContent=j.profile.country_name||'');document.querySelectorAll('[data-my-flag]').forEach(el=>{if(j.profile.flag_url)el.src=j.profile.flag_url});document.body.dataset.countryName=j.profile.country_name||'';document.body.dataset.countryCode=j.profile.country_code||'';}if(j.ok!==false&&f.matches('form[action*="/store/"],form[action*="/inventory/"]')) { markStoreForm(f,j); if(j.item) addInventoryCard(j.item,j.inventory_id); if(j.activated) applyActivatedCosmetic(j); }if(f.dataset.ajaxProfileAction){let b=f.querySelector('button'); if(b){b.textContent='تم إرسال الطلب'; b.disabled=true;}}}catch(err){showNotice('لا يمكن تنفيذ هذه الخطوة الآن. إن ظهرت المشكلة مرة أخرى راجع الإدارة أو جرّب تحديث الصفحة.')}};let msg=f.dataset.confirm;if(msg)showConfirm(msg,run);else run();});
  function markStoreForm(f,j){let b=f.querySelector('button.primary,button:not([type="button"])'); if(b){b.textContent=j.activated?'مفعل':'تم الشراء';b.disabled=!!j.activated} WarqnaSound.shop(); let invTab=document.querySelector('[data-store-tab="inventory"]'); if(invTab && j.item){ localStorage.storeTab='inventory'; }}
  function addInventoryCard(item,inventoryId){let grid=document.querySelector('#inventory .store-grid'); if(!grid)return; let empty=grid.querySelector('.mini-card'); if(empty&&empty.textContent.includes('لا توجد')) empty.remove(); let payload=item.payload||{}; let icon='🎁'; if(item.category==='table')icon='<span class="table-preview '+escapeHtml(payload.table||'')+'"></span>'; else if(item.category==='card_back')icon='<span class="card-back-preview '+escapeHtml(payload.card_back||'')+'">🂠</span>'; else if(item.category==='name_color')icon='<span class="color-orbit-preview" style="--orbit:'+escapeHtml(payload.color||'#facc15')+'">Aa</span>'; else if(item.category==='emoji_pack')icon='<span class="emoji-store-icon">'+escapeHtml(payload.emojis||'😄')+'</span>'; let form=document.createElement('form'); form.className='store-card deluxe inventory-card'; form.method='post'; form.action='/inventory/'+inventoryId+'/activate'; form.setAttribute('data-ajax-soft','1'); form.innerHTML='<input type="hidden" name="_token" value="'+window.CSRF+'"><h3>'+escapeHtml(item.name)+'</h3><p>غير مفعل</p>'+icon+'<button>تفعيل</button>'; grid.prepend(form); }
  function applyActivatedCosmetic(j){let p=j.payload||{}; if(j.category==='name_color'&&p.color){document.body.style.setProperty('--my-name-color',p.color); document.querySelectorAll('.user-chip,.profile-modal-card .name-orbit').forEach(el=>el.style.setProperty('--player-color',p.color));} if(j.category==='text_color'&&p.color){document.body.style.setProperty('--my-text-color',p.color);} if(j.category==='table'&&p.table){document.querySelectorAll('.game-table').forEach(t=>{[...t.classList].filter(c=>c.startsWith('table-')).forEach(c=>t.classList.remove(c)); t.classList.add(p.table);});} if(j.category==='card_back'&&p.card_back){window.MY_CARD_BACK=p.card_back; if(window.LAST_STATE)renderState(window.LAST_STATE);} }

  // Store tabs and previews
  window.previewStoreItem=function(btn){let card=btn?.closest?.('.store-card')||btn;if(!card)return;let form=card.closest('form')||card;let title=card.querySelector('h3')?.textContent||'العنصر';let price=card.querySelector('.price')?.textContent||'';let icon=card.querySelector('.shop-icon')?.innerHTML||'';let action=form?.action||'';let token=window.CSRF||'';showRichNotice(`<div class="store-preview-pop"><div class="profile-preview-card"><div class="shop-icon big">${icon}</div><img src="/assets/avatars/default.svg" alt="avatar"><div class="preview-name" style="color:var(--my-name-color)">معاينة مباشرة على البروفايل</div><h3>${escapeHtml(title)}</h3><p>${escapeHtml(price)}</p><small>بعد الشراء سيظهر العنصر في مشترياتي ويمكن تفعيله فوراً.</small>${action?`<form method="post" action="${escapeHtml(action)}" data-confirm="تأكيد شراء ${escapeHtml(title)}؟" class="preview-buy-form"><input type="hidden" name="_token" value="${escapeHtml(token)}"><button class="primary">شراء الآن</button></form>`:''}</div></div>`);WarqnaSound.ui();};
  function initStore(){let tabs=$$('.store-tabs [data-store-tab]');if(!tabs.length)return;function show(k){tabs.forEach(x=>x.classList.toggle('active',x.dataset.storeTab===k));$$('.store-section').forEach(s=>s.classList.toggle('active',s.id==='cat-'+k||s.id===k));localStorage.storeTab=k;}tabs.forEach(b=>b.addEventListener('click',()=>show(b.dataset.storeTab)));show(localStorage.storeTab&&document.querySelector(`[data-store-tab="${localStorage.storeTab}"]`)?localStorage.storeTab:tabs[0].dataset.storeTab);$$('[data-tier-filter]').forEach(b=>b.addEventListener('click',()=>{let v=b.dataset.tierFilter;$$('[data-tier-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');$$('.category-table .store-card').forEach(c=>c.style.display=(v==='all'||c.dataset.tier===v)?'':'none')}));$$('[data-emoji-filter]').forEach(b=>b.addEventListener('click',()=>{let v=b.dataset.emojiFilter;$$('[data-emoji-filter]').forEach(x=>x.classList.remove('active'));b.classList.add('active');$$('.category-emoji_pack .store-card').forEach(c=>c.style.display=(v==='all'||c.dataset.emojiTier===v)?'':'none')}));}

  // Chat
  window.CHAT_MODE=window.CHAT_HAS_ROOM?'room':'friends'; window.ACTIVE_FRIEND_ID=null;
  function makeDraggable(el){if(!el)return;let h=el.querySelector('.chat-head')||el,dx=0,dy=0;h.onmousedown=e=>{if(e.target.tagName==='BUTTON')return;e.preventDefault();dx=e.clientX;dy=e.clientY;document.onmousemove=ev=>{let x=dx-ev.clientX,y=dy-ev.clientY;dx=ev.clientX;dy=ev.clientY;el.style.top=(el.offsetTop-y)+'px';el.style.left=(el.offsetLeft-x)+'px';el.style.bottom='auto'};document.onmouseup=()=>{document.onmousemove=null;document.onmouseup=null}}}
  window.toggleChat=function(){let b=$('#chatBody'),f=$('.chat-send'),p=$('#emojiPalette');if(!b)return;let hide=b.style.display!=='none';b.style.display=hide?'none':'block';if(f)f.style.display=hide?'none':'flex';if(p)p.style.display=hide?'none':'block';};
  window.minimizeChat=function(){let d=$('#chatDock'),r=$('#chatReopen');d?.classList.add('chat-minimized');d?.classList.remove('chat-expanded');r?.classList.add('hidden');localStorage.chatState='minimized'};
  window.maximizeChat=function(){let d=$('#chatDock'),r=$('#chatReopen');d?.classList.remove('chat-minimized','hidden');d?.classList.add('chat-expanded');r?.classList.add('hidden');localStorage.chatState='open'};
  window.closeChat=function(){let d=$('#chatDock'),r=$('#chatReopen');d?.classList.add('hidden');r?.classList.remove('hidden');localStorage.chatState='closed'};
  window.reopenChat=function(){let d=$('#chatDock'),r=$('#chatReopen');d?.classList.remove('hidden','chat-minimized');d?.classList.add('chat-expanded');r?.classList.add('hidden');localStorage.chatState='open'};
  window.setChatMode=function(mode){window.CHAT_MODE=mode;$$('[data-chat-tab]').forEach(b=>b.classList.toggle('active',b.dataset.chatTab===mode));if(mode==='friends')loadChatFriends();else if(mode==='search')loadChatSearch('');else if(mode==='room'){$('#chatBody').innerHTML='<p class="muted">دردشة اللعبة مفعلة لهذه الغرفة. اكتب رسالتك بالأسفل.</p>';}};
  window.chatSearchChanged=function(q){if(window.CHAT_MODE==='search')loadChatSearch(q);else filterChatList(q)};
  window.filterChatList=function(q){q=String(q||'').toLowerCase();$$('#chatBody .msg,#chatBody .friend-row,.search-player-row').forEach(x=>x.style.display=x.textContent.toLowerCase().includes(q)?'':'none')};
  window.cleanChatText=function(t){let bad=['كلب','حمار','حقير','قذر','وسخ','غبي','تافه','لعنة'];let x=String(t||'').trim();bad.forEach(w=>x=x.replace(new RegExp(w,'giu'),'***'));return x.slice(0,500)};
  window.appendChat=function(m,me=false){let body=$('#chatBody');if(!body)return;if(body.querySelector('.muted'))body.innerHTML='';body.insertAdjacentHTML('beforeend',`<div class="msg ${me?'me':'other'} ${m.emoji?'emoji-msg':''}" style="color:${escapeHtml(m.color||'#fff')}"><b>${escapeHtml(m.name||'لاعب')}:</b> <span>${escapeHtml(m.body||'')}</span></div>`);body.scrollTop=body.scrollHeight};
  window.flagHTML=function(u){return u.flag_url?`<img class="flag-img" src="${escapeHtml(u.flag_url)}" alt="${escapeHtml(u.flag||'flag')}">`:escapeHtml(u.flag||'')};
  window.loadChatFriends=async function(){let b=$('#chatBody');if(!b)return;b.innerHTML='<p class="muted">جاري تحميل الأصدقاء...</p>';try{let r=await fetch('/chat/friends',{headers:{Accept:'application/json'}}),j=await r.json();b.innerHTML=(j.friends||[]).map(u=>`<button type="button" class="friend-row" onclick="openFriendThread(${u.id})"><img src="${escapeHtml(u.avatar)}"><b style="color:${escapeHtml(u.color)}">${escapeHtml(u.username)}</b><small>${flagHTML(u)} Level ${escapeHtml(u.level)}</small></button>`).join('')||'<p class="muted">لا يوجد أصدقاء بعد.</p>'}catch(e){b.innerHTML='<p class="muted">تعذر تحميل الأصدقاء.</p>'}};
  window.loadChatSearch=async function(q=''){let b=$('#chatBody');if(!b)return;b.innerHTML='<p class="muted">جاري البحث...</p>';try{let r=await fetch('/chat/search?q='+encodeURIComponent(q),{headers:{Accept:'application/json'}}),j=await r.json();b.innerHTML=(j.users||[]).map(u=>`<div class="search-player-row"><img src="${escapeHtml(u.avatar)}"><b style="color:${escapeHtml(u.color)}">${escapeHtml(u.username)}</b><small>${flagHTML(u)} ${escapeHtml(u.country)}</small><button type="button" onclick="openProfile(${u.id})">بروفايل</button></div>`).join('')||'<p class="muted">لا توجد نتائج.</p>'}catch(e){b.innerHTML='<p class="muted">تعذر البحث الآن.</p>'}};
  window.openFriendThread=async function(id){window.ACTIVE_FRIEND_ID=id;window.CHAT_MODE='friends';let b=$('#chatBody');b.innerHTML='<p class="muted">جاري تحميل المحادثة...</p>';try{let r=await fetch('/chat/thread/'+id,{headers:{Accept:'application/json'}});let j=await r.json();if(!j.ok){showNotice(j.message||'الرسائل الخاصة للأصدقاء فقط');return}b.innerHTML=(j.messages||[]).map(m=>`<div class="msg ${m.mine?'me':'other'}" style="color:${escapeHtml(m.color)}"><b>${escapeHtml(m.name)}:</b> <span>${escapeHtml(m.body)}</span><small>${escapeHtml(m.time||'')}</small></div>`).join('')||'<p class="muted">لا توجد رسائل بعد.</p>'}catch(e){b.innerHTML='<p class="muted">تعذر تحميل المحادثة.</p>'}};
  window.sendChat=async function(e){e.preventDefault();let i=$('#chatInput');if(!i||!i.value.trim())return;let text=cleanChatText(i.value);if(!text||text.replace(/\*/g,'').trim()===''){showNotice('لا يمكن إرسال هذه الرسالة لأنها تخالف قوانين الدردشة.');return}let color=getComputedStyle(document.body).getPropertyValue('--my-text-color')||'#fff';if(window.CHAT_MODE==='friends'){if(!window.ACTIVE_FRIEND_ID){showNotice('اختر صديقًا أولًا.');return}try{let r=await fetch('/chat/send/'+window.ACTIVE_FRIEND_ID,{method:'POST',headers:headers(true),body:JSON.stringify({body:text})}),j=await r.json();if(j.ok&&j.message){appendChat({name:j.message.name,body:j.message.body,color:j.message.color},true);i.value='';WarqnaSound.message()}else showNotice(j.message||'تعذر الإرسال')}catch(err){showNotice('تعذر إرسال الرسالة الآن.')}return}if(!window.ROOM_CODE){showNotice('دردشة اللعبة تظهر فقط داخل غرفة لعبة.');return}let msg={name:document.body.dataset.user||'أنا',body:text,color,room:window.ROOM_CODE,emoji:false};appendChat(msg,true);try{if(window.ROOM_CHAT_URL)await fetch(window.ROOM_CHAT_URL,{method:'POST',headers:headers(true),body:JSON.stringify({body:text})});if(window.socket)socket.emit('chat_message',msg)}catch(err){}i.value='';WarqnaSound.message()};
  window.sendEmojiChat=function(e){let i=$('#chatInput');if(i){i.value=(i.value?i.value+' ':'')+e;let form=$('.chat-send');if(form)window.sendChat({preventDefault(){}});}else{appendChat({name:document.body.dataset.user||'أنا',body:e,color:getComputedStyle(document.body).getPropertyValue('--my-text-color')||'#fff',emoji:true},true);WarqnaSound.message();}};
  window.renderEmojiPalette=function(){let holder=$('#emojiPalette');if(!holder)return;let emojis=window.WARQNA_EMOJIS||['😂','🤣','😍','👋','👍','😡','😢','😭','😱','🤔','☕','🌹','🎆','🏆','👑','💎','⚡','🎉','🐉','🚀','😎','🙌','🔥','💔','🥳','😤','😇','🤯','🦁','🐯','🦅','🐉','💰','✨','🎲','🃏','😆','😜','🥰','🤩','👏','💪','😬','😴','🥶','😈','💥','🌟'];let groups={free:emojis.slice(0,18),basic:emojis.slice(0,28),pro:emojis.slice(12,40),legendary:['👑','💎','🦁','🐯','🦅','🐉','🔥','⚡','🌟','✨','🏆','🎆','💰','🚀','🃏'],animated:['😂','🤣','🔥','⚡','🎉','✨','💥','🌟','🥳','🤩','👑','💎'],big:emojis.slice(0,48)};window._emojiTabs=groups;let icons={free:'🆓',basic:'🙂',pro:'💪',legendary:'👑',animated:'✨',big:'🔎'};holder.innerHTML='<div class="emoji-tabs compact">'+Object.entries(icons).map(([k,ic],i)=>`<button type="button" data-emoji-tab="${k}" class="${i===0?'active':''}" title="${k}">${ic}</button>`).join('')+'</div><div class="emoji-grid" id="emojiGrid"></div>';$$('[data-emoji-tab]',holder).forEach(b=>b.addEventListener('click',()=>showEmojiTab(b.dataset.emojiTab,b)));showEmojiTab('free')};
  window.showEmojiTab=function(k,btn=null){let grid=$('#emojiGrid');if(!grid)return;$$('.emoji-tabs button').forEach(b=>b.classList.remove('active'));btn?.classList.add('active');let list=(window._emojiTabs&&window._emojiTabs[k])||[];grid.innerHTML=list.map(e=>`<button type="button" class="emoji-btn ${k==='animated'||k==='legendary'?'animated-emoji':''} ${k==='big'?'big-emoji':''}" onclick="sendEmojiChat('${escapeHtml(e)}')">${escapeHtml(e)}</button>`).join('')};

  // Profile
  window.openProfile=async function(id){let m=$('#profileModal');if(!m)return;m.classList.remove('hidden');m.innerHTML='<button class="modal-x" onclick="profileModal.classList.add(\'hidden\')">×</button><b>جاري تحميل البروفايل...</b>';WarqnaSound.ui();if(!id){m.innerHTML='<button class="modal-x" onclick="profileModal.classList.add(\'hidden\')">×</button><div class="profile-page"><h2>🤖 بوت احترافي</h2><p>لاعب آلي داخل اللعبة.</p></div>';return}try{let r=await fetch('/profile/'+id,{headers:{'X-Requested-With':'XMLHttpRequest'}}),t=await r.text();m.innerHTML='<button class="modal-x" onclick="profileModal.classList.add(\'hidden\')">×</button>'+t}catch(e){m.innerHTML='<button class="modal-x" onclick="profileModal.classList.add(\'hidden\')">×</button>تعذر تحميل البروفايل'}};
  window.updateCountryPreview=function(sel){let opt=sel.selectedOptions[0],box=$('#countryPreview');if(box)box.innerHTML=(opt?.dataset.flag?`<img class="flag-img" src="${opt.dataset.flag}"> `:'')+escapeHtml(opt?.textContent||'')};

  // Cards and game
  function suitIcon(c){c=String(c);if(c.includes('hearts'))return '♥';if(c.includes('diamonds'))return '♦';if(c.includes('spades'))return '♠';if(c.includes('clubs'))return '♣';return '🃏'}
  function suitName(s){return{clubs:'♣ سباتي',diamonds:'♦ ديناري',spades:'♠ بستوني',hearts:'♥ كبة'}[s]||s||'لم يحدد'}
  function suitClass(c){c=String(c);return(c.includes('hearts')||c.includes('diamonds'))?'red':'black'}
  function rankVal(c){let r=String(c).split('_')[0];return{'A':14,'K':13,'Q':12,'J':11,'10':10,'9':9,'8':8,'7':7,'6':6,'5':5,'4':4,'3':3,'2':2,'JOKER':20}[r]||0}
  function labelCard(c){let s=String(c);if(s.includes('-'))return escapeHtml(s.replace('-',' | '));let p=s.split('_');return `<b>${escapeHtml(p[0]||'')}</b><em>${suitIcon(s)}</em>`}
  function sortCardsTarneeb(cards){const suitOrder={clubs:0,diamonds:1,spades:2,hearts:3};return (cards||[]).slice().sort((a,b)=>{const as=(a.split('_')[1]||''),bs=(b.split('_')[1]||'');return (suitOrder[as]??9)-(suitOrder[bs]??9)||rankVal(b)-rankVal(a)})}
  window.toggleTablePreview=function(){return false};
  window.selectTablePreview=function(skin){let el=$('#previewTableSkin');if(el)el.className='preview-table '+skin;WarqnaSound.ui()};
  let selectedCards=new Set();
  function actionForCard(st,c){if(st.game_type==='domino'){let side=(prompt('اختر الطرف: left أو right','right')||'right').toLowerCase();return['play_tile',{tile:c,side:side==='left'?'left':'right'}]}if(['hand','banakil','konkan'].includes(st.game_type))return['select_card',{card:c}];return['play_card',{card:c}]}
  window.cardClicked=function(c){let st=window.LAST_STATE||{};let a=actionForCard(st,c);if(a[0]==='select_card'){selectedCards.has(c)?selectedCards.delete(c):selectedCards.add(c);renderState(st);return}roomAction(a[0],a[1])};
  window.meldSelectedCards=function(){let cards=[...selectedCards];if(cards.length<3||cards.length>13){showNotice('اختر مجموعة قانونية من 3 أوراق أو أكثر.');return}roomAction('meld',{cards});selectedCards.clear()};
  window.arrangeMelds=function(){let groups=[];document.querySelectorAll('.meld-slot').forEach(slot=>{let raw=slot.dataset.cards||'[]';try{let g=JSON.parse(raw);if(g.length)groups.push(g)}catch(e){}});if(!groups.length){let cards=[...selectedCards];if(cards.length>=3)groups=[cards];}if(!groups.length){showNotice('اسحب أوراقًا إلى منطقة المجموعات أو حدد أوراقًا أولًا.');return}roomAction('arrange_melds',{groups});selectedCards.clear()};
  window.sortHandVisual=function(){let st=window.LAST_STATE||{};let h=(st.hand||window.INITIAL_HAND||[]).slice().sort((a,b)=>suitIcon(a).localeCompare(suitIcon(b))||rankVal(b)-rankVal(a));st.hand=h;window.INITIAL_HAND=h;try{localStorage.setItem('warqnaHandOrder',JSON.stringify(h))}catch(e){}renderState(st);WarqnaSound.ui()};
  window.roomAction=async function(action,payload={}){if(!window.ROOM_ACTION_URL)return;let box=$('#lastAction');if(box)box.textContent='جاري تنفيذ الحركة...';try{let r=await fetch(window.ROOM_ACTION_URL,{method:'POST',headers:headers(true),body:JSON.stringify({action,payload})});let j=await r.json().catch(()=>({ok:false,message:'تعذر تنفيذ الحركة الآن.'}));if(j.seats)renderSeats(j.seats);if(j.state)renderState(j.state);if(j.ok===false||j.valid===false){showNotice(j.message||'لا يمكن تنفيذ هذه الحركة الآن.');if(box)box.textContent=j.message||'حركة غير مسموحة';return}WarqnaSound.play(action.includes('draw')?'draw':action.includes('discard')?'discard':(action==='bid'||action==='pass')?'bid':'card');}catch(e){showNotice('تعذر الاتصال. جرّب تحديث الدور.')}};
  document.addEventListener('click',e=>{let b=e.target.closest('[data-action]');if(!b)return;let action=b.dataset.action,payload={};if(action==='move_prompt'){let from=prompt('من نقطة رقم؟ 1-24');let to=prompt('إلى نقطة رقم؟ 0 أو 25 للخروج');if(from&&to)roomAction('move',{from:parseInt(from,10),to:parseInt(to,10)});return}if(action==='bid')payload.value=parseInt(b.dataset.value||'7',10);if(action==='choose_trump')payload.suit=b.dataset.suit;if(action==='choose_contract')payload.contract=b.dataset.contract;roomAction(action,payload)});
  window.renderState=function(st){if(!st)return;window.LAST_STATE=st;let gt=st.game_type||'', handCards=st.hand||window.INITIAL_HAND||[];if(['tarneeb','tarneeb_400','tarneeb_41'].includes(st.game||window.GAME_KEY||''))handCards=sortCardsTarneeb(handCards);let phase=$('#phaseTitle');if(phase)phase.textContent=st.phase==='bidding'?'مرحلة الطلب':st.phase==='choose_trump'?'اختيار الطرنيب':st.phase==='playing'?'اللعب جارٍ':st.phase==='finished'?'انتهت الجولة':'بانتظار البداية';if(st.phase==='finished' && !window._lastWinFx){window._lastWinFx=Date.now();try{let fx=document.createElement('div');fx.className='win-effect-overlay';fx.textContent='🏆';document.body.appendChild(fx);setTimeout(()=>fx.remove(),2800);WarqnaSound.play('win')}catch(e){}}let hand=$('#myHand');if(hand){let legal=Array.isArray(st.legal_cards)?st.legal_cards:null;hand.innerHTML=handCards.length?handCards.map((c,i)=>{let illegal=legal&&legal.length&&st.turn===window.MY_PLAYER_KEY&&!legal.includes(c);return `<button draggable="true" ondragstart="dragCardStart(event,'${escapeHtml(c)}')" data-card="${escapeHtml(c)}" class="card ${suitClass(c)} ${window.MY_CARD_BACK||''} ${selectedCards.has(c)?'selected-card':''} ${illegal?'illegal-card':''}" style="--i:${i};--n:${handCards.length}" onclick="${illegal?'showNotice(\'يجب اتباع نوع الورقة المطلوبة إذا كان موجودًا في يدك.\')':'cardClicked(\''+escapeHtml(c)+'\')'}">${labelCard(c)}</button>`}).join(''):'<div class="no-cards">لا توجد أوراق بعد.</div>';}let melds=$('#myMelds');if(melds){let ms=(st.melds&&st.melds[window.MY_PLAYER_KEY])||[];melds.innerHTML='<div class="meld-slots-grid">'+[0,1,2].map(i=>`<div class="meld-slot ${ms[i]?'filled':''}" data-cards='${JSON.stringify(ms[i]||[])}'>${ms[i]?ms[i].map(labelCard).join(' '):'مجموعة '+(i+1)}</div>`).join('')+'</div>'}let trick=$('#tableTrick');if(trick){if(gt==='domino')trick.innerHTML='<div class="domino-board">'+(st.board||[]).map(t=>`<button class="domino-tile">${labelCard(t)}</button>`).join('')+'</div>';else if(gt==='backgammon')trick.innerHTML='<div class="dice-box">النرد: '+((st.dice||[]).join(' - ')||'لم يُرم بعد')+'</div>';else trick.innerHTML=Object.entries(st.trick||{}).map(([p,c])=>`<div class="played-card ${suitClass(c)}"><small>${escapeHtml(p.replace('user:','لاعب ').replace('bot:','بوت '))}</small>${labelCard(c)}</div>`).join('')}$$('.seat-played-card').forEach(x=>x.innerHTML='');Object.entries(st.trick||{}).forEach(([p,c])=>{let el=document.querySelector(`.seat-played-card[data-player-key="${CSS.escape(p)}"]`);if(el)el.innerHTML=`<div class="played-card tiny ${suitClass(c)}">${labelCard(c)}</div>`});let mini=$('#lastTrickMini');if(mini){let last=st.last_trick||{};mini.classList.toggle('hidden',!Object.keys(last).length);let d=mini.querySelector('div');if(d)d.innerHTML=Object.values(last).map(c=>`<span class="mini-card-played ${suitClass(c)}">${labelCard(c)}</span>`).join('')}let scoreA=$('#scoreA'),scoreB=$('#scoreB'),tricksA=$('#tricksA'),tricksB=$('#tricksB'),bid=$('#currentBid'),trump=$('#currentTrump'),log=$('#gameLog'),last=$('#lastAction');if(scoreA)scoreA.textContent=st.score?.teamA??0;if(scoreB)scoreB.textContent=st.score?.teamB??0;if(tricksA)tricksA.textContent=st.round_tricks?.teamA??0;if(tricksB)tricksB.textContent=st.round_tricks?.teamB??0;if(bid)bid.textContent=st.bid?(st.bid.value+' - '+(st.bid.team==='teamA'?'الفريق A':'الفريق B')):'لا يوجد';if(trump)trump.textContent=suitName(st.trump);if(log)log.innerHTML=(st.messages||[]).slice(-8).map(m=>`<div>${escapeHtml(m)}</div>`).join('');if(last&&(st.messages||[]).length)last.textContent=st.messages[st.messages.length-1];$$('#actionPanel [data-action],#actionPanel .meld-btn,#actionPanel .sort-btn').forEach(btn=>{let a=btn.dataset.action,show=false;if(st.phase==='bidding'){show=(a==='bid'||a==='pass');if(a==='bid'&&gt!=='estimation'&&parseInt(btn.dataset.value||0,10)<7)show=false;if(a==='pass'&&gt==='estimation')show=false}if(st.phase==='choose_trump')show=(a==='choose_trump');if(st.phase==='choose_contract')show=(a==='choose_contract');if(st.phase==='baloot_bid')show=(a==='pass'||a==='choose_sun'||a==='choose_hokm');if(st.phase==='playing'&&['hand','banakil','konkan'].includes(gt))show=(a==='draw_deck'||a==='draw_discard'||btn.classList.contains('meld-btn')||btn.classList.contains('sort-btn'));if(st.phase==='playing'&&gt==='domino')show=(a==='draw'||a==='pass');if(st.phase==='playing'&&gt==='backgammon')show=(a==='roll'||a==='move_prompt'||a==='pass');btn.style.display=show?'inline-grid':'none';if(a==='bid'&&st.bid)btn.disabled=parseInt(btn.dataset.value||0,10)<=parseInt(st.bid.value||6,10);else btn.disabled=false});let p=$('.pass-btn');if(p)p.textContent=document.documentElement.lang==='ar'?'تمرير':'Pass';$$('.seat-profile').forEach(x=>x.classList.toggle('is-live-turn',st.turn&&x.dataset.playerKey===st.turn));document.body.classList.toggle('is-hand-like',['hand','banakil','konkan'].includes(gt));if(st.turn&&String(st.turn).startsWith('bot:'))setTimeout(roomSyncNow,450)};
  window.roomSyncNow=async function(){if(!window.ROOM_SYNC_URL)return;try{let r=await fetch(window.ROOM_SYNC_URL,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}),j=await r.json();if(j.seats)renderSeats(j.seats);if(j.state)renderState(j.state)}catch(e){}};
  
  window.renderSeats=function(seats){
    if(!seats)return;
    if(Array.isArray(seats)){
      seats.forEach(s=>{let box=document.querySelector(`.player-seat[data-seat="${s.seat}"]`);if(!box)return;box.innerHTML=seatHtml(s);});
      return;
    }
    Object.entries(seats).forEach(([seat,html])=>{let box=document.querySelector(`.player-seat[data-seat="${seat}"]`);if(box)box.innerHTML=html})
  };
  function seatHtml(s){
    let flag=s.flag_url?`<img class="flag-img" src="${escapeHtml(s.flag_url)}" title="${escapeHtml(s.country||'')}">`: '🤖';
    let click=s.user_id?`onclick="openProfile(${parseInt(s.user_id,10)})"`:'';
    let join=false?`<form class="join-bot-seat" method="post" action="/room/${window.ROOM_CODE}/join"><input type="hidden" name="_token" value="${window.CSRF}"><input type="hidden" name="seat" value="${escapeHtml(s.seat)}"><button type="submit">اجلس مكان البوت</button></form>`:'';
    return `<button class="seat-profile player-glow ${escapeHtml(s.frame||'glow-ocean')} ${s.is_bot?'bot-seat':'human-seat'}" data-player-key="${escapeHtml(s.key)}" style="--player-color:${escapeHtml(s.color||'#facc15')}" ${click}><span class="player-ring"><img src="${escapeHtml(s.avatar||'/assets/avatars/default.svg')}"></span><span class="player-name" style="color:${escapeHtml(s.color||'#facc15')}">${escapeHtml(s.name||'لاعب')}</span><small>${flag} ${escapeHtml(s.country||'')} • ${escapeHtml(s.seat||'')}</small></button>${join}<div class="seat-played-card" data-player-key="${escapeHtml(s.key)}"></div>`;
  }
  async function startGameAjax(form){try{let r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)}),j=await r.json();if(j.ok===false){showNotice(j.message||'تعذر بدء اللعبة');return}if(j.seats)renderSeats(j.seats);if(j.state)renderState(j.state);form.remove();showNotice(j.message||'تم بدء اللعبة');}catch(e){showNotice('تعذر بدء اللعبة الآن. تأكد أن الغرفة صحيحة وأنك صاحب الغرفة أو أدمن.')}}
  let lastTurn=null,turnStarted=Date.now();setInterval(()=>{let st=window.LAST_STATE;if(!st)return;if(st.turn!==lastTurn){lastTurn=st.turn;turnStarted=Date.now()}let el=$('#turnTimer');if(el){let sec=Number(window.ROOM_TURN_TIMEOUT||st.turn_timeout_seconds||7);let left=Math.max(0,Math.ceil(sec-(Date.now()-turnStarted)/1000));el.textContent=left}if(st.turn===window.MY_PLAYER_KEY&&Date.now()-turnStarted>Number(window.ROOM_TURN_TIMEOUT||7)*1000&&window.ROOM_TIMEOUT_URL){turnStarted=Date.now()+999999;fetch(window.ROOM_TIMEOUT_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}).then(r=>r.json()).then(j=>{if(j.state)renderState(j.state);if(j.message)showNotice(j.message)}).catch(()=>{})}},1000);

  document.addEventListener('DOMContentLoaded',()=>{WarqnaSound.setIcon();makeDraggable($('#chatDock'));renderEmojiPalette();initStore();if(window.INITIAL_STATE)renderState(Object.assign({},window.INITIAL_STATE,{hand:window.INITIAL_HAND||[]}));if($('#chatDock'))setChatMode(window.CHAT_HAS_ROOM?'room':'friends');let sel=$('select[name="country_code"]');if(sel){updateCountryPreview(sel);sel.addEventListener('change',()=>updateCountryPreview(sel));}});
  if(window.ROOM_CODE){try{const s=document.createElement('script');s.src='http://localhost:4000/socket.io/socket.io.js';s.onload=()=>{window.socket=io('http://localhost:4000');socket.emit('join_room',{room:ROOM_CODE,name:document.body.dataset.user||'player'});socket.on('state',renderState);socket.on('chat_message',m=>{appendChat(m,false);WarqnaSound.message()});socket.on('table_action',a=>{let box=$('#lastAction');if(box)box.textContent=(a.player||'لاعب')+' لعب '+(a.card||a.action||'حركة')})};document.body.appendChild(s)}catch(e){}}
})();

// v84 additions: language selector, room create ajax, dynamic theme drawer, big emoji splash.
(function(){
  const dict={
    en:{all_games:'All games ▾',rules:'Game rules',store:'Store',clubs:'Clubs',tournaments:'Tournaments',settings:'Settings',contact:'Contact us',player_search:'Player search',my_profile:'My profile',about:'About',admin:'Admin',pasha:'Pasha',days:'days',logout:'Logout',choose_game:'Choose game',choose_theme:'Choose theme',site_language:'Site language',chat_center:'Chat center',game_chat:'Game chat',friends:'Friends',search:'Search',send:'Send'},
    fr:{all_games:'Tous les jeux ▾',rules:'Règles',store:'Boutique',clubs:'Clubs',tournaments:'Tournois',settings:'Paramètres',contact:'Contact',player_search:'Recherche joueurs',my_profile:'Mon profil',about:'À propos',admin:'Admin',pasha:'Pacha',days:'jours',logout:'Sortie',choose_game:'Choisir un jeu',choose_theme:'Choisir le thème',site_language:'Langue',chat_center:'Centre de chat',game_chat:'Chat du jeu',friends:'Amis',search:'Recherche',send:'Envoyer'},
    tr:{all_games:'Tüm oyunlar ▾',rules:'Kurallar',store:'Mağaza',clubs:'Kulüpler',tournaments:'Turnuvalar',settings:'Ayarlar',contact:'İletişim',player_search:'Oyuncu ara',my_profile:'Profilim',about:'Hakkında',admin:'Yönetim',pasha:'Paşa',days:'gün',logout:'Çıkış',choose_game:'Oyun seç',choose_theme:'Tema seç',site_language:'Dil',chat_center:'Sohbet merkezi',game_chat:'Oyun sohbeti',friends:'Arkadaşlar',search:'Ara',send:'Gönder'},
    de:{all_games:'Alle Spiele ▾',rules:'Regeln',store:'Shop',clubs:'Clubs',tournaments:'Turniere',settings:'Einstellungen',contact:'Kontakt',player_search:'Spieler suchen',my_profile:'Mein Profil',about:'Über uns',admin:'Admin',pasha:'Pascha',days:'Tage',logout:'Abmelden',choose_game:'Spiel wählen',choose_theme:'Theme wählen',site_language:'Sprache',chat_center:'Chat-Zentrum',game_chat:'Spielchat',friends:'Freunde',search:'Suche',send:'Senden'},
    es:{all_games:'Todos los juegos ▾',rules:'Reglas',store:'Tienda',clubs:'Clubes',tournaments:'Torneos',settings:'Ajustes',contact:'Contacto',player_search:'Buscar jugador',my_profile:'Mi perfil',about:'Acerca de',admin:'Admin',pasha:'Pasha',days:'días',logout:'Salir',choose_game:'Elegir juego',choose_theme:'Elegir tema',site_language:'Idioma',chat_center:'Centro de chat',game_chat:'Chat del juego',friends:'Amigos',search:'Buscar',send:'Enviar'},
    ar:{all_games:'كل الألعاب ▾',rules:'قوانين الألعاب',store:'المتجر',clubs:'النوادي',tournaments:'المسابقات',settings:'الإعدادات',contact:'اتصل بنا',player_search:'بحث اللاعبين',my_profile:'بروفايلي',about:'حول',admin:'الإدارة',pasha:'باشا',days:'يوم',logout:'خروج',choose_game:'اختر لعبة',choose_theme:'اختر الثيم',site_language:'لغة الموقع',chat_center:'مركز الدردشة',game_chat:'دردشة اللعبة',friends:'الأصدقاء',search:'بحث',send:'إرسال'}
  };
  const exactMap={en:{'كل الألعاب':'All games','المتجر':'Store','النوادي':'Clubs','المسابقات':'Tournaments','قوانين الألعاب':'Game rules','الإعدادات':'Settings','إرسال':'Send','شراء':'Buy','تفعيل':'Activate','معاينة':'Preview'},fr:{'كل الألعاب':'Tous les jeux','المتجر':'Boutique','النوادي':'Clubs','المسابقات':'Tournois','قوانين الألعاب':'Règles','الإعدادات':'Paramètres','إرسال':'Envoyer','شراء':'Acheter','تفعيل':'Activer','معاينة':'Aperçu'},tr:{'كل الألعاب':'Tüm oyunlar','المتجر':'Mağaza','النوادي':'Kulüpler','المسابقات':'Turnuvalar','قوانين الألعاب':'Kurallar','الإعدادات':'Ayarlar','إرسال':'Gönder','شراء':'Satın al','تفعيل':'Etkinleştir','معاينة':'Önizleme'},de:{'كل الألعاب':'Alle Spiele','المتجر':'Shop','النوادي':'Clubs','المسابقات':'Turniere','قوانين الألعاب':'Regeln','الإعدادات':'Einstellungen','إرسال':'Senden','شراء':'Kaufen','تفعيل':'Aktivieren','معاينة':'Vorschau'},es:{'كل الألعاب':'Todos los juegos','المتجر':'Tienda','النوادي':'Clubes','المسابقات':'Torneos','قوانين الألعاب':'Reglas','الإعدادات':'Ajustes','إرسال':'Enviar','شراء':'Comprar','تفعيل':'Activar','معاينة':'Vista previa'}};
  function translateLoose(lang){ if(lang==='ar') return; const m=exactMap[lang]||{}; document.querySelectorAll('button,a,label,h1,h2,h3,small,span').forEach(el=>{ if(el.children.length>2) return; const t=(el.textContent||'').trim(); if(m[t]) el.textContent=m[t]; }); }
  window.setWarqnaLang=function(lang){localStorage.warqnaLang=lang;document.documentElement.lang=lang;document.documentElement.dir=lang==='ar'?'rtl':'ltr';Object.entries(dict[lang]||dict.ar).forEach(([k,v])=>document.querySelectorAll('[data-i18n="'+k+'"]').forEach(el=>el.textContent=v)); translateLoose(lang); if(window.WarqnaSound)WarqnaSound.ui();};
  document.addEventListener('DOMContentLoaded',()=>{setWarqnaLang(localStorage.warqnaLang||document.documentElement.lang||'ar');});
  document.addEventListener('submit',async e=>{let f=e.target;if(!f.matches('form[data-ajax-room]'))return;e.preventDefault();try{let r=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF||'',Accept:'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(f)});let j=await r.json().catch(()=>({ok:false,message:'تعذر إنشاء الغرفة الآن.'}));if(j.ok&&j.url){showNotice(j.message||'تم إنشاء الغرفة.');setTimeout(()=>location.href=j.url,450)}else showNotice(j.message||'لا يمكن إنشاء الغرفة الآن. تأكد من عدد المقاعد ونوع اللعبة، أو غادر الغرفة الحالية أولاً.');}catch(err){showNotice('تعذر إنشاء الغرفة الآن. تأكد أنك داخل مجلد النسخة الجديدة وأنك شغّلت setup-windows ثم reset-database-windows. إن استمر الخطأ أرسل صورة الرسالة.')}});
  const oldSendEmoji=window.sendEmojiChat; window.sendEmojiChat=function(e){let splash=document.createElement('div');splash.className='big-emoji-splash';splash.textContent=e;document.body.appendChild(splash);setTimeout(()=>splash.remove(),5000); if(window.WarqnaSound){const code=(e||'').codePointAt(0)||900; WarqnaSound.play(code%2?'notify':'message');} if(oldSendEmoji)return oldSendEmoji(e);};
})();

// v86: persisted preferences, expanded language dictionary, dynamic chat/profile fixes.
(function(){
  const more={
    en:{'إنشاء الغرفة':'Create room','نوع اللعبة':'Room type','عامة':'Public','خاصة':'Private','لعبة صوتية':'Voice game','عدد المقاعد':'Seats','سرعة اللعب':'Game speed','أقل مستوى للدخول':'Minimum level','نهاية اللعبة':'Game target','المتصدرون':'Leaders','غرف':'Rooms','شراء الآن':'Buy now','مشترياتي':'My items','إضافة صديق':'Add friend','تعديل البروفايل':'Edit profile','المستوى التالي':'Next level','باقي':'Remaining'},
    fr:{'إنشاء الغرفة':'Créer une salle','نوع اللعبة':'Type de salle','عامة':'Publique','خاصة':'Privée','لعبة صوتية':'Jeu vocal','عدد المقاعد':'Places','سرعة اللعب':'Vitesse','أقل مستوى للدخول':'Niveau minimum','نهاية اللعبة':'Objectif','المتصدرون':'Classement','غرف':'Salles','شراء الآن':'Acheter','مشترياتي':'Mes objets','إضافة صديق':'Ajouter ami','تعديل البروفايل':'Modifier profil','المستوى التالي':'Niveau suivant','باقي':'Restant'},
    tr:{'إنشاء الغرفة':'Oda oluştur','نوع اللعبة':'Oda tipi','عامة':'Genel','خاصة':'Özel','لعبة صوتية':'Sesli oyun','عدد المقاعد':'Koltuklar','سرعة اللعب':'Hız','أقل مستوى للدخول':'En düşük seviye','نهاية اللعبة':'Hedef','المتصدرون':'Liderler','غرف':'Odalar','شراء الآن':'Satın al','مشترياتي':'Eşyalarım','إضافة صديق':'Arkadaş ekle','تعديل البروفايل':'Profili düzenle','المستوى التالي':'Sonraki seviye','باقي':'Kalan'},
    de:{'إنشاء الغرفة':'Raum erstellen','نوع اللعبة':'Raumtyp','عامة':'Öffentlich','خاصة':'Privat','لعبة صوتية':'Sprachspiel','عدد المقاعد':'Sitze','سرعة اللعب':'Tempo','أقل مستوى للدخول':'Mindestlevel','نهاية اللعبة':'Ziel','المتصدرون':'Bestenliste','غرف':'Räume','شراء الآن':'Kaufen','مشترياتي':'Meine Items','إضافة صديق':'Freund hinzufügen','تعديل البروفايل':'Profil bearbeiten','المستوى التالي':'Nächstes Level','باقي':'Verbleibend'},
    es:{'إنشاء الغرفة':'Crear sala','نوع اللعبة':'Tipo de sala','عامة':'Pública','خاصة':'Privada','لعبة صوتية':'Juego de voz','عدد المقاعد':'Asientos','سرعة اللعب':'Velocidad','أقل مستوى للدخول':'Nivel mínimo','نهاية اللعبة':'Meta','المتصدرون':'Líderes','غرف':'Salas','شراء الآن':'Comprar','مشترياتي':'Mis objetos','إضافة صديق':'Añadir amigo','تعديل البروفايل':'Editar perfil','المستوى التالي':'Siguiente nivel','باقي':'Restante'}
  };
  const oldLang=window.setWarqnaLang;
  window.setWarqnaLang=function(lang){ oldLang&&oldLang(lang); const map=more[lang]||{}; if(lang!=='ar'){document.querySelectorAll('button,a,label,h1,h2,h3,small,span,p').forEach(el=>{ if(el.children.length>1) return; const t=(el.textContent||'').trim(); if(map[t]) el.textContent=map[t]; });} try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({lang})}); }catch(e){} };
  const oldTheme=window.setSiteTheme;
  window.setSiteTheme=function(theme){ oldTheme&&oldTheme(theme); try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','Content-Type':'application/json'},body:JSON.stringify({theme})}); }catch(e){} };
  document.addEventListener('click',e=>{ const btn=e.target.closest('[data-chat-reopen]'); if(btn){document.querySelector('#chatDock')?.classList.remove('closed','minimized'); btn.remove();} });
  const oldShowNotice=window.showNotice; window.showNotice=function(message){ oldShowNotice(message); const dlg=document.querySelector('#confirmDialog .confirm-card'); if(dlg) dlg.style.maxWidth='min(460px,92vw)'; };
  const oldToggle=window.toggleChatClose; window.toggleChatClose=function(){ const dock=document.querySelector('#chatDock'); if(!dock)return; dock.classList.add('closed'); let b=document.querySelector('[data-chat-reopen]'); if(!b){b=document.createElement('button'); b.className='chat-fab'; b.setAttribute('data-chat-reopen','1'); b.textContent='💬'; document.body.appendChild(b);} };
})();


// v88 Real Competition Upgrade: stable create-room, compact profile, advanced chat, live translation/theme.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
 const headers=(json=false)=>json?{'X-CSRF-TOKEN':window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}:{'X-CSRF-TOKEN':window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json','X-Requested-With':'XMLHttpRequest'};
 window.showNotice=window.showNotice||function(message){alert(message)};
 function toast(msg){ if(window.showNotice) window.showNotice(msg); else alert(msg); }
 // Capture create room before older handlers to avoid duplicate fetch and generic errors.
 document.addEventListener('submit', async function(e){
   const f=e.target; if(!f.matches('form[data-ajax-room]')) return; e.preventDefault(); e.stopImmediatePropagation();
   const btn=f.querySelector('button[type="submit"]'); const old=btn?btn.textContent:''; if(btn){btn.disabled=true;btn.textContent='جاري إنشاء الغرفة...'}
   try{ const res=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(f)});
     let j; try{j=await res.json()}catch(_){j={ok:false,message:'تعذر قراءة رد السيرفر. شغّل reset-database-windows.bat ثم start-windows.bat.'}}
     if(j.ok && j.url){ toast(j.message||'تم إنشاء الغرفة بنجاح'); setTimeout(()=>{location.href=j.url},300); return; }
     toast(j.message||'لا يمكن إنشاء الغرفة الآن. تأكد من قاعدة البيانات وعدد المقاعد ونوع اللعبة.');
   }catch(err){ toast('فشل الاتصال أثناء إنشاء الغرفة. تأكد من تشغيل Laravel ومن وجودك داخل النسخة الجديدة ثم أعد المحاولة.'); }
   finally{ if(btn){btn.disabled=false;btn.textContent=old||'إنشاء الغرفة'} }
 }, true);
 // Profile modal override: compact, centered, never navigates.
 window.openProfile=async function(id){ const m=$('#profileModal'); if(!m) return; m.classList.remove('hidden'); m.innerHTML='<button class="modal-x" type="button">×</button><div class="compact-profile-card"><b>جاري تحميل البروفايل...</b></div>'; $('.modal-x',m)?.addEventListener('click',()=>m.classList.add('hidden')); if(!id){m.innerHTML='<button class="modal-x" type="button">×</button><div class="compact-profile-card"><h2>🤖 بوت احترافي</h2><p>لاعب آلي يحافظ على استمرار اللعبة.</p></div>'; $('.modal-x',m)?.addEventListener('click',()=>m.classList.add('hidden')); return;} try{ const r=await fetch('/profile/'+id,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}}); const t=await r.text(); m.innerHTML='<button class="modal-x" type="button">×</button>'+t; $('.modal-x',m)?.addEventListener('click',()=>m.classList.add('hidden')); }catch(e){m.innerHTML='<button class="modal-x" type="button">×</button><div class="compact-profile-card">تعذر تحميل البروفايل الآن.</div>'; $('.modal-x',m)?.addEventListener('click',()=>m.classList.add('hidden'));} };
 document.addEventListener('click',e=>{ if(e.target.id==='profileModal') e.target.classList.add('hidden'); }); document.addEventListener('keydown',e=>{ if(e.key==='Escape') $('#profileModal')?.classList.add('hidden'); });
 // Country live preview on edit page.
 window.updateCountryPreview=function(sel){ const opt=sel?.selectedOptions?.[0]; const flag=opt?.dataset.flag||''; const name=opt?.dataset.name||opt?.textContent||''; $$('[data-country-preview],#countryPreviewBig').forEach(el=>{el.innerHTML=(flag?`<img class="flag-img flag-small" src="${flag}" alt="flag"> `:'')+`<b>${name}</b>`;}); };
 // Chat improvements.
 window.maximizeChat=function(){const d=$('#chatDock'); if(!d)return; d.classList.remove('minimized','closed'); d.style.width='min(620px,92vw)'; d.style.height='min(700px,78vh)'; $('#chatReopen')?.classList.add('hidden');};
 window.minimizeChat=function(){const d=$('#chatDock'); if(!d)return; d.classList.add('minimized'); $('#chatReopen')?.classList.remove('hidden');};
 window.closeChat=function(){const d=$('#chatDock'); if(!d)return; d.classList.add('closed'); $('#chatReopen')?.classList.remove('hidden');};
 window.reopenChat=function(){const d=$('#chatDock'); if(!d)return; d.classList.remove('closed','minimized'); $('#chatReopen')?.classList.add('hidden');};
 // Translation dictionary extension.
 const T={
  en:{'إنشاء لعبة':'Create game','إنشاء الغرفة':'Create room','نوع اللعبة':'Room type','عامة':'Public','خاصة':'Private','لعبة صوتية':'Voice game','عدد المقاعد':'Seats','سرعة اللعب':'Speed','أقل مستوى للدخول':'Minimum level','نهاية اللعبة':'Target score','دردشة اللعبة':'Game chat','الأصدقاء':'Friends','بحث':'Search','إرسال':'Send','تعديل البروفايل':'Edit profile','المتجر':'Store','النوادي':'Clubs','المسابقات':'Tournaments','قوانين الألعاب':'Rules','مشترياتي':'My items','معاينة':'Preview','شراء الآن':'Buy now','تفعيل':'Activate'},
  fr:{'إنشاء لعبة':'Créer un jeu','إنشاء الغرفة':'Créer une salle','نوع اللعبة':'Type','عامة':'Publique','خاصة':'Privée','لعبة صوتية':'Jeu vocal','عدد المقاعد':'Places','سرعة اللعب':'Vitesse','أقل مستوى للدخول':'Niveau min.','نهاية اللعبة':'Score cible','دردشة اللعبة':'Chat du jeu','الأصدقاء':'Amis','بحث':'Recherche','إرسال':'Envoyer','تعديل البروفايل':'Modifier profil','المتجر':'Boutique','النوادي':'Clubs','المسابقات':'Tournois','قوانين الألعاب':'Règles','مشترياتي':'Mes objets','معاينة':'Aperçu','شراء الآن':'Acheter','تفعيل':'Activer'},
  tr:{'إنشاء لعبة':'Oyun oluştur','إنشاء الغرفة':'Oda oluştur','نوع اللعبة':'Tip','عامة':'Genel','خاصة':'Özel','لعبة صوتية':'Sesli oyun','عدد المقاعد':'Koltuk','سرعة اللعب':'Hız','أقل مستوى للدخول':'Min seviye','نهاية اللعبة':'Hedef','دردشة اللعبة':'Oyun sohbeti','الأصدقاء':'Arkadaşlar','بحث':'Ara','إرسال':'Gönder','تعديل البروفايل':'Profili düzenle','المتجر':'Mağaza','النوادي':'Kulüpler','المسابقات':'Turnuvalar','قوانين الألعاب':'Kurallar','مشترياتي':'Eşyalarım','معاينة':'Önizleme','شراء الآن':'Satın al','تفعيل':'Etkinleştir'},
  de:{'إنشاء لعبة':'Spiel erstellen','إنشاء الغرفة':'Raum erstellen','نوع اللعبة':'Typ','عامة':'Öffentlich','خاصة':'Privat','لعبة صوتية':'Sprachspiel','عدد المقاعد':'Sitze','سرعة اللعب':'Tempo','أقل مستوى للدخول':'Mindestlevel','نهاية اللعبة':'Ziel','دردشة اللعبة':'Spielchat','الأصدقاء':'Freunde','بحث':'Suche','إرسال':'Senden','تعديل البروفايل':'Profil bearbeiten','المتجر':'Shop','النوادي':'Clubs','المسابقات':'Turniere','قوانين الألعاب':'Regeln','مشترياتي':'Meine Items','معاينة':'Vorschau','شراء الآن':'Kaufen','تفعيل':'Aktivieren'},
  es:{'إنشاء لعبة':'Crear juego','إنشاء الغرفة':'Crear sala','نوع اللعبة':'Tipo','عامة':'Pública','خاصة':'Privada','لعبة صوتية':'Juego de voz','عدد المقاعد':'Asientos','سرعة اللعب':'Velocidad','أقل مستوى للدخول':'Nivel mínimo','نهاية اللعبة':'Meta','دردشة اللعبة':'Chat del juego','الأصدقاء':'Amigos','بحث':'Buscar','إرسال':'Enviar','تعديل البروفايل':'Editar perfil','المتجر':'Tienda','النوادي':'Clubes','المسابقات':'Torneos','قوانين الألعاب':'Reglas','مشترياتي':'Mis objetos','معاينة':'Vista previa','شراء الآن':'Comprar','تفعيل':'Activar'}
 };
 const oldLang=window.setWarqnaLang; window.setWarqnaLang=function(lang){ oldLang&&oldLang(lang); localStorage.warqnaLang=lang; document.documentElement.lang=lang; document.documentElement.dir=lang==='ar'?'rtl':'ltr'; const map=T[lang]||{}; if(lang!=='ar') $$('button,a,label,h1,h2,h3,span,small,option').forEach(el=>{ if(el.children.length) return; const k=(el.textContent||'').trim(); if(map[k]) el.textContent=map[k]; }); try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:headers(true),body:JSON.stringify({lang})}); }catch(e){} };
 const oldTheme=window.setSiteTheme; window.setSiteTheme=function(theme){ oldTheme&&oldTheme(theme); document.body.className=document.body.className.replace(/theme-[\w-]+/g,''); document.body.classList.add('theme-'+theme); document.body.dataset.theme=theme; localStorage.warqnaTheme=theme; try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:headers(true),body:JSON.stringify({theme})}); }catch(e){} };
 document.addEventListener('DOMContentLoaded',()=>{ const lang=localStorage.warqnaLang||document.documentElement.lang||'ar'; if(lang) window.setWarqnaLang(lang); const th=localStorage.warqnaTheme||document.body.dataset.theme; if(th) window.setSiteTheme(th); });
 // Store preview purchase direct: works in preview modal too.
 document.addEventListener('submit',async e=>{ const f=e.target; if(!f.matches('.preview-buy-form'))return; e.preventDefault(); e.stopImmediatePropagation(); try{ const r=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(f)}); const j=await r.json(); toast(j.message||'تم تنفيذ العملية'); if(j.ok!==false) setTimeout(()=>location.reload(),500); }catch(err){toast('تعذر الشراء الآن. تأكد من الرصيد أو أعد المحاولة.')} },true);
})();


// v91 upgrade fixes: draggable hand groups, persistent theme/lang, flexible chat, tournament controls.
(function(){
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>[...r.querySelectorAll(s)];
  function h(json=false){return json?{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}:{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','X-Requested-With':'XMLHttpRequest'}}
  window.dragCardStart=function(ev,card){ev.dataTransfer.setData('text/plain',card);};
  function enableMeldDrop(){ $$('.meld-slot').forEach(slot=>{slot.ondragover=e=>{e.preventDefault();slot.classList.add('drop-hover')};slot.ondragleave=()=>slot.classList.remove('drop-hover');slot.ondrop=e=>{e.preventDefault();slot.classList.remove('drop-hover');let card=e.dataTransfer.getData('text/plain'); if(card){ let arr=[];try{arr=JSON.parse(slot.dataset.cards||'[]')}catch(_){arr=[]} if(!arr.includes(card))arr.push(card); slot.dataset.cards=JSON.stringify(arr); if(!window.selectedCards) window.selectedCards=new Set(); window.selectedCards.add(card); slot.innerHTML='<b>المجموعة:</b> '+arr.map(labelCard).join(' '); slot.classList.add('filled'); } };});}
  const oldRender=window.renderState; window.renderState=function(st){ oldRender&&oldRender(st); if(st&&st.score_popup)showScorePopup(st.score_popup); enableMeldDrop(); const gt=(st?.game_type||window.GAME_KEY||''); document.body.classList.toggle('hand-game-active',['hand','banakil','konkan'].includes(gt)); document.body.classList.toggle('tarneeb-game-active',['tarneeb','tarneeb_400','tarneeb_41'].includes(gt||window.GAME_KEY)); const away=st?.away_players?.[window.MY_PLAYER_KEY]; $$('.away-status').forEach(x=>{x.classList.toggle('active',!!away); x.textContent=away?'🟡 أنت الآن في وضع الغائب، الكمبيوتر يلعب بدلك.':'🟢 أنت حاضر وتلعب بنفسك.'}); };
  // single source of truth for theme + language switching
  const baseSetTheme=window.setSiteTheme; window.setSiteTheme=function(theme){ if(!theme)return; document.body.className=document.body.className.replace(/theme-[\w-]+/g,'').trim(); document.body.classList.add('theme-'+theme); document.body.dataset.theme=theme; localStorage.warqnaTheme=theme; localStorage.siteTheme=theme; try{baseSetTheme&&baseSetTheme(theme)}catch(e){} try{if(window.PREF_URL)fetch(window.PREF_URL,{method:'POST',headers:h(true),body:JSON.stringify({theme})})}catch(e){} };
  const labels={
    en:{create_game:'Create new game',leave_game:'Leave game',game_type:'Game type',public:'Public',private:'Private',voice:'Voice game',game_chat:'Game chat',send:'Send',tokens:'Tokens',friends:'Friends',tournaments:'Tournaments',clubs:'Clubs',store:'Store',rules:'Game rules',profile:'Profile',edit_profile:'Edit profile'},
    fr:{create_game:'Créer un jeu',leave_game:'Quitter le jeu',game_type:'Type de jeu',public:'Public',private:'Privé',voice:'Jeu vocal',game_chat:'Chat du jeu',send:'Envoyer',tokens:'Jetons',friends:'Amis',tournaments:'Tournois',clubs:'Clubs',store:'Boutique',rules:'Règles',profile:'Profil',edit_profile:'Modifier profil'},
    tr:{create_game:'Yeni oyun oluştur',leave_game:'Oyundan çık',game_type:'Oyun tipi',public:'Genel',private:'Özel',voice:'Sesli oyun',game_chat:'Oyun sohbeti',send:'Gönder',tokens:'Token',friends:'Arkadaşlar',tournaments:'Turnuvalar',clubs:'Kulüpler',store:'Mağaza',rules:'Kurallar',profile:'Profil',edit_profile:'Profili düzenle'},
    de:{create_game:'Neues Spiel',leave_game:'Spiel verlassen',game_type:'Spieltyp',public:'Öffentlich',private:'Privat',voice:'Sprachspiel',game_chat:'Spielchat',send:'Senden',tokens:'Tokens',friends:'Freunde',tournaments:'Turniere',clubs:'Clubs',store:'Shop',rules:'Regeln',profile:'Profil',edit_profile:'Profil bearbeiten'},
    es:{create_game:'Crear juego',leave_game:'Salir del juego',game_type:'Tipo de juego',public:'Pública',private:'Privada',voice:'Juego de voz',game_chat:'Chat del juego',send:'Enviar',tokens:'Tokens',friends:'Amigos',tournaments:'Torneos',clubs:'Clubes',store:'Tienda',rules:'Reglas',profile:'Perfil',edit_profile:'Editar perfil'},
    ar:{create_game:'أنشئ لعبة جديدة',leave_game:'خروج من اللعبة',game_type:'نوع اللعبة',public:'عامة',private:'خاصة',voice:'لعبة صوتية',game_chat:'دردشة اللعبة',send:'إرسال',tokens:'توكنز',friends:'الأصدقاء',tournaments:'المسابقات',clubs:'النوادي',store:'المتجر',rules:'قوانين الألعاب',profile:'البروفايل',edit_profile:'تعديل البروفايل'}
  };
  const previousLang=window.setWarqnaLang; window.setWarqnaLang=function(lang){ lang=lang||'ar'; localStorage.warqnaLang=lang; document.documentElement.lang=lang; document.documentElement.dir=lang==='ar'?'rtl':'ltr'; try{previousLang&&previousLang(lang)}catch(e){} const map=labels[lang]||labels.ar; $$('[data-i18n-key]').forEach(el=>{const k=el.dataset.i18nKey;if(map[k])el.textContent=map[k]}); try{if(window.PREF_URL)fetch(window.PREF_URL,{method:'POST',headers:h(true),body:JSON.stringify({lang})})}catch(e){} };
  document.addEventListener('DOMContentLoaded',()=>{window.setSiteTheme(localStorage.warqnaTheme||document.body.dataset.theme||'royal');window.setWarqnaLang(localStorage.warqnaLang||document.documentElement.lang||'ar');enableMeldDrop();});
  function showScorePopup(p){let d=document.createElement('div');d.className='score-mini-popup';d.innerHTML='<b>+'+(p.xp||0)+' XP</b><small>+'+(p.tokens||0)+' توكنز</small>';document.body.appendChild(d);setTimeout(()=>d.remove(),2000);}
  // room type UX
  window.roomTypeChanged=function(sel){ const pass=$('#privatePasswordInput'), voice=$('#voiceRoomFlag'); if(pass)pass.classList.toggle('hidden',sel.value!=='private'); if(voice)voice.value=sel.value==='voice'?'1':'0'; };
  // tournament select visual bug fix
  window.updateTourHintV91=function(){ const st=Number($('#tourStages')?.value||1), seats=Number($('#tourSeats')?.value||2); const total=seats*Math.pow(2,st-1); let box=$('#tourHint'); if(box) box.innerHTML='عدد المقاعد الكلي المتوقع: <b>'+total+'</b> — المراحل: '+st; };
  document.addEventListener('change',e=>{ if(e.target.matches('#tourStages,#tourSeats,#tourGameSelect')) updateTourHintV91(); });
  // chat lightweight resize controls
  window.toggleChatSize=function(){const d=$('#chatDock'); if(!d)return; d.classList.toggle('chat-large');};
  document.addEventListener('dblclick',e=>{ if(e.target.closest('.chat-head')) toggleChatSize(); });
})();


// v91 presence: if player closes room page without pressing leave, mark temporarily disconnected so bot can continue.
(function(){
  function csrf(){return window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||''}
  function sendPresence(connected){ if(!window.ROOM_PRESENCE_URL) return; try{ fetch(window.ROOM_PRESENCE_URL,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({connected})}); }catch(e){} }
  document.addEventListener('DOMContentLoaded',()=>sendPresence(true));
  window.addEventListener('beforeunload',()=>{ if(!window.ROOM_PRESENCE_URL) return; try{ const data=new FormData(); data.append('_token',csrf()); data.append('connected','0'); navigator.sendBeacon(window.ROOM_PRESENCE_URL,data); }catch(e){} });
})();


// v93: page-aware chat, create-room modal, store/admin live previews, profile sizing.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 function safe(v){return window.escapeHtml?escapeHtml(v):String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]))}
 const oldSync=window.roomSyncNow;
 window.renderRoomMessages=function(messages){ if(!Array.isArray(messages)||window.CHAT_MODE!=='room') return; const body=$('#chatBody'); if(!body) return; const known=new Set($$('#chatBody .msg[data-mid]').map(x=>x.dataset.mid)); messages.forEach(m=>{ if(known.has(String(m.id))) return; body.insertAdjacentHTML('beforeend',`<div class="msg other" data-mid="${safe(m.id)}" style="color:${safe(m.color||'#fff')}"><b>${safe(m.name||'لاعب')}:</b> <span>${safe(m.body||'')}</span><small>${safe(m.time||'')}</small></div>`); }); body.scrollTop=body.scrollHeight; };
 window.roomSyncNow=async function(){ if(!window.ROOM_SYNC_URL){return oldSync&&oldSync()} try{ const r=await fetch(window.ROOM_SYNC_URL,{headers:{Accept:'application/json','X-Requested-With':'XMLHttpRequest'}}),j=await r.json(); if(j.seats&&window.renderSeats)renderSeats(j.seats); if(j.state&&window.renderState)renderState(j.state); if(j.room_messages)renderRoomMessages(j.room_messages); }catch(e){ if(oldSync) oldSync(); } };
 const oldAppend=window.appendChat; window.appendChat=function(m,me=false){ oldAppend&&oldAppend(m,me); const body=$('#chatBody'); if(body) body.scrollTop=body.scrollHeight; };
 const oldSet=window.setChatMode; window.setChatMode=function(mode){ oldSet&&oldSet(mode); if(mode==='room') setTimeout(()=>roomSyncNow&&roomSyncNow(),120); };
 const oldOpen=window.openProfile; window.openProfile=async function(id){ if(!id) return; await oldOpen(id); const m=$('#profileModal'); if(m){m.classList.toggle('self-view',String(id)===String(window.AUTH_ID)); m.classList.toggle('other-view',String(id)!==String(window.AUTH_ID));} };
 function updateAdminPreview(input){ const row=input.closest('.store-admin-row,.store-admin-create'); if(!row)return; const holder=row.querySelector('.admin-item-preview span,.preview-card-back'); if(!holder)return; const file=input.files&&input.files[0]; if(file){ const url=URL.createObjectURL(file); holder.style.backgroundImage=`url(${url})`; holder.style.backgroundSize='cover'; holder.style.backgroundPosition='center'; holder.textContent=''; holder.classList.add('custom-image'); }}
 document.addEventListener('change',e=>{ if(e.target.matches('.store-admin-row input[type=file],.store-admin-create input[type=file]')) updateAdminPreview(e.target); });
 document.addEventListener('input',e=>{ if(e.target.matches('.store-admin-row input[name="preview_icon"],.store-admin-create input[name="preview_icon"]')){ const row=e.target.closest('.store-admin-row,.store-admin-create'); const h=row?.querySelector('.admin-item-preview span,.preview-card-back'); if(h){h.style.backgroundImage='';h.textContent=e.target.value||'🎁';} } });
 document.addEventListener('DOMContentLoaded',()=>{ if(document.body.classList.contains('is-room-page') && $('#chatDock')){ $('#chatDock').classList.remove('minimized-on-load','chat-minimized','closed','hidden'); setChatMode('room'); setTimeout(()=>roomSyncNow&&roomSyncNow(),250);} if(document.body.classList.contains('is-store-page')){$('#chatDock')?.remove();$('#chatReopen')?.remove();} });
})();

// v94 real visible fixes: embedded room chat, admin/store previews, tournament video recording, responsive polish.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 const safe=v=>window.escapeHtml?escapeHtml(v):String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
 const headers=()=>({'X-CSRF-TOKEN':window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'','Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'});

 function pushEmbeddedMessage(m,me){
   const body=$('#gameRoomChatBody'); if(!body) return;
   if(body.querySelector('.muted')) body.innerHTML='';
   const mid=m.id?` data-mid="${safe(m.id)}"`:'';
   if(m.id && body.querySelector(`[data-mid="${CSS.escape(String(m.id))}"]`)) return;
   body.insertAdjacentHTML('beforeend',`<div class="game-chat-msg ${me?'me':'other'}"${mid} style="color:${safe(m.color||'#fff')}"><b>${safe(m.name||'لاعب')}</b><span>${safe(m.body||'')}</span><small>${safe(m.time||'')}</small></div>`);
   body.scrollTop=body.scrollHeight;
 }
 window.sendEmbeddedRoomChat=async function(e){
   e.preventDefault();
   const input=$('#gameRoomChatInput'); if(!input||!input.value.trim())return;
   const text=(window.cleanChatText?cleanChatText(input.value):input.value).trim(); if(!text)return;
   const color=getComputedStyle(document.body).getPropertyValue('--my-text-color')||'#fff';
   const temp={name:document.body.dataset.user||'أنا',body:text,color,time:new Date().toLocaleTimeString('ar-EG',{hour:'2-digit',minute:'2-digit'})};
   pushEmbeddedMessage(temp,true);
   try{const r=await fetch(window.ROOM_CHAT_URL,{method:'POST',headers:headers(),body:JSON.stringify({body:text})}); const j=await r.json(); if(j.ok&&j.message){/* sync will dedupe/load */}}
   catch(err){ if(window.showNotice)showNotice('تعذر إرسال رسالة الدردشة الآن.'); }
   input.value=''; window.WarqnaSound?.message?.();
 };
 const oldRenderRoomMessages=window.renderRoomMessages;
 window.renderRoomMessages=function(messages){
   oldRenderRoomMessages&&oldRenderRoomMessages(messages);
   if(Array.isArray(messages)) messages.forEach(m=>pushEmbeddedMessage(m,false));
 };
 const oldAppend=window.appendChat;
 window.appendChat=function(m,me=false){oldAppend&&oldAppend(m,me); if(document.body.classList.contains('is-room-page')) pushEmbeddedMessage(m,me)};

 // Stronger page-aware chat: never show global chat in store, always show room chat in room.
 document.addEventListener('DOMContentLoaded',()=>{
   if(document.body.classList.contains('is-store-page')){ $('#chatDock')?.remove(); $('#chatReopen')?.remove(); }
   if(document.body.classList.contains('is-room-page')){
     $('#chatDock')?.classList.remove('hidden','closed','chat-minimized','minimized');
     try{window.setChatMode&&setChatMode('room')}catch(e){}
     setTimeout(()=>window.roomSyncNow&&roomSyncNow(),300);
   }
 });

 // Create room modal: clone current right/left form exactly and bind ajax/start behavior.
 window.openCreateRoomModal=function(){
   const modal=$('#createRoomModal'), body=$('#createRoomModalBody'), form=$('#createRoomPanel form'); if(!modal||!body||!form)return;
   const clone=form.cloneNode(true); clone.classList.add('modal-create-room-form','pro-card'); clone.setAttribute('data-ajax-room','1');
   clone.querySelectorAll('[id]').forEach((el,i)=>el.id=el.id+'_modal_'+i);
   body.innerHTML=''; body.appendChild(clone); modal.classList.remove('hidden'); clone.querySelector('select,input,button')?.focus();
 };

 // Admin/store preview: restore card back, table and text color preview live.
 function filePreview(input, target){
   const f=input.files&&input.files[0]; if(!f||!target)return;
   const url=URL.createObjectURL(f); target.style.backgroundImage=`url(${url})`; target.style.backgroundSize='cover'; target.style.backgroundPosition='center'; target.textContent=''; target.classList.add('custom-image','preview-has-image');
 }
 function updateStoreAdminPreview(row){
   if(!row)return; const box=row.querySelector('.admin-item-preview span,.preview-card-back'); if(!box)return;
   const cat=row.querySelector('[name="category"]')?.value||'';
   const icon=row.querySelector('[name="preview_icon"]')?.value||row.querySelector('[name="css_class"]')?.value||'🎁';
   const color=row.querySelector('[name="color"]')?.value||'#facc15';
   const css=row.querySelector('[name="css_class"]')?.value||'';
   box.className=''; box.classList.add(cat==='card_back'?'card-back-preview':cat==='table'?'table-preview':'admin-preview-icon'); if(css) box.classList.add(...String(css).split(/\s+/).filter(Boolean));
   box.style.setProperty('--text-preview',color); box.style.setProperty('--orbit',color); box.style.color=color;
   if(cat==='text_color') box.innerHTML='<span style="color:'+safe(color)+'">رسالة تجريبية واضحة</span>';
   else if(cat==='name_color') box.innerHTML='<span class="color-orbit-preview" style="--orbit:'+safe(color)+'">Aa</span>';
   else if(!box.classList.contains('preview-has-image')) box.textContent=icon||'🎁';
 }
 document.addEventListener('input',e=>{ if(e.target.closest('.store-admin-row,.store-admin-create')) updateStoreAdminPreview(e.target.closest('.store-admin-row,.store-admin-create')); });
 document.addEventListener('change',e=>{ const row=e.target.closest('.store-admin-row,.store-admin-create'); if(row){ if(e.target.type==='file') filePreview(e.target,row.querySelector('.admin-item-preview span,.preview-card-back')); updateStoreAdminPreview(row); } });
 window.adminPreviewStoreItem=function(btn){
   const row=btn.closest('.store-admin-row,.store-admin-create'); if(!row)return;
   updateStoreAdminPreview(row); const name=row.querySelector('[name="name_ar"]')?.value||'عنصر جديد'; const price=row.querySelector('[name="price"]')?.value||'0'; const clone=row.querySelector('.admin-item-preview span,.preview-card-back')?.cloneNode(true);
   const wrap=document.createElement('div'); wrap.appendChild(clone||document.createTextNode('🎁'));
   showRichNotice(`<div class="store-preview-pop v94-preview"><div class="store-card deluxe"><div class="shop-icon big">${wrap.innerHTML}</div><h3>${safe(name)}</h3><p class="price">🪙 ${safe(price)}</p><button class="btn primary" type="button">معاينة زر الشراء</button></div></div>`);
 };
 document.addEventListener('DOMContentLoaded',()=>{$$('.store-admin-row,.store-admin-create').forEach(updateStoreAdminPreview);});

 // Store item public preview popup
 const oldPreview=window.previewStoreItem;
 window.previewStoreItem=function(btn){
   try{ if(oldPreview) return oldPreview(btn); }catch(e){}
   const card=btn?.closest?.('.store-card'); if(!card)return;
   const icon=card.querySelector('.shop-icon')?.innerHTML||'🎁', name=card.querySelector('h3')?.textContent||'عنصر', price=card.querySelector('.price')?.textContent||'';
   showRichNotice(`<div class="store-preview-pop v94-preview"><div class="store-card deluxe live"><div class="shop-icon big">${icon}</div><h3>${safe(name)}</h3><p>${safe(price)}</p><small>معاينة واضحة قبل الشراء أو التفعيل.</small></div></div>`);
 };

 // Tournament replay video generator (WebM) from saved event frames and final hands.
 window.playTournamentReplay=async function(record){
   const canvas=$('#tournamentReplayCanvas'); if(!canvas)return; const ctx=canvas.getContext('2d');
   const frames=window.TOURNAMENT_REPLAY_FRAMES||[]; const hands=window.TOURNAMENT_FINAL_HANDS||{}; const title=window.TOURNAMENT_REPLAY_TITLE||'تسجيل المسابقة'; const status=$('#replayStatus');
   const stream=record?canvas.captureStream(25):null; let chunks=[],rec=null;
   if(record && window.MediaRecorder){ rec=new MediaRecorder(stream,{mimeType:'video/webm;codecs=vp9'}); rec.ondataavailable=e=>{if(e.data.size)chunks.push(e.data)}; rec.onstop=()=>{const blob=new Blob(chunks,{type:'video/webm'}); const url=URL.createObjectURL(blob); const video=$('#tournamentReplayVideo'); if(video){video.src=url; video.classList.remove('hidden'); video.play().catch(()=>{});} const a=document.createElement('a'); a.href=url; a.download='warqna-tournament-replay.webm'; a.className='btn success'; a.textContent='تحميل فيديو التسجيل WebM'; status?.appendChild(a);}; rec.start();}
   function drawFrame(i,txt,sub){
     const g=ctx.createLinearGradient(0,0,1280,720); g.addColorStop(0,'#06251d'); g.addColorStop(1,'#111827'); ctx.fillStyle=g; ctx.fillRect(0,0,1280,720);
     ctx.fillStyle='rgba(255,255,255,.08)'; ctx.roundRect?.(80,80,1120,560,40); ctx.fill();
     ctx.fillStyle='#facc15'; ctx.font='bold 48px Cairo, Arial'; ctx.textAlign='center'; ctx.fillText(title,640,150);
     ctx.fillStyle='#ffffff'; ctx.font='bold 42px Cairo, Arial'; ctx.fillText(txt,640,310);
     ctx.fillStyle='#d1d5db'; ctx.font='28px Cairo, Arial'; ctx.fillText(sub||'',640,370);
     ctx.fillStyle='#22c55e'; ctx.font='24px Cairo, Arial'; ctx.fillText('Frame '+(i+1)+' / '+Math.max(1,frames.length+2),640,625);
   }
   const all=[{title:'بداية التسجيل',body:'مسابقة ورقنا — إعادة مشاهدة بالفيديو'},...frames,{title:'نهاية التسجيل',body:'عرض أوراق جميع اللاعبين بعد انتهاء المسابقة'}];
   for(let i=0;i<all.length;i++){ const f=all[i]; drawFrame(i,f.title||('حركة '+i), typeof f.body==='string'?f.body:JSON.stringify(f.payload||{})); if(status)status.textContent=(record?'جاري تسجيل الفيديو... ':'تشغيل العرض... ')+(i+1)+'/'+all.length; await new Promise(r=>setTimeout(r,record?900:700)); }
   if(Object.keys(hands).length){
     ctx.fillStyle='#07111f'; ctx.fillRect(0,0,1280,720); ctx.fillStyle='#facc15'; ctx.font='bold 46px Cairo, Arial'; ctx.textAlign='center'; ctx.fillText('أوراق اللاعبين النهائية',640,90); ctx.font='24px Cairo, Arial'; ctx.textAlign='right'; let y=150;
     Object.entries(hands).slice(0,8).forEach(([p,h])=>{ctx.fillStyle='#38bdf8'; ctx.fillText(p,1180,y); ctx.fillStyle='#fff'; ctx.fillText((h||[]).join('  '),1120,y+34); y+=76;}); await new Promise(r=>setTimeout(r,record?1500:1200));
   }
   if(rec){rec.stop(); if(status)status.textContent='تم توليد الفيديو. يمكنك تشغيله أو تحميله من الزر الظاهر.';} else if(status)status.textContent='انتهى العرض.';
 };
})();


// v95 visible fixes: logical timer, voice room UI, store/admin previews, hidden theme/lang, faster UX.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 const safe=v=>window.escapeHtml?escapeHtml(v):String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
 function clampTimeout(v){v=Number(v||7); if(!Number.isFinite(v))v=7; return Math.max(5,Math.min(10,v));}
 window.WARQNA_TURN_SECONDS = clampTimeout(window.ROOM_TURN_TIMEOUT || 7);
 window.ROOM_TURN_TIMEOUT = window.WARQNA_TURN_SECONDS;
 document.addEventListener('DOMContentLoaded',()=>{
   // Remove theme/language controls entirely from main interface as requested.
   $$('.theme-master-btn,.language-master-btn,#themePanel,#langPanel').forEach(x=>x.remove());
   document.body.classList.add('v95-fast-ui');
   const timer=$('#turnTimer'); if(timer) timer.textContent=String(window.WARQNA_TURN_SECONDS);
   document.querySelectorAll('select[name="speed"]').forEach(sel=>{
     sel.addEventListener('change',()=>{
       const cost=sel.closest('form')?.querySelector('.create-cost-preview');
       const sec=sel.value==='fast'?5:sel.value==='slow'?10:7;
       if(cost) cost.textContent='سرعة الدور '+sec+' ثوانٍ فقط.';
     });
   });
   // Store/admin tabs start clean and scroll less.
   document.querySelector('[data-store-tab]')?.click?.();
   document.querySelector('[data-store-admin-tab]')?.click?.();
   $$('form[action*="/rooms/"][action$="/leave"]').forEach(f=>f.removeAttribute('data-confirm'));
 });

 // Override renderState with timer clamp and score popup hook without breaking old renderer.
 const oldRender=window.renderState;
 window.renderState=function(st){
   if(st){ st.turn_timeout_seconds=clampTimeout(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT||7); window.ROOM_TURN_TIMEOUT=st.turn_timeout_seconds; window.WARQNA_TURN_SECONDS=st.turn_timeout_seconds; }
   oldRender&&oldRender(st);
   const timer=$('#turnTimer'); if(timer && st) timer.textContent=String(clampTimeout(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT));
   if(st?.score_popups && !window._v95ScorePopupShown){
     const mine=st.score_popups[window.MY_PLAYER_KEY];
     if(mine){ window._v95ScorePopupShown=true; const b=document.createElement('div'); b.className='score-pop-mini'; b.textContent=`+${mine.xp||0} XP  +${mine.tokens||0} 🪙`; document.body.appendChild(b); setTimeout(()=>b.remove(),2000); }
   }
   if(st?.closed_reason){ setTimeout(()=>{ if(window.GAME_KEY) location.href='/games/'+window.GAME_KEY+'/rooms'; },800); }
 };

 // Faster presence close handling.
 window.addEventListener('beforeunload',()=>{
   if(window.ROOM_PRESENCE_URL){ try{ navigator.sendBeacon(window.ROOM_PRESENCE_URL, new Blob([JSON.stringify({connected:false,_token:window.CSRF})],{type:'application/json'})); }catch(e){} }
 });
 const oldSync=window.roomSyncNow;
 window.roomSyncNow=async function(){
   if(oldSync) await oldSync();
   try{
     if(window.LAST_STATE?.closed_reason && window.GAME_KEY) location.href='/games/'+window.GAME_KEY+'/rooms';
   }catch(e){}
 };

 // Voice room browser layer: mic permission, mute, audio level, and UI sounds.
 window.WarqnaVoice=(function(){let stream=null,muted=false,ctx=null,analyser=null,raf=null;
   function status(t){const el=$('#voiceStatus'); if(el)el.textContent=t;}
   function drawLevel(){ if(!analyser)return; const data=new Uint8Array(analyser.frequencyBinCount); analyser.getByteFrequencyData(data); const avg=data.reduce((a,b)=>a+b,0)/Math.max(1,data.length); document.documentElement.style.setProperty('--voice-level', Math.min(1,avg/120)); raf=requestAnimationFrame(drawLevel); }
   return {
    async start(){ try{ stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true},video:false}); window.__warqnaVoiceStream=stream; ctx=new (window.AudioContext||window.webkitAudioContext)(); analyser=ctx.createAnalyser(); analyser.fftSize=128; ctx.createMediaStreamSource(stream).connect(analyser); muted=false; status('الميكروفون يعمل. عند النشر الخارجي يحتاج ربط WebSocket/STUN/TURN لسماع اللاعبين من أجهزة مختلفة.'); $('#voiceRoomPanel')?.classList.add('voice-on'); drawLevel(); window.WarqnaSound?.notify?.(); }catch(e){status('لم يتم السماح بالمايك أو المتصفح لا يدعم الصوت.'); window.showNotice?.('اسمح للمتصفح باستخدام الميكروفون لتفعيل اللعبة الصوتية.');} },
    mute(){ if(!stream)return status('شغّل المايك أولًا.'); muted=!muted; stream.getAudioTracks().forEach(t=>t.enabled=!muted); status(muted?'الميكروفون مكتوم.':'الميكروفون يعمل.'); window.WarqnaSound?.ui?.(); },
    stop(){ if(stream){stream.getTracks().forEach(t=>t.stop());stream=null;} if(ctx){ctx.close().catch(()=>{});ctx=null;} cancelAnimationFrame(raf); $('#voiceRoomPanel')?.classList.remove('voice-on'); status('تم إيقاف الميكروفون.'); window.WarqnaSound?.ui?.(); }
   };
 })();

 // Store previews: isolate table/card/text color previews so they never float into the wrong category.
 window.previewStoreItem=function(btn){
   const card=btn?.closest?.('.store-card'); if(!card)return;
   const name=card.querySelector('h3')?.textContent||'عنصر'; const price=card.querySelector('.price')?.textContent||''; const icon=card.querySelector('.shop-icon')?.innerHTML||'🎁';
   showRichNotice(`<div class="store-preview-pop v95-preview"><div class="store-card deluxe live"><div class="shop-icon big isolated-preview">${icon}</div><h3>${safe(name)}</h3><p>${safe(price)}</p><small>معاينة منفصلة لا تختلط مع الطاولة أو لون الدردشة.</small></div></div>`);
 };
 function updateColorTextPreviews(){
   $$('.category-text_color .store-card,.store-card[data-category="text_color"]').forEach(card=>{
     const span=card.querySelector('.text-color-preview'); if(!span)return; const c=getComputedStyle(span).getPropertyValue('--text-preview')||span.style.color||'#fff';
     span.innerHTML=`<b style="color:${safe(c.trim()||'#fff')}">كلمة تجريبية</b>`;
   });
 }
 document.addEventListener('DOMContentLoaded',updateColorTextPreviews);
 document.addEventListener('click',e=>{ if(e.target.closest('.text-color-preview')){ const el=e.target.closest('.text-color-preview'); showRichNotice(`<div class="chat-color-live-preview"><b style="color:${safe(getComputedStyle(el).color)}">كلمة تجريبية</b><p>هذا اللون سيظهر في دردشة اللعبة ودردشة الأصدقاء عند تفعيله.</p></div>`); } });

 // Admin store live preview stronger tabs.
 const oldAdmin=window.adminPreviewStoreItem;
 window.adminPreviewStoreItem=function(btn){
   const row=btn.closest('.store-admin-row,.store-admin-create'); if(!row){oldAdmin&&oldAdmin(btn);return;}
   const cat=row.querySelector('[name="category"]')?.value||''; const name=row.querySelector('[name="name_ar"]')?.value||'عنصر'; const price=row.querySelector('[name="price"]')?.value||'0'; const color=row.querySelector('[name="color"]')?.value||'#facc15'; const icon=row.querySelector('[name="preview_icon"]')?.value||'🎁';
   let visual=icon;
   if(cat==='table') visual='<span class="table-preview admin-big-table"></span>';
   if(cat==='card_back') visual='<span class="card-back-preview admin-big-card">🂠</span>';
   if(cat==='text_color') visual=`<span class="text-color-preview admin-text-color" style="--text-preview:${safe(color)}"><b style="color:${safe(color)}">كلمة تجريبية</b></span>`;
   showRichNotice(`<div class="store-preview-pop v95-preview"><div class="store-card deluxe"><div class="shop-icon big isolated-preview">${visual}</div><h3>${safe(name)}</h3><p class="price">🪙 ${safe(price)}</p><small>معاينة إدارية واضحة داخل تبويب ${safe(cat)}</small></div></div>`);
 };

 // Page transition polish.
 document.addEventListener('click',e=>{const a=e.target.closest('a[href]'); if(a && a.origin===location.origin && !a.href.includes('#') && !a.target){document.body.classList.add('page-leaving');}});
})();

// v96: final visible fixes layer - cache-proof UI, store/admin previews, timer clamp, WebRTC signaling hooks.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 const safe=v=>window.escapeHtml?escapeHtml(v):String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
 function clamp(v){v=Number(v||7);return v<=5?5:v>=10?10:7}
 function enableFirstTab(sel, panels, attr){const buttons=$$(sel); if(!buttons.length)return; buttons.forEach(btn=>btn.addEventListener('click',()=>{buttons.forEach(b=>b.classList.remove('active'));btn.classList.add('active');$$(panels).forEach(p=>p.classList.toggle('active',p.id===(attr+btn.dataset.storeTab||'') || p.id===(attr+btn.dataset.storeAdminTab||'')));})); if(!buttons.some(b=>b.classList.contains('active'))) buttons[0].click();}
 document.addEventListener('DOMContentLoaded',()=>{
   // Hard remove old theme/lang UI from any cached layout or injected HTML.
   $$('.theme-master-btn,.language-master-btn,#themePanel,#langPanel,.theme-panel,.lang-panel').forEach(el=>el.remove());
   document.body.classList.add('v96-real-visible');
   enableFirstTab('[data-store-tab]','.store-section','cat-');
   enableFirstTab('[data-store-admin-tab]','.admin-store-section','admin-store-');
   // Keep inventory reachable.
   $$('[data-store-tab="inventory"]').forEach(b=>b.addEventListener('click',()=>{$$('.store-section').forEach(p=>p.classList.remove('active')); $('#inventory')?.classList.add('active');}));
   // Timer always 5/7/10; never old huge values.
   window.ROOM_TURN_TIMEOUT=clamp(window.ROOM_TURN_TIMEOUT||7); window.WARQNA_TURN_SECONDS=window.ROOM_TURN_TIMEOUT; const t=$('#turnTimer'); if(t)t.textContent=String(window.ROOM_TURN_TIMEOUT);
   // Speed dropdown labels/cost.
   $$('select[name="speed"]').forEach(sel=>{function u(){const sec=sel.value==='fast'?5:sel.value==='slow'?10:7;const box=sel.closest('form')?.querySelector('.create-cost-preview');if(box)box.innerHTML='سرعة الدور <b>'+sec+'</b> ثوانٍ فقط.';} sel.addEventListener('change',u);u();});
   // Remove any confirm from leave/start buttons.
   $$('form[data-ajax-start]').forEach(f=>f.removeAttribute('data-confirm')); $$('form[action*="/leave"]').forEach(f=>f.dataset.confirm='هل تريد الخروج؟ تنبيه: إذا خرجت 3 مرات من نفس اللعبة لن تستطيع العودة لها مرة أخرى.');
   // Add click sounds to general buttons.
   $$('button,a.btn,.store-card button').forEach(el=>el.addEventListener('click',()=>{try{window.WarqnaSound?.ui?.()}catch(e){}}));
 });
 const oldRender=window.renderState;
 window.renderState=function(st){ if(st){st.turn_timeout_seconds=clamp(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT||7); window.ROOM_TURN_TIMEOUT=st.turn_timeout_seconds;} oldRender&&oldRender(st); const t=$('#turnTimer'); if(t)t.textContent=String(window.ROOM_TURN_TIMEOUT||7); if(st?.score_popup&&!window._scorePopupV96){window._scorePopupV96=true; const b=document.createElement('div');b.className='score-pop-mini';b.textContent='+'+(st.score_popup.xp||0)+' XP  +'+(st.score_popup.tokens||0)+' 🪙';document.body.appendChild(b);setTimeout(()=>b.remove(),2000)} if(st?.closed_reason && window.GAME_KEY){setTimeout(()=>location.href='/games/'+window.GAME_KEY+'/rooms',600);} };
 // Cleaner store preview: use the exact preview node only; no floating mixed elements.
 window.previewStoreItem=function(btn){const card=btn?.closest?.('.store-card'); if(!card)return; const name=card.querySelector('h3')?.textContent||'عنصر'; const price=card.querySelector('.price')?.textContent||''; const icon=card.querySelector('.shop-icon')?.innerHTML||'🎁'; showRichNotice(`<div class="store-preview-pop v96-preview"><div class="store-card deluxe" data-category="${safe(card.dataset.category||'')}"><div class="shop-icon isolated-preview">${icon}</div><h3>${safe(name)}</h3><p class="price">${safe(price)}</p><small>هذه معاينة منفصلة للمقتنى فقط بدون تداخل مع الطاولات أو ألوان الدردشة.</small></div></div>`)};
 document.addEventListener('click',e=>{const c=e.target.closest('.text-color-preview'); if(c){const col=c.style.color||getComputedStyle(c).color||'#fff'; showRichNotice(`<div class="chat-color-live-preview"><b style="color:${safe(col)}">كلمة تجريبية</b><p>سيظهر هذا اللون مباشرة في دردشة اللعبة ودردشة الأصدقاء بعد الشراء والتفعيل.</p></div>`)}});
 window.adminPreviewStoreItem=function(btn){const row=btn.closest('.store-admin-row,.store-admin-create'); if(!row)return; const cat=row.querySelector('[name="category"]')?.value || row.closest('.admin-store-section')?.id?.replace('admin-store-','') || ''; const name=row.querySelector('[name="name_ar"]')?.value||'عنصر'; const price=row.querySelector('[name="price"]')?.value||'0'; const color=row.querySelector('[name="color"]')?.value||'#facc15'; let visual='🎁'; const current=row.querySelector('.admin-item-preview .table-preview,.admin-item-preview .card-back-preview,.admin-item-preview .text-color-preview,.admin-item-preview .generic-admin-icon'); if(current) visual=current.outerHTML; if(cat==='text_color') visual=`<span class="text-color-preview" style="color:${safe(color)};--text-preview:${safe(color)}"><b style="color:${safe(color)}">كلمة تجريبية</b></span>`; showRichNotice(`<div class="store-preview-pop v96-preview"><div class="store-card deluxe" data-category="${safe(cat)}"><div class="shop-icon isolated-preview">${visual}</div><h3>${safe(name)}</h3><p class="price">🪙 ${safe(price)}</p><small>معاينة إدارية واضحة داخل تبويب ${safe(cat||'عام')}</small></div></div>`)};
 // Update admin preview when fields/files change.
 document.addEventListener('input',e=>{const row=e.target.closest('.store-admin-row,.store-admin-create'); if(!row)return; if(e.target.name==='color'){const p=row.querySelector('.text-color-preview'); if(p){p.style.color=e.target.value;p.style.setProperty('--text-preview',e.target.value); const b=p.querySelector('b'); if(b)b.style.color=e.target.value;}}});
 document.addEventListener('change',e=>{if(e.target.matches('input[type="file"][name="asset"]')){const row=e.target.closest('.store-admin-row,.store-admin-create'); const file=e.target.files?.[0]; if(!row||!file)return; const url=URL.createObjectURL(file); let target=row.querySelector('.admin-item-preview .table-preview,.admin-item-preview .card-back-preview,.preview-card-back'); if(target){target.classList.add('custom-image');target.style.backgroundImage=`url(${url})`;target.textContent='';}}});
 // Voice signaling through optional socket.io server on port 4000. Works on local network/server when npm run socket is active.
 window.WarqnaVoiceNet=(function(){let socket=null,stream=null,peers={}; const cfg={iceServers:[{urls:'stun:stun.l.google.com:19302'}]}; function box(){let b=$('#voicePeers'); if(!b){b=document.createElement('div');b.id='voicePeers';b.className='voice-peers';$('#voiceRoomPanel')?.appendChild(b);}return b} function note(t){const s=$('#voiceStatus'); if(s)s.textContent=t;}
   async function ensureScript(){if(window.io)return true; return await new Promise(res=>{const sc=document.createElement('script');sc.src=(location.protocol==='https:'?'https':'http')+'://'+location.hostname+':4000/socket.io/socket.io.js';sc.onload=()=>res(true);sc.onerror=()=>res(false);document.head.appendChild(sc);setTimeout(()=>res(!!window.io),2500);});}
   async function connect(){if(socket)return socket; const ok=await ensureScript(); if(!ok){note('الصوت المحلي يعمل، لكن خادم الصوت Socket غير مشغل. شغّل npm run socket للصوت بين اللاعبين.');return null;} socket=io((location.protocol==='https:'?'https':'http')+'://'+location.hostname+':4000',{transports:['websocket','polling']}); socket.on('connect',()=>{socket.emit('voice_join',{room:window.ROOM_CODE,name:document.body.dataset.user||'لاعب'});note('متصل بنظام الصوت. شغّل المايك للتحدث.');}); socket.on('voice_peer',async({id,name})=>{box().insertAdjacentHTML('beforeend',`<div class="voice-peer" data-peer="${safe(id)}">🎧 ${safe(name||id)}</div>`); if(stream) await callPeer(id);}); socket.on('voice_offer',async({from,offer})=>{await answerPeer(from,offer);}); socket.on('voice_answer',async({from,answer})=>{await peers[from]?.setRemoteDescription(new RTCSessionDescription(answer));}); socket.on('voice_ice',async({from,candidate})=>{try{await peers[from]?.addIceCandidate(new RTCIceCandidate(candidate));}catch(e){}}); socket.on('voice_leave',({id})=>{document.querySelector(`[data-peer="${CSS.escape(String(id))}"]`)?.remove(); peers[id]?.close?.(); delete peers[id];}); return socket;}
   function makePeer(id){const pc=new RTCPeerConnection(cfg); peers[id]=pc; if(stream)stream.getTracks().forEach(t=>pc.addTrack(t,stream)); pc.onicecandidate=e=>{if(e.candidate)socket?.emit('voice_ice',{room:window.ROOM_CODE,to:id,candidate:e.candidate});}; pc.ontrack=e=>{let a=document.getElementById('audio_'+id); if(!a){a=document.createElement('audio');a.id='audio_'+id;a.autoplay=true;document.body.appendChild(a);} a.srcObject=e.streams[0];}; return pc;}
   async function callPeer(id){const pc=makePeer(id); const offer=await pc.createOffer(); await pc.setLocalDescription(offer); socket?.emit('voice_offer',{room:window.ROOM_CODE,to:id,offer});}
   async function answerPeer(id,offer){const pc=makePeer(id); await pc.setRemoteDescription(new RTCSessionDescription(offer)); const answer=await pc.createAnswer(); await pc.setLocalDescription(answer); socket?.emit('voice_answer',{room:window.ROOM_CODE,to:id,answer});}
   return {async start(s){stream=s||stream; await connect(); if(socket&&stream){socket.emit('voice_ready',{room:window.ROOM_CODE});note('الميكروفون متصل — اللاعبون في نفس الغرفة يستطيعون سماع بعض عند تشغيل خادم الصوت.');}}, stop(){Object.values(peers).forEach(p=>p.close());peers={};socket?.emit('voice_leave',{room:window.ROOM_CODE});}};
 })();
 const oldVoice=window.WarqnaVoice;
 if(oldVoice){const baseStart=oldVoice.start; oldVoice.start=async function(){await baseStart.call(oldVoice); try{await window.WarqnaVoiceNet.start(window.__warqnaVoiceStream||null)}catch(e){}};}
})();

// v96 card-back uploaded image renderer.
(function(){const old=window.renderState; window.renderState=function(st){old&&old(st); if(window.MY_CARD_BACK_IMAGE){document.querySelectorAll('#myHand .card').forEach(c=>{c.style.backgroundImage=`linear-gradient(rgba(255,255,255,.86),rgba(255,255,255,.86)),url(${window.MY_CARD_BACK_IMAGE})`; c.style.backgroundSize='cover'; c.style.backgroundPosition='center';});}};})();


// v97 visible fixes: store buying preview, chat game tab, richer sounds, leave confirm, voice permissions.
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 const safe=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
 document.addEventListener('DOMContentLoaded',()=>{
   document.body.classList.add('v97-real-fixes');
   // remove zero-cost text everywhere in room pages
   $$('.create-cost-preview').forEach(e=>e.remove());
   // force leave confirmation after cached v96 removed it
   $$('form[action*="/leave"]').forEach(f=>{f.dataset.confirm='هل تريد الخروج من اللعبة؟ إذا خرجت 3 مرات من نفس اللعبة لن تستطيع العودة لها مرة أخرى.'});
   // show live sample on every text color card immediately
   $$('.store-card[data-category="text_color"], .category-text_color .store-card').forEach(card=>{
     const col=card.dataset.color || card.querySelector('[name="color"]')?.value || card.querySelector('.text-color-preview')?.style.color || '#ef4444';
     let preview=card.querySelector('.text-color-preview');
     if(preview){preview.style.color=col;preview.style.setProperty('--text-preview',col);preview.innerHTML='<span class="pen-icon">✍️</span><b style="color:'+safe(col)+'">كلمة تجريبية</b><small>دردشة اللعبة والأصدقاء بنفس اللون</small>';}
   });
   // faster visual navigation without waiting for heavy animations
   document.documentElement.style.setProperty('--page-speed','.10s');
   // tab default for game chat in chat center
   if(window.CHAT_HAS_ROOM && typeof window.setChatMode==='function') setTimeout(()=>window.setChatMode('room'),150);
   // sound on important controls
   document.addEventListener('click',e=>{if(e.target.closest('button,a,.store-card,.player-seat,.club-tile')){try{window.WarqnaSound?.ui?.()}catch(_){}}},true);
 });
 window.previewStoreItem=function(btn){
   const card=btn?.closest?.('.store-card')||btn?.closest?.('form')||btn; if(!card)return;
   const form=card.closest('form')||card; const action=form?.action||''; const token=window.CSRF||'';
   const name=card.querySelector('h3')?.textContent?.trim()||'عنصر'; const price=card.querySelector('.price')?.textContent?.trim()||'🪙 0';
   const cat=card.dataset.category||''; let visual=card.querySelector('.shop-icon')?.innerHTML||card.querySelector('.admin-item-preview')?.innerHTML||'🎁';
   if(cat==='text_color'){
     const col=card.dataset.color||card.querySelector('.text-color-preview')?.style.color||'#ef4444';
     visual='<div class="text-buy-preview"><div class="pen-big">✍️</div><b style="color:'+safe(col)+'">كلمة تجريبية</b><small>سيظهر النص بهذا اللون في الدردشة</small></div>';
   }
   const buy=action?'<form method="post" action="'+safe(action)+'" data-confirm="هل تريد شراء '+safe(name)+'؟ سيتم خصم التوكنز من حسابك وتحويلها إلى حساب الإدارة." class="preview-buy-form"><input type="hidden" name="_token" value="'+safe(token)+'"><button class="primary buy-now-modal">شراء الآن</button></form>':'';
   showRichNotice('<div class="store-preview-pop v97-preview"><div class="store-card deluxe preview-only"><div class="shop-icon isolated-preview">'+visual+'</div><h3>'+safe(name)+'</h3><p class="price">'+safe(price)+'</p><small>بعد الشراء ستجد العنصر داخل مشترياتي ويمكنك تفعيله بأي وقت.</small>'+buy+'</div></div>');
   try{window.WarqnaSound?.shop?.()}catch(_){ }
 };
 window.adminPreviewStoreItem=function(btn){
   const row=btn.closest('.store-admin-row,.store-admin-create'); if(!row)return;
   const cat=row.querySelector('[name="category"]')?.value || row.closest('.admin-store-section')?.id?.replace('admin-store-','') || '';
   const name=row.querySelector('[name="name_ar"]')?.value||'عنصر'; const price=row.querySelector('[name="price"]')?.value||'0';
   const color=row.querySelector('[name="color"]')?.value||'#facc15'; let visual='🎁';
   const node=row.querySelector('.admin-item-preview .table-preview,.admin-item-preview .card-back-preview,.admin-item-preview .text-color-preview,.admin-item-preview .generic-admin-icon');
   if(node) visual=node.outerHTML;
   if(cat==='text_color') visual='<div class="text-buy-preview"><div class="pen-big">✍️</div><b style="color:'+safe(color)+'">كلمة تجريبية</b><small>لون الكتابة في الدردشة</small></div>';
   showRichNotice('<div class="store-preview-pop v97-preview"><div class="store-card deluxe preview-only"><div class="shop-icon isolated-preview">'+visual+'</div><h3>'+safe(name)+'</h3><p class="price">🪙 '+safe(price)+'</p><small>معاينة إدارية واضحة بدون تداخل بين الطاولات والورق والألوان.</small></div></div>');
 };
 // enhance voice controls with permission checks and clearer errors
 window.WarqnaVoiceV97={
   stream:null,
   async request(){
     const status=$('#voiceStatus');
     if(!navigator.mediaDevices?.getUserMedia){ if(status)status.textContent='المتصفح لا يدعم المايك. استخدم Chrome/Edge وعلى https أو localhost.'; return null; }
     try{
       if(navigator.permissions?.query){ try{ const p=await navigator.permissions.query({name:'microphone'}); if(status)status.textContent='صلاحية المايك: '+p.state+' — وافق على الطلب عند ظهوره.';}catch(_){} }
       this.stream=await navigator.mediaDevices.getUserMedia({audio:{echoCancellation:true,noiseSuppression:true,autoGainControl:true},video:false});
       window.__warqnaVoiceStream=this.stream;
       if(status)status.textContent='✅ المايك يعمل. يتم الآن الاتصال بباقي لاعبي الغرفة.';
       await window.WarqnaVoiceNet?.start?.(this.stream);
       return this.stream;
     }catch(e){ if(status)status.textContent='تعذر تشغيل المايك: اسمح بصلاحية Microphone من المتصفح وتأكد من تشغيل npm run socket للصوت بين اللاعبين.'; return null; }
   },
   mute(){ this.stream?.getAudioTracks?.().forEach(t=>t.enabled=!t.enabled); const status=$('#voiceStatus'); if(status)status.textContent='تم تبديل حالة الكتم/التشغيل.';},
   stop(){ this.stream?.getTracks?.().forEach(t=>t.stop()); this.stream=null; window.WarqnaVoiceNet?.stop?.(); const status=$('#voiceStatus'); if(status)status.textContent='تم إيقاف الصوت.';}
 };
 const oldVoice=window.WarqnaVoice||{};
 window.WarqnaVoice=Object.assign({},oldVoice,{start(){return window.WarqnaVoiceV97.request()},mute(){return window.WarqnaVoiceV97.mute()},stop(){return window.WarqnaVoiceV97.stop()}});
})();

// v98 final applied layer: visible store/chat/speed/voice fixes.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>window.escapeHtml?escapeHtml(v):String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  function clampSpeed(v){v=Number(v||7); if(v<=5)return 5; if(v>=10)return 10; return 7;}
  function softToast(msg){
    let el=document.createElement('div'); el.className='store-success-inline'; el.textContent=msg||'تمت العملية بنجاح'; document.body.appendChild(el);
    setTimeout(()=>{el.style.opacity='0';el.style.transform='translateX(-50%) translateY(-8px)';},1700); setTimeout(()=>el.remove(),2300);
  }
  function visualFromCard(card){
    if(!card)return '🎁'; const cat=card.dataset.category||'';
    let node=card.querySelector('.shop-icon')||card.querySelector('.table-live-preview-mini,.card-back-showcase,.text-color-preview,.color-orbit-preview,.emoji-store-icon');
    let html=node?node.innerHTML||node.outerHTML:'🎁';
    if(cat==='text_color'){
      const color=card.dataset.color||card.querySelector('.text-color-preview')?.style?.color||'#fff';
      html='<div class="text-buy-preview" style="--text-preview:'+safe(color)+';color:'+safe(color)+'"><div class="pen-big">✍️</div><b style="color:'+safe(color)+'">كلمة تجريبية</b><small>سيظهر بهذا اللون في دردشة اللعبة والأصدقاء</small></div>';
    }
    return html;
  }
  window.previewStoreItem=function(btn){
    const card=btn?.closest?.('.store-card')||btn; if(!card)return;
    const name=card.querySelector('h3')?.textContent?.trim()||'مقتنى';
    const price=card.querySelector('.price')?.textContent?.trim()||'';
    const action=card.getAttribute('action')||card.action||card.closest('form')?.action||'';
    const token=csrf();
    const visual=visualFromCard(card);
    let buy=action?'<form method="post" action="'+safe(action)+'" data-v98-store-buy="1" data-confirm="هل تريد شراء '+safe(name)+'؟"><input type="hidden" name="_token" value="'+safe(token)+'"><button class="primary buy-now-modal" type="submit">شراء الآن</button></form>':'';
    showRichNotice('<div class="store-preview-pop v98-preview"><div class="store-card deluxe preview-only" data-category="'+safe(card.dataset.category||'')+'"><div class="shop-icon isolated-preview">'+visual+'</div><h3>'+safe(name)+'</h3><p class="price">'+safe(price)+'</p><small>هذه معاينة واضحة كما سيظهر العنصر داخل اللعبة أو الدردشة.</small>'+buy+'</div></div>');
    try{window.WarqnaSound?.ui?.()}catch(_){ }
  };
  async function storeAjaxSubmit(form){
    const name=form.querySelector('h3')?.textContent?.trim() || form.closest('.store-preview-pop')?.querySelector('h3')?.textContent?.trim() || 'المقتنى';
    const run=async()=>{
      try{
        const r=await fetch(form.action,{method:(form.method||'POST').toUpperCase(),headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
        const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تم الشراء':'تعذر الشراء'};
        if(j.ok===false){showNotice(j.message||'تعذر تنفيذ العملية');return;}
        softToast(j.message || ('تم شراء '+name+' بنجاح'));
        try{window.WarqnaSound?.shop?.()}catch(_){ }
        // Do not refresh and do not move away from the active store tab.
        if(j.item && j.inventory_id && j.item.category!=='pasha') addInventoryV98(j.item,j.inventory_id);
        if(j.activated) applyActivationV98(j);
        const btn=form.querySelector('button.primary,button.buy-btn,button:not([type="button"])'); if(btn && !form.closest('.preview-buy-form')){btn.textContent='تم الشراء';btn.disabled=true;}
      }catch(err){showNotice('لا يمكن تنفيذ الشراء الآن. تحقق من الاتصال ثم جرّب مرة أخرى.');}
    };
    const msg=form.dataset.confirm||('هل تريد شراء '+name+'؟');
    showConfirm(msg,run);
  }
  function addInventoryV98(item,inventoryId){
    const grid=$('#inventory .store-grid'); if(!grid)return; const empty=grid.querySelector('.mini-card'); if(empty)empty.remove();
    const p=item.payload||{}; let icon='🎁';
    if(item.category==='table') icon='<div class="table-live-preview-mini"><span class="table-preview '+safe(p.table||'')+'"></span><span class="mini-card-on-table"></span></div>';
    else if(item.category==='card_back') icon='<div class="card-back-showcase"><span class="card-back-preview '+safe(p.card_back||'')+'">🂠</span><span class="card-back-preview '+safe(p.card_back||'')+'">🂠</span><span class="card-back-preview '+safe(p.card_back||'')+'">🂠</span><span class="card-back-preview '+safe(p.card_back||'')+'">🂠</span></div>';
    else if(item.category==='text_color') icon='<span class="text-color-preview" style="--text-preview:'+safe(p.color||'#fff')+';color:'+safe(p.color||'#fff')+'"><span class="pen-icon">✍️</span><b style="color:'+safe(p.color||'#fff')+'">كلمة تجريبية</b><small>سيظهر بهذا اللون في الدردشة</small></span>';
    else if(item.category==='name_color') icon='<span class="color-orbit-preview" style="--orbit:'+safe(p.color||'#facc15')+'">Aa</span>';
    else if(item.category==='emoji_pack') icon='<span class="emoji-store-icon">'+safe(p.emojis||'😄')+'</span>';
    const form=document.createElement('form'); form.className='store-card deluxe inventory-card'; form.dataset.category=item.category; form.method='post'; form.action='/inventory/'+inventoryId+'/activate'; form.setAttribute('data-ajax-soft','1');
    form.innerHTML='<input type="hidden" name="_token" value="'+safe(csrf())+'"><h3>'+safe(item.name)+'</h3><p>غير مفعل</p>'+icon+'<button>تفعيل</button>';
    grid.prepend(form);
  }
  function applyActivationV98(j){const p=j.payload||{}; if(j.category==='text_color'&&p.color)document.body.style.setProperty('--my-text-color',p.color); if(j.category==='name_color'&&p.color)document.body.style.setProperty('--my-name-color',p.color);}
  document.addEventListener('submit',function(e){
    const f=e.target;
    if(f.matches('form[action*="/store/"][action$="/buy"],form[data-v98-store-buy]')){e.preventDefault(); e.stopImmediatePropagation(); storeAjaxSubmit(f); return false;}
  },true);
  function initV98Store(){
    $$('.category-text_color .store-card,.store-card[data-category="text_color"]').forEach(card=>{
      const color=card.dataset.color||card.querySelector('[style*="--text-preview"]')?.style?.getPropertyValue('--text-preview')||'#fff';
      const holder=card.querySelector('.shop-icon');
      if(holder && !holder.querySelector('.pen-icon')) holder.innerHTML='<span class="text-color-preview" style="--text-preview:'+safe(color)+';color:'+safe(color)+'"><span class="pen-icon">✍️</span><b style="color:'+safe(color)+'">كلمة تجريبية</b><small>دردشة اللعبة والأصدقاء</small></span>';
    });
    const activeTab=localStorage.storeTab&&document.querySelector('[data-store-tab="'+CSS.escape(localStorage.storeTab)+'"]')?localStorage.storeTab:($('[data-store-tab]')?.dataset.storeTab);
    if(activeTab && window.dispatchEvent) setTimeout(()=>document.querySelector('[data-store-tab="'+CSS.escape(activeTab)+'"]')?.click(),50);
    $$('[data-tier-filter]').forEach((b,i)=>{if(i===0)b.classList.add('active');});
    $$('[data-emoji-filter]').forEach((b,i)=>{if(i===0)b.classList.add('active');});
  }
  function initV98Timer(){
    window.ROOM_TURN_TIMEOUT=clampSpeed(window.ROOM_TURN_TIMEOUT||7); window.WARQNA_TURN_SECONDS=window.ROOM_TURN_TIMEOUT;
    const t=$('#turnTimer'); if(t && Number(t.textContent)>10)t.textContent=String(window.ROOM_TURN_TIMEOUT);
  }
  const oldRender=window.renderState;
  window.renderState=function(st){
    if(st){st.turn_timeout_seconds=clampSpeed(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT||7); window.ROOM_TURN_TIMEOUT=st.turn_timeout_seconds; window.WARQNA_TURN_SECONDS=st.turn_timeout_seconds;}
    oldRender&&oldRender(st);
    const t=$('#turnTimer'); if(t && Number(t.textContent)>10)t.textContent=String(clampSpeed(window.ROOM_TURN_TIMEOUT||7));
  };
  const oldSet=window.setChatMode;
  window.setChatMode=function(mode){
    window.CHAT_MODE=mode; $$('[data-chat-tab]').forEach(b=>b.classList.toggle('active',b.dataset.chatTab===mode));
    const body=$('#chatBody');
    if(mode==='room'){
      if(!window.CHAT_HAS_ROOM && !window.ROOM_CODE){ if(body)body.innerHTML='<p class="muted">دردشة اللعبة تظهر هنا تلقائيًا عندما تكون داخل غرفة أو لعبة.</p>'; return; }
      if(body)body.innerHTML='<p class="muted">دردشة اللعبة مفعلة لهذه الغرفة. اكتب رسالتك بالأسفل.</p>';
      setTimeout(()=>window.roomSyncNow&&window.roomSyncNow(),120); return;
    }
    oldSet&&oldSet(mode);
  };

  window.openCreateRoomModal=function(){
    const modal=$('#createRoomModal'), body=$('#createRoomModalBody'), form=$('#createRoomPanel form');
    if(!modal||!body||!form)return;
    const clone=form.cloneNode(true);
    clone.classList.add('modal-create-room-form','pro-card');
    clone.setAttribute('data-ajax-room','1');
    clone.querySelectorAll('[id]').forEach((el,i)=>{el.id=el.id+'V98'+i});
    body.innerHTML=''; body.appendChild(clone); modal.classList.remove('hidden'); clone.querySelector('select,input,button')?.focus();
    try{window.WarqnaSound?.ui?.()}catch(_){ }
  };
  window.WarqnaVoice=Object.assign(window.WarqnaVoice||{}, {
    mutedPeers: window.WarQnaMutedPeers || new Set(),
    mutePeer(key){ this.mutedPeers.add(String(key)); softToast('تم كتم هذا اللاعب عندك فقط'); document.querySelectorAll('audio[data-peer="'+CSS.escape(String(key))+'"]').forEach(a=>a.muted=true); },
    unmutePeer(key){ this.mutedPeers.delete(String(key)); document.querySelectorAll('audio[data-peer="'+CSS.escape(String(key))+'"]').forEach(a=>a.muted=false); }
  });
  // Friend buttons consistency
  document.addEventListener('click',e=>{ if(e.target.closest('.friend-row button,.search-player-row button,.voice-seat-icons button,.voice-controls button')){try{window.WarqnaSound?.ui?.()}catch(_){}} },true);
  document.addEventListener('DOMContentLoaded',()=>{initV98Store();initV98Timer(); if(document.body.classList.contains('is-room-page')){setTimeout(()=>window.setChatMode&&setChatMode('room'),200);} });
})();


// v99: competition/groups rename, polished Tarneeb bidding, live notifications, no button wrapping.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';

  function renameUiV99(){
    const pairs=[
      ['المسابقات','المنافسات'],['مسابقة','منافسة'],['المسابقة','المنافسة'],
      ['النوادي','المجموعات'],['نادي','مجموعة'],['النادي','المجموعة'],['نوادي','مجموعات']
    ];
    $$('a,button,h1,h2,h3,b,span,small,p,label,option,title').forEach(el=>{
      if(el.children.length>1) return;
      let t=el.textContent||'', nt=t;
      pairs.forEach(([a,b])=>{ nt=nt.split(a).join(b); });
      if(nt!==t) el.textContent=nt;
    });
    document.title=(document.title||'').replaceAll('المسابقات','المنافسات').replaceAll('النوادي','المجموعات');
  }

  function initTarneebV99(){
    document.body.classList.toggle('tarneeb-v99', ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||''));
    $$('.tarneeb-bid').forEach(btn=>{
      btn.setAttribute('title','اعتماد طلب '+btn.dataset.value);
      if(!btn.querySelector('small')){
        const val=btn.textContent.trim();
        btn.innerHTML='<span>'+safe(val)+'</span><small>طلب</small>';
      }
    });
    $$('.trump-card').forEach(btn=>{
      btn.setAttribute('title','اختيار '+(btn.querySelector('span')?.textContent||btn.dataset.suit));
    });
  }

  const oldRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    if(action==='bid'){
      const value=Number(payload.value||0), current=Number(window.LAST_STATE?.bid?.value||6);
      if(value<7 || value>13 || value<=current){
        showNotice('الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.');
        return;
      }
      $$('.tarneeb-bid').forEach(b=>b.disabled=true);
      showNotice('تم اعتماد طلب '+value+'، انتظر باقي اللاعبين.');
    }
    if(action==='choose_trump'){
      const name={hearts:'الكبة ♥',diamonds:'الديناري ♦',spades:'البستوني ♠',clubs:'السباتي ♣'}[payload.suit]||payload.suit;
      showNotice('تم اختيار الطرنيب: '+name);
    }
    return oldRoomAction ? oldRoomAction(action,payload) : null;
  };

  const oldRender=window.renderState;
  window.renderState=function(st){
    oldRender&&oldRender(st);
    initTarneebV99();
    const current=Number(st?.bid?.value||6);
    $$('.tarneeb-bid').forEach(btn=>{
      const val=Number(btn.dataset.value||0);
      btn.disabled = (st?.phase==='bidding' && val<=current);
      btn.classList.toggle('highest-bid', st?.bid && val===Number(st.bid.value));
      if(st?.phase==='bidding' && val<=current) btn.title='يجب أن يكون الطلب أعلى من '+current;
    });
    $$('.trump-chooser-panel').forEach(panel=>panel.classList.toggle('active', st?.phase==='choose_trump'));
    $$('.tarneeb-request-panel').forEach(panel=>panel.classList.toggle('active', st?.phase==='bidding'));
    if(st?.last_error_message) showNotice(st.last_error_message);
  };

  async function pollNotifications(){
    try{
      const r=await fetch('/notifications/counts',{headers:{'Accept':'application/json','X-Requested-With':'XMLHttpRequest'}});
      if(!r.ok) return;
      const j=await r.json();
      Object.entries({club:j.club,game:j.game,competition:j.competition,message:j.message}).forEach(([k,v])=>{
        const b=$('.notif-live-btn[data-notif-type="'+k+'"] b');
        if(!b) return;
        b.textContent=Number(v||0);
        b.classList.toggle('hidden', !Number(v||0));
        b.closest('button')?.classList.toggle('has-new-notif', Number(v||0)>0);
      });
    }catch(e){}
  }

  document.addEventListener('DOMContentLoaded',()=>{
    renameUiV99();
    initTarneebV99();
    $$('.quick-icons').forEach(x=>x.remove());
    document.body.classList.add('v99-polish');
    pollNotifications();
    setInterval(pollNotifications, 8000);
    // keep top controls on one line
    $$('.top-icons, .userbar').forEach(el=>el.classList.add('no-wrap-controls'));
  });
})();


// v100: polish Tarneeb buttons, no-refresh store, chat single dock, leave redirect, layout fit.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  function notice(msg){ if(window.showNotice) showNotice(msg); else alert(msg); }

  function clampButtons(){
    $$('button,.btn,a.btn').forEach(btn=>{
      btn.classList.add('btn-centered-v100');
      if(!btn.style.alignItems) btn.style.alignItems='center';
      if(!btn.style.justifyContent) btn.style.justifyContent='center';
    });
  }

  function polishTarneeb(){
    if(!['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||'')) return;
    document.body.classList.add('tarneeb-v100');
    $$('.tarneeb-bid').forEach(btn=>{
      const val=btn.dataset.value || btn.textContent.trim().replace(/\D/g,'');
      btn.innerHTML='<span>'+safe(val)+'</span>';
      btn.title='اعتماد طلب '+safe(val);
    });
    const pass=$('.pass-btn');
    if(pass){ pass.textContent='تمرير'; pass.classList.add('tarneeb-pass-v100'); }
  }

  // Stronger action guard for bidding + direct visual feedback
  const previousRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    if(action==='bid'){
      const value=Number(payload.value||0), current=Number(window.LAST_STATE?.bid?.value||6);
      if(value<7 || value>13 || value<=current){
        notice('الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.');
        return;
      }
      $$('.tarneeb-bid').forEach(b=>b.disabled=true);
      const chosen=$('.tarneeb-bid[data-value="'+value+'"]');
      chosen?.classList.add('bid-accepted-now');
      notice('تم اعتماد طلب '+value+'، انتظر باقي اللاعبين.');
    }
    const res = previousRoomAction ? await previousRoomAction(action,payload) : null;
    setTimeout(polishTarneeb,80);
    return res;
  };

  const oldRender=window.renderState;
  window.renderState=function(st){
    oldRender&&oldRender(st);
    polishTarneeb();
    const current=Number(st?.bid?.value||6);
    $$('.tarneeb-bid').forEach(btn=>{
      const val=Number(btn.dataset.value||0);
      btn.disabled = st?.phase==='bidding' && val<=current;
      btn.classList.toggle('highest-bid', st?.bid && val===Number(st.bid.value));
    });
    // show turn counter near player seat
    $$('.seat-profile').forEach(seat=>{
      let key=seat.dataset.playerKey;
      seat.classList.toggle('v100-current-turn', !!st?.turn && key===st.turn);
      let badge=seat.querySelector('.turn-badge-v100');
      if(!badge){ badge=document.createElement('span'); badge.className='turn-badge-v100'; badge.textContent='دورك'; seat.appendChild(badge); }
      badge.style.display=(!!st?.turn && key===st.turn)?'grid':'none';
    });
    // played cards stay in center / front of player if possible
    Object.entries(st?.trick||{}).forEach(([p,c])=>{
      const el=document.querySelector(`.seat-played-card[data-player-key="${CSS.escape(p)}"]`);
      if(el){ el.classList.add('played-front-v100'); }
    });
  };

  // Leave game: confirm then AJAX leave and redirect to same game rooms; blocks chat after leaving.
  document.addEventListener('submit', async function(e){
    const form=e.target;
    if(!form.matches('form[action*="/leave"]')) return;
    e.preventDefault(); e.stopImmediatePropagation();
    const ok=confirm('هل تريد الخروج من اللعبة؟ إذا خرجت 3 مرات من نفس اللعبة لن تستطيع العودة لنفس اللعبة.');
    if(!ok) return false;
    try{
      await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
    }catch(_){}
    window.CHAT_LEFT_ROOM=true;
    const target='/games/'+(window.GAME_KEY||'tarneeb')+'/rooms';
    location.href=target;
    return false;
  },true);

  // Block game chat after leaving
  const oldSendEmbedded=window.sendEmbeddedRoomChat;
  window.sendEmbeddedRoomChat=function(e){
    if(window.CHAT_LEFT_ROOM){ e?.preventDefault?.(); notice('خرجت من الغرفة، لا يمكنك الكتابة في دردشة اللعبة.'); return false; }
    return oldSendEmbedded ? oldSendEmbedded(e) : false;
  };
  const oldSendChat=window.sendChat;
  window.sendChat=function(e){
    if(window.CHAT_MODE==='room' && window.CHAT_LEFT_ROOM){ e?.preventDefault?.(); notice('خرجت من الغرفة، لا يمكنك الكتابة في دردشة اللعبة.'); return false; }
    return oldSendChat ? oldSendChat(e) : false;
  };

  // Store buy/activate without reload or navigation
  async function ajaxStoreForm(form){
    const btn=form.querySelector('button[type="submit"],button:not([type])');
    const old=btn?btn.textContent:'';
    if(btn){btn.disabled=true;btn.textContent='جاري...';}
    try{
      const r=await fetch(form.action,{method:(form.method||'POST').toUpperCase(),headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تمت العملية بنجاح':'تعذر تنفيذ العملية'};
      if(j.ok===false){ notice(j.message||'تعذر تنفيذ العملية'); }
      else{
        notice(j.message||'تمت العملية بنجاح بدون تحديث الصفحة');
        form.classList.add('just-purchased-v100');
        if(btn) btn.textContent='تم';
      }
    }catch(err){ notice('تعذر الاتصال الآن'); if(btn)btn.textContent=old; }
    finally{ if(btn){setTimeout(()=>{btn.disabled=false;if(btn.textContent==='تم'){}else btn.textContent=old;},900);} }
  }
  document.addEventListener('submit',function(e){
    const f=e.target;
    if(f.matches('form[action*="/store/"][action$="/buy"],form[action*="/inventory/"][action$="/activate"],form[data-v98-store-buy],.preview-buy-form')){
      e.preventDefault(); e.stopImmediatePropagation();
      if(f.action.includes('/buy')){
        const name=f.querySelector('h3')?.textContent || f.closest('.store-preview-pop')?.querySelector('h3')?.textContent || 'هذا العنصر';
        if(!confirm('هل تريد شراء '+name+'؟')) return false;
      }
      ajaxStoreForm(f);
      return false;
    }
  },true);

  // Better preview for tables: show whole table room-like
  window.previewStoreItem = function(btn){
    const card=btn?.closest?.('.store-card')||btn; if(!card) return;
    const cat=card.dataset.category||'';
    const name=card.querySelector('h3')?.textContent?.trim()||'مقتنى';
    const price=card.querySelector('.price')?.textContent?.trim()||'';
    let visual=card.querySelector('.shop-icon')?.innerHTML||'🎁';
    if(cat==='table'){
      const cls=card.querySelector('.table-preview')?.className||'table-preview table-1';
      visual='<div class="full-table-preview-v100"><div class="'+safe(cls)+'"><div class="mock-seat top"></div><div class="mock-seat right"></div><div class="mock-seat bottom"></div><div class="mock-seat left"></div><div class="mock-cards">🂡 🂮 🂭</div></div></div>';
    }
    if(cat==='text_color'){
      const col=card.dataset.color||card.querySelector('.text-color-preview')?.style.color||'#fff';
      visual='<div class="text-buy-preview" style="color:'+safe(col)+'"><div class="pen-big">✍️</div><b style="color:'+safe(col)+'">كلمة تجريبية</b></div>';
    }
    const action=card.action||card.getAttribute('action')||card.closest('form')?.action||'';
    const buy=action?'<form method="post" action="'+safe(action)+'" class="preview-buy-form"><input type="hidden" name="_token" value="'+safe(csrf())+'"><button type="submit" class="primary buy-now-modal">شراء الآن</button></form>':'';
    showRichNotice('<div class="store-preview-pop v100-preview"><div class="store-card deluxe preview-only"><div class="shop-icon isolated-preview">'+visual+'</div><h3>'+safe(name)+'</h3><p class="price">'+safe(price)+'</p>'+buy+'</div></div>');
  };

  // store sub tabs filters
  window.filterStoreSubtab=function(type,value){
    const scope=document.querySelector('[data-store-section="'+type+'"]') || document;
    scope.querySelectorAll('.store-card').forEach(card=>{
      const tier=card.dataset.tier||card.dataset.subcategory||card.dataset.kind||'all';
      card.style.display=(value==='all'||tier===value)?'':'none';
    });
    scope.querySelectorAll('[data-subtab]').forEach(b=>b.classList.toggle('active',b.dataset.subtab===value));
  };

  // Chat: only left dock, one-click buttons, no bottom reopen button
  window.toggleChat=function(){ const d=$('#chatDock'); if(!d)return; d.classList.toggle('minimized'); };
  window.minimizeChat=function(){ const d=$('#chatDock'); if(!d)return; d.classList.add('minimized'); };
  window.maximizeChat=function(){ const d=$('#chatDock'); if(!d)return; d.classList.remove('minimized','closed'); d.classList.toggle('chat-large'); };
  window.closeChat=function(){ const d=$('#chatDock'); if(!d)return; d.classList.add('closed'); };
  window.reopenChat=function(){ const d=$('#chatDock'); if(!d)return; d.classList.remove('closed','minimized'); };

  function initPage(){
    document.body.classList.add('v100-fit');
    polishTarneeb();
    clampButtons();
    $('#chatReopen')?.remove();
    // inventory direct tab
    if(location.hash==='#inventory'){
      document.querySelector('[data-store-tab="inventory"],[href="#inventory"]')?.click?.();
      document.getElementById('inventory')?.scrollIntoView({behavior:'smooth',block:'start'});
    }
    // make notification drawer fixed and not page-scroll
    $$('.notification-drawer,.top-panel').forEach(p=>p.classList.add('v100-top-panel'));
    // Hide any join-bot-seat remnants for seated players
    $$('.join-bot-seat').forEach(x=>x.remove());
  }
  document.addEventListener('DOMContentLoaded',initPage);
})();


// v101: complete missing pieces from previous request: real subfilters, no-refresh activate, profile->inventory, cleaner chat and table fit.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';

  function toast(msg){ if(window.showNotice) showNotice(msg); else alert(msg); }

  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-table')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"]').forEach(card=>{
      const val=card.dataset.tier||'pro';
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-emoji_pack')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||'vip';
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  function initStoreDefaults(){
    document.querySelector('.category-table [data-tier-filter="all"]')?.classList.add('active');
    document.querySelector('.category-emoji_pack [data-emoji-filter="all"]')?.classList.add('active');
    // open inventory from profile/hash and keep user in store page
    if(location.hash==='#inventory'){
      document.querySelector('[data-store-tab="inventory"]')?.click();
      setTimeout(()=>document.getElementById('inventory')?.scrollIntoView({behavior:'smooth',block:'start'}),120);
    }
    // mark current tab in localStorage
    document.querySelectorAll('[data-store-tab]').forEach(b=>b.addEventListener('click',()=>localStorage.setItem('storeTab',b.dataset.storeTab||'')));
  }

  async function softSubmit(form){
    const btn=form.querySelector('button[type="submit"],button:not([type])');
    const old=btn?btn.innerHTML:'';
    if(btn){ btn.disabled=true; btn.innerHTML='⏳'; }
    try{
      const r=await fetch(form.action,{method:(form.method||'POST').toUpperCase(),headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تمت العملية بنجاح':'تعذر تنفيذ العملية'};
      if(j.ok===false){ toast(j.message||'تعذر تنفيذ العملية'); if(btn)btn.innerHTML=old; return; }
      toast(j.message||'تمت العملية بنجاح بدون تحديث الصفحة');
      form.classList.add('v101-soft-done');
      if(j.activated || form.action.includes('/inventory/')){
        document.querySelectorAll('.inventory-card[data-category="'+safe(j.category||form.dataset.category||'')+'"]').forEach(c=>c.classList.remove('active-inventory'));
        form.classList.add('active-inventory');
        const p=form.querySelector('p'); if(p)p.textContent='✅ مفعل الآن';
      }
      if(btn){ btn.innerHTML='✅'; setTimeout(()=>{btn.innerHTML=old; btn.disabled=false;},1200); }
    }catch(e){ toast('فشل الاتصال بدون تحديث الصفحة.'); if(btn){btn.innerHTML=old;btn.disabled=false;} }
  }

  document.addEventListener('submit',function(e){
    const f=e.target;
    if(f.matches('form[action*="/store/"][action$="/buy"], form[action*="/inventory/"][action$="/activate"], .preview-buy-form')){
      e.preventDefault(); e.stopImmediatePropagation();
      if(f.action.includes('/store/') && f.action.includes('/buy')){
        const n=f.querySelector('h3')?.textContent || f.closest('.store-preview-pop')?.querySelector('h3')?.textContent || 'هذا العنصر';
        if(!confirm('هل تريد شراء '+n+'؟')) return false;
      }
      softSubmit(f);
      return false;
    }
  },true);

  // Stronger leave redirect and chat lock
  document.addEventListener('submit',async function(e){
    const f=e.target;
    if(!f.matches('form[action*="/leave"]')) return;
    e.preventDefault(); e.stopImmediatePropagation();
    if(!confirm('هل تريد الخروج من اللعبة؟ إذا خرجت 3 مرات لن تستطيع العودة لنفس اللعبة.')) return false;
    try{
      const r=await fetch(f.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(f)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{};
      window.CHAT_LEFT_ROOM=true;
      sessionStorage.setItem('leftRoom_'+(window.ROOM_CODE||''),'1');
      location.href=j.redirect||('/games/'+(window.GAME_KEY||'tarneeb')+'/rooms');
    }catch(_){
      window.CHAT_LEFT_ROOM=true;
      location.href='/games/'+(window.GAME_KEY||'tarneeb')+'/rooms';
    }
    return false;
  },true);

  // Make center played card more visible
  const oldRender=window.renderState;
  window.renderState=function(st){
    oldRender&&oldRender(st);
    document.querySelectorAll('.tarneeb-bid small').forEach(x=>x.remove());
    document.querySelectorAll('.tarneeb-bid').forEach(btn=>{
      const val=btn.dataset.value||btn.textContent.replace(/\D/g,'');
      btn.innerHTML='<span>'+safe(val)+'</span>';
    });
    if(st?.turn){
      document.querySelectorAll('.seat-profile').forEach(s=>{
        const active=s.dataset.playerKey===st.turn;
        s.classList.toggle('v101-turn-now',active);
        let b=s.querySelector('.turn-mini-counter');
        if(!b){ b=document.createElement('span'); b.className='turn-mini-counter'; b.textContent='الدور'; s.appendChild(b); }
        b.style.display=active?'grid':'none';
      });
    }
  };

  // Chat only left dock. One click state.
  function normalizeChat(){
    document.getElementById('chatReopen')?.remove();
    document.getElementById('gameRoomChat')?.remove();
    const dock=document.getElementById('chatDock');
    if(dock){ dock.classList.remove('minimized-on-load'); dock.classList.add('v101-chat-dock'); }
    document.querySelectorAll('.chat-head button').forEach(b=>{ b.type='button'; });
  }
  window.toggleChat=function(){document.getElementById('chatDock')?.classList.toggle('minimized')};
  window.minimizeChat=function(){document.getElementById('chatDock')?.classList.add('minimized')};
  window.maximizeChat=function(){const d=document.getElementById('chatDock'); if(d){d.classList.remove('minimized','closed');d.classList.toggle('chat-large')}};
  window.closeChat=function(){document.getElementById('chatDock')?.classList.add('closed')};

  // Add voice icons under/near seats without covering table
  function addVoiceSeatIcons(){
    if(!document.body.classList.contains('is-room-page')) return;
    document.querySelectorAll('.seat-profile').forEach(seat=>{
      if(seat.querySelector('.voice-seat-actions-v101')) return;
      const key=seat.dataset.playerKey||'';
      const box=document.createElement('span');
      box.className='voice-seat-actions-v101';
      box.innerHTML='<button type="button" title="كتم هذا اللاعب" onclick="event.stopPropagation();WarqnaVoice?.mutePeer?.(\''+safe(key)+'\')">🔇</button><button type="button" title="إعدادات الصوت" onclick="event.stopPropagation();document.getElementById(\'voiceRoomPanel\')?.scrollIntoView({behavior:\'smooth\',block:\'center\'})">⚙️</button>';
      seat.appendChild(box);
    });
  }

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v101-complete');
    initStoreDefaults();
    normalizeChat();
    addVoiceSeatIcons();
    document.querySelectorAll('.join-bot-seat').forEach(x=>x.remove());
    // Make all buttons recentered again after components render
    document.querySelectorAll('button,.btn,a.btn').forEach(b=>b.classList.add('v101-button'));
    setTimeout(addVoiceSeatIcons,500);
  });
})();


// v103 bot polish
(function(){
 document.addEventListener('DOMContentLoaded',function(){
   document.querySelectorAll('.tarneeb-bid small').forEach(function(el){el.remove();});
   document.querySelectorAll('.tarneeb-bid').forEach(function(btn){
     var val=(btn.dataset.value||btn.textContent||'').replace(/[^0-9]/g,'');
     if(val) btn.innerHTML='<span>'+val+'</span>';
   });
   document.querySelectorAll('.seat-profile .player-name').forEach(function(el){ el.textContent=el.textContent.trim().replace(/\s*BOT\s*$/i,''); });
 });
})();


// v104: Tarneeb-only controls, stronger store/admin subfilters, no-scroll notifications, bigger bot seats/cards.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  function isTarneeb(){
    return ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||document.body.dataset.game||'');
  }

  function tarneebOnlyControls(){
    if(!isTarneeb()) return;
    document.body.classList.add('v104-tarneeb-only');
    $$('#actionPanel .hand-btn,#actionPanel .backgammon-btn,#actionPanel .domino-btn,#actionPanel .meld-btn,#actionPanel .sort-btn,#actionPanel .estimation-bid-grid').forEach(el=>el.remove());
    $$('#actionPanel .tarneeb-bid').forEach(btn=>{
      const val=btn.dataset.value||btn.textContent.replace(/\D/g,'');
      btn.innerHTML='<span>'+safe(val)+'</span>';
      btn.title='اعتماد طلب '+safe(val);
    });
    const pass=$('#actionPanel .pass-btn');
    if(pass){ pass.textContent='تمرير'; pass.classList.add('v104-pass-btn'); }
  }

  const prevRender=window.renderState;
  window.renderState=function(st){
    prevRender&&prevRender(st);
    tarneebOnlyControls();
    const phase=st?.phase||'';
    const current=Number(st?.bid?.value||6);
    $$('#actionPanel .tarneeb-bid').forEach(btn=>{
      const val=Number(btn.dataset.value||0);
      btn.disabled=(phase==='bidding' && val<=current);
      btn.classList.toggle('highest-bid', !!st?.bid && val===Number(st.bid.value));
    });
    $$('#actionPanel .tarneeb-request-panel').forEach(p=>p.style.display=phase==='bidding'?'flex':'none');
    $$('#actionPanel .trump-chooser-panel').forEach(p=>p.style.display=phase==='choose_trump'?'flex':'none');
    // visible turn badge beside every player/bot
    $$('.seat-profile').forEach(seat=>{
      const active=!!st?.turn && seat.dataset.playerKey===st.turn;
      seat.classList.toggle('v104-turn-active',active);
      let b=seat.querySelector('.v104-turn-badge');
      if(!b){ b=document.createElement('span'); b.className='v104-turn-badge'; b.textContent='الدور'; seat.appendChild(b); }
      b.style.display=active?'grid':'none';
    });
  };

  const prevRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    if(isTarneeb() && action==='bid'){
      const v=Number(payload.value||0), cur=Number(window.LAST_STATE?.bid?.value||6);
      if(v<7 || v>13 || v<=cur){ showNotice?.('الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.'); return; }
      $$('#actionPanel .tarneeb-bid').forEach(btn=>btn.disabled=true);
    }
    return prevRoomAction ? prevRoomAction(action,payload) : null;
  };

  // Store tab filters (actual show/hide)
  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-table')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"]').forEach(card=>{
      const val=(card.dataset.tier||'pro').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-emoji_pack')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=(card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||'vip').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterAdminStoreTier=function(btn,tier){
    const section=btn.closest('.admin-store-section')||document;
    section.querySelectorAll('[data-admin-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-admin-row,.admin-store-row').forEach(row=>{
      const val=(row.dataset.tier||row.querySelector('[name="tier"]')?.value||row.querySelector('[name="tab"]')?.value||'pro').toLowerCase();
      row.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterAdminEmojiTier=function(btn,tier){
    const section=btn.closest('.admin-store-section')||document;
    section.querySelectorAll('[data-admin-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-admin-row,.admin-store-row').forEach(row=>{
      const raw=(row.dataset.emojiTier||row.querySelector('[name="tier"]')?.value||row.querySelector('[name="tab"]')?.value||row.textContent||'vip').toLowerCase();
      let val=raw.includes('animated')||raw.includes('متحرك')?'animated':raw.includes('laugh')||raw.includes('ضحك')?'laugh':raw.includes('happy')||raw.includes('فرح')?'happy':raw.includes('angry')||raw.includes('عصب')?'angry':raw.includes('sad')||raw.includes('حزن')?'sad':raw.includes('free')||raw.includes('مجاني')?'free':raw.includes('vip')?'vip':'vip';
      row.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  // Notifications should open as overlay panel not scrolling page
  const oldToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    oldToggle&&oldToggle(id);
    const panel=document.getElementById(id);
    if(panel){ panel.classList.add('v104-notif-panel'); panel.scrollTop=0; document.body.classList.add('notif-open-v104'); }
    document.querySelectorAll('.top-panel').forEach(p=>{ if(p.id!==id) p.classList.remove('v104-notif-panel'); });
  };
  document.addEventListener('click',e=>{
    if(!e.target.closest('.top-icons') && !e.target.closest('.top-panel')) document.body.classList.remove('notif-open-v104');
  });

  function init(){
    document.body.classList.add('v104-polish');
    tarneebOnlyControls();
    document.querySelectorAll('.category-table [data-tier-filter="all"],.category-emoji_pack [data-emoji-filter="all"]').forEach(b=>b.classList.add('active'));
    // make admin rows filterable when possible
    document.querySelectorAll('.admin-store-section .store-admin-row').forEach(row=>{
      if(!row.dataset.tier){
        row.dataset.tier=(row.querySelector('[name="tier"]')?.value||row.querySelector('[name="tab"]')?.value||'pro').toLowerCase();
      }
    });
  }
  document.addEventListener('DOMContentLoaded',init);
})();

// v105 manifest-based UI behaviors
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 function norm(v){return String(v||'').toLowerCase().trim()}
 window.filterStoreTier=function(btn,tier){const sec=btn.closest('.store-section')||document.querySelector('.category-table')||document;sec.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));sec.querySelectorAll('.store-card[data-category="table"]').forEach(card=>{let val=norm(card.dataset.tier||card.dataset.subcategory||card.dataset.kind||'pro');card.style.display=(tier==='all'||val===tier)?'':'none';});};
 window.filterEmojiTier=function(btn,tier){const sec=btn.closest('.store-section')||document.querySelector('.category-emoji_pack')||document;sec.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));sec.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{let val=norm(card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||'vip');card.style.display=(tier==='all'||val===tier)?'':'none';});};
 window.filterAdminStoreTier=function(btn,tier){const sec=btn.closest('.admin-store-section')||document;sec.querySelectorAll('[data-admin-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));sec.querySelectorAll('.store-admin-row').forEach(row=>{let val=norm(row.dataset.adminTier||row.dataset.tier||row.querySelector('[name="tier"]')?.value||row.querySelector('[name="tab"]')?.value||'pro');row.style.display=(tier==='all'||val===tier)?'grid':'none';});};
 window.filterAdminEmojiTier=function(btn,tier){const sec=btn.closest('.admin-store-section')||document;sec.querySelectorAll('[data-admin-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));sec.querySelectorAll('.store-admin-row').forEach(row=>{let raw=norm(row.dataset.adminEmojiTier||row.textContent||'vip');let val=raw.includes('animated')||raw.includes('متحرك')?'animated':raw.includes('laugh')||raw.includes('ضحك')?'laugh':raw.includes('happy')||raw.includes('فرح')?'happy':raw.includes('angry')||raw.includes('عصب')?'angry':raw.includes('sad')||raw.includes('حزن')?'sad':raw.includes('free')||raw.includes('مجاني')?'free':raw.includes('vip')?'vip':'vip';row.style.display=(tier==='all'||val===tier)?'grid':'none';});};
 const oldToggle=window.toggleTopPanel;window.toggleTopPanel=function(id){oldToggle&&oldToggle(id);document.querySelectorAll('.top-panel').forEach(p=>{if(p.id===id){p.classList.add('v105-overlay-panel');p.style.overflow='visible';p.style.maxHeight='none';}else p.classList.remove('v105-overlay-panel')});};
 document.addEventListener('DOMContentLoaded',()=>{document.body.classList.add('v105-manifest');document.querySelectorAll('.category-table [data-tier-filter="all"],.category-emoji_pack [data-emoji-filter="all"],.admin-table-tier-tabs [data-admin-tier-filter="all"],.admin-emoji-tier-tabs [data-admin-emoji-filter="all"]').forEach(b=>b.classList.add('active'));document.querySelectorAll('.top-panel').forEach(p=>p.classList.add('notif-scrollless-list'));});
})();


// v106 UI + Tarneeb completion layer
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  function isTarneeb(){ return ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||''); }

  function centerButtons(){
    $$('button,.btn,a.btn,input[type=submit]').forEach(b=>{
      b.classList.add('v106-centered');
      b.style.alignItems='center';
      b.style.justifyContent='center';
      b.style.textAlign='center';
    });
  }

  function compactSeats(){
    $$('.seat-profile').forEach(seat=>{
      seat.classList.add('v106-seat-compact');
      const name=seat.querySelector('.player-name, span:not(.player-ring)');
      if(name) name.textContent=name.textContent.replace(/\s*BOT\s*$/i,'').trim();
    });
  }

  function tarneebControls(st){
    if(!isTarneeb()) return;
    document.body.classList.add('v106-tarneeb');
    $$('#actionPanel .hand-btn,#actionPanel .backgammon-btn,#actionPanel .domino-btn,#actionPanel .meld-btn,#actionPanel .sort-btn,#actionPanel .estimation-bid-grid').forEach(x=>x.remove());
    $$('#actionPanel .tarneeb-bid').forEach(btn=>{
      const val=btn.dataset.value || btn.textContent.replace(/\D/g,'');
      btn.innerHTML='<span>'+safe(val)+'</span>';
      btn.title='اعتماد طلب '+safe(val);
    });
    const phase=st?.phase || window.LAST_STATE?.phase || '';
    document.body.classList.toggle('v106-tarneeb-bidding', phase==='bidding');
    document.body.classList.toggle('v106-tarneeb-trump-ready', phase==='choose_trump');
    $$('#actionPanel .tarneeb-request-panel').forEach(p=>p.style.display=phase==='bidding'?'flex':'none');
    $$('#actionPanel .trump-chooser-panel').forEach(p=>p.style.display=phase==='choose_trump'?'flex':'none');
    const current=Number(st?.bid?.value || window.LAST_STATE?.bid?.value || 6);
    $$('#actionPanel .tarneeb-bid').forEach(btn=>{
      const v=Number(btn.dataset.value||0);
      btn.disabled=(phase==='bidding' && v<=current);
      btn.classList.toggle('highest-bid', !!(st?.bid) && v===Number(st.bid.value));
    });
  }

  const oldRender=window.renderState;
  window.renderState=function(st){
    window.LAST_STATE=st||window.LAST_STATE;
    oldRender&&oldRender(st);
    compactSeats();
    centerButtons();
    tarneebControls(st);
    if(st?.last_error_message && !String(st.last_error_message).includes('غير قانونية')) {
      if(window.showNotice) showNotice(st.last_error_message);
    }
  };

  const oldRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    if(isTarneeb()){
      const phase=window.LAST_STATE?.phase||'';
      if(action==='bid'){
        const v=Number(payload.value||0), cur=Number(window.LAST_STATE?.bid?.value||6);
        if(phase!=='bidding'){ showNotice?.('مرحلة الطلب انتهت. انتظر اختيار الطرنيب أو بدء اللعب.'); return; }
        if(v<7 || v>13 || v<=cur){ showNotice?.('الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.'); return; }
      }
      if(action==='choose_trump' && phase!=='choose_trump'){
        showNotice?.('اختيار نوع الطرنيب يظهر بعد انتهاء الطلب واعتماد أعلى طلب.');
        return;
      }
    }
    return oldRoomAction ? await oldRoomAction(action,payload) : null;
  };

  // Keep store/profile/notifications visible in-page, without page scroll jumps.
  function stabilizePanels(){
    $$('.top-panel,.notification-drawer').forEach(p=>{
      p.classList.add('v106-inline-overlay');
      p.style.overflow='visible';
      p.style.maxHeight='none';
    });
    $$('.profile-modal,.profile-popup,.player-profile-modal,.store-preview-pop,.store-preview-modal').forEach(p=>{
      p.classList.add('v106-no-scroll-panel');
      p.style.overflow='visible';
      p.style.maxHeight='none';
    });
    $$('.store-section,#inventory,.store-inventory,.admin-store-section').forEach(p=>{
      p.style.overflow='visible';
      p.style.maxHeight='none';
    });
  }

  document.addEventListener('click',function(e){
    const preview=e.target.closest('[onclick*="preview"],.preview-btn,[data-preview]');
    if(preview) setTimeout(stabilizePanels,80);
    const notif=e.target.closest('.top-icons button');
    if(notif) setTimeout(stabilizePanels,80);
  },true);

  document.addEventListener('DOMContentLoaded',()=>{
    centerButtons();
    compactSeats();
    tarneebControls(window.LAST_STATE||null);
    stabilizePanels();
    setInterval(()=>{centerButtons(); compactSeats();},1500);
  });
})();


// v107 tournament admin polish: bid bubbles, sound steps, panels inside page
(function(){
 const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
 function isTarneeb(){return ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||'')}
 window._soundVolumeLevel=Number(localStorage.soundVolumeLevel||3);
 function soundPop(){let old=$('.sound-level-pop'); if(old)old.remove(); let d=document.createElement('div');d.className='sound-level-pop';d.innerHTML='الصوت: '+(window._soundVolumeLevel*20)+'%<div class="bar"><span style="width:'+(window._soundVolumeLevel*20)+'%"></span></div>';document.body.appendChild(d);setTimeout(()=>d.remove(),1500)}
 if(window.WarqnaSound && !window.WarqnaSound.cycleVolume){ window.WarqnaSound.cycleVolume=function(){window._soundVolumeLevel=(window._soundVolumeLevel+1)%6; if(window._soundVolumeLevel===0){localStorage.soundEnabled='0';}else{localStorage.soundEnabled='1';} localStorage.soundVolumeLevel=window._soundVolumeLevel; let b=$('#soundToggle'); if(b)b.textContent=window._soundVolumeLevel?'🔊 '+(window._soundVolumeLevel*20)+'%':'🔇'; soundPop(); try{window.WarqnaSound.ui()}catch(e){} }; }
 function updateBidBubbles(st){ if(!st)return; $$('.bid-status-bubble').forEach(x=>x.remove()); let latest={}; (st.bids||[]).forEach(b=>{latest[b.player]=b}); Object.entries(latest).forEach(([player,b])=>{let seat=document.querySelector('.seat-profile[data-player-key="'+CSS.escape(player)+'"]'); if(!seat)return; let s=document.createElement('span');s.className='bid-status-bubble '+(b.type==='pass'?'pass':'');s.textContent=b.type==='pass'?'تمرير':'طلب '+b.value;seat.appendChild(s);}); }
 function polishPanels(){ $$('#gamesCurtain,.top-panel,.notification-drawer,#notifPanel,#msgPanel').forEach(p=>{p.style.overflow='visible';p.style.maxHeight='none';}); }
 const oldRender=window.renderState; window.renderState=function(st){ oldRender&&oldRender(st); if(isTarneeb())updateBidBubbles(st); polishPanels(); };
 const oldAction=window.roomAction; window.roomAction=async function(action,payload={}){ if(isTarneeb()&&action==='bid'){let b=document.querySelector('.tarneeb-bid[data-value="'+payload.value+'"]'); if(b)b.classList.add('bid-clicked'); setTimeout(()=>b?.classList.remove('bid-clicked'),900);} return oldAction?oldAction(action,payload):null; };
 document.addEventListener('DOMContentLoaded',()=>{polishPanels(); let b=$('#soundToggle'); if(b&&window._soundVolumeLevel)b.textContent='🔊 '+(window._soundVolumeLevel*20)+'%';});
})();


// v108: volume slider, dynamic themes, no-scroll panels, professional game dialogs, store previews, Tarneeb UI fixes.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  const THEMES=['royal','midnight','emerald','desert','galaxy','crimson','ocean'];
  function applyTheme(theme,save=true){
    if(!THEMES.includes(theme)) return;
    document.body.className=document.body.className.replace(/\btheme-[^\s]+/g,'').trim()+' theme-'+theme;
    document.body.dataset.theme=theme;
    localStorage.warqnaTheme=theme;
    $$('.theme-grid-v108 [data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    if(save && window.PREF_URL){
      fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
    }
    window.WarqnaSound?.ui?.();
  }
  window.setSiteTheme=applyTheme;

  function initThemePicker(){
    const stored=localStorage.warqnaTheme;
    if(stored) applyTheme(stored,false);
    $$('.theme-grid-v108 [data-theme-pick]').forEach(btn=>btn.onclick=()=>{applyTheme(btn.dataset.themePick,true);closeTopPanelsExcept(null);});
  }

  function setVolume(v,show=true){
    v=Math.max(0,Math.min(100,Number(v)||0));
    localStorage.soundVolume=v;
    localStorage.soundEnabled=v>0?'1':'0';
    window.WARQNA_VOLUME=v/100;
    if(window.WarqnaSound) window.WarqnaSound.volume=window.WARQNA_VOLUME;
    ['soundVolumeRange','settingsSoundRange'].forEach(id=>{const el=document.getElementById(id); if(el && Number(el.value)!==v) el.value=v;});
    const btn=$('#soundToggle'); if(btn) btn.textContent=v===0?'🔇':'🔊 '+v+'%';
    if(show) soundToast(v);
  }
  function soundToast(v){
    let d=$('.sound-level-pop'); if(!d){d=document.createElement('div'); d.className='sound-level-pop v108-volume-pop'; document.body.appendChild(d);}
    d.innerHTML='الصوت: '+v+'%<div class="bar"><span style="width:'+v+'%"></span></div><small>استخدم ↑ ↓ للتحكم</small>';
    clearTimeout(d._t); d._t=setTimeout(()=>d.remove(),1300);
  }
  window.setWarqnaVolume=setVolume;
  function initVolume(){
    const initial=Number(localStorage.soundVolume ?? (localStorage.soundVolumeLevel?Number(localStorage.soundVolumeLevel)*20:80));
    setVolume(initial,false);
    ['soundVolumeRange','settingsSoundRange'].forEach(id=>{
      const el=document.getElementById(id); if(!el) return;
      el.value=Math.round(window.WARQNA_VOLUME*100);
      el.addEventListener('input',()=>setVolume(el.value,true));
      el.addEventListener('keydown',e=>{
        if(e.key==='ArrowUp'){e.preventDefault();setVolume(Number(el.value)+1,true)}
        if(e.key==='ArrowDown'){e.preventDefault();setVolume(Number(el.value)-1,true)}
        if(e.key==='PageUp'){e.preventDefault();setVolume(Number(el.value)+10,true)}
        if(e.key==='PageDown'){e.preventDefault();setVolume(Number(el.value)-10,true)}
      });
    });
    if(window.WarqnaSound){
      const oldPlay=window.WarqnaSound.play?.bind(window.WarqnaSound);
      window.WarqnaSound.play=function(name){ if((Number(localStorage.soundVolume||0))<=0) return; try{oldPlay&&oldPlay(name);}catch(e){} };
      window.WarqnaSound.toggleMute=function(){ setVolume(Number(localStorage.soundVolume||80)>0?0:80,true); };
      window.WarqnaSound.cycleVolume=function(){ setVolume((Number(localStorage.soundVolume||0)+10)%110,true); };
    }else{
      window.WarqnaSound={volume:window.WARQNA_VOLUME,play(){},ui(){},toggleMute(){setVolume(Number(localStorage.soundVolume||80)>0?0:80,true)}};
    }
  }

  function closeTopPanelsExcept(id){
    $$('.top-panel,.games-curtain').forEach(p=>{ if(!id || p.id!==id) p.classList.add('hidden'); });
    document.body.classList.toggle('v108-panel-open',!!id);
  }
  const oldToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    const p=document.getElementById(id); if(!p) return oldToggle&&oldToggle(id);
    const willOpen=p.classList.contains('hidden');
    closeTopPanelsExcept(willOpen?id:null);
    if(willOpen){p.classList.remove('hidden');p.classList.add('v108-pop-panel');}
    document.body.classList.toggle('v108-panel-open',willOpen);
  };
  document.addEventListener('click',e=>{
    if(!e.target.closest('.top-panel,.top-icons,.nav-drop-btn,.theme-switch-btn,.games-curtain')) closeTopPanelsExcept(null);
  });
  document.addEventListener('keydown',e=>{ if(e.key==='Escape'){ closeTopPanelsExcept(null); $('#confirmDialog')?.classList.add('hidden'); $('#profileModal')?.classList.add('hidden'); } });

  function professionalDialogs(){
    window.showNotice=function(message){
      let d=$('#confirmDialog'); 
      if(!d){d=document.createElement('div');d.id='confirmDialog';d.className='confirm-dialog hidden';document.body.appendChild(d);}
      d.innerHTML=`<div class="confirm-card notice-card v108-game-dialog"><button class="modal-x" type="button" onclick="document.getElementById('confirmDialog').classList.add('hidden')">×</button><div class="dialog-icon">🎮</div><div class="notice-body">${safe(message)}</div></div>`;
      d.classList.remove('hidden');
    };
    window.showRichNotice=function(html){
      let d=$('#confirmDialog'); 
      if(!d){d=document.createElement('div');d.id='confirmDialog';d.className='confirm-dialog hidden';document.body.appendChild(d);}
      d.innerHTML=`<div class="confirm-card notice-card v108-game-dialog"><button class="modal-x" type="button" onclick="document.getElementById('confirmDialog').classList.add('hidden')">×</button><div class="notice-body">${String(html||'')}</div></div>`;
      d.classList.remove('hidden');
    };
  }

  function initEmojiVisibility(){
    const palette=$('#emojiPalette');
    if(!palette) return;
    palette.classList.add('hidden');
    const input=$('#chatInput');
    if(input){
      input.addEventListener('focus',()=>palette.classList.remove('hidden'));
      input.addEventListener('blur',()=>setTimeout(()=>palette.classList.add('hidden'),350));
    }
  }

  function isTarneeb(){return ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||'')}
  function polishTarneeb(st){
    if(!isTarneeb()) return;
    const phase=st?.phase||window.LAST_STATE?.phase||'';
    document.body.classList.toggle('v108-bidding',phase==='bidding');
    document.body.classList.toggle('v108-choose-trump',phase==='choose_trump');
    $$('#actionPanel .tarneeb-bid').forEach(btn=>{
      const v=btn.dataset.value||btn.textContent.replace(/\D/g,'');
      btn.innerHTML='<span>'+safe(v)+'</span>';
      btn.title='اعتماد طلب '+safe(v);
      btn.disabled=(phase==='bidding' && Number(v)<=Number(st?.bid?.value||window.LAST_STATE?.bid?.value||6));
    });
    $$('#actionPanel .hand-btn,#actionPanel .backgammon-btn,#actionPanel .domino-btn,#actionPanel .meld-btn,#actionPanel .sort-btn,#actionPanel .estimation-bid-grid').forEach(x=>x.remove());
    $$('#actionPanel .tarneeb-request-panel').forEach(x=>x.style.display=phase==='bidding'?'flex':'none');
    $$('#actionPanel .trump-chooser-panel').forEach(x=>x.style.display=phase==='choose_trump'?'flex':'none');
    if(phase==='choose_trump') showOnce('trump_'+(st?.round||''),'اختر نوع الطرنيب الآن. الأحمر أحمر والأسود أسود.');
  }
  function showOnce(k,msg){window._v108Once=window._v108Once||{}; if(window._v108Once[k])return; window._v108Once[k]=1; setTimeout(()=>window.showNotice&&showNotice(msg),150);}
  const oldRender=window.renderState;
  window.renderState=function(st){window.LAST_STATE=st||window.LAST_STATE; oldRender&&oldRender(st); polishTarneeb(st); fitSeats();};
  const oldRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    if(isTarneeb()){
      const phase=window.LAST_STATE?.phase||'';
      if(action==='bid'){
        const v=Number(payload.value||0), cur=Number(window.LAST_STATE?.bid?.value||6);
        if(phase!=='bidding') return showNotice('انتهت مرحلة الطلب. انتظر اختيار الطرنيب أو بداية اللعب.');
        if(v<7||v>13||v<=cur) return showNotice('الطلب غير صحيح: يجب أن يكون من 7 إلى 13 وأعلى من الطلب الحالي.');
        $$('.tarneeb-bid').forEach(b=>b.disabled=true);
      }
      if(action==='choose_trump' && phase!=='choose_trump') return showNotice('اختيار الطرنيب يظهر فقط بعد انتهاء الطلب لصاحب أعلى طلب.');
    }
    return oldRoomAction?oldRoomAction(action,payload):null;
  };

  function fitSeats(){
    $$('.seat-profile').forEach(s=>{
      s.classList.add('v108-seat-front');
      s.style.zIndex=80;
      const img=s.querySelector('img'); if(img) img.loading='lazy';
    });
  }

  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-table')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=(card.dataset.tier||card.dataset.subcategory||'pro').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-emoji_pack')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=(card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||'vip').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  window.previewStoreItem=function(btn){
    const card=btn.closest?.('.store-card,.store-admin-row,.admin-store-row')||btn.closest?.('form')||btn;
    if(!card) return;
    const cat=card.dataset.category||card.getAttribute('data-category')||'';
    const name=card.querySelector('h3,b,[name="name_ar"]')?.value||card.querySelector('h3,b')?.textContent||'مقتنى';
    const price=card.querySelector('.price')?.textContent||card.querySelector('[name="price"]')?.value||'';
    let visual=card.querySelector('.shop-icon,.admin-item-preview')?.innerHTML||'🎁';
    if(cat==='table'){
      visual='<div class="v108-real-table-preview"><div class="mock-table"><span class="p north"></span><span class="p east"></span><span class="p south"></span><span class="p west"></span><div class="mock-hand">🂡 🂮 🂭 🂫</div></div></div>';
    }else if(cat==='card_back'){
      visual='<div class="v108-cardback-preview"><span>🂠</span><span>🂠</span><span>🂠</span><b>ظهر الورق الحقيقي</b></div>';
    }else if(cat==='name_color'||cat==='text_color'){
      const color=card.dataset.color||'#facc15';
      visual='<div class="v108-profile-preview"><img src="/assets/avatars/default.svg"><b style="color:'+safe(color)+';text-shadow:0 0 14px '+safe(color)+'">اسم اللاعب</b><p style="color:'+safe(color)+'">كلمة تجريبية</p></div>';
    }
    showRichNotice(`<div class="store-preview-pop v108-preview-pop"><h3>${safe(name)}</h3><div class="v108-preview-area">${visual}</div><p class="price">${safe(price)}</p><button class="primary" onclick="document.getElementById('confirmDialog')?.classList.add('hidden')">إغلاق</button></div>`);
  };

  document.addEventListener('DOMContentLoaded',()=>{
    initVolume(); initThemePicker(); professionalDialogs(); initEmojiVisibility(); fitSeats(); polishTarneeb(window.LAST_STATE||null);
    $$('.theme-grid-v108 [data-theme-pick="'+(localStorage.warqnaTheme||document.body.dataset.theme||'royal')+'"]').forEach(b=>b.classList.add('active'));
    $$('.store-tabs button,.sub-tabs button,.admin-tabs button,.admin-store-tabs button,button,.btn').forEach(b=>b.classList.add('v108-centered-btn'));
  });
})();


// v111 stable requested fixes: themes, games panel, chat reopen, card sorting, last trick side, previews.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const clean=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const themes=['royal','midnight','emerald','desert','galaxy','crimson','ocean'];

  function saveTheme(theme){
    if(!themes.includes(theme)) return;
    document.body.classList.remove(...Array.from(document.body.classList).filter(c=>c.startsWith('theme-')));
    document.body.classList.add('theme-'+theme,'v111-themed');
    document.body.dataset.theme=theme;
    document.documentElement.dataset.theme=theme;
    localStorage.warqnaTheme=theme;
    $$('.theme-grid-v108 [data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    if(window.PREF_URL){
      fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
    }
    if(window.showNotice) showNotice('تم تفعيل الثيم بنجاح');
  }
  window.setSiteTheme=saveTheme;

  function bindThemeButtons(){
    const current=localStorage.warqnaTheme || document.body.dataset.theme || 'royal';
    if(themes.includes(current)){
      document.body.classList.add('theme-'+current,'v111-themed');
      document.documentElement.dataset.theme=current;
    }
    $$('.theme-grid-v108 [data-theme-pick]').forEach(btn=>{
      btn.onclick=function(e){e.preventDefault();saveTheme(btn.dataset.themePick);};
      btn.classList.toggle('active',btn.dataset.themePick===current);
    });
  }

  function toggleGames(){
    const p=$('#gamesCurtain'); if(!p) return;
    p.classList.toggle('hidden');
    p.classList.add('v111-games-panel');
    if(!p.classList.contains('hidden')) p.scrollIntoView({behavior:'smooth',block:'nearest'});
  }
  const previousToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    if(id==='gamesCurtain') return toggleGames();
    return previousToggle ? previousToggle(id) : null;
  };

  // close dialogs/panels by clicking outside
  document.addEventListener('click',function(e){
    if(e.target.matches('.confirm-dialog')) e.target.classList.add('hidden');
    if(e.target.matches('#profileModal')) e.target.classList.add('hidden');
    if(!e.target.closest('.top-panel,.theme-switch-btn,.nav-drop-btn,.games-curtain,.top-icons')){
      $$('.top-panel').forEach(p=>p.classList.add('hidden'));
    }
  },true);
  document.addEventListener('keydown',function(e){
    if(e.key==='Escape'){
      $$('.confirm-dialog,#profileModal,.top-panel,.games-curtain').forEach(p=>p.classList.add('hidden'));
    }
  });

  // chat close returns as bottom icon
  window.closeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d) d.classList.add('hidden');
    if(r) r.classList.remove('hidden');
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d) d.classList.remove('hidden','chat-minimized','minimized','closed');
    if(r) r.classList.add('hidden');
    localStorage.chatState='open';
  };

  function sortCardsVisual(){
    const suitOrder={clubs:1,diamonds:2,spades:3,hearts:4};
    const rankOrder={'A':14,'K':13,'Q':12,'J':11,'10':10,'9':9,'8':8,'7':7,'6':6,'5':5,'4':4,'3':3,'2':2};
    $$('.hand-row').forEach(row=>{
      const cards=$$('.card',row);
      cards.forEach(c=>{
        const id=c.dataset.card||c.getAttribute('data-card')||'';
        const parts=id.split('_');
        c.dataset.sortKey=(suitOrder[parts[1]]||9)*100-(rankOrder[parts[0]]||0);
        c.classList.add('v111-card-polish');
      });
      cards.sort((a,b)=>Number(a.dataset.sortKey||0)-Number(b.dataset.sortKey||0)).forEach(c=>row.appendChild(c));
    });
  }

  function moveLastTrick(){
    const mini=$('#lastTrickMini');
    const table=$('.game-table');
    if(mini && table && !mini.classList.contains('v111-side-trick')){
      mini.classList.add('v111-side-trick');
      table.appendChild(mini);
    }
  }

  function isTarneeb(){return ['tarneeb','tarneeb_400','tarneeb_41'].includes(window.GAME_KEY||'');}
  const oldRender=window.renderState;
  window.renderState=function(st){
    if(oldRender) oldRender(st);
    window.LAST_STATE=st||window.LAST_STATE;
    sortCardsVisual();
    moveLastTrick();
    if(isTarneeb()){
      const phase=st?.phase||window.LAST_STATE?.phase||'';
      $$('#actionPanel .hand-btn,#actionPanel .backgammon-btn,#actionPanel .domino-btn,#actionPanel .meld-btn,#actionPanel .sort-btn,#actionPanel .estimation-bid-grid').forEach(x=>x.remove());
      $$('#actionPanel .tarneeb-bid').forEach(btn=>{
        const val=btn.dataset.value||btn.textContent.replace(/\D/g,'');
        btn.innerHTML='<span>'+clean(val)+'</span>';
        btn.disabled=(phase==='bidding' && Number(val)<=Number(st?.bid?.value||6));
      });
      $$('#actionPanel .tarneeb-request-panel').forEach(x=>x.style.display=phase==='bidding'?'flex':'none');
      $$('#actionPanel .trump-chooser-panel').forEach(x=>x.style.display=phase==='choose_trump'?'flex':'none');
    }
  };

  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-table')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=(card.dataset.tier||card.dataset.subcategory||card.dataset.kind||'pro').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document.querySelector('.category-emoji_pack')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=(card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||card.dataset.tier||'vip').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  window.previewStoreItem=function(btn){
    const card=btn.closest?.('.store-card,.store-admin-row,.admin-store-row')||btn.closest?.('form')||btn;
    const cat=card?.dataset?.category||card?.getAttribute?.('data-category')||'';
    const name=card?.querySelector?.('h3,b')?.textContent||card?.querySelector?.('[name="name_ar"]')?.value||'مقتنى';
    let visual=card?.querySelector?.('.shop-icon,.admin-item-preview')?.innerHTML||'🎁';
    if(cat==='table'){
      visual='<div class="v111-live-table-preview"><div class="v111-table"><span class="seat n"></span><span class="seat e"></span><span class="seat s"></span><span class="seat w"></span><div class="cards">🂡 🂮 🂭 🂫 🂪</div></div></div>';
    }else if(cat==='card_back'){
      visual='<div class="v111-card-preview"><span>🂠</span><span>🂠</span><span>🂠</span><small>معاينة ظهر الورق داخل اللعبة</small></div>';
    }else if(cat==='emoji_pack'){
      visual='<div class="v111-emoji-preview">😂 😡 😢 🥳 ✨<small>الإيموجز تظهر عند إرسال رسالة فقط</small></div>';
    }
    if(window.showRichNotice){
      showRichNotice(`<div class="v111-preview-pop"><h3>${clean(name)}</h3>${visual}<button class="primary" onclick="document.getElementById('confirmDialog')?.classList.add('hidden')">إغلاق</button></div>`);
    }
  };

  document.addEventListener('DOMContentLoaded',function(){
    bindThemeButtons();
    sortCardsVisual();
    moveLastTrick();
    $('#chatReopen')?.classList.add('v111-reopen-chat');
    $$('.curtain-grid a').forEach(a=>a.classList.add('v111-game-tile'));
  });
})();


// v112 final stability polish: notifications page, friend actions, chat resize, store category filtering, no theme popup on load.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const esc=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const THEMES=['royal','midnight','emerald','desert','galaxy','crimson','ocean'];

  function applyThemeV112(theme, save=true, notify=false){
    if(!THEMES.includes(theme)) return;
    document.body.classList.remove(...Array.from(document.body.classList).filter(c=>c.startsWith('theme-')));
    document.body.classList.add('theme-'+theme,'v112-themed');
    document.body.dataset.theme=theme;
    document.documentElement.dataset.theme=theme;
    localStorage.warqnaTheme=theme;
    $$('.theme-grid-v108 [data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    if(save && window.PREF_URL){
      fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
    }
    if(notify && window.showNotice) showNotice('تم تفعيل الثيم بنجاح');
  }
  window.setSiteTheme=applyThemeV112;

  function bindTheme(){
    const initial=localStorage.warqnaTheme||document.body.dataset.theme||'royal';
    if(THEMES.includes(initial)) applyThemeV112(initial,false,false);
    $$('.theme-grid-v108 [data-theme-pick]').forEach(btn=>{
      btn.onclick=e=>{e.preventDefault(); applyThemeV112(btn.dataset.themePick,true,true);};
    });
  }

  function goNotifications(){
    location.href='/notifications';
  }

  // Override notification buttons to navigate.
  function bindNotifications(){
    $$('[data-notif-type="club"],[data-notif-type="game"],[data-notif-type="competition"]').forEach(b=>b.onclick=goNotifications);
    $$('.nav-drop-btn').forEach(a=>{ if((a.textContent||'').includes('الإشعارات')) a.onclick=null; });
  }

  // Games panel becomes a full elegant panel inside page.
  const oldToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    if(id==='gamesCurtain'){
      const p=$('#gamesCurtain'); if(!p) return;
      p.classList.toggle('hidden');
      p.classList.add('v112-games-full');
      if(!p.classList.contains('hidden')) p.scrollIntoView({behavior:'smooth',block:'start'});
      return;
    }
    return oldToggle?oldToggle(id):null;
  };

  // Chat: draggable + resizable + closes to icon.
  function initChat(){
    const dock=$('#chatDock'), reopen=$('#chatReopen'); if(!dock) return;
    dock.classList.add('v112-chat');
    dock.style.resize='both';
    dock.style.overflow='hidden';
    const head=dock.querySelector('.chat-head');
    if(head && !head.dataset.dragReady){
      head.dataset.dragReady='1';
      let sx=0, sy=0, ox=0, oy=0, drag=false;
      head.addEventListener('mousedown',e=>{
        if(e.target.closest('button')) return;
        drag=true; sx=e.clientX; sy=e.clientY; const r=dock.getBoundingClientRect(); ox=r.left; oy=r.top; dock.style.bottom='auto'; dock.style.right='auto'; dock.style.position='fixed';
        document.body.classList.add('dragging-chat');
      });
      document.addEventListener('mousemove',e=>{
        if(!drag) return;
        dock.style.left=Math.max(8,Math.min(window.innerWidth-dock.offsetWidth-8,ox+(e.clientX-sx)))+'px';
        dock.style.top=Math.max(8,Math.min(window.innerHeight-dock.offsetHeight-8,oy+(e.clientY-sy)))+'px';
      });
      document.addEventListener('mouseup',()=>{drag=false;document.body.classList.remove('dragging-chat');});
    }
    window.closeChat=function(){ dock.classList.add('hidden'); reopen?.classList.remove('hidden'); localStorage.chatState='closed'; };
    window.minimizeChat=function(){ dock.classList.toggle('chat-minimized'); reopen?.classList.add('hidden'); localStorage.chatState='minimized'; };
    window.maximizeChat=function(){ dock.classList.remove('hidden','chat-minimized'); dock.classList.toggle('chat-expanded'); reopen?.classList.add('hidden'); localStorage.chatState='open'; };
    window.reopenChat=function(){ dock.classList.remove('hidden','chat-minimized','minimized','closed'); reopen?.classList.add('hidden'); localStorage.chatState='open'; };
  }

  function inferTier(card){
    const raw=(card.dataset.tier||card.dataset.subcategory||card.dataset.kind||card.dataset.itemKey||card.className||card.textContent||'').toLowerCase();
    if(raw.includes('beginner')||raw.includes('مبتد')) return 'beginner';
    if(raw.includes('medium')||raw.includes('متوسط')) return 'medium';
    if(raw.includes('featured')||raw.includes('premium')||raw.includes('مميز')) return 'featured';
    if(raw.includes('legend')||raw.includes('أسطور')) return 'legendary';
    if(raw.includes('animated')||raw.includes('متحرك')) return 'animated';
    if(raw.includes('pro')||raw.includes('محترف')) return 'pro';
    return 'pro';
  }
  function inferEmoji(card){
    const raw=(card.dataset.emojiTier||card.dataset.tier||card.textContent||card.className||'').toLowerCase();
    if(raw.includes('free')||raw.includes('مجاني')) return 'free';
    if(raw.includes('laugh')||raw.includes('ضحك')||raw.includes('😂')||raw.includes('🤣')) return 'laugh';
    if(raw.includes('happy')||raw.includes('فرح')||raw.includes('🥳')||raw.includes('😊')) return 'happy';
    if(raw.includes('angry')||raw.includes('عصب')||raw.includes('😡')) return 'angry';
    if(raw.includes('sad')||raw.includes('حزن')||raw.includes('😢')||raw.includes('😭')) return 'sad';
    if(raw.includes('animated')||raw.includes('متحرك')) return 'animated';
    if(raw.includes('vip')) return 'vip';
    return 'vip';
  }

  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=inferTier(card);
      card.dataset.tier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=inferEmoji(card);
      card.dataset.emojiTier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  // Better store/admin previews
  window.previewStoreItem=function(btn){
    const card=btn.closest?.('.store-card,.store-admin-row,.admin-store-row')||btn.closest?.('form')||btn;
    const cat=card?.dataset?.category||card?.getAttribute?.('data-category')||'';
    const name=card?.querySelector?.('h3,b')?.textContent||card?.querySelector?.('[name="name_ar"]')?.value||'مقتنى';
    let visual=card?.querySelector?.('.shop-icon,.admin-item-preview')?.innerHTML||'🎁';
    if(cat==='table'){
      visual='<div class="v112-live-table-preview"><div class="v112-table"><span class="seat n"></span><span class="seat e"></span><span class="seat s"></span><span class="seat w"></span><div class="cards">🂡 🂮 🂭 🂫 🂪</div></div></div>';
    }else if(cat==='card_back'){
      visual='<div class="v112-card-preview"><span>🂠</span><span>🂠</span><span>🂠</span><small>معاينة ظهر الورق داخل اللعبة</small></div>';
    }else if(cat==='emoji_pack'){
      visual='<div class="v112-emoji-preview">😂 🤣 😡 😢 🥳 ✨<small>تظهر هذه الإيموجز في الدردشة بعد الشراء أو التفعيل</small></div>';
    }else if(cat==='xp_booster'){
      visual='<div class="v112-booster-preview"><span>✈️</span><b>مسرّع XP</b><small>مضاعفة نقاط الخبرة لمدة محددة</small></div>';
    }
    showRichNotice?.(`<div class="v112-preview-pop"><h3>${esc(name)}</h3>${visual}<button class="primary" onclick="document.getElementById('confirmDialog')?.classList.add('hidden')">إغلاق</button></div>`);
  };

  // Friend forms like social networks: no refresh, button changes.
  document.addEventListener('submit',async e=>{
    const form=e.target;
    if(!form.matches('[data-ajax-soft="1"],[data-ajax-profile-action="1"]')) return;
    e.preventDefault();
    const btn=form.querySelector('button');
    const old=btn?btn.textContent:'';
    if(btn){btn.disabled=true;btn.textContent='جاري...';}
    try{
      const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تمت العملية':'تمت العملية'};
      showNotice?.(j.message||'تمت العملية');
      if(btn){
        if(j.status==='pending'||old.includes('طلب')) btn.textContent='تم إرسال الطلب';
        else if(form.action.includes('/respond')) btn.textContent='تم';
        else btn.textContent='تم';
      }
      form.closest('.friend-row')?.classList.add('friend-row-done');
    }catch(err){ if(btn){btn.disabled=false;btn.textContent=old;} showNotice?.('تعذر تنفيذ العملية الآن'); }
  },true);

  function initProfileBadges(){
    $$('.favorite-game-line-v112').forEach(x=>x.classList.add('show'));
    $$('.country-line').forEach(x=>x.classList.add('country-line-v112'));
  }

  document.addEventListener('DOMContentLoaded',()=>{
    bindTheme(); bindNotifications(); initChat(); initProfileBadges();
    $('#chatReopen')?.classList.add('v112-reopen-chat');
    $$('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(c=>c.dataset.tier=inferTier(c));
    $$('.store-card[data-category="emoji_pack"]').forEach(c=>c.dataset.emojiTier=inferEmoji(c));
    $$('.curtain-grid a').forEach(a=>a.classList.add('v112-game-tile'));
  });
})();


// v113 focused final polish: hard notification links, no theme popup on load, advanced chat drag/resize, full games modal, real store filtering/previews.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const themeList=['royal','midnight','emerald','desert','galaxy','crimson','ocean'];

  // Themes: apply silently on load, only show dialog on real click.
  function applyTheme(theme, save=false, showMessage=false){
    if(!themeList.includes(theme)) return;
    document.body.classList.remove(...Array.from(document.body.classList).filter(c=>c.startsWith('theme-')));
    document.body.classList.add('theme-'+theme,'v113-themed');
    document.documentElement.dataset.theme=theme;
    document.body.dataset.theme=theme;
    localStorage.warqnaTheme=theme;
    $$('.theme-grid-v108 [data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    if(save && window.PREF_URL){
      fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
    }
    if(showMessage && window.showNotice) showNotice('تم تفعيل الثيم بنجاح');
  }
  window.setSiteTheme=applyTheme;

  function bindThemeButtons(){
    const initial=localStorage.warqnaTheme||document.body.dataset.theme||'royal';
    applyTheme(initial,false,false);
    $$('.theme-grid-v108 [data-theme-pick]').forEach(btn=>{
      btn.onclick=e=>{e.preventDefault();applyTheme(btn.dataset.themePick,true,true);};
    });
  }

  function hardenNotificationLinks(){
    $$('[data-notif-type],.notif-page-go').forEach(btn=>{
      const type=btn.dataset.notifType||'';
      if(['club','game','competition'].includes(type) || btn.classList.contains('notif-page-go')){
        btn.onclick=e=>{e.preventDefault(); window.location.href='/notifications';};
      }
    });
    $$('.nav-drop-btn,.notif-page-link').forEach(el=>{
      if((el.textContent||'').includes('الإشعارات')){ el.onclick=null; if(el.tagName!=='A') el.onclick=()=>location.href='/notifications'; }
    });
  }

  // Full screen all games modal, not floating weird.
  function openGamesFull(){
    const p=$('#gamesCurtain'); if(!p) return;
    p.classList.toggle('hidden');
    p.classList.add('v113-games-fullscreen');
    document.body.classList.toggle('v113-games-open',!p.classList.contains('hidden'));
  }
  const oldToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    if(id==='gamesCurtain') return openGamesFull();
    return oldToggle?oldToggle(id):null;
  };

  // Chat drag + resize + close-to-icon
  function initChatPro(){
    const d=$('#chatDock'), r=$('#chatReopen'); if(!d) return;
    d.classList.add('v113-chat-pro');
    d.style.resize='both';
    d.style.overflow='hidden';
    if(r){ r.classList.add('v113-chat-icon'); r.onclick=e=>{e.preventDefault(); window.reopenChat&&window.reopenChat();}; }
    const head=d.querySelector('.chat-head');
    if(head && !head.dataset.v113Drag){
      head.dataset.v113Drag='1';
      let down=false, sx=0, sy=0, ox=0, oy=0;
      head.addEventListener('mousedown',e=>{
        if(e.target.closest('button')) return;
        down=true; sx=e.clientX; sy=e.clientY;
        const rect=d.getBoundingClientRect(); ox=rect.left; oy=rect.top;
        d.style.position='fixed'; d.style.left=ox+'px'; d.style.top=oy+'px'; d.style.right='auto'; d.style.bottom='auto';
        document.body.classList.add('v113-dragging');
      });
      document.addEventListener('mousemove',e=>{
        if(!down) return;
        d.style.left=Math.max(8,Math.min(window.innerWidth-d.offsetWidth-8,ox+(e.clientX-sx)))+'px';
        d.style.top=Math.max(8,Math.min(window.innerHeight-d.offsetHeight-8,oy+(e.clientY-sy)))+'px';
      });
      document.addEventListener('mouseup',()=>{down=false;document.body.classList.remove('v113-dragging');});
    }
    window.closeChat=function(){ d.classList.add('hidden'); r?.classList.remove('hidden'); localStorage.chatState='closed'; };
    window.minimizeChat=function(){ d.classList.toggle('chat-minimized'); r?.classList.add('hidden'); localStorage.chatState='minimized'; };
    window.maximizeChat=function(){ d.classList.remove('hidden','chat-minimized','minimized','closed'); d.classList.toggle('chat-expanded'); r?.classList.add('hidden'); localStorage.chatState='open'; };
    window.reopenChat=function(){ d.classList.remove('hidden','chat-minimized','minimized','closed'); r?.classList.add('hidden'); localStorage.chatState='open'; };
  }

  function inferTableTier(card){
    const raw=(card.dataset.tier||card.dataset.subcategory||card.dataset.kind||card.dataset.itemKey||card.className||card.textContent||'').toLowerCase();
    if(raw.includes('beginner')||raw.includes('مبتد')) return 'beginner';
    if(raw.includes('medium')||raw.includes('متوسط')) return 'medium';
    if(raw.includes('featured')||raw.includes('premium')||raw.includes('advanced')||raw.includes('مميز')) return 'featured';
    if(raw.includes('legend')||raw.includes('أسطور')) return 'legendary';
    if(raw.includes('animated')||raw.includes('motion')||raw.includes('متحرك')) return 'animated';
    if(raw.includes('pro')||raw.includes('محترف')||raw.includes('احترافي')) return 'pro';
    return 'pro';
  }
  function inferEmojiTier(card){
    const raw=(card.dataset.emojiTier||card.dataset.tier||card.dataset.itemKey||card.textContent||card.className||'').toLowerCase();
    if(raw.includes('free')||raw.includes('مجاني')) return 'free';
    if(raw.includes('laugh')||raw.includes('ضحك')||raw.includes('😂')||raw.includes('🤣')) return 'laugh';
    if(raw.includes('happy')||raw.includes('فرح')||raw.includes('🥳')||raw.includes('😊')||raw.includes('😍')) return 'happy';
    if(raw.includes('angry')||raw.includes('عصب')||raw.includes('😡')) return 'angry';
    if(raw.includes('sad')||raw.includes('حزن')||raw.includes('😢')||raw.includes('😭')) return 'sad';
    if(raw.includes('animated')||raw.includes('متحرك')) return 'animated';
    if(raw.includes('vip')||raw.includes('pro')||raw.includes('legend')) return 'vip';
    return 'vip';
  }
  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=inferTableTier(card); card.dataset.tier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=inferEmojiTier(card); card.dataset.emojiTier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };

  // Premium previews for store/admin items
  window.previewStoreItem=function(btn){
    const card=btn.closest?.('.store-card,.store-admin-row,.admin-store-row')||btn.closest?.('form')||btn;
    const cat=card?.dataset?.category||card?.getAttribute?.('data-category')||'';
    const name=card?.querySelector?.('h3,b')?.textContent||card?.querySelector?.('[name="name_ar"]')?.value||'مقتنى';
    let visual=card?.querySelector?.('.shop-icon,.admin-item-preview')?.innerHTML||'🎁';
    if(cat==='table'){
      const tier=inferTableTier(card);
      visual=`<div class="v113-table-preview ${tier}"><div class="table-surface"><span class="seat n"></span><span class="seat e"></span><span class="seat s"></span><span class="seat w"></span><div class="cards">🂡 🂮 🂭 🂫 🂪</div></div></div>`;
    }else if(cat==='card_back'){
      visual='<div class="v113-cardback-preview"><span>🂠</span><span>🂠</span><span>🂠</span><small>معاينة ظهر الورق داخل اللعبة</small></div>';
    }else if(cat==='emoji_pack'){
      visual='<div class="v113-emoji-preview">😂 🤣 😡 😢 🥳 ✨<small>تظهر هذه الإيموجز داخل الدردشة بعد التفعيل</small></div>';
    }else if(cat==='xp_booster'){
      const color=card.querySelector('.xp-shuttle-v113')?.style.getPropertyValue('--booster-color')||'#38bdf8';
      visual=`<div class="v113-booster-preview" style="--booster-color:${color}"><span>🚀</span><b>مكوك XP</b><small>مسرّع خبرة ثلاثي الأبعاد</small></div>`;
    }
    showRichNotice?.(`<div class="v113-preview-pop"><h3>${safe(name)}</h3>${visual}<button class="primary" onclick="document.getElementById('confirmDialog')?.classList.add('hidden')">إغلاق</button></div>`);
  };

  // Friend actions as social network style.
  document.addEventListener('submit',async e=>{
    const form=e.target;
    if(!form.matches('[data-ajax-soft="1"],[data-ajax-profile-action="1"]')) return;
    e.preventDefault();
    const btn=form.querySelector('button');
    const old=btn?btn.textContent:'';
    if(btn){btn.disabled=true;btn.textContent='جاري...';}
    try{
      const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تمت العملية':'تمت العملية'};
      showNotice?.(j.message||'تمت العملية');
      if(btn){
        if(j.status==='pending'||old.includes('طلب')) btn.textContent='تم إرسال الطلب';
        else if(j.status==='cancelled') btn.textContent='تم الإلغاء';
        else if(j.status==='unblocked') btn.textContent='تم إلغاء الحظر';
        else btn.textContent='تم';
      }
      form.closest('.friend-row')?.classList.add('friend-row-done');
    }catch(err){ if(btn){btn.disabled=false;btn.textContent=old;} showNotice?.('تعذر تنفيذ العملية الآن'); }
  },true);

  // Safe popup closing outside.
  document.addEventListener('click',e=>{
    if(e.target.matches('.confirm-dialog')) e.target.classList.add('hidden');
    if(e.target.matches('#profileModal')) e.target.classList.add('hidden');
  },true);

  function init(){
    bindThemeButtons();
    hardenNotificationLinks();
    initChatPro();
    $$('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(c=>c.dataset.tier=inferTableTier(c));
    $$('.store-card[data-category="emoji_pack"]').forEach(c=>c.dataset.emojiTier=inferEmojiTier(c));
    $('#gamesCurtain')?.classList.add('v113-games-fullscreen');
    $('#chatReopen')?.classList.add('v113-chat-icon');
    $$('.curtain-grid a').forEach(a=>a.classList.add('v113-game-tile'));
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v114: professional social/chat/gameplay polish inspired by large card platforms, without copying branding.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const esc=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  function toast(msg,type='info'){
    let wrap=$('.v114-toast-wrap');
    if(!wrap){wrap=document.createElement('div');wrap.className='v114-toast-wrap';document.body.appendChild(wrap);}
    const t=document.createElement('div');
    t.className='v114-toast '+type;
    t.innerHTML='<b>'+esc(msg)+'</b>';
    wrap.appendChild(t);
    setTimeout(()=>{t.classList.add('hide');setTimeout(()=>t.remove(),350)},2200);
  }
  const oldNotice=window.showNotice;
  window.showNotice=function(m){ toast(m||'تمت العملية'); };

  function initChatPro(){
    const dock=$('#chatDock'), reopen=$('#chatReopen');
    if(!dock) return;
    dock.classList.add('v114-chat-pro');
    dock.style.resize='both';
    dock.style.overflow='hidden';
    const head=dock.querySelector('.chat-head');
    if(head && !head.dataset.v114){
      head.dataset.v114='1';
      const status=document.createElement('small');
      status.className='chat-online-dot';
      status.textContent='● متصل';
      head.prepend(status);
      let down=false,sx=0,sy=0,ox=0,oy=0;
      head.addEventListener('mousedown',e=>{
        if(e.target.closest('button')) return;
        down=true; sx=e.clientX; sy=e.clientY;
        const r=dock.getBoundingClientRect(); ox=r.left; oy=r.top;
        dock.style.position='fixed'; dock.style.left=ox+'px'; dock.style.top=oy+'px'; dock.style.right='auto'; dock.style.bottom='auto';
      });
      document.addEventListener('mousemove',e=>{
        if(!down) return;
        dock.style.left=Math.max(8,Math.min(window.innerWidth-dock.offsetWidth-8,ox+(e.clientX-sx)))+'px';
        dock.style.top=Math.max(8,Math.min(window.innerHeight-dock.offsetHeight-8,oy+(e.clientY-sy)))+'px';
      });
      document.addEventListener('mouseup',()=>down=false);
    }
    window.closeChat=function(){ dock.classList.add('hidden'); reopen?.classList.remove('hidden'); localStorage.chatState='closed'; };
    window.reopenChat=function(){ dock.classList.remove('hidden','chat-minimized','minimized','closed'); reopen?.classList.add('hidden'); localStorage.chatState='open'; };
    window.minimizeChat=function(){ dock.classList.toggle('chat-minimized'); localStorage.chatState=dock.classList.contains('chat-minimized')?'minimized':'open'; };
    window.maximizeChat=function(){ dock.classList.toggle('chat-expanded'); dock.classList.remove('hidden','chat-minimized'); reopen?.classList.add('hidden'); localStorage.chatState='open'; };
  }

  // Social friend request UI: similar logical behavior: send / pending / accept / reject / block / unblock.
  document.addEventListener('submit',async e=>{
    const form=e.target;
    if(!form.matches('[data-ajax-soft="1"],[data-ajax-profile-action="1"],.v114-friend-form')) return;
    e.preventDefault();
    const btn=form.querySelector('button');
    const old=btn?btn.textContent:'';
    if(btn){btn.disabled=true;btn.textContent='جاري التنفيذ...';}
    try{
      const r=await fetch(form.action,{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=(r.headers.get('content-type')||'').includes('json')?await r.json():{ok:r.ok,message:r.ok?'تمت العملية':'تمت العملية'};
      toast(j.message||'تمت العملية',j.ok===false?'bad':'ok');
      if(btn){
        let s=j.status||'done';
        btn.dataset.status=s;
        if(s==='pending') btn.textContent='تم إرسال الطلب';
        else if(s==='accepted') btn.textContent='أصدقاء';
        else if(s==='cancelled') btn.textContent='تم إلغاء الطلب';
        else if(s==='blocked') btn.textContent='محظور';
        else if(s==='unblocked') btn.textContent='تم إلغاء الحظر';
        else btn.textContent='تم';
      }
      form.closest('.friend-row')?.classList.add('friend-row-done');
    }catch(err){
      if(btn){btn.disabled=false;btn.textContent=old;}
      toast('تعذر تنفيذ العملية الآن','bad');
    }
  },true);

  function sortCards(){
    const suitOrder={clubs:1,diamonds:2,spades:3,hearts:4,joker:9};
    const rankOrder={'A':14,'K':13,'Q':12,'J':11,'10':10,'9':9,'8':8,'7':7,'6':6,'5':5,'4':4,'3':3,'2':2,'JOKER':20};
    $$('.hand-row').forEach(row=>{
      const cards=$$('.card',row);
      cards.forEach(c=>{
        const id=c.dataset.card||c.getAttribute('data-card')||'';
        const p=id.split('_');
        c.dataset.sortKey=(suitOrder[p[1]]||9)*100-(rankOrder[p[0]]||0);
        c.classList.add('v114-premium-card');
      });
      cards.sort((a,b)=>Number(a.dataset.sortKey||0)-Number(b.dataset.sortKey||0)).forEach(c=>row.appendChild(c));
    });
  }

  // Make room chat look like game social chat.
  function renderRoomMessages(messages){
    if(!messages || !window.CHAT_HAS_ROOM) return;
    const body=$('#chatBody');
    if(!body || window.CHAT_MODE!=='room') return;
    body.innerHTML='';
    messages.forEach(m=>{
      const row=document.createElement('div');
      row.className='chat-msg '+(m.mine?'mine':'');
      row.innerHTML='<b style="color:'+(m.color||'#fff')+'">'+esc(m.name||'لاعب')+'</b><p>'+esc(m.body||'')+'</p><small>'+esc(m.time||'')+'</small>';
      body.appendChild(row);
    });
    body.scrollTop=body.scrollHeight;
  }
  const oldRenderState=window.renderState;
  window.renderState=function(st){
    oldRenderState&&oldRenderState(st);
    sortCards();
    if(st?.last_error_message) toast(st.last_error_message,'bad');
  };
  const oldSync=window.syncRoomState||window.syncState;
  // Patch fetch sync response globally without replacing unknown functions: observe room messages in fetch wrapper.
  const oldFetch=window.fetch;
  window.fetch=async function(...args){
    const res=await oldFetch.apply(this,args);
    try{
      const clone=res.clone();
      const url=String(args[0]||'');
      if(url.includes('/sync')){
        clone.json().then(j=>{ if(j?.room_messages) renderRoomMessages(j.room_messages); }).catch(()=>{});
      }
    }catch(e){}
    return res;
  };

  // Better buttons and dialogs close outside.
  document.addEventListener('click',e=>{
    if(e.target.matches('.confirm-dialog')) e.target.classList.add('hidden');
    if(e.target.matches('#profileModal')) e.target.classList.add('hidden');
  },true);

  function init(){
    initChatPro();
    sortCards();
    $('#chatReopen')?.classList.add('v114-chat-reopen');
    document.body.classList.add('v114-pro-mode');
    $$('.friend-row form').forEach(f=>f.classList.add('v114-friend-form'));
    $$('.room-shell').forEach(r=>r.classList.add('v114-room-shell'));
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v115 professional foundation: admin health, PWA helpers, responsive hardening.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

  function markInstallable(){
    window.addEventListener('beforeinstallprompt',e=>{
      e.preventDefault();
      window.WARQNA_INSTALL_PROMPT=e;
      document.body.classList.add('pwa-installable');
    });
  }

  function responsiveGuard(){
    $$('button,.btn,a.btn,.topbar a,.topbar button,.userbar button,.userbar a').forEach(el=>{
      el.classList.add('v115-safe-control');
      el.style.maxWidth='100%';
    });
    $$('.store-grid,.friends-dashboard,.rules-list,.curtain-grid').forEach(el=>el.classList.add('v115-auto-grid'));
  }

  function adminTabsFix(){
    document.addEventListener('click',e=>{
      const b=e.target.closest('[data-admin-tab]');
      if(!b) return;
      const key=b.dataset.adminTab;
      document.querySelectorAll('.admin-section').forEach(s=>s.classList.remove('active'));
      document.querySelector('#admin-'+key)?.classList.add('active');
      document.querySelectorAll('[data-admin-tab]').forEach(x=>x.classList.toggle('active',x===b));
    });
  }

  function init(){
    markInstallable();
    responsiveGuard();
    adminTabsFix();
    document.body.classList.add('v115-pro-foundation');
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v116 realtime/social/economy/admin monitoring foundation.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const roomCode=()=>document.querySelector('.room-shell')?.dataset?.room||null;

  async function heartbeat(){
    if(!window.AUTH_ID) return;
    try{
      const payload={scope:roomCode()?'room':'site', room_code:roomCode(), meta:{path:location.pathname}};
      const r=await fetch('/realtime/heartbeat',{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify(payload)});
      const j=await r.json();
      renderOnline(j.online||[]);
    }catch(e){}
  }

  function renderOnline(list){
    let box=document.getElementById('onlinePresenceBox');
    if(!box && document.querySelector('.room-shell')){
      box=document.createElement('div');
      box.id='onlinePresenceBox';
      box.className='online-presence-box';
      box.innerHTML='<b>🟢 المتواجدون</b><div></div>';
      document.querySelector('.room-info')?.appendChild(box);
    }
    if(!box) return;
    const body=box.querySelector('div');
    body.innerHTML=(list||[]).slice(0,8).map(u=>`<span><img src="${u.avatar||'/assets/avatars/default.svg'}"> ${u.name}</span>`).join('') || '<small>لا يوجد نشاط حديث</small>';
  }

  window.loadAdminSnapshot=async function(){
    const box=document.getElementById('adminSnapshot');
    if(box) box.innerHTML='<div class="pro-card">جاري تحميل حالة النظام...</div>';
    try{
      const r=await fetch('/admin/monitor/snapshot',{headers:{'Accept':'application/json'}});
      const j=await r.json(); const d=j.data||{};
      if(box){
        box.innerHTML=Object.entries({
          'المستخدمون':d.users,'المتصلون الآن':d.online,'الغرف المفتوحة':d.open_rooms,'رسائل اليوم':d.messages_today,
          'إشعارات غير مقروءة':d.notifications_unread,'صداقات':d.friendships,'عناصر المتجر':d.store_items,'مقتنيات مفعلة':d.active_inventory
        }).map(([k,v])=>`<div class="pro-card monitor-card"><b>${k}</b><span>${v??0}</span></div>`).join('');
      }
    }catch(e){ if(box) box.innerHTML='<div class="pro-card danger">تعذر تحميل حالة النظام</div>'; }
  };

  function init(){
    heartbeat();
    setInterval(heartbeat,30000);
    if(document.getElementById('adminSnapshot')) window.loadAdminSnapshot();
    document.body.classList.add('v116-realtime-ready');
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v117 complete pro roadmap: admin economy UI polish and realtime readiness indicators.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s);
  function addProBadges(){
    const admin=document.querySelector('#admin-monitor');
    if(admin && !admin.querySelector('.v117-monitor-note')){
      const note=document.createElement('div');
      note.className='v117-monitor-note pro-card';
      note.innerHTML='<b>✅ مراقبة احترافية مفعلة</b><p>هذه اللوحة تعرض snapshot مباشر، ومع WebSocket لاحقًا ستتحول لتحديث لحظي كامل.</p>';
      admin.prepend(note);
    }
    const economy=document.querySelector('#admin-economy');
    if(economy && !economy.querySelector('.v117-economy-note')){
      const note=document.createElement('div');
      note.className='v117-economy-note mini-card';
      note.textContent='يمكن من هنا إدارة المواسم والعروض والمقتنيات النادرة بدون تعديل الكود.';
      economy.prepend(note);
    }
  }
  document.addEventListener('DOMContentLoaded',()=>{addProBadges();document.body.classList.add('v117-complete-pro');});
})();


// v118 Gemini radical platform: premium UI, interactions, libraries, anti-cheat hints.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';

  function toast(msg,type='info'){
    let box=$('.v118-toast-box'); if(!box){box=document.createElement('div');box.className='v118-toast-box';document.body.appendChild(box);}
    const t=document.createElement('div'); t.className='v118-toast '+type; t.textContent=msg; box.appendChild(t);
    setTimeout(()=>{t.classList.add('hide');setTimeout(()=>t.remove(),350)},2500);
  }
  const oldNotice=window.showNotice;
  window.showNotice=function(m){toast(m||'تمت العملية','ok');};

  async function heartbeatV118(){
    try{
      if(!window.AUTH_ID) return;
      await fetch('/realtime/heartbeat',{method:'POST',headers:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({scope:document.querySelector('.room-shell')?'room':'site',room_code:document.querySelector('.room-shell')?.dataset?.room||null,meta:{v:'118',path:location.pathname}})});
    }catch(e){}
  }

  function enhanceCards(){
    $$('.hand-row .card,.played-card,.card').forEach(card=>card.classList.add('v118-real-card'));
  }

  function buildQuickInteractionPanel(){
    if(!document.querySelector('.room-shell') || document.querySelector('#v118ThrowPanel')) return;
    const panel=document.createElement('div');
    panel.id='v118ThrowPanel';
    panel.className='v118-throw-panel';
    panel.innerHTML='<b>🎯 تفاعلات سريعة</b><button data-item="tomato">🍅</button><button data-item="rose">🌹</button><button data-item="coffee">☕</button><button data-item="shoe">👟</button><button data-item="smoke">💨</button><button data-item="royal_crown">👑</button>';
    document.body.appendChild(panel);
    panel.addEventListener('click',e=>{
      const b=e.target.closest('button[data-item]'); if(!b) return;
      toast('اختر اللاعب ثم أرسل: '+b.textContent+' — تم تجهيز نظام التفاعلات في V118','info');
    });
  }

  function installFullscreenGameLibrary(){
    $$('.game-pro-card-v118').forEach(card=>{
      card.addEventListener('mouseenter',()=>card.classList.add('hovering'));
      card.addEventListener('mouseleave',()=>card.classList.remove('hovering'));
    });
  }

  function antiCheatVisuals(){
    if(document.querySelector('.room-shell') && !document.querySelector('.anti-cheat-badge-v118')){
      const badge=document.createElement('div');
      badge.className='anti-cheat-badge-v118';
      badge.textContent='🛡️ Server-Authoritative';
      document.querySelector('.room-info')?.appendChild(badge);
    }
  }

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v118-gemini-pro');
    enhanceCards();
    buildQuickInteractionPanel();
    installFullscreenGameLibrary();
    antiCheatVisuals();
    heartbeatV118();
    setInterval(heartbeatV118,30000);
  });
})();


// v119 APK-ready mobile shell: install prompt, mobile API bootstrap, touch safe UI.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s);
  const $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  let deferredInstallPrompt=null;

  function toast(msg){
    const t=$('#mobileSafeToast'); if(!t) return;
    t.textContent=msg; t.classList.remove('hidden');
    clearTimeout(window.__warqnaMobileToastTimer);
    window.__warqnaMobileToastTimer=setTimeout(()=>t.classList.add('hidden'),2600);
  }

  window.addEventListener('beforeinstallprompt',e=>{
    e.preventDefault();
    deferredInstallPrompt=e;
    const btn=$('#installAppBtn');
    if(btn) btn.classList.remove('hidden');
  });

  async function installApp(){
    if(!deferredInstallPrompt){ toast('التثبيت متاح من قائمة المتصفح أو بعد رفع الموقع HTTPS.'); return; }
    deferredInstallPrompt.prompt();
    await deferredInstallPrompt.userChoice.catch(()=>null);
    deferredInstallPrompt=null;
    $('#installAppBtn')?.classList.add('hidden');
  }

  async function bootstrapMobile(){
    try{
      const r=await fetch('/api/mobile/bootstrap',{headers:{'Accept':'application/json'}});
      const j=await r.json();
      window.WARQNA_MOBILE_BOOTSTRAP=j;
      document.body.dataset.apkReady=j.apk_ready?'1':'0';
      if(j.apk_ready) document.body.classList.add('apk-ready-shell');
    }catch(e){}
  }

  function mobileControls(){
    $('#installAppBtn')?.addEventListener('click',installApp);
    $$('.topbar a,.topbar button,.userbar a,.userbar button,.btn,button').forEach(el=>el.classList.add('touch-target-v119'));
    document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px');
    window.addEventListener('resize',()=>document.documentElement.style.setProperty('--vh', (window.innerHeight * 0.01) + 'px'));
  }

  function fixMobileRoom(){
    if(!document.querySelector('.room-shell')) return;
    document.body.classList.add('mobile-room-v119');
    const panels=['#actionPanel','#chatDock','#v118ThrowPanel'];
    panels.forEach(sel=>$(sel)?.classList.add('mobile-panel-v119'));
  }

  function init(){
    document.body.classList.add('v119-apk-mobile-pro');
    bootstrapMobile();
    mobileControls();
    fixMobileRoom();
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v121 final stability: v115-like chat close, full games page, better store previews/tabs, robust card action UX.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  // Chat: close means disappear fully, reopen only from icon. Same spirit as stable v115.
  window.closeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.add('hidden'); d.classList.remove('chat-expanded','chat-minimized','minimized-on-load');}
    if(r){r.classList.remove('hidden'); r.style.display='grid';}
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.remove('hidden','chat-minimized','minimized-on-load'); d.classList.add('chat-expanded'); d.style.display='';}
    if(r){r.classList.add('hidden'); r.style.display='none';}
    localStorage.chatState='open';
  };
  window.minimizeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.toggle('chat-minimized'); d.classList.remove('hidden');}
    if(r)r.classList.add('hidden');
    localStorage.chatState=d?.classList.contains('chat-minimized')?'minimized':'open';
  };
  function initChatV121(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(!d) return;
    d.classList.add('chat-dock-v121');
    if(localStorage.chatState==='closed'){d.classList.add('hidden');r?.classList.remove('hidden');}
    let head=d.querySelector('.chat-head');
    if(head && !head.dataset.v121Drag){
      head.dataset.v121Drag='1';
      let down=false,sx=0,sy=0,ox=0,oy=0;
      head.addEventListener('mousedown',e=>{
        if(e.target.closest('button')) return;
        down=true; sx=e.clientX; sy=e.clientY;
        const rect=d.getBoundingClientRect(); ox=rect.left; oy=rect.top;
        d.style.position='fixed'; d.style.left=ox+'px'; d.style.top=oy+'px'; d.style.right='auto'; d.style.bottom='auto';
      });
      document.addEventListener('mousemove',e=>{
        if(!down) return;
        d.style.left=Math.max(8,Math.min(window.innerWidth-d.offsetWidth-8,ox+(e.clientX-sx)))+'px';
        d.style.top=Math.max(8,Math.min(window.innerHeight-d.offsetHeight-8,oy+(e.clientY-sy)))+'px';
      });
      document.addEventListener('mouseup',()=>down=false);
    }
  }

  // Store filters: tables and card backs share tiers; emojis share emoji tiers.
  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=(card.dataset.tier||'pro').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=(card.dataset.emojiTier||card.getAttribute('data-emoji-tier')||'vip').toLowerCase();
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.previewStoreItem=function(btn){
    const card=btn?.closest?.('.store-card')||btn;
    if(!card) return;
    const title=card.querySelector('h3')?.textContent||'عنصر المتجر';
    const price=card.querySelector('.price')?.textContent||'';
    const cat=card.dataset.category||'';
    const color=card.dataset.color||'#d4af37';
    const tier=card.dataset.tier||'pro';
    const action=(card.closest('form')||card).action||'';
    let visual='';
    if(cat==='table'){
      visual=`<div class="preview-table-v121 tier-${safe(tier)}"><div class="felt"><span class="seat n"></span><span class="seat e"></span><span class="seat s"></span><span class="seat w"></span><div class="center-cards">🂡 🂮 🂭 🂫</div></div></div>`;
    }else if(cat==='card_back'){
      visual=`<div class="preview-cardback-v121"><span>🂠</span><span>🂠</span><span>🂠</span><small>تظهر على أوراقك داخل الطاولة فور التفعيل</small></div>`;
    }else if(cat==='emoji_pack'){
      visual=`<div class="preview-emoji-v121">${safe(card.querySelector('.emoji-store-icon')?.textContent||'😂 🤣 😍 😡 🥳 👑')}<small>تستخدم داخل دردشة الغرفة والأصدقاء</small></div>`;
    }else if(cat==='xp_booster'){
      visual=`<div class="preview-booster-v121" style="--booster-color:${safe(color)}"><span>🚀</span><b>مسرّع خبرة</b><small>ألوان ثلاثية الأبعاد حسب نوع المسرّع</small></div>`;
    }else{
      visual=`<div class="preview-generic-v121">${card.querySelector('.shop-icon')?.innerHTML||'🎁'}</div>`;
    }
    const token=window.CSRF||'';
    const buy=action?`<form method="post" action="${safe(action)}" class="preview-buy-form"><input type="hidden" name="_token" value="${safe(token)}"><button class="primary">شراء الآن</button></form>`:'';
    showRichNotice?.(`<div class="store-preview-pop v121-preview"><h2>${safe(title)}</h2>${visual}<p>${safe(price)}</p><small>معاينة واقعية قبل الشراء، وبعد الشراء يظهر في مشترياتي للتفعيل.</small>${buy}</div>`);
  };

  // UX for Tarneeb actions: clear old errors after successful state render.
  const oldRender=window.renderState;
  window.renderState=function(st){
    oldRender&&oldRender(st);
    if(!st) return;
    document.body.classList.toggle('v121-my-turn', st.turn===window.MY_PLAYER_KEY);
    const panel=$('#actionPanel');
    if(panel){
      panel.classList.toggle('is-bidding', st.phase==='bidding');
      panel.classList.toggle('is-choosing-trump', st.phase==='choose_trump');
      panel.classList.toggle('is-playing', st.phase==='playing');
    }
    if(st.phase==='choose_trump' && st.turn===window.MY_PLAYER_KEY) showNotice?.('اختَر نوع الطرنيب الآن، ثم ستبدأ اللعب مباشرة.');
  };

  document.addEventListener('DOMContentLoaded',()=>{
    initChatV121();
    document.body.classList.add('v121-final-polish');
    document.querySelector('.category-table [data-tier-filter="all"]')?.classList.add('active');
    document.querySelector('.category-card_back [data-tier-filter="all"]')?.classList.add('active');
    document.querySelector('.category-emoji_pack [data-emoji-filter="all"]')?.classList.add('active');
  });
})();


// v122 premium competitor-inspired final polish: full library, stable v115 chat, rich store previews, category inference.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  // v115-style chat: close means fully hidden, only icon returns it.
  window.closeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){ d.classList.add('hidden'); d.classList.remove('chat-expanded','chat-minimized','minimized','closed'); d.style.display='none'; }
    if(r){ r.classList.remove('hidden'); r.style.display='grid'; }
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){ d.classList.remove('hidden','chat-minimized','minimized','closed'); d.classList.add('chat-expanded','chat-dock-v122'); d.style.display=''; }
    if(r){ r.classList.add('hidden'); r.style.display='none'; }
    localStorage.chatState='open';
  };
  window.minimizeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(!d) return;
    d.style.display='';
    d.classList.toggle('chat-minimized');
    d.classList.remove('hidden');
    r?.classList.add('hidden');
    localStorage.chatState=d.classList.contains('chat-minimized')?'minimized':'open';
  };
  function initChatDrag(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(!d) return;
    d.classList.add('chat-dock-v122');
    if(localStorage.chatState==='closed'){ d.classList.add('hidden'); d.style.display='none'; r?.classList.remove('hidden'); if(r)r.style.display='grid'; }
    const head=d.querySelector('.chat-head');
    if(head && !head.dataset.v122Drag){
      head.dataset.v122Drag='1';
      let down=false,sx=0,sy=0,ox=0,oy=0;
      head.addEventListener('mousedown',e=>{
        if(e.target.closest('button')) return;
        down=true; sx=e.clientX; sy=e.clientY;
        const rect=d.getBoundingClientRect(); ox=rect.left; oy=rect.top;
        d.style.position='fixed'; d.style.left=ox+'px'; d.style.top=oy+'px'; d.style.right='auto'; d.style.bottom='auto';
      });
      document.addEventListener('mousemove',e=>{
        if(!down) return;
        d.style.left=Math.max(8,Math.min(window.innerWidth-d.offsetWidth-8,ox+(e.clientX-sx)))+'px';
        d.style.top=Math.max(8,Math.min(window.innerHeight-d.offsetHeight-8,oy+(e.clientY-sy)))+'px';
      });
      document.addEventListener('mouseup',()=>down=false);
    }
  }

  function inferTier(card){
    const raw=((card.dataset.tier||'')+' '+(card.dataset.itemKey||'')+' '+card.className+' '+card.textContent).toLowerCase();
    if(raw.includes('beginner')||raw.includes('مبتد')) return 'beginner';
    if(raw.includes('medium')||raw.includes('متوسط')) return 'medium';
    if(raw.includes('featured')||raw.includes('premium')||raw.includes('مميز')) return 'featured';
    if(raw.includes('legend')||raw.includes('mythic')||raw.includes('أسطور')) return 'legendary';
    if(raw.includes('animated')||raw.includes('motion')||raw.includes('متحرك')) return 'animated';
    return 'pro';
  }
  function inferEmojiTier(card){
    const raw=((card.dataset.emojiTier||'')+' '+(card.dataset.itemKey||'')+' '+card.textContent).toLowerCase();
    if(raw.includes('free')||raw.includes('مجاني')||raw.includes('0')) return 'free';
    if(raw.includes('laugh')||raw.includes('ضحك')||raw.includes('😂')||raw.includes('🤣')) return 'laugh';
    if(raw.includes('happy')||raw.includes('فرح')||raw.includes('😍')||raw.includes('🥳')) return 'happy';
    if(raw.includes('angry')||raw.includes('عصب')||raw.includes('😡')) return 'angry';
    if(raw.includes('sad')||raw.includes('حزن')||raw.includes('😢')||raw.includes('😭')) return 'sad';
    if(raw.includes('animated')||raw.includes('متحرك')) return 'animated';
    return 'vip';
  }
  window.filterStoreTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-tier-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(card=>{
      const val=(card.dataset.tier||inferTier(card)).toLowerCase(); card.dataset.tier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.filterEmojiTier=function(btn,tier){
    const section=btn.closest('.store-section')||document;
    section.querySelectorAll('[data-emoji-filter]').forEach(b=>b.classList.toggle('active',b===btn));
    section.querySelectorAll('.store-card[data-category="emoji_pack"]').forEach(card=>{
      const val=(card.dataset.emojiTier||inferEmojiTier(card)).toLowerCase(); card.dataset.emojiTier=val;
      card.style.display=(tier==='all'||val===tier)?'':'none';
    });
  };
  window.previewStoreItem=function(btn){
    const card=btn?.closest?.('.store-card,.store-admin-row,.admin-store-row')||btn;
    if(!card) return;
    const title=card.querySelector('h3,b,[name="name_ar"]')?.textContent||card.querySelector('[name="name_ar"]')?.value||'عنصر متجر';
    const price=card.querySelector('.price')?.textContent||'';
    const cat=card.dataset.category||'';
    const tier=card.dataset.tier||inferTier(card);
    const color=card.dataset.color||card.querySelector('[style*="--booster-color"]')?.style.getPropertyValue('--booster-color')||'#d4af37';
    const action=(card.closest('form')||card).action||'';
    let visual='';
    if(cat==='table'){
      visual=`<div class="preview-table-v122 tier-${safe(tier)}"><div class="felt"><span class="seat n"></span><span class="seat e"></span><span class="seat s"></span><span class="seat w"></span><div class="table-logo">WZ</div><div class="center-cards">🂡 🂮 🂭 🂫</div></div></div>`;
    }else if(cat==='card_back'){
      visual=`<div class="preview-cardback-v122 tier-${safe(tier)}"><span>🂠</span><span>🂠</span><span>🂠</span><small>ظهر ورق فخم يظهر مباشرة داخل اللعبة</small></div>`;
    }else if(cat==='emoji_pack'){
      const em=card.querySelector('.emoji-store-icon')?.textContent||card.querySelector('.shop-icon')?.textContent||'😂 🤣 😍 😡 🥳 👑';
      visual=`<div class="preview-emoji-v122">${safe(em)}<small>تظهر في دردشة الطاولة والأصدقاء</small></div>`;
    }else if(cat==='xp_booster'){
      visual=`<div class="preview-booster-v122" style="--booster-color:${safe(color)}"><span>🚀</span><b>مكوك XP ثلاثي الأبعاد</b><small>مسرّع خبرة ملون وفخم</small></div>`;
    }else if(cat==='effect'){
      visual=`<div class="preview-effect-v122"><span>✨</span><b>مؤثر فوز فاخر</b><small>عملات ولمعان واحتفال</small></div>`;
    }else{
      visual=`<div class="preview-generic-v122">${card.querySelector('.shop-icon')?.innerHTML||'🎁'}</div>`;
    }
    const token=window.CSRF||'';
    const buy=action?`<form method="post" action="${safe(action)}" class="preview-buy-form"><input type="hidden" name="_token" value="${safe(token)}"><button class="primary">شراء الآن</button></form>`:'';
    showRichNotice?.(`<div class="store-preview-pop v122-preview"><h2>${safe(title)}</h2>${visual}<p>${safe(price)}</p><small>المعاينة لا تخرجك من الصفحة. بعد الشراء يظهر العنصر في مشترياتي للتفعيل.</small>${buy}</div>`);
  };

  function initV122(){
    document.body.classList.add('v122-premium-final');
    initChatDrag();
    $$('.store-card[data-category="table"],.store-card[data-category="card_back"]').forEach(c=>c.dataset.tier=c.dataset.tier||inferTier(c));
    $$('.store-card[data-category="emoji_pack"]').forEach(c=>c.dataset.emojiTier=c.dataset.emojiTier||inferEmojiTier(c));
    document.querySelector('.category-table [data-tier-filter="all"]')?.classList.add('active');
    document.querySelector('.category-card_back [data-tier-filter="all"]')?.classList.add('active');
    document.querySelector('.category-emoji_pack [data-emoji-filter="all"]')?.classList.add('active');
  }
  document.addEventListener('DOMContentLoaded',initV122);
})();


// v123 real redesign runtime: hard no-overflow guard, stable chat, luxury previews, room action protection.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  window.closeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.add('hidden');d.style.display='none';d.classList.remove('chat-expanded','chat-minimized');}
    if(r){r.classList.remove('hidden');r.style.display='grid';}
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.remove('hidden','chat-minimized');d.classList.add('chat-expanded','chat-dock-v123');d.style.display='';}
    if(r){r.classList.add('hidden');r.style.display='none';}
    localStorage.chatState='open';
  };

  function noOverflowGuard(){
    document.documentElement.style.overflowX='hidden';
    document.body.style.overflowX='hidden';
    document.querySelectorAll('*').forEach(el=>{
      const rect=el.getBoundingClientRect?.();
      if(rect && rect.width > window.innerWidth + 24){
        el.classList.add('wz-overflow-fixed-v123');
        el.style.maxWidth='100%';
      }
    });
  }

  function makePreview(cat,title,price,tier,color,content,action){
    const token=window.CSRF||'';
    const buy=action?`<form method="post" action="${safe(action)}" class="preview-buy-form"><input type="hidden" name="_token" value="${safe(token)}"><button class="primary">شراء الآن</button></form>`:'';
    return `<div class="v123-preview-modal"><h2>${safe(title)}</h2><div class="v123-preview-stage type-${safe(cat)} tier-${safe(tier)}" style="--item-color:${safe(color)}">${content}</div><p class="preview-price">${safe(price)}</p><small>معاينة مباشرة مثل التطبيق قبل الشراء أو التفعيل.</small>${buy}</div>`;
  }

  window.previewStoreItem=function(btn){
    const card=btn?.closest?.('.wz-store-item-v123,.store-card,.inventory-owned')||btn;
    if(!card) return;
    const cat=card.dataset.category||'generic';
    const title=card.querySelector('h3')?.textContent||'عنصر فاخر';
    const price=card.querySelector('.price')?.textContent||'';
    const tier=card.dataset.tier||'pro';
    const color=card.dataset.color||'#d4af37';
    const action=(card.closest('form')||card).action||'';
    let content='';
    if(cat==='table') content='<div class="full-table-preview-v123"><i>WZ</i><b>🂡 🂮 🂭</b><span class="seat s"></span><span class="seat n"></span><span class="seat e"></span><span class="seat w"></span></div>';
    else if(cat==='card_back') content='<div class="full-cardback-preview-v123"><span>🂠</span><span>🂠</span><span>🂠</span></div>';
    else if(cat==='emoji_pack') content='<div class="full-emoji-preview-v123">'+safe(card.querySelector(".mini-emoji-v123,.emoji-store-icon")?.textContent||"😂 🤣 😍 😡 🥳 👑")+'</div>';
    else if(cat==='xp_booster') content='<div class="full-rocket-preview-v123">🚀<b>XP Shuttle</b></div>';
    else if(cat==='effect') content='<div class="full-effect-preview-v123">✨🏆✨</div>';
    else content='<div class="full-generic-preview-v123">'+(card.querySelector(".shop-icon,.mini-generic-v123")?.innerHTML||"🎁")+'</div>';
    if(window.showRichNotice) showRichNotice(makePreview(cat,title,price,tier,color,content,action));
  };

  const oldRender=window.renderState;
  window.renderState=function(st){
    if(oldRender) oldRender(st);
    if(!st) return;
    const panel=$('#actionPanel');
    if(panel){
      panel.dataset.phase=st.phase||'';
      panel.dataset.turn=st.turn||'';
      panel.classList.toggle('my-turn-v123', st.turn===window.MY_PLAYER_KEY);
    }
    if(st.last_error_message){
      const err=$('#roomErrorV123')||document.createElement('div');
      err.id='roomErrorV123'; err.className='room-error-v123'; err.textContent=st.last_error_message;
      panel?.prepend(err);
      setTimeout(()=>err.remove(),4200);
    }
  };

  function init(){
    document.body.classList.add('v123-real-redesign');
    if(localStorage.chatState==='closed') window.closeChat();
    noOverflowGuard();
    setTimeout(noOverflowGuard,700);
    window.addEventListener('resize',()=>setTimeout(noOverflowGuard,80));
  }
  document.addEventListener('DOMContentLoaded',init);
})();


// v124 ATLAS LUXURY runtime: real app shell, dense lobby, stable chat, normalized action feedback.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

  function applyShell(){
    document.body.classList.add('v124-atlas-luxury');
    document.documentElement.style.overflowX='hidden';
    document.body.style.overflowX='hidden';
    const lobby=$('#wzLobby');
    if(lobby && !localStorage.wzDense) lobby.classList.add('ultra-dense');
  }

  window.closeChat=function(){
    const dock=$('#chatDock'), reopen=$('#chatReopen');
    if(dock){dock.classList.add('hidden');dock.style.display='none';dock.classList.remove('chat-expanded','chat-minimized');}
    if(reopen){reopen.classList.remove('hidden');reopen.style.display='grid';}
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const dock=$('#chatDock'), reopen=$('#chatReopen');
    if(dock){dock.classList.remove('hidden','chat-minimized');dock.classList.add('chat-expanded','chat-dock-v124');dock.style.display='';}
    if(reopen){reopen.classList.add('hidden');reopen.style.display='none';}
    localStorage.chatState='open';
  };

  function fixChat(){
    const dock=$('#chatDock'), reopen=$('#chatReopen');
    if(!dock) return;
    dock.classList.add('chat-dock-v124');
    if(localStorage.chatState==='closed'){window.closeChat();}
    if(reopen && localStorage.chatState!=='closed') reopen.classList.add('hidden');
  }

  function roomFeedback(){
    const panel=$('#actionPanel');
    if(!panel) return;
    panel.addEventListener('click',e=>{
      const b=e.target.closest('button[data-action]');
      if(!b) return;
      panel.classList.add('action-clicked-v124');
      setTimeout(()=>panel.classList.remove('action-clicked-v124'),350);
    });
  }

  function noOverflow(){
    document.querySelectorAll('.wz-lobby-v123,.wz-games-stage-v123,.wz-games-frame-v123,.wz-store-v123,.room-shell').forEach(el=>{
      el.style.maxWidth='100%';
      el.style.overflowX='hidden';
    });
  }

  document.addEventListener('DOMContentLoaded',()=>{applyShell();fixChat();roomFeedback();noOverflow();setTimeout(noOverflow,600);});
  window.addEventListener('resize',()=>setTimeout(noOverflow,80));
})();


// v126 stable top-nav + safe card action patch: prevent duplicate clicks and normalize card payload client-side.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s);

  function normalizeClientCard(c){
    if(typeof c==='object' && c) return c.id || c.card || c.card_id || ((c.rank||'')+'_'+(c.suit||c.type||''));
    return String(c||'').trim();
  }

  const oldRoomAction=window.roomAction;
  let pendingAction=false;
  window.roomAction=async function(action,payload={}){
    if(pendingAction){
      if(window.showNotice) showNotice('انتظر لحظة، يتم تنفيذ الحركة السابقة.');
      return;
    }
    if(action==='play_card'){
      payload=Object.assign({},payload,{card:normalizeClientCard(payload.card||payload.card_id||payload.id)});
    }
    pendingAction=true;
    document.body.classList.add('room-action-pending-v126');
    try{
      await oldRoomAction?.(action,payload);
    }finally{
      setTimeout(()=>{pendingAction=false;document.body.classList.remove('room-action-pending-v126');},280);
    }
  };

  window.cardClicked=function(c){
    c=normalizeClientCard(c);
    const st=window.LAST_STATE||{};
    if(st.turn && window.MY_PLAYER_KEY && st.turn!==window.MY_PLAYER_KEY){
      showNotice?.('ليس دورك الآن. انتظر شارة الدور.');
      return;
    }
    if(st.legal_cards && st.legal_cards.length && !st.legal_cards.includes(c)){
      showNotice?.('يجب اتباع نوع الورقة المطلوبة إذا كان موجودًا في يدك.');
      return;
    }
    let gt=st.game_type||window.GAME_KEY||'';
    if(gt==='domino'){
      let side=(prompt('اختر الطرف: left أو right','right')||'right').toLowerCase();
      return window.roomAction('play_tile',{tile:c,side:side==='left'?'left':'right'});
    }
    if(['hand','banakil','konkan','pinochle','rummy','concan'].includes(gt)){
      if(window.selectedCards){ window.selectedCards.has(c)?window.selectedCards.delete(c):window.selectedCards.add(c); }
      return window.roomAction('select_card',{card:c});
    }
    return window.roomAction('play_card',{card:c,card_id:c});
  };

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v126-topnav-stable');
  });
})();


// v127: compact all-games navbar curtain filtering and separated store boot marker.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

  function initNavGamesV127(){
    const curtain=$('#gamesCurtain.games-curtain-v127');
    if(!curtain) return;
    const input=$('#navGameSearchV127',curtain);
    const tabs=$$('[data-nav-family-v127]',curtain);
    function apply(){
      const q=(input?.value||'').toLowerCase().trim();
      const fam=curtain.querySelector('[data-nav-family-v127].active')?.dataset.navFamilyV127||'all';
      $$('.curtain-game-card-v127',curtain).forEach(card=>{
        const okFam=fam==='all'||card.dataset.family===fam;
        const okText=!q||(card.dataset.name||'').includes(q);
        card.hidden=!(okFam&&okText);
      });
    }
    tabs.forEach(t=>t.addEventListener('click',()=>{tabs.forEach(x=>x.classList.toggle('active',x===t));apply();}));
    input?.addEventListener('input',apply);
    apply();
  }

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v127-store-and-games-fixed');
    initNavGamesV127();
  });
})();


// v128 premium UX: top-only games menu, instant themes, fast chat reopen, safer table/card previews.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const safe=v=>String(v??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  function initThemeV128(){
    $$('.theme-grid-v108 [data-theme-pick]').forEach(btn=>{
      btn.addEventListener('click',()=>{
        const theme=btn.dataset.themePick;
        if(window.setSiteTheme) window.setSiteTheme(theme);
        $$('.theme-grid-v108 [data-theme-pick]').forEach(b=>b.classList.toggle('active',b===btn));
        fetch(window.PREF_URL||'',{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
        showNotice?.('تم تفعيل الثيم مباشرة: '+btn.textContent.trim());
      }, {capture:true});
    });
  }

  function initGamesMenuV128(){
    const curtain=$('#gamesCurtain');
    if(!curtain) return;
    curtain.classList.add('games-menu-compact-v128');
    const input=$('#navGameSearchV127',curtain);
    const tabs=$$('[data-nav-family-v127]',curtain);
    function apply(){
      const q=(input?.value||'').toLowerCase().trim();
      const fam=curtain.querySelector('[data-nav-family-v127].active')?.dataset.navFamilyV127||'all';
      $$('.curtain-game-card-v127',curtain).forEach(card=>{
        card.hidden=!((fam==='all'||card.dataset.family===fam) && (!q||(card.dataset.name||'').includes(q)));
      });
    }
    tabs.forEach(t=>t.addEventListener('click',()=>{tabs.forEach(x=>x.classList.toggle('active',x===t));apply();}));
    input?.addEventListener('input',apply);
    apply();
  }

  window.closeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.add('hidden');d.style.display='none';d.classList.remove('chat-expanded','chat-minimized');}
    if(r){r.classList.remove('hidden');r.style.display='grid';}
    localStorage.chatState='closed';
  };
  window.reopenChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.remove('hidden','chat-minimized');d.classList.add('chat-expanded');d.style.display='';}
    if(r){r.classList.add('hidden');r.style.display='none';}
    localStorage.chatState='open';
  };
  window.minimizeChat=function(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.add('hidden');d.style.display='none';}
    if(r){r.classList.remove('hidden');r.style.display='grid';}
    localStorage.chatState='minimized-icon';
  };

  const oldPreview=window.previewStoreItem;
  window.previewStoreItem=function(btn){
    const card=btn?.closest?.('.store-product-card-v127,.store-card,.inventory-owned')||btn;
    if(!card) return oldPreview?.(btn);
    const title=card.querySelector('h3')?.textContent||'عنصر متجر';
    const price=card.querySelector('.price')?.textContent||'';
    const cat=card.dataset.category||'generic';
    const c1=card.dataset.color || getComputedStyle(card.querySelector('.product-preview-v127')||card).getPropertyValue('--item-color') || '#064e3b';
    let visual='';
    if(cat==='table'){
      const el=card.querySelector('.product-table-v127');
      visual=`<div class="full-table-preview-v123" style="background:${getComputedStyle(el||card).backgroundImage || 'radial-gradient(circle,#10b981,#064e3b)'}"><i>WZ</i><b>🂡 🂮 🂭</b><span class="seat s"></span><span class="seat n"></span><span class="seat e"></span><span class="seat w"></span></div>`;
    }else if(cat==='card_back'){
      const emblem=card.querySelector('.product-cardback-v127 i')?.textContent||'♣';
      visual=`<div class="full-cardback-preview-v123"><span>${safe(emblem)}</span><span>${safe(emblem)}</span><span>${safe(emblem)}</span></div>`;
    }else if(cat==='emoji_pack'){
      visual='<div class="full-emoji-preview-v123">'+safe(card.querySelector('.product-emoji-v127')?.textContent||'😂 🤣 😍')+'</div>';
    }else if(cat==='xp_booster'){
      visual='<div class="full-rocket-preview-v123">🚀<b>XP Pasha</b></div>';
    }else if(cat==='pasha'){
      visual='<div class="full-generic-preview-v123">👑<p>باشا: XP أعلى، صلاحيات VIP، إنشاء نوادي ومنافسات، وأولوية بالغرف.</p></div>';
    }else{
      visual='<div class="full-generic-preview-v123">'+(card.querySelector('.product-generic-v127')?.innerHTML||'🎁')+'</div>';
    }
    const action=(card.closest('form')||card).action||'';
    const buy=action?`<form method="post" action="${safe(action)}"><input type="hidden" name="_token" value="${safe(window.CSRF||'')}"><button class="primary">شراء الآن</button></form>`:'';
    showRichNotice?.(`<div class="v123-preview-modal"><h2>${safe(title)}</h2><div class="v123-preview-stage">${visual}</div><p class="preview-price">${safe(price)}</p><small>معاينة فخمة قبل الشراء أو التفعيل.</small>${buy}</div>`);
  };

  const oldRender=window.renderState;
  window.renderState=function(st){
    oldRender&&oldRender(st);
    if(!st) return;
    const hudA=$('#hudScoreA'), hudB=$('#hudScoreB'), hudP=$('#hudPhase'), hudT=$('#hudTurn');
    if(hudA) hudA.textContent=st.score?.teamA??0;
    if(hudB) hudB.textContent=st.score?.teamB??0;
    if(hudP) hudP.textContent=st.phase||'';
    if(hudT) hudT.textContent=String(st.turn||'---').replace('user:','لاعب ').replace('bot:','بوت ');
  };

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v128-premium-polish');
    initThemeV128();
    initGamesMenuV128();
    if(localStorage.chatState==='closed'||localStorage.chatState==='minimized-icon') window.closeChat();
  });
})();


// v129: ephemeral games menu, no overflow, stronger action aliases.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

  function positionGamesMenu(){
    const menu=$('#gamesCurtain'); const btn=$('.games-top-only-v128');
    if(!menu||!btn||menu.classList.contains('hidden')) return;
    const r=btn.getBoundingClientRect();
    menu.style.position='fixed';
    menu.style.top=Math.min(window.innerHeight-120, r.bottom+8)+'px';
    menu.style.right=Math.max(8, window.innerWidth-r.right)+'px';
    menu.style.left='auto';
    menu.style.bottom='auto';
    const maxW=Math.min(980, window.innerWidth-16);
    menu.style.width=maxW+'px';
    if(window.innerWidth<980){ menu.style.right='8px'; menu.style.left='8px'; menu.style.width='auto'; }
  }

  const oldToggle=window.toggleTopPanel;
  window.toggleTopPanel=function(id){
    oldToggle ? oldToggle(id) : (document.getElementById(id)?.classList.toggle('hidden'));
    if(id==='gamesCurtain') setTimeout(positionGamesMenu,20);
  };

  function hideGamesMenu(){
    const m=$('#gamesCurtain'); if(m) m.classList.add('hidden');
  }

  function initEphemeralGames(){
    const m=$('#gamesCurtain'); if(!m) return;
    m.classList.add('games-ephemeral-v129');
    $$('.curtain-game-card-v127',m).forEach(a=>{
      a.addEventListener('click',()=>{
        hideGamesMenu();
        document.body.classList.add('navigating-game-v129');
      },{capture:true});
    });
    document.addEventListener('click',e=>{
      if(!m.classList.contains('hidden') && !e.target.closest('#gamesCurtain') && !e.target.closest('.games-top-only-v128')) hideGamesMenu();
    });
    window.addEventListener('scroll',()=>{ if(!m.classList.contains('hidden')) hideGamesMenu(); }, {passive:true});
    window.addEventListener('resize',()=>{ positionGamesMenu(); noOverflowV129(); });
    document.addEventListener('keydown',e=>{ if(e.key==='Escape') hideGamesMenu(); });
  }

  function noOverflowV129(){
    document.documentElement.style.overflowX='hidden';
    document.body.style.overflowX='hidden';
    ['.page','.store-separated-v127','.wz-lobby-v123','.royale-hub-v125','.room-shell','.game-table','.clubs-horizontal-grid','.tournament-grid-horizontal','.store-section-frame-v127'].forEach(sel=>{
      $$(sel).forEach(el=>{el.style.maxWidth='100%'; el.style.overflowX='hidden';});
    });
    const menu=$('#gamesCurtain');
    if(menu){ menu.style.maxHeight=Math.max(280, Math.min(650, window.innerHeight-110))+'px'; }
  }

  const oldRoomAction=window.roomAction;
  window.roomAction=async function(action,payload={}){
    const aliases={roll:'roll_dice',move_prompt:'move_token',move_piece:'move_token',card:'play_card',domino:'play_tile'};
    action=aliases[action]||action;
    if(action==='play_card' && payload && typeof payload.card==='object'){
      payload.card=payload.card.id||payload.card.card||payload.card.card_id||((payload.card.rank||'')+'_'+(payload.card.suit||payload.card.type||''));
    }
    return oldRoomAction ? oldRoomAction(action,payload) : undefined;
  };

  document.addEventListener('DOMContentLoaded',()=>{document.body.classList.add('v129-ephemeral-games-real-engines');initEphemeralGames();noOverflowV129();setTimeout(noOverflowV129,700);});
})();


// v130 CLEAN LUXURY REBUILD — final conflict-free menu, theme, chat, overflow behavior.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));

  function menu(){return document.getElementById('gamesCurtain');}
  function openGames(){
    const m=menu(); if(!m) return;
    document.querySelectorAll('.top-panel').forEach(p=>p.classList.add('hidden'));
    m.classList.remove('hidden');
    document.body.classList.add('games-menu-open-v130');
    setTimeout(()=>document.getElementById('navGameSearchV130')?.focus(),40);
    filterGames();
  }
  function closeGames(){
    const m=menu(); if(!m) return;
    m.classList.add('hidden');
    document.body.classList.remove('games-menu-open-v130');
  }
  function toggleGames(){
    const m=menu(); if(!m) return;
    m.classList.contains('hidden') ? openGames() : closeGames();
  }

  window.toggleTopPanel=function(id){
    if(id==='gamesCurtain'){ toggleGames(); return; }
    closeGames();
    document.querySelectorAll('.top-panel,.wz-games-menu-v130').forEach(p=>{ if(p.id!==id) p.classList.add('hidden'); });
    const p=document.getElementById(id); if(p) p.classList.toggle('hidden');
    window.WarqnaSound?.ui?.();
  };

  function activeFamily(){
    return document.querySelector('[data-game-family-v130].active')?.dataset.gameFamilyV130 || 'all';
  }
  function filterGames(){
    const m=menu(); if(!m) return;
    const q=(document.getElementById('navGameSearchV130')?.value||'').toLowerCase().trim();
    const fam=activeFamily();
    m.querySelectorAll('.wz-game-pop-v130').forEach(a=>{
      const okFam=fam==='all'||a.dataset.family===fam;
      const okText=!q||(a.dataset.name||'').includes(q);
      a.hidden=!(okFam&&okText);
    });
  }

  function initGamesMenu(){
    const m=menu(); if(!m) return;
    m.addEventListener('click',e=>{ if(e.target===m) closeGames(); });
    m.querySelectorAll('[data-games-close-v130]').forEach(b=>b.addEventListener('click',closeGames));
    m.querySelectorAll('[data-game-link-v130]').forEach(a=>a.addEventListener('click',()=>{closeGames();document.body.classList.add('navigating-game-v129');},{capture:true}));
    m.querySelectorAll('[data-game-family-v130]').forEach(b=>b.addEventListener('click',()=>{
      m.querySelectorAll('[data-game-family-v130]').forEach(x=>x.classList.toggle('active',x===b));
      filterGames();
    }));
    document.getElementById('navGameSearchV130')?.addEventListener('input',filterGames);
  }

  function initThemes(){
    const themes=['royal','midnight','emerald','desert','galaxy','crimson','ocean'];
    function apply(theme,save=true){
      if(!themes.includes(theme)) return;
      document.body.className=document.body.className.replace(/\btheme-[^\s]+/g,'').trim()+' theme-'+theme;
      document.body.dataset.theme=theme;
      localStorage.siteTheme=theme; localStorage.warqnaTheme=theme;
      document.querySelectorAll('[data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
      if(save && window.PREF_URL){
        fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF,'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
      }
      window.WarqnaSound?.ui?.();
    }
    window.setSiteTheme=apply;
    const stored=localStorage.siteTheme||localStorage.warqnaTheme;
    if(stored) apply(stored,false);
    document.querySelectorAll('[data-theme-pick]').forEach(btn=>{
      btn.onclick=function(ev){ev.preventDefault(); apply(btn.dataset.themePick,true);};
    });
  }

  function closeChatIcon(){
    const d=document.getElementById('chatDock'), r=document.getElementById('chatReopen');
    if(d){d.classList.add('hidden'); d.style.display='none';}
    if(r){r.classList.remove('hidden'); r.style.display='grid';}
    localStorage.chatState='closed';
  }
  function openChatIcon(){
    const d=document.getElementById('chatDock'), r=document.getElementById('chatReopen');
    if(d){d.classList.remove('hidden','chat-minimized'); d.classList.add('chat-expanded'); d.style.display='';}
    if(r){r.classList.add('hidden'); r.style.display='none';}
    localStorage.chatState='open';
  }
  window.closeChat=closeChatIcon; window.minimizeChat=closeChatIcon; window.reopenChat=openChatIcon;

  function noOverflow(){
    document.documentElement.style.overflowX='hidden';
    document.body.style.overflowX='hidden';
    ['.page','.wz-lobby-v130','.wz-games-wall-v130','.store-separated-v127','.store-section-frame-v127','.room-shell','.game-table','.clubs-horizontal-grid','.tournament-grid-horizontal'].forEach(sel=>{
      document.querySelectorAll(sel).forEach(el=>{el.style.maxWidth='100%'; el.style.overflowX='hidden';});
    });
  }

  document.addEventListener('click',e=>{
    if(!e.target.closest('#gamesCurtain') && !e.target.closest('.games-top-only-v128') && !menu()?.classList.contains('hidden')) closeGames();
  }, true);
  document.addEventListener('keydown',e=>{ if(e.key==='Escape'){closeGames(); document.querySelectorAll('.top-panel').forEach(p=>p.classList.add('hidden'));} });
  window.addEventListener('resize',noOverflow);
  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v130-clean-luxury');
    initGamesMenu(); initThemes(); noOverflow();
    if(localStorage.chatState==='closed'||localStorage.chatState==='minimized-icon') closeChatIcon();
    setTimeout(noOverflow,800);
  });
})();




// v131 — profile fix, professional chat dock, notifications toast, fast UI.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));
  const closeBtn=`<button class="modal-x" type="button" onclick="document.getElementById('profileModal')?.classList.add('hidden')">×</button>`;

  window.openProfile=async function(id){
    const m=$('#profileModal'); if(!m) return;
    m.classList.remove('hidden');
    m.innerHTML=closeBtn+`<div class="profile-loading-v131">جاري تحميل البروفايل...</div>`;
    if(!id){
      m.innerHTML=closeBtn+`<div class="profile-page"><h2>🤖 بوت 3D</h2><p>لاعب آلي داخل اللعبة.</p></div>`;
      return;
    }
    try{
      const r=await fetch('/profile/'+id,{headers:{'X-Requested-With':'XMLHttpRequest','Accept':'text/html'}});
      const ct=r.headers.get('content-type')||'';
      const text=await r.text();
      if(r.status===401 || text.trim().startsWith('{"ok":false') || ct.includes('json')){
        m.innerHTML=closeBtn+`<div class="profile-auth-required">يجب تسجيل الدخول أولًا لفتح البروفايل. <a href="/login">تسجيل الدخول</a></div>`;
        return;
      }
      m.innerHTML=closeBtn+text;
    }catch(e){
      m.innerHTML=closeBtn+`<div class="profile-auth-required">تعذر تحميل البروفايل الآن.</div>`;
    }
  };

  function chat(){return $('#chatDock');}
  function reopen(){const d=chat(),r=$('#chatReopen'); if(d){d.classList.remove('hidden','chat-minimized','chat-closed');d.classList.add('chat-expanded');d.style.display='';} if(r){r.classList.add('hidden');r.style.display='none';} localStorage.chatState='open';}
  function icon(){const d=chat(),r=$('#chatReopen'); if(d){d.classList.add('hidden','chat-closed');d.classList.remove('chat-expanded','chat-maximized');d.style.display='none';} if(r){r.classList.remove('hidden');r.style.display='grid';} localStorage.chatState='icon';}
  window.reopenChat=reopen;
  window.closeChat=icon;
  window.minimizeChat=function(){const d=chat(),r=$('#chatReopen'); if(d){d.classList.add('chat-minimized');d.classList.remove('chat-maximized');d.style.display='';d.classList.remove('hidden');} if(r){r.classList.add('hidden');r.style.display='none';} localStorage.chatState='minimized';};
  window.toggleChat=function(){const d=chat(); if(!d)return; d.classList.contains('chat-minimized')?reopen():window.minimizeChat();};
  window.maximizeChat=function(){const d=chat(); if(!d)return; d.classList.toggle('chat-maximized');d.classList.remove('chat-minimized','hidden');d.style.display='';$('#chatReopen')?.classList.add('hidden');localStorage.chatState=d.classList.contains('chat-maximized')?'max':'open';};

  function dragChat(){
    const d=chat(); if(!d) return;
    const head=d.querySelector('.chat-head'); if(!head) return;
    let down=false, sx=0, sy=0, ox=0, oy=0;
    head.style.cursor='move';
    head.addEventListener('pointerdown',e=>{
      if(e.target.closest('button')) return;
      down=true; sx=e.clientX; sy=e.clientY;
      const rect=d.getBoundingClientRect(); ox=rect.left; oy=rect.top;
      d.setPointerCapture?.(e.pointerId); e.preventDefault();
    });
    head.addEventListener('pointermove',e=>{
      if(!down) return;
      let x=ox+(e.clientX-sx), y=oy+(e.clientY-sy);
      x=Math.max(6,Math.min(window.innerWidth-d.offsetWidth-6,x));
      y=Math.max(58,Math.min(window.innerHeight-d.offsetHeight-6,y));
      d.style.left=x+'px'; d.style.top=y+'px'; d.style.right='auto'; d.style.bottom='auto';
      localStorage.chatPos=JSON.stringify({x,y});
    });
    head.addEventListener('pointerup',()=>{down=false;});
    try{const p=JSON.parse(localStorage.chatPos||'null'); if(p){d.style.left=p.x+'px';d.style.top=p.y+'px';d.style.right='auto';d.style.bottom='auto';}}catch(e){}
  }

  function initNotifs(){
    const rows=$$('.notification-drawer .drawer-row');
    if(!rows.length || sessionStorage.notifToastShownV131==='1') return;
    const box=document.createElement('div'); box.className='notif-toast-v131';
    box.innerHTML='<b>🔔 إشعارات جديدة</b>'+rows.slice(0,3).map(r=>'<p>'+esc(r.innerText.trim()).slice(0,90)+'</p>').join('');
    document.body.appendChild(box);
    sessionStorage.notifToastShownV131='1';
    setTimeout(()=>box.remove(),6200);
  }

  document.addEventListener('DOMContentLoaded',()=>{
    document.body.classList.add('v131-premium-final');
    dragChat();
    const state=localStorage.chatState||'open';
    if(state==='icon') icon(); else if(state==='minimized') window.minimizeChat(); else reopen();
    initNotifs();
  });
})();

// v131 turn timeout hard clamp: every game 5-10 seconds.
(function(){
  function clamp(){
    const v=Number(window.ROOM_TURN_TIMEOUT||7);
    window.ROOM_TURN_TIMEOUT=Math.max(5,Math.min(10,v||7));
    document.querySelectorAll('[data-turn-timeout]').forEach(el=>el.dataset.turnTimeout=String(window.ROOM_TURN_TIMEOUT));
  }
  document.addEventListener('DOMContentLoaded',clamp);
  clamp();
})();


// v132 — Tarneeb luxury table, phase classes, strict 5/7/10 timer and compact reactions.
(function(){
  const oldRender=window.renderState;
  window.renderState=function(st){
    if(oldRender) oldRender(st);
    if(!st) return;
    document.body.dataset.gamePhase=st.phase||'';
    document.body.classList.toggle('phase-bidding',st.phase==='bidding');
    document.body.classList.toggle('phase-choose-trump',st.phase==='choose_trump');
    document.body.classList.toggle('phase-playing',st.phase==='playing');
    document.body.classList.toggle('tarneeb-v132', (st.game_type||'')==='tarneeb' || (window.GAME_KEY||'').startsWith('tarneeb'));
    const sec=Math.max(5,Math.min(10,Number(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT||7)));
    window.ROOM_TURN_TIMEOUT=sec;
    const t=document.getElementById('turnTimer'); if(t && Number(t.textContent)>10) t.textContent=String(sec);
  };
  document.addEventListener('click',e=>{
    if(e.target.closest('[data-action="choose_trump"]')) setTimeout(()=>document.body.classList.remove('phase-choose-trump'),350);
  });
  setInterval(()=>{ const t=document.getElementById('turnTimer'); if(t && Number(t.textContent)>10) t.textContent=String(Math.max(5,Math.min(10,Number(window.ROOM_TURN_TIMEOUT||7)))); },250);
})();


// v133 — no-refresh store, direct themes, local notifications, premium sounds.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  window.WZSoundV133={
    ctx:null,
    enabled:localStorage.wzSound!=='off',
    beep(freq=620,dur=.07,type='sine'){
      if(!this.enabled) return;
      try{
        this.ctx=this.ctx||new (window.AudioContext||window.webkitAudioContext)();
        const o=this.ctx.createOscillator(), g=this.ctx.createGain();
        o.type=type; o.frequency.value=freq; g.gain.value=.035;
        o.connect(g); g.connect(this.ctx.destination); o.start();
        g.gain.exponentialRampToValueAtTime(.001,this.ctx.currentTime+dur);
        o.stop(this.ctx.currentTime+dur+.02);
      }catch(e){}
    },
    ui(){this.beep(740,.055,'triangle')},
    buy(){this.beep(880,.08,'sine');setTimeout(()=>this.beep(1170,.08,'sine'),85)},
    notify(){this.beep(660,.08,'triangle');setTimeout(()=>this.beep(990,.08,'triangle'),90)}
  };

  function showToast(msg,good=true){
    if(window.showNotice) showNotice(msg);
    const t=document.createElement('div');
    t.className='toast-v133 '+(good?'ok':'bad');
    t.innerHTML=esc(msg);
    document.body.appendChild(t);
    setTimeout(()=>t.remove(),3400);
  }

  function switchStoreTab(cat){
    document.querySelectorAll('[data-store-tab-v127]').forEach(b=>b.classList.toggle('active',b.dataset.storeTabV127===cat));
    document.querySelectorAll('[data-store-section-v127]').forEach(s=>{
      const active=s.dataset.storeSectionV127===cat;
      s.hidden=!active; s.classList.toggle('active',active);
    });
    history.replaceState(null,'','#'+cat);
  }

  function addInventoryCard(item,inventoryId){
    const grid=document.querySelector('[data-store-section-v127="inventory"] .inventory-grid-v127');
    if(!grid||!item) return;
    const empty=grid.querySelector('.empty-store-v127'); if(empty) empty.remove();
    const payload=item.payload||{};
    const name=item.name||item.key||'عنصر';
    const icon=payload.preview_icon || (item.category==='pasha'?'👑':'🎁');
    const html=`<article class="store-product-card-v127 inventory-owned inventory-new-v133" data-category="inventory" data-name="${esc((name+' '+(item.key||'')).toLowerCase())}">
      <div class="product-preview-v127 type-${esc(item.category||'generic')}"><div class="shop-icon product-generic-v127">${esc(icon)}</div></div>
      <div class="product-info-v127"><h3>${esc(name)}</h3><p>${esc(item.category||'')}</p></div>
      <div class="product-actions-v127">
        <button type="button" onclick="previewStoreItem(this)">معاينة</button>
        ${inventoryId?`<form method="post" action="/inventory/${inventoryId}/activate"><input type="hidden" name="_token" value="${esc(window.CSRF||'')}"><button class="primary" type="submit">تفعيل</button></form>`:`<span class="active-owned-v131">مفعّل</span>`}
      </div>
    </article>`;
    grid.insertAdjacentHTML('afterbegin',html);
  }

  document.addEventListener('click',e=>{
    const themeBtn=e.target.closest('[data-theme-pick]');
    if(themeBtn){
      e.preventDefault();
      const theme=themeBtn.dataset.themePick;
      if(window.setSiteTheme) window.setSiteTheme(theme);
      document.querySelectorAll('[data-theme-pick]').forEach(b=>b.classList.toggle('active',b===themeBtn));
      WZSoundV133.ui();
      showToast('تم تفعيل الثيم مباشرة: '+theme);
    }
  });

  document.addEventListener('submit',async e=>{
    const form=e.target;
    const isStore=form.matches('form[action*="/store/"],form[action*="/inventory/"]');
    if(!isStore) return;
    e.preventDefault();
    try{
      const r=await fetch(form.action,{method:(form.method||'POST').toUpperCase(),headers:{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','X-Requested-With':'XMLHttpRequest'},body:new FormData(form)});
      const j=await r.json();
      showToast(j.message||'تم التنفيذ',j.ok!==false);
      if(j.ok===false){WZSoundV133.beep(240,.12,'sawtooth'); return;}
      WZSoundV133.buy();
      if(j.item) addInventoryCard(j.item,j.inventory_id||null);
      if(j.activated){
        form.closest('.inventory-owned')?.classList.add('inventory-active-v133');
        form.outerHTML='<span class="active-owned-v131">مفعّل</span>';
      }
      if(j.item && j.item.category!=='pasha') switchStoreTab('inventory');
    }catch(err){
      showToast('تعذر تنفيذ العملية بدون تحديث الصفحة.',false);
    }
  },true);

  document.addEventListener('click',e=>{
    if(e.target.closest('.notif-live-btn')){WZSoundV133.notify();}
    if(e.target.closest('.store-category-tabs-v127 button')){WZSoundV133.ui();}
  },true);

  document.addEventListener('DOMContentLoaded',()=>{
    const hash=(location.hash||'').replace('#','');
    if(hash && document.querySelector(`[data-store-section-v127="${CSS.escape(hash)}"]`)) switchStoreTab(hash);
    const sound=document.createElement('button');
    sound.type='button'; sound.className='sound-toggle-v133'; sound.textContent=WZSoundV133.enabled?'🔊':'🔇';
    sound.onclick=()=>{WZSoundV133.enabled=!WZSoundV133.enabled;localStorage.wzSound=WZSoundV133.enabled?'on':'off';sound.textContent=WZSoundV133.enabled?'🔊':'🔇';WZSoundV133.ui();};
    document.body.appendChild(sound);
  });
})();


// v134 — critical UI hard fixes.
(function(){
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  function applyThemeV134(theme, save=true){
    const allowed=['royal','midnight','emerald','desert','galaxy','crimson','ocean','obsidian','aurora'];
    if(!allowed.includes(theme)) return;
    document.body.className=document.body.className.replace(/\btheme-[^\s]+/g,'').trim();
    document.body.classList.add('theme-'+theme);
    document.body.dataset.theme=theme;
    localStorage.siteTheme=theme;
    localStorage.warqnaTheme=theme;
    $$('[data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    const select=document.querySelector('[name="active_site_theme"]'); if(select) select.value=theme;
    if(save && window.PREF_URL){
      fetch(window.PREF_URL,{method:'POST',headers:{'X-CSRF-TOKEN':window.CSRF||'','Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'},body:JSON.stringify({theme})}).catch(()=>{});
    }
    window.WZSoundV133?.ui?.(); window.WarqnaSound?.ui?.();
  }
  window.setSiteTheme=applyThemeV134;

  function reopenChatV134(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.remove('hidden','chat-minimized','chat-closed');d.classList.add('chat-expanded');d.style.display='grid';}
    if(r){r.classList.add('hidden');r.style.display='none';}
    localStorage.chatState='open';
  }
  function closeChatIconV134(){
    const d=$('#chatDock'), r=$('#chatReopen');
    if(d){d.classList.add('hidden','chat-closed');d.classList.remove('chat-expanded','chat-maximized','chat-minimized');d.style.display='none';}
    if(r){r.classList.remove('hidden');r.style.display='grid';}
    localStorage.chatState='icon';
  }
  window.reopenChat=reopenChatV134;
  window.closeChat=closeChatIconV134;
  window.minimizeChat=closeChatIconV134;

  document.addEventListener('click',e=>{
    const themeBtn=e.target.closest('[data-theme-pick]');
    if(themeBtn){ e.preventDefault(); applyThemeV134(themeBtn.dataset.themePick,true); }
    if(e.target.closest('.emoji-picker button,.emoji-btn,.quick-reactions-box-v132 button')){
      const txt=(e.target.textContent||'').trim();
      const freq=txt==='😂'?930:txt==='🔥'?780:txt==='👑'?1040:txt==='👏'?660:880;
      window.WZSoundV133?.beep?.(freq,.09,'triangle');
    }
    if(e.target.closest('.topbar a,.topbar button,.userbar button,.userbar a')) window.WZSoundV133?.ui?.();
  },true);

  document.addEventListener('DOMContentLoaded',()=>{
    applyThemeV134(localStorage.siteTheme||localStorage.warqnaTheme||document.body.dataset.theme||'royal',false);
    if(localStorage.chatState==='icon') closeChatIconV134(); else reopenChatV134();
    const country=document.querySelector('.country-select-v134');
    if(country){ country.addEventListener('change',()=>{window.WZSoundV133?.ui?.();}); }
  });
})();

// v136 — final professional UX/gameplay integration patch.
(function(){
  'use strict';
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const headers=(json=false)=>json?{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'}:{'X-CSRF-TOKEN':csrf(),'Accept':'application/json','X-Requested-With':'XMLHttpRequest'};
  const esc=s=>String(s??'').replace(/[&<>"']/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[m]));

  function ensureChatReopen(){
    let r=$('#chatReopen');
    if(!r){
      r=document.createElement('button');r.id='chatReopen';r.type='button';r.className='chat-reopen hidden';r.textContent='💬';r.setAttribute('aria-label','فتح الدردشة');document.body.appendChild(r);
    }
    r.onclick=function(){ window.reopenChat?.(); };
    return r;
  }
  window.reopenChat=function(){
    const d=$('#chatDock'), r=ensureChatReopen();
    if(d){d.classList.remove('hidden','chat-closed','chat-minimized');d.classList.add('chat-expanded');d.style.display='grid';}
    r.classList.add('hidden');r.style.display='none';localStorage.chatState='open';
    window.WZSoundV133?.ui?.(); window.WarqnaSound?.ui?.();
  };
  window.closeChat=window.minimizeChat=function(){
    const d=$('#chatDock'), r=ensureChatReopen();
    if(d){d.classList.add('hidden','chat-closed');d.classList.remove('chat-expanded','chat-maximized','chat-minimized');d.style.display='none';}
    r.classList.remove('hidden');r.style.display='grid';localStorage.chatState='icon';
    window.WZSoundV133?.ui?.(); window.WarqnaSound?.ui?.();
  };

  function applyThemeV136(theme,save=true){
    if(!theme) return;
    const allowed=['royal','midnight','emerald','desert','galaxy','crimson','ocean','obsidian','aurora'];
    if(!allowed.includes(theme)) theme='royal';
    document.body.className=document.body.className.replace(/\btheme-[^\s]+/g,'').trim();
    document.body.classList.add('theme-'+theme);document.body.dataset.theme=theme;
    localStorage.warqnaTheme=theme;localStorage.siteTheme=theme;
    $$('[data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    const select=$('[name="active_site_theme"]'); if(select) select.value=theme;
    if(save && window.PREF_URL){fetch(window.PREF_URL,{method:'POST',headers:headers(true),body:JSON.stringify({theme})}).catch(()=>{});}
  }
  window.setSiteTheme=applyThemeV136;
  document.addEventListener('click',e=>{const b=e.target.closest('[data-theme-pick]'); if(!b)return; e.preventDefault(); applyThemeV136(b.dataset.themePick,true);},true);

  document.addEventListener('submit',async function(e){
    const f=e.target.closest('form[data-ajax-soft]');
    if(!f) return;
    e.preventDefault();
    try{
      const r=await fetch(f.action,{method:(f.method||'POST').toUpperCase(),headers:headers(false),body:new FormData(f)});
      const j=await r.json().catch(()=>({ok:true}));
      const row=f.closest('.notif-row-v136');
      if(f.action.includes('/delete')) row?.remove();
      else row?.classList.remove('is-unread');
      if(j.ok!==false){window.WZSoundV133?.ui?.(); window.WarqnaSound?.ui?.();}
      if(!$('.notif-row-v136')) $('.notif-list-v136')?.insertAdjacentHTML('beforeend','<p class="notif-empty-v136">لا توجد إشعارات الآن.</p>');
    }catch(err){ if(window.showNotice) showNotice('تعذر تحديث الإشعار الآن.'); }
  },true);

  function seedPlayerNameMap(){
    const map={};
    $$('.seat-profile[data-player-key], [data-player-key].seat-profile').forEach(seat=>{
      const key=seat.dataset.playerKey; if(!key) return;
      const name=seat.querySelector('.player-name,b,strong')?.textContent?.trim();
      if(name) map[key]=name;
    });
    window.WARQNA_PLAYER_NAMES=Object.assign(window.WARQNA_PLAYER_NAMES||{},map);
  }
  function displayNameForKey(key){
    seedPlayerNameMap();
    if(window.WARQNA_PLAYER_NAMES?.[key]) return window.WARQNA_PLAYER_NAMES[key];
    if(String(key).startsWith('bot:')){
      const raw=String(key).replace('bot:','').replace(/[_-]/g,' ').trim();
      const names=['عاصم','جميل','كنان','همام','معاذ','مصطفى','مهند','يامن','حلا','جمانة','آلاء','حور'];
      const n=parseInt(raw.match(/\d+/)?.[0]||'0',10);
      return raw && !/^\d+$/.test(raw) ? raw : names[n%names.length];
    }
    if(String(key).startsWith('user:')) return 'لاعب';
    return String(key||'لاعب');
  }
  function patchPlayedNames(st){
    if(!st) return;
    const entries=Object.entries(st.trick||{});
    $$('#tableTrick .played-card small').forEach((el,i)=>{const key=entries[i]?.[0]; if(key) el.textContent=displayNameForKey(key);});
    (st.bids||[]).forEach(b=>{const seat=document.querySelector('.seat-profile[data-player-key="'+CSS.escape(b.player||'')+'"]'); if(!seat)return; let bb=seat.querySelector('.bid-status-bubble'); if(!bb){bb=document.createElement('span');bb.className='bid-status-bubble';seat.appendChild(bb);} bb.textContent=b.type==='pass'?'تمرير':'طلب '+b.value; bb.classList.toggle('pass',b.type==='pass');});
  }
  function markRoomBody(st){
    const game=st?.game_type||window.GAME_KEY||'';
    if(window.ROOM_CODE) document.body.classList.add('is-room-page','game-'+game);
    document.body.classList.toggle('is-tarneeb-room', /^tarneeb/.test(game)||['tarneeb_400','syrian_tarneeb'].includes(game));
    document.body.classList.toggle('is-handlike-room',['hand','hand_partner','saudi_hand','banakil','pinochle','solitaire_multiplayer'].includes(game));
  }
  const previousRender=window.renderState;
  window.renderState=function(st){
    window.LAST_STATE=st||window.LAST_STATE;
    if(previousRender) previousRender(st);
    markRoomBody(st||window.LAST_STATE);
    patchPlayedNames(st||window.LAST_STATE);
    updateTurnDeadline(st||window.LAST_STATE);
  };

  let lastTurnKey=null,lastPhase=null,turnDeadline=0,timeoutBusy=false;
  function updateTurnDeadline(st){
    if(!st||!window.ROOM_TIMEOUT_URL) return;
    const key=(st.turn||'')+'|'+(st.phase||'');
    const sec=Math.max(5,Math.min(10,Number(st.turn_timeout_seconds||window.ROOM_TURN_TIMEOUT||7)));
    if(key!==lastTurnKey+'|'+lastPhase){lastTurnKey=st.turn||null;lastPhase=st.phase||null;turnDeadline=Date.now()+sec*1000;}
    const t=$('#turnTimer'); if(t && Number(t.textContent)>sec) t.textContent=String(sec);
  }
  setInterval(async()=>{
    if(!window.ROOM_TIMEOUT_URL||timeoutBusy||!window.LAST_STATE) return;
    const st=window.LAST_STATE;
    if(st.phase!=='playing' && st.phase!=='bidding' && st.phase!=='choose_trump') return;
    if(st.turn!==window.MY_PLAYER_KEY) return;
    const t=$('#turnTimer');
    const visible=Number(t?.textContent||0);
    if(Date.now()<turnDeadline && visible>0) return;
    timeoutBusy=true;
    try{
      const r=await fetch(window.ROOM_TIMEOUT_URL,{method:'POST',headers:headers(true),body:JSON.stringify({auto:true,source:'v136_timer_zero'})});
      const j=await r.json().catch(()=>null);
      if(j?.state) window.renderState(j.state);
      if(j?.ok) { window.WZSoundV133?.beep?.(520,.08,'triangle'); window.WarqnaSound?.play?.('card'); }
    }catch(err){} finally{setTimeout(()=>timeoutBusy=false,900);}
  },650);

  document.addEventListener('click',e=>{
    const emoji=e.target.closest('.emoji-picker button,.emoji-btn,.quick-reactions-box-v132 button,.emoji-store-icon');
    if(!emoji) return;
    emoji.classList.remove('emoji-pop-v136'); void emoji.offsetWidth; emoji.classList.add('emoji-pop-v136');
    const txt=(emoji.textContent||'').trim();
    const freq=txt.includes('😂')?930:txt.includes('🔥')?790:txt.includes('👑')?1120:txt.includes('👏')?660:txt.includes('😮')?1040:880;
    window.WZSoundV133?.beep?.(freq,.10,'triangle'); window.WarqnaSound?.play?.('message');
  },true);

  function polishStoreCards(){
    $$('.store-product-card-v127[data-category="pasha"] .product-actions-v127 button[onclick*="previewStoreItem"]').forEach(b=>b.remove());
    $$('.store-product-card-v127[data-category="card_back"] .product-info-v127 p').forEach(p=>{ if(!p.textContent.trim()) p.textContent='مقتنى فاخر داخل Warqnaa'; });
  }
  function polishAdminStore(){
    const first=$('[data-store-admin-tab].active')||$('[data-store-admin-tab]');
    if(first && !$('.admin-store-section.active')) first.click();
    $$('.store-admin-row').forEach(r=>{r.querySelectorAll('input,select').forEach(x=>x.setAttribute('title',x.placeholder||x.name||''));});
  }
  function polishTokenLedger(){
    if(!$('.token-ledger-v136')) return;
    document.body.classList.add('is-token-ledger');
  }
  function addFriendsSearchInsideChat(){
    const dock=$('#chatDock'); if(!dock || dock.querySelector('.friend-search-mini-v136')) return;
    const target=dock.querySelector('.chat-body,.chat-tabs,.chat-content')||dock;
    const box=document.createElement('div');
    box.className='friend-search-mini-v136';
    box.innerHTML='<input type="search" placeholder="بحث اللاعبين من داخل دردشة الأصدقاء..." aria-label="بحث اللاعبين"><small>بحث اللاعبين صار من داخل الدردشة للاعبين، وبقيت صفحة البحث للمدير فقط.</small>';
    target.prepend(box);
    box.querySelector('input').addEventListener('input',e=>{
      const q=e.target.value.trim().toLowerCase();
      $$('.friend-row,.chat-friend-row,.user-chip').forEach(row=>{row.style.display=!q || row.textContent.toLowerCase().includes(q)?'':'none';});
    });
  }

  document.addEventListener('DOMContentLoaded',()=>{
    ensureChatReopen(); if(localStorage.chatState==='icon') window.closeChat();
    applyThemeV136(localStorage.warqnaTheme||localStorage.siteTheme||document.body.dataset.theme||'royal',false);
    seedPlayerNameMap(); markRoomBody(window.INITIAL_STATE||window.LAST_STATE); patchPlayedNames(window.INITIAL_STATE||window.LAST_STATE);
    if(window.INITIAL_STATE && window.renderState) setTimeout(()=>window.renderState(window.INITIAL_STATE),50);
    polishStoreCards(); polishAdminStore(); polishTokenLedger(); addFriendsSearchInsideChat();
  });
})();

// v138: final override for real theme icon, full language icon, hidden quick reactions, and admin chat live preview.
(function(){
  const $=(s,r=document)=>r.querySelector(s), $$=(s,r=document)=>Array.from(r.querySelectorAll(s));
  const csrf=()=>window.CSRF||document.querySelector('meta[name="csrf-token"]')?.content||'';
  const headers=()=>({'X-CSRF-TOKEN':csrf(),'Accept':'application/json','Content-Type':'application/json','X-Requested-With':'XMLHttpRequest'});
  const themes=['royal','midnight','emerald','desert','galaxy','crimson','ocean','obsidian','aurora'];
  window.setSiteTheme=function(theme){
    theme=themes.includes(theme)?theme:'royal';
    document.body.className=document.body.className.replace(/\btheme-[\w-]+\b/g,'').replace(/\s+/g,' ').trim();
    document.body.classList.add('theme-'+theme);
    document.body.dataset.theme=theme;
    localStorage.siteTheme=theme; localStorage.warqnaTheme=theme;
    $$('[data-theme-pick]').forEach(b=>b.classList.toggle('active',b.dataset.themePick===theme));
    try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:headers(),body:JSON.stringify({theme})}); }catch(e){}
    try{window.WarqnaSound?.ui?.()}catch(e){}
  };
  function cycleTheme(){const current=document.body.dataset.theme||localStorage.siteTheme||'royal'; const i=themes.indexOf(current); setSiteTheme(themes[(i+1+themes.length)%themes.length]);}
  const dict={
    ar:{all_games:'🎮 الألعاب ▾',rules:'قوانين الألعاب',store:'المتجر',rewards:'المكافآت',groups:'المجموعات',clubs:'النوادي',competitions:'المنافسات',tournaments:'المسابقات',settings:'الإعدادات',contact:'اتصل بنا',my_profile:'بروفايلي',about:'حول',admin:'الإدارة',pasha:'باشا',days:'يوم',logout:'خروج',chat_center:'مركز الدردشة',game_chat:'دردشة اللعبة',friends:'الأصدقاء',search:'بحث',send:'إرسال',site_language:'لغة الموقع',choose_theme:'الثيمات',tokens:'التوكنز',create_room:'إنشاء الغرفة',room_type:'نوع اللعبة',public:'عامة',private:'خاصة',voice:'لعبة صوتية',buy:'شراء',activate:'تفعيل',preview:'معاينة',read_more:'اقرأ المزيد'},
    en:{all_games:'🎮 Games ▾',rules:'Game rules',store:'Store',rewards:'Rewards',groups:'Groups',clubs:'Clubs',competitions:'Competitions',tournaments:'Tournaments',settings:'Settings',contact:'Contact us',my_profile:'My profile',about:'About',admin:'Admin',pasha:'Pasha',days:'days',logout:'Logout',chat_center:'Chat center',game_chat:'Game chat',friends:'Friends',search:'Search',send:'Send',site_language:'Language',choose_theme:'Themes',tokens:'Tokens',create_room:'Create room',room_type:'Room type',public:'Public',private:'Private',voice:'Voice game',buy:'Buy',activate:'Activate',preview:'Preview',read_more:'Read more'},
    de:{all_games:'🎮 Spiele ▾',rules:'Spielregeln',store:'Shop',rewards:'Belohnungen',groups:'Gruppen',clubs:'Clubs',competitions:'Wettbewerbe',tournaments:'Turniere',settings:'Einstellungen',contact:'Kontakt',my_profile:'Mein Profil',about:'Über uns',admin:'Admin',pasha:'Pascha',days:'Tage',logout:'Abmelden',chat_center:'Chat-Zentrum',game_chat:'Spielchat',friends:'Freunde',search:'Suche',send:'Senden',site_language:'Sprache',choose_theme:'Themes',tokens:'Tokens',create_room:'Raum erstellen',room_type:'Raumtyp',public:'Öffentlich',private:'Privat',voice:'Sprachspiel',buy:'Kaufen',activate:'Aktivieren',preview:'Vorschau',read_more:'Mehr lesen'},
    tr:{all_games:'🎮 Oyunlar ▾',rules:'Oyun kuralları',store:'Mağaza',rewards:'Ödüller',groups:'Gruplar',clubs:'Kulüpler',competitions:'Yarışmalar',tournaments:'Turnuvalar',settings:'Ayarlar',contact:'İletişim',my_profile:'Profilim',about:'Hakkında',admin:'Yönetim',pasha:'Paşa',days:'gün',logout:'Çıkış',chat_center:'Sohbet merkezi',game_chat:'Oyun sohbeti',friends:'Arkadaşlar',search:'Ara',send:'Gönder',site_language:'Dil',choose_theme:'Temalar',tokens:'Token',create_room:'Oda oluştur',room_type:'Oda tipi',public:'Genel',private:'Özel',voice:'Sesli oyun',buy:'Satın al',activate:'Etkinleştir',preview:'Önizleme',read_more:'Devamını oku'},
    fr:{all_games:'🎮 Jeux ▾',rules:'Règles du jeu',store:'Boutique',rewards:'Récompenses',groups:'Groupes',clubs:'Clubs',competitions:'Compétitions',tournaments:'Tournois',settings:'Paramètres',contact:'Contact',my_profile:'Mon profil',about:'À propos',admin:'Admin',pasha:'Pacha',days:'jours',logout:'Déconnexion',chat_center:'Centre de chat',game_chat:'Chat du jeu',friends:'Amis',search:'Recherche',send:'Envoyer',site_language:'Langue',choose_theme:'Thèmes',tokens:'Jetons',create_room:'Créer une salle',room_type:'Type de salle',public:'Public',private:'Privé',voice:'Jeu vocal',buy:'Acheter',activate:'Activer',preview:'Aperçu',read_more:'Lire plus'},
    es:{all_games:'🎮 Juegos ▾',rules:'Reglas',store:'Tienda',rewards:'Recompensas',groups:'Grupos',clubs:'Clubes',competitions:'Competiciones',tournaments:'Torneos',settings:'Ajustes',contact:'Contacto',my_profile:'Mi perfil',about:'Acerca de',admin:'Admin',pasha:'Pasha',days:'días',logout:'Salir',chat_center:'Centro de chat',game_chat:'Chat del juego',friends:'Amigos',search:'Buscar',send:'Enviar',site_language:'Idioma',choose_theme:'Temas',tokens:'Tokens',create_room:'Crear sala',room_type:'Tipo de sala',public:'Pública',private:'Privada',voice:'Juego de voz',buy:'Comprar',activate:'Activar',preview:'Vista previa',read_more:'Leer más'}
  };
  const phrase={
    en:{'المكافآت':'Rewards','المجموعات':'Groups','كل الألعاب':'Games','المتجر':'Store','النوادي':'Clubs','المسابقات':'Competitions','قوانين الألعاب':'Game rules','الإعدادات':'Settings','اتصل بنا':'Contact us','حول':'About','الإدارة':'Admin','خروج':'Logout','مركز الدردشة':'Chat center','دردشة اللعبة':'Game chat','الأصدقاء':'Friends','بحث':'Search','إرسال':'Send','شراء':'Buy','شراء الآن':'Buy now','تفعيل':'Activate','معاينة':'Preview','إنشاء الغرفة':'Create room','نوع اللعبة':'Room type','عامة':'Public','خاصة':'Private','لعبة صوتية':'Voice game','عدد المقاعد':'Seats','سرعة اللعب':'Speed','أقل مستوى للدخول':'Minimum level','نهاية اللعبة':'Target score','غرفة':'Room','اللعبة':'Game','الحالة':'Status','اللاعبون':'Players','سرعة الدور':'Turn timer','النتيجة':'Score','خروج من اللعبة':'Leave game','العودة للغرف':'Back to rooms','مرحلة الطلب':'Bidding','اختر نوع الطرنيب بعد تأكيد الطلب':'Choose trump after bidding','تمرير':'Pass','أوراقك':'Your cards'},
    de:{'المكافآت':'Belohnungen','المجموعات':'Gruppen','كل الألعاب':'Spiele','المتجر':'Shop','النوادي':'Clubs','المسابقات':'Wettbewerbe','قوانين الألعاب':'Spielregeln','الإعدادات':'Einstellungen','اتصل بنا':'Kontakt','حول':'Über uns','الإدارة':'Admin','خروج':'Abmelden','مركز الدردشة':'Chat-Zentrum','دردشة اللعبة':'Spielchat','الأصدقاء':'Freunde','بحث':'Suche','إرسال':'Senden','شراء':'Kaufen','شراء الآن':'Jetzt kaufen','تفعيل':'Aktivieren','معاينة':'Vorschau','إنشاء الغرفة':'Raum erstellen','نوع اللعبة':'Raumtyp','عامة':'Öffentlich','خاصة':'Privat','لعبة صوتية':'Sprachspiel','عدد المقاعد':'Sitze','سرعة اللعب':'Tempo','أقل مستوى للدخول':'Mindestlevel','نهاية اللعبة':'Zielpunktzahl','غرفة':'Raum','اللعبة':'Spiel','الحالة':'Status','اللاعبون':'Spieler','سرعة الدور':'Zugzeit','النتيجة':'Punktestand','خروج من اللعبة':'Spiel verlassen','العودة للغرف':'Zurück zu Räumen','مرحلة الطلب':'Reizen','اختر نوع الطرنيب بعد تأكيد الطلب':'Trumpf nach dem Reizen wählen','تمرير':'Passen','أوراقك':'Deine Karten'},
    tr:{'المكافآت':'Ödüller','المجموعات':'Gruplar','كل الألعاب':'Oyunlar','المتجر':'Mağaza','النوادي':'Kulüpler','المسابقات':'Yarışmalar','قوانين الألعاب':'Oyun kuralları','الإعدادات':'Ayarlar','اتصل بنا':'İletişim','حول':'Hakkında','الإدارة':'Yönetim','خروج':'Çıkış','مركز الدردشة':'Sohbet merkezi','دردشة اللعبة':'Oyun sohbeti','الأصدقاء':'Arkadaşlar','بحث':'Ara','إرسال':'Gönder','شراء':'Satın al','شراء الآن':'Satın al','تفعيل':'Etkinleştir','معاينة':'Önizleme','إنشاء الغرفة':'Oda oluştur','نوع اللعبة':'Oda tipi','عامة':'Genel','خاصة':'Özel','لعبة صوتية':'Sesli oyun','عدد المقاعد':'Koltuklar','سرعة اللعب':'Hız','أقل مستوى للدخول':'Minimum seviye','نهاية اللعبة':'Hedef skor','غرفة':'Oda','اللعبة':'Oyun','الحالة':'Durum','اللاعبون':'Oyuncular','سرعة الدور':'Tur süresi','النتيجة':'Skor','خروج من اللعبة':'Oyundan çık','العودة للغرف':'Odalara dön','مرحلة الطلب':'İhale','اختر نوع الطرنيب بعد تأكيد الطلب':'İhaleden sonra koz seç','تمرير':'Pas','أوراقك':'Kartların'},
    fr:{'المكافآت':'Récompenses','المجموعات':'Groupes','كل الألعاب':'Jeux','المتجر':'Boutique','النوادي':'Clubs','المسابقات':'Compétitions','قوانين الألعاب':'Règles','الإعدادات':'Paramètres','اتصل بنا':'Contact','حول':'À propos','الإدارة':'Admin','خروج':'Déconnexion','مركز الدردشة':'Centre de chat','دردشة اللعبة':'Chat du jeu','الأصدقاء':'Amis','بحث':'Recherche','إرسال':'Envoyer','شراء':'Acheter','شراء الآن':'Acheter','تفعيل':'Activer','معاينة':'Aperçu','إنشاء الغرفة':'Créer une salle','نوع اللعبة':'Type de salle','عامة':'Public','خاصة':'Privé','لعبة صوتية':'Jeu vocal','عدد المقاعد':'Places','سرعة اللعب':'Vitesse','أقل مستوى للدخول':'Niveau minimum','نهاية اللعبة':'Score cible','غرفة':'Salle','اللعبة':'Jeu','الحالة':'Statut','اللاعبون':'Joueurs','سرعة الدور':'Temps du tour','النتيجة':'Score','خروج من اللعبة':'Quitter le jeu','العودة للغرف':'Retour aux salles','مرحلة الطلب':'Enchères','اختر نوع الطرنيب بعد تأكيد الطلب':'Choisir l’atout après enchère','تمرير':'Passer','أوراقك':'Vos cartes'},
    es:{'المكافآت':'Recompensas','المجموعات':'Grupos','كل الألعاب':'Juegos','المتجر':'Tienda','النوادي':'Clubes','المسابقات':'Competiciones','قوانين الألعاب':'Reglas','الإعدادات':'Ajustes','اتصل بنا':'Contacto','حول':'Acerca de','الإدارة':'Admin','خروج':'Salir','مركز الدردشة':'Centro de chat','دردشة اللعبة':'Chat del juego','الأصدقاء':'Amigos','بحث':'Buscar','إرسال':'Enviar','شراء':'Comprar','شراء الآن':'Comprar ahora','تفعيل':'Activar','معاينة':'Vista previa','إنشاء الغرفة':'Crear sala','نوع اللعبة':'Tipo de sala','عامة':'Pública','خاصة':'Privada','لعبة صوتية':'Juego de voz','عدد المقاعد':'Asientos','سرعة اللعب':'Velocidad','أقل مستوى للدخول':'Nivel mínimo','نهاية اللعبة':'Puntuación objetivo','غرفة':'Sala','اللعبة':'Juego','الحالة':'Estado','اللاعبون':'Jugadores','سرعة الدور':'Tiempo de turno','النتيجة':'Puntuación','خروج من اللعبة':'Salir del juego','العودة للغرف':'Volver a salas','مرحلة الطلب':'Subasta','اختر نوع الطرنيب بعد تأكيد الطلب':'Elegir triunfo después de la subasta','تمرير':'Pasar','أوراقك':'Tus cartas'}
  };
  function preserve(el){ if(!el.dataset.i18nOriginal) el.dataset.i18nOriginal=(el.textContent||'').trim(); }
  function replaceLoose(text,lang){ if(lang==='ar') return text; const map=phrase[lang]||{}; let out=text; Object.keys(map).sort((a,b)=>b.length-a.length).forEach(k=>{ out=out.replaceAll(k,map[k]); }); return out; }
  function translateNode(el,lang){
    if(!el || el.closest('script,style,code,textarea,.no-translate,[data-no-translate]')) return;
    if(el.dataset.i18n){ const v=(dict[lang]||dict.ar)[el.dataset.i18n]; if(v) el.textContent=v; return; }
    if(['BUTTON','A','SPAN','SMALL','B','H1','H2','H3','H4','P','LABEL','OPTION','TH','TD'].includes(el.tagName) && el.children.length===0){
      preserve(el); const original=el.dataset.i18nOriginal; if(!original) return; el.textContent=replaceLoose(original,lang);
    }
    if((el.tagName==='INPUT'||el.tagName==='TEXTAREA') && el.placeholder){ if(!el.dataset.placeholderOriginal) el.dataset.placeholderOriginal=el.placeholder; el.placeholder=replaceLoose(el.dataset.placeholderOriginal,lang); }
  }
  function translateAll(lang){
    lang=['ar','en','de','tr','fr','es'].includes(lang)?lang:'ar';
    localStorage.warqnaLang=lang;
    document.documentElement.lang=lang;
    document.documentElement.dir=lang==='ar'?'rtl':'ltr';
    document.body?.setAttribute('data-lang',lang);
    $$('[data-lang-pick]').forEach(b=>b.classList.toggle('active',b.dataset.langPick===lang));
    $$('[data-i18n],button,a,span,small,b,h1,h2,h3,h4,p,label,option,th,td,input,textarea').forEach(el=>translateNode(el,lang));
    try{ if(window.PREF_URL) fetch(window.PREF_URL,{method:'POST',headers:headers(),body:JSON.stringify({lang})}); }catch(e){}
    try{window.WarqnaSound?.ui?.()}catch(e){}
  }
  window.setWarqnaLang=translateAll;
  function observeLanguage(){
    const obs=new MutationObserver((ms)=>{const lang=localStorage.warqnaLang||window.WARQNA_LOCALE||'ar'; if(lang==='ar') return; ms.forEach(m=>m.addedNodes.forEach(n=>{ if(n.nodeType===1){ translateNode(n,lang); n.querySelectorAll?.('[data-i18n],button,a,span,small,b,h1,h2,h3,h4,p,label,option,th,td,input,textarea').forEach(el=>translateNode(el,lang)); }}));});
    obs.observe(document.body,{childList:true,subtree:true});
  }
  function initAdminDesignerV138(){
    const form=$('.designer-shell-v137'); if(!form) return;
    const live=()=>{ const root=$('.admin-live-surface-v137'); if(!root) return; const get=n=>form.querySelector(`[name="${n}"]`)?.value;
      root.style.setProperty('--demo-chat-w',(get('ui_chat_width')||340)+'px');root.style.setProperty('--demo-chat-h',(get('ui_chat_height')||560)+'px');root.style.setProperty('--demo-chat-radius',(get('ui_chat_radius')||24)+'px');root.style.setProperty('--demo-chat-font',(get('ui_chat_font')||14)+'px');root.style.setProperty('--demo-chat-btn-w',(get('ui_chat_button_width')||82)+'px');root.style.setProperty('--demo-chat-btn-h',(get('ui_chat_button_height')||40)+'px');root.style.setProperty('--demo-chat-btn-radius',(get('ui_chat_button_radius')||14)+'px');root.style.setProperty('--demo-chat-input-h',(get('ui_chat_input_height')||44)+'px');root.style.setProperty('--demo-chat-emoji',(get('ui_chat_emoji_size')||34)+'px');root.style.setProperty('--demo-chat-gap',(get('ui_chat_gap')||8)+'px');root.style.setProperty('--demo-chat-bg',get('ui_chat_bg')||'#0f172a');root.style.setProperty('--demo-chat-head',get('ui_chat_header_bg')||'#312e81');root.style.setProperty('--demo-chat-btn-bg',get('ui_chat_button_bg')||'#2e225f');root.style.setProperty('--demo-chat-btn-text',get('ui_chat_button_text')||'#fff');root.style.setProperty('--demo-chat-input',get('ui_chat_input_bg')||'#020617');root.style.setProperty('--demo-chat-message',get('ui_chat_message_bg')||'#1e293b'); };
    form.addEventListener('input',live); live();
  }
  function hardenQuickReactions(){ const box=$('#quickReactionsV132'); if(box) box.classList.add('hidden'); const t=$('.reaction-toggle-v132'); if(t){t.setAttribute('aria-expanded','false'); t.addEventListener('click',()=>{setTimeout(()=>t.setAttribute('aria-expanded', String(!box?.classList.contains('hidden'))),0);},true);} }
  document.addEventListener('click',e=>{const b=e.target.closest('[data-theme-pick]'); if(b){e.preventDefault(); setSiteTheme(b.dataset.themePick);} const l=e.target.closest('[data-lang-pick]'); if(l){e.preventDefault(); translateAll(l.dataset.langPick);} if(e.target.closest('.theme-switch-btn') && e.detail===2){cycleTheme();}});
  document.addEventListener('DOMContentLoaded',()=>{setSiteTheme(localStorage.siteTheme||localStorage.warqnaTheme||document.body.dataset.theme||'royal'); translateAll(localStorage.warqnaLang||window.WARQNA_LOCALE||document.documentElement.lang||'ar'); observeLanguage(); initAdminDesignerV138(); hardenQuickReactions();});
})();
