<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">
            
            <!-- 3 Main KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                
                <!-- Keuntungan Card -->
                <div class="bg-gradient-to-br from-green-50 to-green-100 rounded-xl shadow-lg p-8 border-l-4 border-green-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-green-700 uppercase tracking-wider">Keuntungan Hari Ini</p>
                            <p class="text-3xl font-bold text-green-900 mt-3">Rp {{ number_format($todaySales, 0, ',', '.') }}</p>
                        </div>
                        <div class="bg-green-500 bg-opacity-20 p-4 rounded-full">
                            <svg class="w-8 h-8 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                @if(config('features.kasbon'))
                    <!-- Kasbon Card -->
                    <div class="bg-gradient-to-br from-red-50 to-red-100 rounded-xl shadow-lg p-8 border-l-4 border-red-500">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-semibold text-red-700 uppercase tracking-wider">Total Kasbon</p>
                                <p class="text-3xl font-bold text-red-900 mt-3">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</p>
                            </div>
                            <div class="bg-red-500 bg-opacity-20 p-4 rounded-full">
                                <svg class="w-8 h-8 text-red-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Stok Kritis Card -->
                <div class="bg-gradient-to-br from-yellow-50 to-yellow-100 rounded-xl shadow-lg p-8 border-l-4 border-yellow-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-semibold text-yellow-700 uppercase tracking-wider">Stok Kritis</p>
                            <p class="text-3xl font-bold text-yellow-900 mt-3">{{ $lowStockProducts->count() }} Produk</p>
                        </div>
                        <div class="bg-yellow-500 bg-opacity-20 p-4 rounded-full">
                            <svg class="w-8 h-8 text-yellow-600" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M12 2a1 1 0 01.894.553l1.926 3.853a1 1 0 00.894.553h4.252a1 1 0 01.97 1.244l-3.236 2.963a1 1 0 00-.273 1.179l1.926 3.853a1 1 0 01-1.489 1.294L13.38 13.08a1 1 0 00-1.175-.102l-3.854 2.826a1 1 0 01-1.494-1.355l1.926-3.853a1 1 0 00-.273-1.179L2.694 8.403a1 1 0 01.97-1.244h4.252a1 1 0 00.894-.553l1.926-3.853A1 1 0 0112 2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Stok Kritis -->
            @if($lowStockProducts->count() > 0)
            <div class="bg-white rounded-xl shadow-lg overflow-hidden">
                <div class="bg-red-50 border-b-2 border-red-200 px-6 py-4">
                    <h3 class="text-lg font-bold text-red-700 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                          Peringatan Stok Rendah
                    </h3>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b border-gray-200">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-700 uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-4 text-center text-xs font-semibold text-gray-700 uppercase tracking-wider">Min</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @foreach($lowStockProducts as $product)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-sm text-gray-600">{{ $product->category->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold">
                                        {{ $product->stock }} {{ $product->unit->short_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center text-sm text-gray-600">{{ $product->min_stock }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-green-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-lg text-gray-600 font-medium">✅ Semua stok barang dalam kondisi aman</p>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>