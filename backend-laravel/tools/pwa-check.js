const fs=require('fs');
const required=[
 'public/manifest.webmanifest',
 'public/sw.js',
 'public/offline.html',
 'public/assets/icons/icon.svg',
 'public/assets/icons/icon-192.png',
 'public/assets/icons/icon-512.png',
 'capacitor.config.json'
];
let ok=true;
for(const f of required){
 if(!fs.existsSync(f)){console.error('MISSING:',f); ok=false;}
 else console.log('OK:',f);
}
if(fs.existsSync('.github/workflows/build-apk.yml')) console.log('OK: .github/workflows/build-apk.yml');
if(fs.existsSync('.github/workflows/pwa-apk-check.yml')) console.log('OK: .github/workflows/pwa-apk-check.yml');
try{
 const manifest=JSON.parse(fs.readFileSync('public/manifest.webmanifest','utf8'));
 if(!manifest.start_url || !manifest.icons || manifest.icons.length<2){console.error('INVALID manifest'); ok=false;}
 else console.log('OK: manifest structure');
 const cap=JSON.parse(fs.readFileSync('capacitor.config.json','utf8'));
 if(!cap.appId || !cap.appName || !cap.webDir){console.error('INVALID capacitor config'); ok=false;}
 else console.log('OK: capacitor config');
}catch(e){ console.error(e.message); ok=false; }
process.exit(ok?0:1);
