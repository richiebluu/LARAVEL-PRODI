<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('Login | Program Studi Teknologi Informasi') }}</title>
<meta name="description" content="Login Mahasiswa dan Staff Prodi Teknologi Informasi.">
<link rel="icon" href="{{ asset('images/logo-ti.png') }}" type="image/png">
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
<link rel="stylesheet" href="{{ asset('css/app.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
  body{ background:var(--grey-50); min-height:100vh; }
  .login-shell{ min-height:100vh; display:grid; grid-template-columns:1.05fr 1fr; }
  .login-visual{
    background:var(--grad-primary); position:relative; overflow:hidden;
    display:flex; flex-direction:column; align-items:center; justify-content:center; padding:60px; color:#fff; text-align:center;
  }
  .login-visual::before{ content:''; position:absolute; width:480px; height:480px; border-radius:50%; background:radial-gradient(circle, rgba(56,189,248,.3), transparent 70%); top:-160px; left:-140px; }
  .login-visual::after{ content:''; position:absolute; width:380px; height:380px; border-radius:50%; background:radial-gradient(circle, rgba(56,189,248,.2), transparent 70%); bottom:-140px; right:-100px; }
  .login-visual img{ width:220px; position:relative; z-index:2; animation:floaty 4.5s ease-in-out infinite; margin-bottom:26px; }
  .login-visual h2{ color:#fff; font-size:1.7rem; position:relative; z-index:2; max-width:420px; }
  .login-visual p{ color:rgba(255,255,255,.78); position:relative; z-index:2; max-width:380px; margin-top:10px; }
  .login-badge-row{ display:flex; gap:12px; margin-top:30px; position:relative; z-index:2; }
  .login-badge-row span{ background:rgba(255,255,255,.12); border:1px solid rgba(255,255,255,.2); padding:8px 18px; border-radius:999px; font-size:.8rem; font-weight:600; }

  .login-form-side{ display:flex; align-items:center; justify-content:center; padding:40px; }
  .login-card{ width:100%; max-width:400px; }
  .login-card .brand{ display:flex; align-items:center; gap:12px; margin-bottom:34px; }
  .login-card .brand-badge{ width:44px; height:44px; border-radius:12px; background:var(--grad-primary); color:#fff; display:flex; align-items:center; justify-content:center; font-family:var(--font-display); font-weight:800; }
  .login-card .brand-text .b1{ font-weight:700; font-size:.92rem; color:var(--navy-900); display:block; }
  .login-card .brand-text .b2{ font-size:.72rem; color:var(--grey-500); display:block; }

  .login-card h1{ font-size:1.5rem; margin-bottom:6px; }
  .login-card .login-lead{ color:var(--grey-500); font-size:.9rem; margin-bottom:28px; }

  .field-group{ margin-bottom:18px; }
  .field-group label{ display:block; font-size:.82rem; font-weight:600; color:var(--navy-900); margin-bottom:8px; }
  .input-wrap{ position:relative; }
  .input-wrap i.left-icon{ position:absolute; left:16px; top:50%; transform:translateY(-50%); color:var(--grey-500); font-size:.9rem; }
  .input-wrap input{
    width:100%; padding:13px 16px 13px 44px; border-radius:12px; border:1.5px solid var(--grey-300);
    font-family:var(--font-body); font-size:.9rem; outline:none; transition:border-color .2s ease;
  }
  .input-wrap input:focus{ border-color:var(--blue-600); }
  .input-wrap select{
    width:100%; padding:13px 16px 13px 44px; border-radius:12px; border:1.5px solid var(--grey-300);
    font-family:var(--font-body); font-size:.9rem; outline:none; transition:border-color .2s ease; background:#fff; color:var(--navy-900);
  }
  .input-wrap select:focus{ border-color:var(--blue-600); }
  .login-card .brand-badge.brand-logo{ background:transparent; padding:0; border-radius:0; }
  .input-wrap .toggle-pass{ position:absolute; right:16px; top:50%; transform:translateY(-50%); background:none; border:none; color:var(--grey-500); cursor:pointer; font-size:.9rem; }

  .field-row{ display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; }
  .remember-check{ display:flex; align-items:center; gap:8px; font-size:.85rem; color:var(--grey-700); }
  .remember-check input{ width:16px; height:16px; accent-color:var(--blue-600); }

  .login-error{
    display:none; align-items:center; gap:10px; background:#FEE2E2; color:#B91C1C; padding:12px 16px; border-radius:10px;
    font-size:.85rem; margin-bottom:18px;
  }
  .login-error.show{ display:flex; }

  .login-note{ display:flex; align-items:center; gap:10px; background:var(--grey-50); border:1px solid var(--grey-100); padding:14px 16px; border-radius:12px; font-size:.8rem; color:var(--grey-500); margin-top:24px; }
  .login-note i{ color:var(--blue-600); }
  .back-home{ display:inline-flex; align-items:center; gap:8px; font-size:.85rem; color:var(--grey-500); margin-top:24px; }
  .back-home:hover{ color:var(--blue-600); }

  @media(max-width:900px){
    .login-shell{ grid-template-columns:1fr; }
    .login-visual{ display:none; }
  }
</style>
</head>
<body>

  <div class="login-shell">
    <div class="login-visual">
      <img src="{{ asset('images/Kodex.png') }}" alt="{{ __('Kodex - Maskot Program Studi Teknologi Informasi') }}">
      <h2>{{ __('Satu Pintu untuk Mahasiswa & Staff Prodi') }}</h2>
      <p>{{ __('Mahasiswa mengajukan prestasi dan memperbarui profil, Staff Prodi mengelola Data Master dan memverifikasi prestasi.') }}</p>
      <div class="login-badge-row">
        <span><i class="fa-solid fa-user-graduate"></i> {{ __('Mahasiswa') }}</span>
        <span><i class="fa-solid fa-user-shield"></i> {{ __('Staff Prodi') }}</span>
      </div>
    </div>

    <div class="login-form-side">
      <div class="login-card">
        <div class="brand">
          <div class="brand-badge brand-logo"><img src="{{ asset('images/logo-ti.png') }}" alt="{{ __('Logo Program Studi Teknologi Informasi') }}"></div>
          <div class="brand-text">
            <span class="b1">POLITALA</span>
            <span class="b2">{{ __('Program Studi Teknologi Informasi') }}</span>
          </div>
        </div>

        <h1>{{ __('Masuk ke Sistem') }}</h1>
        <p class="login-lead">{{ __('Gunakan akun Mahasiswa atau Staff Prodi.') }}</p>

        <div class="login-error {{ $errors->any() ? 'show' : '' }}" id="loginError">
          <i class="fa-solid fa-circle-exclamation"></i>
          <span>{{ $errors->first() ?: __('Email atau password salah.') }}</span>
        </div>

        <form id="loginForm" method="POST" action="{{ route('login.process') }}">
          @csrf
          <div class="field-group">
            <label for="role">{{ __('Masuk Sebagai') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-user-tag left-icon"></i>
              <select id="role" name="role" required>
                <option value="">{{ __('-- Pilih jenis akun --') }}</option>
                @foreach (\App\Models\User::ROLE as $kodeRole => $labelRole)
                  <option value="{{ $kodeRole }}" data-domain="{{ \App\Models\User::domainEmail($kodeRole) }}" @selected(old('role') === $kodeRole)>{{ __($labelRole) }}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="field-group">
            <label>{{ __('Email') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-user left-icon"></i>
              <input type="email" id="username" name="email" value="{{ old('email') }}" placeholder="{{ __('nama@politala.ac.id') }}" autocomplete="username" required>
            </div>
          </div>

          <div class="field-group">
            <label>{{ __('Password') }}</label>
            <div class="input-wrap">
              <i class="fa-solid fa-lock left-icon"></i>
              <input type="password" id="password" name="password" placeholder="{{ __('Masukkan password') }}" autocomplete="current-password" required>
              <button type="button" class="toggle-pass" id="togglePass" aria-label="{{ __('Tampilkan password') }}"><i class="fa-solid fa-eye"></i></button>
            </div>
          </div>

          <div class="field-row">
            <label class="remember-check">
              <input type="checkbox" id="rememberMe" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}>
              {{ __('Ingat saya') }}
            </label>
          </div>

          <button type="submit" class="btn btn-primary" style="width:100%;">
            <i class="fa-solid fa-right-to-bracket"></i> {{ __('Masuk ke Dashboard') }}
          </button>
        </form>

        <a href="{{ route('login.google') }}" class="btn btn-outline" style="width:100%; margin-top:12px;">
          <i class="fa-brands fa-google"></i> {{ __('Login dengan Google') }}
        </a>

        <div class="login-note">
          <i class="fa-solid fa-circle-info"></i>
          <span>{{ __('Pilih jenis akun sesuai role Anda. Mahasiswa memakai email') }} <code>&#64;{{ \App\Models\User::domainEmail('mahasiswa') }}</code>{{ __(', Staff Prodi memakai email') }} <code>&#64;{{ \App\Models\User::domainEmail('staff') }}</code>{{ __('. Login dengan Google hanya untuk email Politala yang sudah terdaftar.') }}</span>
        </div>

        <a href="{{ url('/') }}" class="back-home"><i class="fa-solid fa-arrow-left"></i> {{ __('Kembali ke Beranda') }}</a>
      </div>
    </div>
  </div>

<script>
  (function () {
    var role = document.getElementById('role');
    var email = document.getElementById('username');
    function ubahContoh() {
      var opsi = role.options[role.selectedIndex];
      var domain = opsi ? opsi.getAttribute('data-domain') : '';
      email.placeholder = domain ? 'nama@' + domain : 'nama@politala.ac.id';
    }
    role.addEventListener('change', ubahContoh);
    ubahContoh();
  })();
  document.getElementById('togglePass').addEventListener('click', function () {
    var pass = document.getElementById('password');
    var icon = this.querySelector('i');
    if (pass.type === 'password') {
      pass.type = 'text';
      icon.classList.remove('fa-eye'); icon.classList.add('fa-eye-slash');
    } else {
      pass.type = 'password';
      icon.classList.remove('fa-eye-slash'); icon.classList.add('fa-eye');
    }
  });
</script>
</body>
</html>
