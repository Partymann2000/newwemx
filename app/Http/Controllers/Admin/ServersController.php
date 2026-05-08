<?php

namespace App\Http\Controllers\Admin;

use App\Models\ServerConnection;

class ServersController
{
    public function index()
    {
        return view('admin::servers.index');
    }
    public function connections()
    {
        return view('admin::servers.connections');
    }
    public function createConnection()
    {
        return view('admin::servers.create-connection');
    }
    public function editConnection(ServerConnection $connection)
    {
        return view('admin::servers.edit-connection', ['connection' => $connection]);
    }
}
