<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AppsController extends Controller
{
    public function chat()
    {
        return view('apps.chat');
    }

    public function mailbox()
    {
        return view('apps.mailbox');
    }

    public function todolist()
    {
        return view('apps.todolist');
    }

    public function notes()
    {
        return view('apps.notes');
    }

    public function scrumboard()
    {
        return view('apps.scrumboard');
    }

    public function contacts()
    {
        return view('apps.contacts');
    }

    public function invoiceList()
    {
        return view('apps.invoice.list');
    }

    public function invoicePreview()
    {
        return view('apps.invoice.preview');
    }

    public function invoiceEdit()
    {
        return view('apps.invoice.edit');
    }

    public function invoiceAdd()
    {
        return view('apps.invoice.add');
    }

    public function calendar()
    {
        return view('apps.calendar');
    }
}
