<x-app-layout>
    <x-slot name="header">
        <h2 class="text-2xl font-bold">
            Products > Edit Product
        </h2>
    </x-slot>
    <div class="p-6">
        <div class="bg-white rounded-xl shadow-md p-6 max-w-4xl mx-auto">
            @if ($errors->any())
                <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <form action="{{ route('inventory.update', $product->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-6">
                    <!-- Product Name -->
                    <div>
                        <label class="block mb-2 font-medium">Product Name</label>
                        <input type="text" name="name" class="w-full rounded-lg border-gray-300" value="{{ old('name', $product->name) }}" required>
                    </div>
                    <!-- Category -->
                    <div>
                        <label class="block mb-2 font-medium">Category</label>
                        <div class="flex gap-2">
                            <select name="category_id" id="category_id" class="flex-1 rounded-lg border-gray-300" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" onclick="openModal('category')"
                                title="Tambah kategori baru"
                                class="px-3 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold text-xl leading-none">
                                +
                            </button>
                        </div>
                    </div>
                    <!-- Unit -->
                    <div>
                        <label class="block mb-2 font-medium">Unit</label>
                        <div class="flex gap-2">
                            <select name="unit_id" id="unit_id" class="flex-1 rounded-lg border-gray-300" required>
                                <option value="">Select Unit</option>
                                @foreach($units as $unit)
                                    <option value="{{ $unit->id }}" {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                                        {{ $unit->name }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="button" onclick="openModal('unit')"
                                title="Tambah satuan baru"
                                class="px-3 rounded-lg bg-blue-100 text-blue-700 hover:bg-blue-200 font-bold text-xl leading-none">
                                +
                            </button>
                        </div>
                    </div>
                    <!-- Purchase Price -->
                    <div>
                        <label class="block mb-2 font-medium">Purchase Price</label>
                        <input type="number" name="purchase_price" class="w-full rounded-lg border-gray-300" value="{{ old('purchase_price', $product->purchase_price) }}" required>
                    </div>
                    <!-- Selling Price -->
                    <div>
                        <label class="block mb-2 font-medium">Selling Price</label>
                        <input type="number" name="selling_price" class="w-full rounded-lg border-gray-300" value="{{ old('selling_price', $product->selling_price) }}" required>
                    </div>
                    <!-- Stock -->
                    <div>
                        <label class="block mb-2 font-medium">Stock</label>
                        <input type="number" name="stock" value="{{ old('stock', $product->stock) }}" class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <!-- Minimum Stock -->
                    <div>
                        <label class="block mb-2 font-medium">Minimum Stock</label>
                        <input type="number" name="min_stock" value="{{ old('min_stock', $product->min_stock) }}" class="w-full rounded-lg border-gray-300" required>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('inventory.index') }}" class="px-5 py-2 rounded-lg bg-gray-200">Cancel</a>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Tambah Kategori -->
    <div id="modal-category" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-xl shadow-lg p-6 w-80">
            <h3 class="text-lg font-bold mb-4">Tambah Kategori Baru</h3>
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Nama Kategori</label>
                <input type="text" id="new-category-name" class="w-full rounded-lg border-gray-300" placeholder="Contoh: Minuman">
                <p id="category-error" class="text-red-600 text-sm mt-2 hidden"></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('category')" class="px-4 py-2 rounded-lg bg-gray-200 text-sm">Batal</button>
                <button type="button" onclick="saveCategory()" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Simpan</button>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Satuan -->
    <div id="modal-unit" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 hidden">
        <div class="bg-white rounded-xl shadow-lg p-6 w-80">
            <h3 class="text-lg font-bold mb-4">Tambah Satuan Baru</h3>
            <div class="mb-3">
                <label class="block mb-1 text-sm font-medium">Nama Satuan</label>
                <input type="text" id="new-unit-name" class="w-full rounded-lg border-gray-300" placeholder="Contoh: Kilogram">
            </div>
            <div class="mb-4">
                <label class="block mb-1 text-sm font-medium">Singkatan</label>
                <input type="text" id="new-unit-short" class="w-full rounded-lg border-gray-300" placeholder="Contoh: kg">
                <p id="unit-error" class="text-red-600 text-sm mt-2 hidden"></p>
            </div>
            <div class="flex justify-end gap-2">
                <button type="button" onclick="closeModal('unit')" class="px-4 py-2 rounded-lg bg-gray-200 text-sm">Batal</button>
                <button type="button" onclick="saveUnit()" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm">Simpan</button>
            </div>
        </div>
    </div>

    <script>
        function openModal(type) {
            document.getElementById('modal-' + type).classList.remove('hidden');
            setTimeout(() => document.getElementById('new-' + type + '-name').focus(), 50);
        }

        function closeModal(type) {
            document.getElementById('modal-' + type).classList.add('hidden');
            document.getElementById(type + '-error').classList.add('hidden');
            document.getElementById('new-' + type + '-name').value = '';
            if (type === 'unit') document.getElementById('new-unit-short').value = '';
        }

        async function saveCategory() {
            const name  = document.getElementById('new-category-name').value.trim();
            const errEl = document.getElementById('category-error');
            errEl.classList.add('hidden');

            if (!name) {
                errEl.textContent = 'Nama kategori wajib diisi.';
                errEl.classList.remove('hidden');
                return;
            }

            const res = await fetch('{{ route("categories.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name }),
            });

            if (res.ok) {
                const data = await res.json();
                const select = document.getElementById('category_id');
                const option = new Option(data.name, data.id, true, true);
                select.add(option);
                closeModal('category');
            } else {
                const data = await res.json();
                errEl.textContent = data.errors?.name?.[0] ?? data.message ?? 'Gagal menyimpan.';
                errEl.classList.remove('hidden');
            }
        }

        async function saveUnit() {
            const name  = document.getElementById('new-unit-name').value.trim();
            const short = document.getElementById('new-unit-short').value.trim();
            const errEl = document.getElementById('unit-error');
            errEl.classList.add('hidden');

            if (!name || !short) {
                errEl.textContent = 'Nama dan singkatan wajib diisi.';
                errEl.classList.remove('hidden');
                return;
            }

            const res = await fetch('{{ route("units.store") }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ name, short_name: short }),
            });

            if (res.ok) {
                const data = await res.json();
                const select = document.getElementById('unit_id');
                const option = new Option(data.name, data.id, true, true);
                select.add(option);
                closeModal('unit');
            } else {
                const data = await res.json();
                errEl.textContent = data.errors?.name?.[0] ?? data.errors?.short_name?.[0] ?? data.message ?? 'Gagal menyimpan.';
                errEl.classList.remove('hidden');
            }
        }

        // Tutup modal saat klik backdrop
        document.getElementById('modal-category').addEventListener('click', function (e) {
            if (e.target === this) closeModal('category');
        });
        document.getElementById('modal-unit').addEventListener('click', function (e) {
            if (e.target === this) closeModal('unit');
        });
    </script>
</x-app-layout>
