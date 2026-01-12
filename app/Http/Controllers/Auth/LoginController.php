<?php

namespace App\Http\Controllers\Auth;

use DB;
use Auth;
use Session;
use Carbon\Carbon;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use RealRashid\SweetAlert\Facades\Alert;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    public function __construct()
    {
        $this->middleware('guest')->except(['logout', 'locked', 'unlock']);
    }

    /** Display the login page */
    public function login()
    {
        return view('auth.login');
    }

    /** Authenticate user and redirect */
    public function authenticate(Request $request)
    {
        $request->validate([
            'email'    => 'required|string',
            'password' => 'required|string',
        ]);

        try {
            $credentials = $request->only('email', 'password') + ['status' => 'Active'];

            if (Auth::attempt($credentials)) {
                $user = Auth::user();
                Session::put($this->getUserSessionData($user));

                // Update last login
                $user->update(['last_login' => Carbon::now()]);

                // Redirect berdasarkan role
                if (in_array($user->role_name, ['kesekretariatan', 'direktur_utama', 'direktur_umum'])) {
                    return redirect()->route('dashboard.kesekretariatan')
                        ->with('success', 'Selamat datang di Dashboard Kesekretariatan!');
                } elseif ($user->role_name === 'user') {
                    return redirect()->route('dashboard.penerima')
                        ->with('success', 'Selamat datang di Dashboard Penerima Disposisi!');
                }
            }

            Alert::Error('Gagal!', 'Periksa Email dan Password Anda');
            return redirect('login');
        } catch (\Exception $e) {
            \Log::error('Login error: ' . $e->getMessage());
            Alert::Error('Gagal!', 'Periksa Email dan Password Anda');
            return redirect()->back();
        }
    }

    /** Prepare User Session Data */
    private function getUserSessionData($user)
    {
        return [
            'name'                => $user->name,
            'email'               => $user->email,
            'user_id'             => $user->user_id,
            'join_date'           => $user->join_date,
            'phone_number'        => $user->phone_number,
            'status'              => $user->status,
            'role_name'           => $user->role_name,
            'avatar'              => $user->avatar,
            'position'            => $user->position,
            'department'          => $user->department,
            'line_manager'        => $user->line_manager,
            'second_line_manager' => $user->second_line_manager,
        ];
    }

    /** Logout and clear session */
    public function logout(Request $request)
    {
        $request->session()->flush();
        Auth::logout();
        Alert::success('Logout!', 'Sampai Jumpa Kembali');
        return redirect('login');
    }
}
