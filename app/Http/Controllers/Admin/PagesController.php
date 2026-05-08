<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;

class PagesController extends Controller
{
    public function index()
    {
        return view('admin::pages.index');
    }

    public function create()
    {
        return view('admin::pages.create');
    }

    public function view(CustomPage $page)
    {
        return view('admin::pages.view', compact('page'));
    }

    public function edit(CustomPage $page)
    {
        return view('admin::pages.edit', compact('page'));
    }
}
