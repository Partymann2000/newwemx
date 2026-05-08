<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoriesController extends Controller
{
    public function index()
    {
        return view('admin::categories.index');
    }

    public function create()
    {
        return view('admin::categories.create');
    }

    public function edit(Category $category)
    {
        return view('admin::categories.edit', compact('category'));
    }
}
