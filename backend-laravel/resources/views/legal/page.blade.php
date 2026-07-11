<!doctype html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale()==='ar' ? 'rtl' : 'ltr' }}">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>{{ $title }} — {{ config('app.name','Warqna') }}</title>
<style>
:root{color-scheme:dark}body{margin:0;background:#06111f;color:#f7f9fc;font-family:Arial,sans-serif;line-height:1.8}.wrap{max-width:920px;margin:auto;padding:28px 18px 70px}.card{background:#0c1c30;border:1px solid #243b59;border-radius:24px;padding:24px;box-shadow:0 24px 70px #0007}h1{color:#ffd166;margin-top:0}h2{color:#8bd3ff;margin-top:28px}a{color:#ffd166}.meta{color:#9fb0c5;font-size:14px}.notice{background:#142945;border-radius:16px;padding:14px;border-inline-start:4px solid #ffd166}ul{padding-inline-start:22px}code{background:#06101d;padding:2px 7px;border-radius:6px}</style>
</head>
<body><main class="wrap"><article class="card"><h1>{{ $title }}</h1><p class="meta">آخر تحديث: {{ now()->format('Y-m-d') }} • الإصدار 1.53</p>{!! $content !!}<hr><p><a href="{{ url('/') }}">العودة إلى Warqna</a></p></article></main></body>
</html>
