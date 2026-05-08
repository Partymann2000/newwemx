<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\CustomPage;

class PagesController extends Controller
{
    public function view(CustomPage $page)
    {
        abort_if(! $page->isActive(), 404);

        return view('theme::pages.view', compact('page'));
    }
}
