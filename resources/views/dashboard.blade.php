<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-on-surface leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-6xl mx-auto">

            <!-- Filter Waktu -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4 mb-6 flex flex-wrap items-center gap-3">
                <span class="text-sm font-semibold text-on-surface-variant mr-1">Periode:</span>

                @foreach(['today' => 'Hari Ini', 'week' => '7 Hari', 'month' => 'Bulan Ini'] as $key => $label)
                <a href="{{ route('dashboard', ['period' => $key]) }}"
                    class="px-4 py-2 rounded-full text-sm font-semibold transition
                        {{ $period === $key
                            ? 'bg-primary text-white shadow-sm'
                            : 'bg-surface-container text-on-surface-variant hover:bg-surface-high' }}">
                    {{ $label }}
                </a>
                @endforeach

                <button type="button" id="btn-custom"
                    class="px-4 py-2 rounded-full text-sm font-semibold transition
                        {{ $period === 'custom'
                            ? 'bg-primary text-white shadow-sm'
                            : 'bg-surface-container text-on-surface-variant hover:bg-surface-high' }}">
                    Kustom
                </button>

                <form id="custom-form" method="GET" action="{{ route('dashboard') }}"
                    class="{{ $period === 'custom' ? 'flex' : 'hidden' }} items-center gap-2">
                    <input type="hidden" name="period" value="custom">
                    <input type="date" name="start_date" value="{{ $startDate }}"
                        class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm py-2 px-3">
                    <span class="text-on-surface-variant">–</span>
                    <input type="date" name="end_date" value="{{ $endDate }}"
                        class="rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm py-2 px-3">
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-full text-sm font-semibold hover:bg-primary-dark">
                        Terapkan
                    </button>
                </form>
            </div>

            <!-- Label periode aktif -->
            <p class="text-sm text-on-surface-variant mb-4 -mt-2">
                Menampilkan data: <span class="font-semibold text-primary">{{ $periodLabel }}</span>
            </p>

            <!-- KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-5 mb-8">

                <!-- Total Penjualan -->
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-secondary">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Penjualan</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">{{ $periodLabel }}</p>
                    <p class="font-mono text-xl font-bold text-secondary mt-2">Rp {{ number_format($periodSales, 0, ',', '.') }}</p>
                </div>

                <!-- Jumlah Transaksi -->
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-primary">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Jumlah Transaksi</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">{{ $periodLabel }}</p>
                    <p class="font-mono text-xl font-bold text-primary mt-2">{{ $periodTransactions }} Transaksi</p>
                </div>

                @if(config('features.kasbon'))
                <!-- Kasbon -->
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-red-500">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Kasbon</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Semua piutang aktif</p>
                    <p class="font-mono text-xl font-bold text-red-600 mt-2">Rp {{ number_format($totalReceivables, 0, ',', '.') }}</p>
                </div>
                @endif

                <!-- Stok Kritis -->
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-amber-500">
                    <p class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Stok Kritis</p>
                    <p class="text-xs text-on-surface-variant mt-0.5">Perlu restock segera</p>
                    <p class="font-mono text-xl font-bold text-amber-600 mt-2">{{ $lowStockProducts->count() }} Produk</p>
                </div>
            </div>

            <!-- Tabel Stok Kritis -->
            @if($lowStockProducts->count() > 0)
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden mb-8">
                <div class="bg-red-50 border-b border-red-200 px-6 py-4">
                    <h3 class="text-base font-bold text-red-700 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                        </svg>
                        Peringatan Stok Rendah
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-surface-low border-b border-outline-variant">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Nama Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Kategori</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Stok</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Min</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @foreach($lowStockProducts as $product)
                            <tr class="hover:bg-surface-low transition">
                                <td class="px-6 py-4 text-sm font-semibold text-on-surface">{{ $product->name }}</td>
                                <td class="px-6 py-4 text-sm text-on-surface-variant">{{ $product->category->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="font-mono inline-block px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm font-bold">
                                        {{ $product->stock }} {{ $product->unit->short_name }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center font-mono text-sm text-on-surface-variant">{{ $product->min_stock }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @else
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-10 text-center mb-8">
                <svg class="w-12 h-12 mx-auto text-secondary mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m7 0a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-base text-on-surface-variant font-medium">Semua stok barang dalam kondisi aman</p>
            </div>
            @endif

            <!-- Grafik Penjualan -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-6">
                <h3 class="text-base font-bold text-on-surface mb-0.5">Grafik Penjualan</h3>
                <p class="text-sm text-on-surface-variant mb-4">{{ $periodLabel }}</p>
                <canvas id="salesChart" height="80"></canvas>
            </div>

        </div>
    </div>

    <script>
        window.salesChartLabels = {!! json_encode($chartLabels) !!};
        window.salesChartData   = {!! json_encode($chartData) !!};

        document.getElementById('btn-custom').addEventListener('click', function() {
            const form = document.getElementById('custom-form');
            form.classList.toggle('hidden');
            form.classList.toggle('flex');
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    @vite('resources/js/dashboard.js')
</x-app-layout>
