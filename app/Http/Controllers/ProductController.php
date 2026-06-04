<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

        $product = Product::create([
            'sku' => $sku,
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
        ]);

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id'     => $product->id,
                'user_id'        => Auth::id(),
                'type'           => 'in',
                'quantity'       => $product->stock,
                'reference_type' => 'Stok Awal',
                'reference_id'   => $product->id,
                'description'    => "Stok awal produk {$product->name}",
            ]);
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'TAMBAH_PRODUK',
            'description' => "Menambahkan produk {$product->name} (SKU: {$product->sku}), stok: {$product->stock}",
            'ip_address'  => request()->ip(),
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

        $oldStock = $product->stock;

        $product->update([
            'name' => $request->name,
            'category_id' => $request->category_id,
            'unit_id' => $request->unit_id,
            'purchase_price' => $request->purchase_price,
            'selling_price' => $request->selling_price,
            'stock' => $request->stock,
            'min_stock' => $request->min_stock,
        ]);

        $newStock = (int) $request->stock;

        if ($newStock !== $oldStock) {
            StockMovement::create([
                'product_id'     => $product->id,
                'user_id'        => Auth::id(),
                'type'           => $newStock > $oldStock ? 'in' : 'out',
                'quantity'       => abs($newStock - $oldStock),
                'reference_type' => 'Penyesuaian',
                'reference_id'   => $product->id,
                'description'    => "Penyesuaian stok {$product->name}: {$oldStock} → {$newStock}",
            ]);
        }

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'UBAH_PRODUK',
            'description' => "Mengubah produk {$product->name}" .
                             ($newStock !== $oldStock ? " (stok: {$oldStock}→{$newStock})" : ''),
            'ip_address'  => request()->ip(),
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
        $productName = $product->name;
        $productSku  = $product->sku;

        $product->delete();

        AuditLog::create([
            'user_id'     => Auth::id(),
            'action'      => 'HAPUS_PRODUK',
            'description' => "Menghapus produk {$productName} (SKU: {$productSku})",
            'ip_address'  => request()->ip(),
        ]);

        return redirect()
            ->route('inventory.index')
            ->with(
                'success',
                'Produk berhasil dihapus'
            );
    }
}
