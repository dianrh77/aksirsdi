<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function profile()
    {
        return view('users.profile');
    }

    public function accountSettings()
    {
        return view('users.account-settings');
    }
}
