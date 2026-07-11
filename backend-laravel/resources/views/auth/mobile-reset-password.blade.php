<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>إعادة تعيين كلمة المرور | Warqna</title>
  <style>
    *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:radial-gradient(circle at 80% 0,#18385a,#071421 55%,#02070d);font-family:Arial,sans-serif;color:#f8fafc;padding:18px}.card{width:min(460px,100%);background:rgba(12,29,45,.96);border:1px solid rgba(255,255,255,.12);border-radius:28px;padding:26px;box-shadow:0 30px 90px rgba(0,0,0,.45)}h1{margin:0 0 8px;font-size:28px}.sub{color:#9fb0c2;line-height:1.7;margin-bottom:20px}label{display:block;font-weight:800;margin:14px 0 7px}input{width:100%;height:52px;border-radius:15px;border:1px solid #35506a;background:#0b1d2e;color:white;padding:0 14px;font-size:16px;outline:none}input:focus{border-color:#f9c85c;box-shadow:0 0 0 3px rgba(249,200,92,.12)}button{width:100%;height:54px;margin-top:20px;border:0;border-radius:16px;background:linear-gradient(135deg,#ffd36d,#e7a633);font-size:17px;font-weight:900;color:#15100a;cursor:pointer}.msg{padding:12px;border-radius:14px;margin-bottom:14px;line-height:1.6}.ok{background:rgba(34,197,94,.14);border:1px solid rgba(34,197,94,.35)}.bad{background:rgba(239,68,68,.14);border:1px solid rgba(239,68,68,.35)}.brand{text-align:center;color:#f9c85c;font-weight:900;letter-spacing:3px;margin-bottom:18px}</style>
</head>
<body>
  <main class="card">
    <div class="brand">WARQNA</div>
    <h1>إعادة تعيين كلمة المرور</h1>
    <p class="sub">أدخل كلمة مرور جديدة قوية لحسابك. بعد نجاح العملية سيتم إغلاق جلسات الدخول القديمة لحماية الحساب.</p>
    @if(session('success'))<div class="msg ok">{{ session('success') }}</div>@endif
    @if(session('error'))<div class="msg bad">{{ session('error') }}</div>@endif
    @if($errors->any())<div class="msg bad">{{ $errors->first() }}</div>@endif
    <form method="post" action="{{ route('password.reset.web') }}">
      @csrf
      <input type="hidden" name="token" value="{{ $token }}">
      <label>البريد الإلكتروني</label>
      <input type="email" name="email" value="{{ old('email',$email) }}" required autocomplete="email">
      <label>كلمة المرور الجديدة</label>
      <input type="password" name="password" required minlength="8" autocomplete="new-password">
      <label>تأكيد كلمة المرور</label>
      <input type="password" name="password_confirmation" required minlength="8" autocomplete="new-password">
      <button type="submit">حفظ كلمة المرور الجديدة</button>
    </form>
  </main>
</body>
</html>
