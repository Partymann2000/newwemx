<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\DatabaseSqlExportService;
use Illuminate\Http\Response;
use RuntimeException;

class UpdatesController extends Controller
{
    public function exportDatabase(DatabaseSqlExportService $exporter): Response
    {
        abort_unless(auth()->user()->isPrimaryAdmin(), 403);

        try {
            $sql = $exporter->export();
        } catch (RuntimeException $exception) {
            abort(422, $exception->getMessage());
        }

        $filename = $exporter->suggestedFilename();

        return response($sql, 200, [
            'Content-Type' => 'application/sql; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$filename.'"',
            'Cache-Control' => 'no-store, no-cache, must-revalidate',
        ]);
    }
}
