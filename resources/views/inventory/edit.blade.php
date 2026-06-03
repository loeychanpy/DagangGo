<x-app-layout>
    <x-slot name="header">
        <h2
        class="text-2xl font-bold">
            Products > Edit Product
        </h2>
    </x-slot>
    <div class="p-6">
        <div class="bg-white rounded-xl shadow-md p-6 max-w-4xl mx-auto">
            @if ($errors->any())
                <div
                class="bg-red-100 text-red-700 p-4 rounded mb-4">

                    <ul>

                        @foreach ($errors->all() as $error)

                            <li>{{ $error }}</li>

                        @endforeach

                    </ul>

                </div>
            @endif
            <form action="{{ route('inventory.update',$product->id) }}"method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-2 gap-6">
                    <!-- Product Name -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Product Name
                        </label>
                        <input type="text" name="name" class="w-full rounded-lg border-gray-300"value="{{ old('name',$product->name) }}" required>
                    </div>
                    <!-- Category -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Category
                        </label>
                        <select name="category_id" class="w-full rounded-lg border-gray-300" required>
                            <option value="">
                                Select Category
                            </option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Unit -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Unit
                        </label>
                        <select name="unit_id" class="w-full rounded-lg border-gray-300" required>
                            <option value="">
                                Select Unit
                            </option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}"
                                {{ $product->unit_id == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <!-- Purchase Price -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Purchase Price
                        </label>
                        <input type="number" name="purchase_price" class="w-full rounded-lg border-gray-300" value="{{ old('purchase_price',$product->purchase_price) }}" required>
                    </div>
                    <!-- Selling Price -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Selling Price
                        </label>
                        <input type="number" name="selling_price" class="w-full rounded-lg border-gray-300" value="{{ old('selling_price',$product->selling_price) }}" required>
                    </div>
                    <!-- Stock -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Stock
                        </label>
                        <input type="number" name="stock" value="{{ old('stock',$product->stock) }}" class="w-full rounded-lg border-gray-300" required>
                    </div>
                    <!-- Minimum Stock -->
                    <div>
                        <label class="block mb-2 font-medium">
                            Minimum Stock
                        </label>
                        <input type="number" name="min_stock" value="{{ old('min_stock',$product->min_stock) }}" class="w-full rounded-lg border-gray-300" required>
                    </div>
                </div>
                <div class="flex justify-end gap-3 mt-8">
                    <a href="{{ route('inventory.index') }}" class="px-5 py-2 rounded-lg bg-gray-200">
                        Cancel
                    </a>
                    <button type="submit" class="px-5 py-2 rounded-lg bg-blue-600 text-white">
                        Update Product
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>