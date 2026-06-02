<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with([
            'category',
            'unit'
        ]);

        // Search
        if($request->filled('search'))
        {
            $query->where(
                'name',
                'like',
                '%'.$request->search.'%'
            );
        }

        // Filter Category
        if($request->filled('category'))
        {
            $query->where(
                'category_id',
                $request->category
            );
        }

        $products = $query
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::all();

        return view(
            'inventory.index',
            compact(
                'products',
                'categories'
            )
        );
    }

    #Menampilkan form untuk menambahkan produk baru
    public function create()
    {
        $categories = Category::all();
        $units = Unit::all();

        return view(
            'inventory.create',
            compact(
                'categories', 
                'units'
            )
        );
    }
    #Untuk menyimpan data produk baru ke database
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'unit_id' => 'required',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $lastProduct = Product::latest('id') ->first();

        $nextId = $lastProduct ?
        $lastProduct->id + 1 : 1;

        $sku = 'PRD' .
        str_pad(
            $nextId,
            4,
            '0',
            STR_PAD_LEFT
        );

        Product::create([
            'sku' => $sku,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
        ]);

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Produk berhasil ditambahkan'
            );
    }

    #Menampilkan form untuk mengedit produk yang sudah ada
    public function edit(Product $product)
    {
        $categories = Category::all();
        $units = Unit::all();

        return view(
            'inventory.edit',
            compact(
                'product',
                'categories',
                'units'
            )
        );
    }

    #Untuk menyimpan perubahan data produk ke database
    public function update(
        Request $request,
        Product $product
    )
    {
        $request->validate([
            'name' => 'required',
            'category_id' => 'required',
            'unit_id' => 'required',
            'purchase_price' => 'required|numeric',
            'selling_price' => 'required|numeric',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
        ]);

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Produk berhasil diperbarui'
            );
    }
    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Produk berhasil dihapus'
            );
    }
}
