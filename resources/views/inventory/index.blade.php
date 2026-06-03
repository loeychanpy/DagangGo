<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="text-xl font-bold text-on-surface">Products</h2>
            <a href="{{ route('inventory.create') }}"
                class="bg-primary hover:bg-primary-dark text-white px-4 py-2 rounded-lg text-sm font-semibold transition">
                + Add Product
            </a>
        </div>
    </x-slot>

    <div class="p-6">
        @if(session('success'))
        <div class="bg-secondary/10 text-secondary-dark border border-secondary/30 px-4 py-3 rounded-lg mb-4 text-sm font-medium">
            {{ session('success') }}
        </div>
        @endif

        <div class="bg-white rounded-xl border border-outline-variant shadow-sm">
            <!-- Toolbar -->
            <div class="p-4 border-b border-outline-variant">
                <form id="searchForm" method="GET" action="{{ route('inventory.index') }}" class="flex gap-3 flex-wrap">
                    <input id="search" name="search" type="text" value="{{ request('search') }}"
                        placeholder="Search product..."
                        class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm w-72">
                    <select id="categoryFilter" name="category"
                        class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        <option value="">All Categories</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary-dark transition">
                        Cari
                    </button>
                    @if(request('search') || request('category'))
                    <a href="{{ route('inventory.index') }}" class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                        Reset
                    </a>
                    @endif
                </form>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-surface-low border-b border-outline-variant">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">SKU</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Produk</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kategori</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Satuan</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Harga Beli</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Harga Jual</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Stok</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-outline-variant">
                        @forelse($products as $product)
                            <tr class="hover:bg-surface-low transition">
                                <td class="px-4 py-4 font-mono text-xs text-on-surface-variant">{{ $product->sku }}</td>
                                <td class="px-4 py-4">
                                    <p class="font-semibold text-sm text-on-surface">{{ $product->name }}</p>
                                </td>
                                <td class="px-4 py-4 text-sm text-on-surface-variant">{{ $product->category->name }}</td>
                                <td class="px-4 py-4 text-sm text-on-surface-variant">{{ $product->unit->short_name }}</td>
                                <td class="px-4 py-4 text-right font-mono text-sm text-on-surface-variant">
                                    Rp {{ number_format($product->purchase_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-right font-mono text-sm font-semibold text-primary">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-4 text-center">
                                    @if($product->stock > 20)
                                        <span class="font-mono px-3 py-1 bg-secondary/10 text-secondary rounded-full text-xs font-bold">
                                            {{ $product->stock }}
                                        </span>
                                    @elseif($product->stock > 0)
                                        <span class="font-mono px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-bold">
                                            {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="font-mono px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">
                                            Habis
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('inventory.edit', $product->id) }}"
                                            class="bg-primary/10 text-primary hover:bg-primary/20 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                            Edit
                                        </a>
                                        <button type="button"
                                            onclick="openDeleteModal('{{ route('inventory.destroy', $product->id) }}')"
                                            class="bg-red-50 text-red-600 hover:bg-red-100 px-3 py-1.5 rounded-lg text-xs font-semibold transition">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-10 text-on-surface-variant text-sm">
                                    Data produk belum tersedia
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4 border-t border-outline-variant">
                {{ $products->links() }}
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-xl border border-outline-variant p-6 w-96 shadow-2xl">
            <h3 class="text-base font-bold text-on-surface mb-2">Hapus Produk</h3>
            <p class="text-sm text-on-surface-variant mb-6">Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan.</p>
            <div class="flex justify-end gap-3">
                <button onclick="closeDeleteModal()"
                    class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                    Batal
                </button>
                <form id="deleteForm" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg text-sm font-semibold hover:bg-red-700 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openDeleteModal(url) {
            document.getElementById('deleteForm').action = url;
            const modal = document.getElementById('deleteModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }
        function closeDeleteModal() {
            const modal = document.getElementById('deleteModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Auto-submit on input change
        const searchInput = document.getElementById('search');
        const categoryFilter = document.getElementById('categoryFilter');
        let searchTimer;
        searchInput?.addEventListener('input', function() {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(() => document.getElementById('searchForm').submit(), 400);
        });
        categoryFilter?.addEventListener('change', function() {
            document.getElementById('searchForm').submit();
        });
    </script>
</x-app-layout>
