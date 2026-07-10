const fs = require('fs');
const required = [
  'public/manifest.webmanifest',
  'public/sw.js',
  'public/offline.html',
  'public/assets/css/mobile-app.css',
  'public/assets/js/mobile-app.js',
  'public/assets/icons/icon-192.png',
  'public/assets/icons/icon-512.png',
  'capacitor.config.json',
  'package.json'
];
let ok = true;
for (const f of required) {
  if (!fs.existsSync(f)) { console.error('Missing:', f); ok = false; }
}
const manifest = JSON.parse(fs.readFileSync('public/manifest.webmanifest','utf8'));
for (const k of ['name','short_name','start_url','display','icons']) {
  if (!manifest[k]) { console.error('Manifest missing:', k); ok = false; }
}
if (!Array.isArray(manifest.icons) || manifest.icons.length < 2) { console.error('Manifest needs multiple icons.'); ok = false; }
const cfg = JSON.parse(fs.readFileSync('capacitor.config.json','utf8'));
if (!cfg.appId || !cfg.appName || !cfg.webDir) { console.error('Capacitor config incomplete.'); ok = false; }
const layout = fs.readFileSync('resources/views/layouts/app.blade.php','utf8');
if (!layout.includes('mobile-app.css') || !layout.includes('mobile-app.js')) { console.error('Mobile app assets are not included in layout.'); ok = false; }
if (ok) console.log('Warqna mobile app/PWA readiness check passed.');
process.exit(ok ? 0 : 1);
