<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;

class RolesController extends Controller
{
    public function index()
    {
        return view('admin::roles.index');
    }

    public function create()
    {
        return view('admin::roles.create');
    }

    public function edit(Role $role)
    {
        return view('admin::roles.edit', compact('role'));
    }
}
