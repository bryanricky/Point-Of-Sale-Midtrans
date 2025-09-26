<?php

namespace App\Actions\Fortify;
use App\Models\User;

use App\Models\LogKasir;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Validation\ValidationException;

class CustomAuthenticateUser
{
    public function __invoke(Request $request): LoginResponse
    {
        $request->validate([
            'email'     => ['required', 'email'],
            'password'  => ['required'],
            'role'      => ['required', 'in:admin,kasir'],
        ]);

        // Cek user berdasarkan email
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['Email tidak terdaftar.'],
                'password' => [''],
            ]);
        }
        $selectedRole = $request->role;
            $expectedLevel = $request->role === 'admin' ? 1 : 2;

        if ($user->level != $expectedLevel) {
            throw ValidationException::withMessages([
                'role' => ['Role tidak sesuai.'],
            ]);
        }

        // Validasi kas_awal hanya jika level kasir
        // Jika kasir, cek apakah sudah pernah login hari ini
        $existingLog = null;
        if ($user->level == 2) {
            $existingLog = LogKasir::where('user_id', $user->id)
                ->whereDate('login_at', now()->toDateString())
                ->first();

            // Hanya validasi kas_awal jika belum pernah login hari ini
            if (!$existingLog) {
                $request->validate([
                    'kas_awal' => ['required', 'numeric', 'min:0'],
                ]);
            }
        }

        // Proses login (Fortify akan handle login dengan Auth::login)
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            throw ValidationException::withMessages([
                'password' => ['Password salah.'],
            ]);
        }

        $request->session()->regenerate();

        // Hanya untuk kasir
        if ($user->level == 2) {
            Session::put('kas_awal', $request->kas_awal);

            $existingLog = LogKasir::where('user_id', $user->id)
                ->whereDate('login_at', now()->toDateString())
                ->first();

            if (!$existingLog) {
                LogKasir::create([
                    'user_id'  => $user->id,
                    'name'     => $user->name,
                    'level'    => $user->level,
                    'kas_awal' => $request->kas_awal,
                    'login_at' => now(),
                ]);
            }
        }

        // Hanya kembalikan response, jangan pakai Auth::login()
        return app(LoginResponse::class);
    }
}

