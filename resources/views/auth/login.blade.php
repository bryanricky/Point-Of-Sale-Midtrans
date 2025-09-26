@extends('layouts.auth')

@section('login')
<style>
  body {
    background: linear-gradient(to right, #667eea, #764ba2);
    font-family: 'Poppins', sans-serif;
    min-height: 100vh;
    margin: 0;
    display: flex;
    align-items: center;
    justify-content: center;
  }
  .login-box {
    margin-top: 70px;
  }
  .login-box-body {
    background: #fff;
    border-radius: 10px;
    box-shadow: 0 15px 25px rgba(0,0,0,0.2);
    padding: 30px;
  }
  .login-logo img {
    max-width: 100%;
    width: 240px;
  }
  .btn-primary {
    background-color: #667eea;
    border-color: #667eea;
  }
  .btn-primary:hover {
    background-color: #5a67d8;
    border-color: #5a67d8;
  }
  .checkbox label {
    font-weight: normal;
    font-size: 14px;
  }
</style>

<div class="login-box">
  <div class="login-box-body text-center">
    <div class="login-logo">
      <img src="{{ asset('img/logo.png')}}" alt="Logo">
    </div>

    <p class="login-box-msg">Masuk ke akun Anda</p>

    <form action="{{ route('login') }}" method="post">
      @csrf

      <div class="form-group has-feedback @error('email') has-error @enderror">
        <input type="email" name="email" id="email" class="form-control" placeholder="Email" required autofocus value="{{ old('email') }}">
        <span class="glyphicon glyphicon-envelope form-control-feedback"></span>
        @error('email')
          <span class="help-block text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group has-feedback @error('password') has-error @enderror" style="position: relative;">
        <input type="password" name="password" id="password" class="form-control" placeholder="Password" required>

        {{-- Icon eye di dalam kolom --}}
        <span onclick="togglePassword()" style="position: absolute; top: 10px; right: 10px; cursor: pointer; z-index: 2;">
          <i class="glyphicon glyphicon-eye-open" id="togglePasswordIcon"></i>
        </span>

        @error('password')
          <span class="help-block text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="form-group">
        <select name="role" id="roleSelect" class="form-control" required>
          <option value="">-- Pilih Role --</option>
          <option value="admin">Admin</option>
          <option value="kasir">Kasir</option>
        </select>
        @error('role')
          <span id="role-error" style="color: red;">{{ $message }}</span>
        @enderror
      </div>

       <!-- Modal Kas Awal -->
      <div id="kasAwalGroup" class="form-group has-feedback @error('kas_awal') has-error @enderror" style="display: none;">
        <input type="number" name="kas_awal" class="form-control" placeholder="Modal Awal (Rp)" min="0" value="{{ old('kas_awal') }}">
        <span class="glyphicon glyphicon-usd form-control-feedback"></span>
        @error('kas_awal')
          <span class="help-block text-danger">{{ $message }}</span>
        @enderror
      </div>

      <div class="row">
        <div class="col-xs-6 text-left">
          <div class="checkbox icheck" style="padding-left: 20px;">
            <label>
              <input type="checkbox" name="remember"> Ingat saya
            </label>
          </div>
        </div>

        <div class="col-xs-6">
          <button type="submit" class="btn btn-primary btn-block btn-flat">Masuk</button>
        </div>
      </div>
    </form>

    <br>
    <a href="{{ route('password.request') }}">Lupa password?</a>
    <!-- <a href="{{ route('register') }}">Daftar</a> -->
  </div>
</div>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
<script>
  document.addEventListener('DOMContentLoaded', function () {
  const roleSelect = document.getElementById('roleSelect');
  const emailInput = document.getElementById('email');
  const kasAwalGroup = document.getElementById('kasAwalGroup');

  function checkKasirLogin() {
    if (roleSelect.value === 'kasir') {
      const email = emailInput.value;
      if (email) {
        fetch('/check-kasir-login?email=' + encodeURIComponent(email))
          .then(response => response.json())
          .then(data => {
            if (data.sudah_login) {
              kasAwalGroup.style.display = 'none';
            } else {
              kasAwalGroup.style.display = 'block';
            }
          })
          .catch(err => {
            console.error(err);
            kasAwalGroup.style.display = 'block';
          });
      } else {
        kasAwalGroup.style.display = 'none';
      }
    } else {
      kasAwalGroup.style.display = 'none';
    }
  }

  // Trigger cek saat ganti role
  roleSelect.addEventListener('change', checkKasirLogin);

  // ✅ Trigger cek juga saat email diubah
  emailInput.addEventListener('blur', checkKasirLogin);
  // Optional: bisa pakai input kalau mau lebih real-time
  // emailInput.addEventListener('input', checkKasirLogin);
  // ✅ Auto-hide pesan error role setelah 3 detik
    const roleError = document.getElementById('role-error');
    if (roleError) {
      setTimeout(() => {
        roleError.style.display = 'none';
      }, 3000);
    }
});
</script>


@endsection
