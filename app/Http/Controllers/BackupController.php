<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BackupController extends Controller
{
    public function index()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        return view('backups.index');
    }

    public function export()
    {
        abort_unless(Auth::user()->isSuperAdmin(), 403);

        $tables = [
            'tenants',
            'affiliated_companies',
            'sender_profiles',
            'users',
            'delivery_zones',
            'shipments',
            'shipment_events',
            'app_settings',
            'audit_logs',
        ];

        $fileName = 'backup-tus-envios-'.now()->format('Y-m-d-His').'.json';

        return response()->streamDownload(function () use ($tables) {
            $payload = [
                'generated_at' => now()->toDateTimeString(),
                'tables' => [],
            ];

            foreach ($tables as $table) {
                $payload['tables'][$table] = DB::table($table)->get();
            }

            echo json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }, $fileName, [
            'Content-Type' => 'application/json; charset=UTF-8',
        ]);
    }
}

