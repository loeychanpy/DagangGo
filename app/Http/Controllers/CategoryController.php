<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CategoryController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100|unique:categories,name',
        ]);

        $category = Category::create(['name' => $request->name]);

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'TAMBAH_KATEGORI',
            'description' => "Menambahkan kategori {$category->name}",
            'ip_address'  => $request->ip(),
        ]);

        return response()->json(['id' => $category->id, 'name' => $category->name]);
    }
}
