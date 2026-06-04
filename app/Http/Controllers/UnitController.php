<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UnitController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name'       => 'required|string|max:100|unique:units,name',
            'short_name' => 'required|string|max:20',
        ]);

        $unit = Unit::create([
            'name'       => $request->name,
            'short_name' => $request->short_name,
        ]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'TAMBAH_SATUAN',
            'description' => "Menambahkan satuan {$unit->name} ({$unit->short_name})",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json(['id' => $unit->id, 'name' => $unit->name, 'short_name' => $unit->short_name]);
    }
}
