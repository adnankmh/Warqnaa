@echo off
setlocal
cd /d "%~dp0"
title Warqna Android Crash Log
where adb >nul 2>nul || (
  echo adb was not found. Install Android platform-tools and add adb to PATH.
  pause
  exit /b 1
)
set PACKAGE=com.warqna.warqna_mobile
adb logcat -c
adb shell am force-stop %PACKAGE% >nul 2>nul
adb shell monkey -p %PACKAGE% -c android.intent.category.LAUNCHER 1 >nul 2>nul
timeout /t 8 >nul
adb logcat -d -v time AndroidRuntime:E flutter:E libc:F *:S > WARQNA_ANDROID_CRASH_LOG.txt
echo Log saved to WARQNA_ANDROID_CRASH_LOG.txt
pause
