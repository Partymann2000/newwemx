<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Email;
use App\Mail\CustomerMail;

class EmailsController extends Controller
{
    public function index()
    {
        return view('admin::emails.index');
    }

    public function view(Email $email)
    {
        return new CustomerMail($email);
    }

    public function configure()
    {
        return view('admin::emails.configure');
    }
}
