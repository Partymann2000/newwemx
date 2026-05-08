<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Group;

class GroupsController extends Controller
{
    public function index()
    {
        return view('admin::groups.index');
    }

    public function create()
    {
        return view('admin::groups.create');
    }

    public function edit(Group $group)
    {
        return view('admin::groups.edit', ['id' => $group->id]);
    }

    public function assignGroups()
    {
        return view('admin::groups.assign');
    }
}
