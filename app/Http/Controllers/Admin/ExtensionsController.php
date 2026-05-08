<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Extension;

class ExtensionsController extends Controller
{
    public function index()
    {
        return view('admin::extensions.index');
    }

    public function discover()
    {
        Extension::discover();
        return redirect()->back()->with('success', 'Extensions discovered successfully.');
    }

    public function toggle($extension, $redirect = true)
    {
        $extension = Extension::findOrFail($extension);
        if ($extension->isEnabled()) {
            $extension->disable();
        } else {
            $extension->enable();
        }
        if ($redirect) {
            return redirect()->back()->with('success', 'Extension toggled successfully.');
        }
        return response()->json(['status' => 'success']);
    }
}
