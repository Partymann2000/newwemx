<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Package;

class PackagesController extends Controller
{
    public function index()
    {
        $categories = Category::query()
            ->with([
                'packages' => static function ($query): void {
                    $query->with('serverConnection')
                        ->orderBy('sort_order')
                        ->orderBy('name');
                },
            ])
            ->has('packages')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $packagesWithoutCategory = Package::query()
            ->with('serverConnection')
            ->whereDoesntHave('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin::packages.index', compact('categories', 'packagesWithoutCategory'));
    }

    public function create()
    {
        return view('admin::packages.create');
    }

    public function edit(Package $package)
    {
        return view('admin::packages.edit.index', compact('package'));
    }
}
