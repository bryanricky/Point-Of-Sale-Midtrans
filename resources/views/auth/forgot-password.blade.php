@extends('layouts.auth')

@section('login')
<div class="login-box">
  <div class="login-box-body text-center">

    <h3>Reset Password</h3>

    {{-- Tampilkan pesan sukses kalau ada --}}
    @if (session('status'))
      <div class="alert alert-success">
        {{ session('status') }}
      </div>
    @endif

    {{-- Tampilkan pesan error kalau ada --}}
    @if ($errors->any())
      <div class="alert alert-danger">
        <ul class="mb-0">
          @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('password.update') }}">
      @csrf

      {{-- Kolom Email --}}
      <div class="form-group">
        <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda" value="{{ old('email') }}" required>
      </div>

      {{-- Kolom Password Baru --}}
      <div class="form-group">
        <input type="password" name="password" class="form-control" placeholder="Password Baru" required>
      </div>

      {{-- Kolom Konfirmasi Password --}}
      <div class="form-group">
        <input type="password" name="password_confirmation" class="form-control" autocomplete="new-password" placeholder="Ulangi Password Baru" required>
      </div>

      <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>

    <br>
    <a href="{{ route('login') }}">Kembali ke Login</a>

  </div>
</div>
@endsection
