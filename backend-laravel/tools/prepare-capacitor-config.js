const fs = require('fs');
const path = require('path');

function cleanUrl(value) {
  const raw = String(value || '').trim().replace(/\/+$/, '');
  if (!raw) return '';
  if (!/^https:\/\//i.test(raw)) {
    throw new Error('WARQNA_APP_URL must be a public HTTPS URL, for example: https://your-domain.com');
  }
  return raw;
}

const appUrl = cleanUrl(process.env.WARQNA_APP_URL || process.env.APP_URL || '');
const cfgPath = path.join(process.cwd(), 'capacitor.config.json');
const cfg = fs.existsSync(cfgPath) ? JSON.parse(fs.readFileSync(cfgPath, 'utf8')) : {};

cfg.appId = cfg.appId || 'com.warqna.zone';
cfg.appName = cfg.appName || 'Warqna Zone';
cfg.webDir = cfg.webDir || 'public';
cfg.server = cfg.server || {};
cfg.server.androidScheme = 'https';
cfg.server.cleartext = false;

if (appUrl) {
  cfg.server.url = appUrl;
  cfg.server.allowNavigation = [new URL(appUrl).hostname];
  console.log('Capacitor will load Laravel from:', appUrl);
} else {
  delete cfg.server.url;
  cfg.server.allowNavigation = ['*'];
  console.warn('WARQNA_APP_URL is not set. Capacitor will use local webDir only. For Laravel production APK, set WARQNA_APP_URL to your HTTPS domain.');
}

cfg.plugins = Object.assign({
  SplashScreen: {
    launchShowDuration: 1800,
    backgroundColor: '#020617',
    androidSplashResourceName: 'splash',
    showSpinner: false,
    splashFullScreen: true,
    splashImmersive: true
  },
  Keyboard: { resize: 'body', style: 'dark', resizeOnFullScreen: true },
  StatusBar: { style: 'DARK', backgroundColor: '#020617', overlaysWebView: false }
}, cfg.plugins || {});

fs.writeFileSync(cfgPath, JSON.stringify(cfg, null, 2) + '\n');
