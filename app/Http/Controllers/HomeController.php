<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('dashboard.home');
    }

    public function analytics()
    {
        return view('dashboard.analytics');
    }
    
    public function finance()
    {
        return view('dashboard.finance');
    }

    public function crypto()
    {
        return view('dashboard.crypto');
    }

    /** Display the lock screen */
    public function lockscreen() {
        if(!Session::has('email')) {
            return redirect('login');
        }
        $user = User::where('email', Session::get('email'))->first();
        return view('auth.lockscreen', compact('user'));
    }
}
