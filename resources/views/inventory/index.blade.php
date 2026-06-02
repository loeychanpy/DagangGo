<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-2xl font-bold text-gray-800">
                    Products
                </h2>
            </div>
            <a href="{{ route('inventory.create') }}"
                class="bg-slate-700 hover:bg-slate-800 text-white px-4 py-2 rounded-lg">
                + Add Product
            </a>
        </div>
    </x-slot>
    <div class="p-6">
        @if(session('success'))
        <div class="bg-green-100 text-green-700 p-3 rounded">
            {{ session('success') }}
        </div>
        @endif
        <div class="bg-white rounded-xl shadow-sm border">
            <!-- Toolbar -->
            <div class="p-4 border-b">
                <div class="flex justify-between items-center">
                    <div>
                        <form id="searchForm" method="GET" action="{{ route('inventory.index') }}">
                            <input id="search" name="search" type="text" value="{{ request('search') }}" placeholder="Search product..."class="rounded-lg border-gray-300 w-72">
                            <select id="categoryFilter" name="category" class="rounded-lg border-gray-300">
                                <option value="">
                                    All Categories
                                </option>
                            @foreach($categories as $category)
                                <option
                                    value="{{ $category->id }}"
                                    {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-3 text-left">
                                ID
                            </th>
                            <th class="px-4 py-3 text-left">
                                Product
                            </th>
                            <th class="px-4 py-3 text-left">
                                Category
                            </th>
                            <th class="px-4 py-3 text-left">
                                Unit
                            </th>
                            <th class="px-4 py-3 text-right">
                                Buy Price
                            </th>
                            <th class="px-4 py-3 text-right">
                                Sell Price
                            </th>
                            <th class="px-4 py-3 text-center">
                                Stock
                            </th>
                            <th class="px-4 py-3 text-center">
                                Action
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="px-4 py-4">
                                    {{ $product->sku }}
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div>
                                            <p class="font-semibold">
                                                {{ $product->name }}
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">
                                    {{ $product->category->name }}
                                </td>
                                <td class="px-4 py-4">
                                    {{ $product->unit->short_name }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    Rp {{ number_format($product->purchase_price,0,',','.') }}
                                </td>
                                <td class="px-4 py-4 text-right">
                                    Rp {{ number_format($product->selling_price,0,',','.') }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($product->stock > 20)
                                        <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm">
                                            {{ $product->stock }}
                                        </span>
                                    @elseif($product->stock > 0)
                                        <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm">
                                            {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm">
                                            Habis
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('inventory.edit',$product->id) }}"
                                            class="bg-blue-100 text-blue-700 px-3 py-2 rounded">
                                            Edit
                                        </a>

                                        <form action="{{ route('inventory.destroy',$product->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" onclick="openDeleteModal('{{ route('inventory.destroy',$product->id) }}')"
                                            class="bg-red-100 text-red-700 px-3 py-2 rounded">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td
                                    colspan="8"
                                    class="text-center py-10 text-gray-500">
                                    Data produk belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <!-- Pagination -->
            <div class="p-4">
                {{ $products->links() }}
            </div>
        </div>
    </div>
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-96">
            <h3 class="text-xl font-bold mb-3">
                Hapus Produk
            </h3>
            <p class="text-gray-600 mb-6">
                Apakah Anda yakin ingin menghapus produk ini?
            </p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteModal()" class="px-4 py-2 bg-gray-200 rounded-lg">
                    Batal
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                    class="px-4 py-2 bg-red-600 text-white rounded-lg">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>