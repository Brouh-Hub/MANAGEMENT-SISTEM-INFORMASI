<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogLogin;
use App\Models\Sekolah;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    private const MAKS_GAGAL = 5;
    private const DETIK_KUNCI = 300;

    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('admin.dashboard');
        }

        $sekolah = Sekolah::first();

        return view('auth.login', compact('sekolah'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required'],
        ]);

        $key = strtolower($credentials['email']) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, self::MAKS_GAGAL)) {
            $this->catat($request, 'terkunci', $credentials['email']);

            throw ValidationException::withMessages([
                'email' => 'Terlalu banyak percobaan. Coba lagi dalam ' . RateLimiter::availableIn($key) . ' detik.',
            ]);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();
            $this->catat($request, 'berhasil', $credentials['email'], Auth::id());

            return redirect()->intended(route('admin.dashboard'));
        }

        RateLimiter::hit($key, self::DETIK_KUNCI);
        $this->catat($request, 'gagal', $credentials['email']);

        $sisa = RateLimiter::remaining($key, self::MAKS_GAGAL);

        throw ValidationException::withMessages([
            'email' => 'Email atau password salah. Sisa percobaan: ' . $sisa . '.',
        ]);
    }

    private function catat(Request $request, string $status, string $email, ?int $userId = null): void
    {
        LogLogin::create([
            'user_id'    => $userId,
            'email'      => $email,
            'ip'         => $request->ip(),
            'user_agent' => Str::limit((string) $request->userAgent(), 250, ''),
            'status'     => $status,
        ]);
    }

    public function logout(Request $request)
    {
        if ($user = Auth::user()) {
            $this->catat($request, 'logout', $user->email, $user->id);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
