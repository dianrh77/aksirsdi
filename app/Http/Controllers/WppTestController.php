<?php

namespace App\Http\Controllers;

use App\Helper\WppHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class WppTestController extends Controller
{
    public function index()
    {
        return view('test.wpp-test');
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'number' => 'required',
            'message' => 'required',
        ]);

        $result = WppHelper::sendMessage($request->number, $request->message);

        return back()->with('result', $result);
    }
}
