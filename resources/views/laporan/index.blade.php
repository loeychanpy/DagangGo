<x-app-layout>
    <x-slot name="header">
        <h2 class="font-bold text-xl text-on-surface leading-tight">
            {{ __('Laporan Penjualan & Keuangan') }}
        </h2>
    </x-slot>

    <div class="py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-7xl mx-auto">

            <!-- Filter & Export -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 mb-6">
                <form action="{{ route('laporan.index') }}" method="GET" class="flex flex-col md:flex-row md:items-end gap-3">
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}"
                            class="block w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                    </div>
                    <div class="flex-1">
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Tanggal Selesai</label>
                        <input type="date" name="end_date" value="{{ $endDate }}"
                            class="block w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-primary-dark transition">
                        Filter
                    </button>
                    <a href="{{ route('laporan.index') }}" class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-surface-high transition text-center">
                        Reset
                    </a>
                    <a href="{{ route('laporan.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}"
                       class="px-4 py-2 bg-red-600 text-white rounded-lg font-semibold text-xs uppercase tracking-widest hover:bg-red-700 transition text-center">
                        PDF
                    </a>
                </form>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-primary">
                    <h3 class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Omzet</h3>
                    <p class="font-mono text-2xl font-bold text-primary mt-2">Rp {{ number_format($totalSales, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-secondary">
                    <h3 class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Uang Masuk</h3>
                    <p class="font-mono text-2xl font-bold text-secondary mt-2">Rp {{ number_format($totalReceived, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 border-l-4 border-l-red-500">
                    <h3 class="text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total Piutang</h3>
                    <p class="font-mono text-2xl font-bold text-red-600 mt-2">Rp {{ number_format($totalDebt, 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Tabel Transaksi -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-outline-variant">
                    <h3 class="font-bold text-base text-on-surface">
                        Daftar Transaksi — <span class="font-mono text-on-surface-variant font-normal text-sm">{{ $startDate }} s/d {{ $endDate }}</span>
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-surface-low border-b border-outline-variant">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Tanggal</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Invoice</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Pelanggan</th>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Metode</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Total</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Status</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Sisa</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-on-surface-variant uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-outline-variant">
                            @forelse ($transactions as $transaction)
                            <tr class="hover:bg-surface-low transition" data-id="{{ $transaction->id }}">
                                <td class="px-4 py-3 whitespace-nowrap text-xs text-on-surface-variant font-mono">
                                    {{ $transaction->created_at->format('d/m/Y H:i') }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="font-mono text-xs font-semibold text-primary">{{ $transaction->invoice_number }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-on-surface">
                                    {{ $transaction->customer->name ?? 'Umum' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap">
                                    <span class="text-xs uppercase font-semibold text-on-surface-variant bg-surface-container px-2 py-0.5 rounded-full">
                                        {{ $transaction->payment_method }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right">
                                    <span class="font-mono text-sm font-bold text-on-surface">Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center cell-status">
                                    <span class="px-2 py-0.5 text-xs font-bold rounded-full
                                        {{ $transaction->status === 'paid'
                                            ? 'bg-secondary/10 text-secondary'
                                            : ($transaction->status === 'partial'
                                                ? 'bg-amber-100 text-amber-700'
                                                : 'bg-red-100 text-red-700') }}">
                                        {{ strtoupper($transaction->status) }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-right cell-remaining">
                                    <span class="font-mono text-sm font-semibold {{ $transaction->remaining_bill > 0 ? 'text-red-600' : 'text-on-surface-variant' }}">
                                        Rp {{ number_format($transaction->remaining_bill, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-center">
                                    <div class="relative inline-block action-dropdown">
                                        <button type="button" class="action-trigger inline-flex items-center gap-1 px-3 py-1.5 bg-surface-container text-on-surface-variant rounded-lg text-xs font-semibold hover:bg-surface-high transition">
                                            Aksi
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                                        </button>
                                        <div class="action-menu hidden absolute right-0 top-full mt-1 w-48 bg-white border border-outline-variant rounded-xl shadow-lg z-20 overflow-hidden">
                                            @if($transaction->customer)
                                            <button type="button"
                                                class="btn-info-customer w-full text-left px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low flex items-center gap-2.5 transition"
                                                data-url="{{ route('customers.show', $transaction->customer_id) }}"
                                                data-name="{{ $transaction->customer->name }}">
                                                <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                                Info Pelanggan
                                            </button>
                                            @endif
                                            <a href="{{ route('transactions.invoice', $transaction->id) }}" target="_blank"
                                                class="w-full text-left px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low flex items-center gap-2.5 transition">
                                                <svg class="w-4 h-4 text-red-600 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                                Unduh PDF
                                            </a>
                                            @php
                                                $proofs = $transaction->payments->whereNotNull('proof_photo');
                                            @endphp
                                            @if($proofs->isNotEmpty())
                                            <button type="button"
                                                class="btn-lihat-bukti w-full text-left px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low flex items-center gap-2.5 transition"
                                                data-invoice="{{ $transaction->invoice_number }}"
                                                data-proofs="{{ json_encode($proofs->map(fn($p) => [
                                                    'url'       => asset('storage/' . $p->proof_photo),
                                                    'method'    => strtoupper($p->payment_method),
                                                    'amount'    => $p->amount,
                                                    'reference' => $p->reference_number,
                                                    'date'      => $p->created_at->format('d/m/Y H:i'),
                                                ])->values()) }}">
                                                <svg class="w-4 h-4 text-primary flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                                Lihat Bukti
                                            </button>
                                            @endif
                                            @if(config('features.delivery'))
                                                @if($transaction->delivery)
                                                <a href="{{ route('transactions.surat-jalan', $transaction->id) }}" target="_blank"
                                                    class="w-full text-left px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low flex items-center gap-2.5 transition">
                                                    <svg class="w-4 h-4 text-on-surface-variant flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m8-11h2l2 3v4h-4m-6 0H3"/></svg>
                                                    Cetak Surat Jalan
                                                </a>
                                                @else
                                                <button type="button"
                                                    class="btn-surat-jalan w-full text-left px-4 py-2.5 text-sm text-on-surface hover:bg-surface-low flex items-center gap-2.5 transition"
                                                    data-url="{{ route('transactions.delivery.store', $transaction->id) }}"
                                                    data-invoice="{{ $transaction->invoice_number }}"
                                                    data-customer="{{ $transaction->customer->name ?? 'Umum' }}"
                                                    data-address="{{ $transaction->customer->address ?? '' }}">
                                                    <svg class="w-4 h-4 text-on-surface-variant flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l2 1m8-11h2l2 3v4h-4m-6 0H3"/></svg>
                                                    Buat Surat Jalan
                                                </button>
                                                @endif
                                            @endif
                                            @if($transaction->remaining_bill > 0)
                                            <div class="border-t border-outline-variant">
                                                <button type="button"
                                                    class="btn-bayar w-full text-left px-4 py-2.5 text-sm text-secondary font-semibold hover:bg-secondary/10 flex items-center gap-2.5 transition"
                                                    data-url="{{ route('laporan.pay', $transaction->id) }}"
                                                    data-invoice="{{ $transaction->invoice_number }}"
                                                    data-customer="{{ $transaction->customer->name ?? 'Umum' }}"
                                                    data-remaining="{{ $transaction->remaining_bill }}">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm0 0V9"/></svg>
                                                    Catat Pembayaran
                                                </button>
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="px-6 py-10 text-center text-on-surface-variant text-sm italic">
                                    Tidak ada transaksi pada rentang tanggal ini.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="px-6 py-4 border-t border-outline-variant">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Modal Surat Jalan -->
    <div id="modal-surat-jalan" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl border border-outline-variant shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-on-surface">Data Pengiriman</h3>
                <button id="btn-close-surat-jalan" type="button" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
            </div>
            <div class="bg-surface-low rounded-lg p-4 mb-5 space-y-1 text-sm">
                <p class="text-on-surface-variant">Invoice: <span id="sj-invoice" class="font-semibold text-on-surface"></span></p>
                <p class="text-on-surface-variant">Pelanggan: <span id="sj-customer" class="font-semibold text-on-surface"></span></p>
            </div>
            <form id="form-surat-jalan" data-url="" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Alamat Pengiriman <span class="text-red-500">*</span></label>
                    <textarea id="sj-address" name="shipping_address" rows="3" required
                        class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm"
                        placeholder="Masukkan alamat lengkap tujuan pengiriman"></textarea>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Nama Supir / Kurir</label>
                    <input id="sj-driver" name="driver_name" type="text"
                        class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm"
                        placeholder="Opsional">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Plat Nomor Kendaraan</label>
                    <input id="sj-plate" name="license_plate" type="text"
                        class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm"
                        placeholder="Contoh: B 1234 ABC">
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-surat-jalan').classList.add('hidden')"
                        class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-primary text-white rounded-lg text-sm font-semibold hover:bg-primary-dark transition disabled:opacity-50">
                        Simpan & Cetak
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Catat Pembayaran -->
    <div id="modal-bayar" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl border border-outline-variant shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-on-surface">Catat Pembayaran Hutang</h3>
                <button id="btn-close-bayar" type="button" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
            </div>
            <div class="bg-surface-low rounded-lg p-4 mb-5 space-y-1 text-sm">
                <p class="text-on-surface-variant">Invoice: <span id="bayar-invoice" class="font-semibold text-on-surface font-mono"></span></p>
                <p class="text-on-surface-variant">Pelanggan: <span id="bayar-customer" class="font-semibold text-on-surface"></span></p>
                <p class="text-on-surface-variant">Sisa Tagihan: <span id="bayar-sisa-text" class="font-mono font-bold text-red-600"></span></p>
            </div>
            <form id="form-bayar" data-url="" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Metode Pembayaran</label>
                    <select id="bayar-method" name="payment_method"
                        class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        <option value="cash">Cash</option>
                        <option value="transfer">Transfer Bank</option>
                        <option value="qris">QRIS</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Jumlah Bayar (Rp)</label>
                    <input id="bayar-amount" name="amount" type="number" min="1" step="1"
                        class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary font-mono text-sm">
                </div>
                <div id="proof-section" class="hidden space-y-3">
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">No. Referensi</label>
                        <input id="bayar-reference" name="reference_number" type="text"
                            class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm"
                            placeholder="Nomor bukti transfer / kode QRIS">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Foto Bukti <span class="text-red-500">*</span></label>
                        <input id="bayar-proof" name="proof_photo" type="file" accept="image/*" capture="environment"
                            class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button"
                        onclick="document.getElementById('modal-bayar').classList.add('hidden')"
                        class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-secondary text-white rounded-lg text-sm font-semibold hover:bg-secondary-dark transition disabled:opacity-50">
                        Simpan Pembayaran
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Info Pelanggan -->
    <div id="modal-info-customer" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl border border-outline-variant shadow-2xl w-full max-w-md mx-4 p-6">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-on-surface">Info Pelanggan</h3>
                <button type="button" id="btn-close-info-customer" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
            </div>
            <div id="customer-info-loading" class="text-center py-8 text-on-surface-variant text-sm">Memuat data...</div>
            <div id="customer-info-content" class="hidden space-y-4">
                <div class="bg-surface-low rounded-lg p-4 space-y-2 text-sm">
                    <p class="text-on-surface-variant">Nama: <span id="ci-name" class="font-semibold text-on-surface"></span></p>
                    <p class="text-on-surface-variant">Telepon: <span id="ci-phone" class="font-semibold text-on-surface"></span></p>
                    <p class="text-on-surface-variant">Alamat: <span id="ci-address" class="font-semibold text-on-surface"></span></p>
                    <div class="flex items-center gap-2 pt-1">
                        <label for="ci-credit-input" class="text-on-surface-variant whitespace-nowrap">Limit Kredit:</label>
                        <input id="ci-credit-input" type="number" min="0" step="1000"
                            class="flex-1 min-w-0 rounded-lg border-outline-variant focus:border-primary focus:ring-primary font-mono text-sm py-1"
                            placeholder="0 = tanpa limit">
                        <button type="button" id="btn-save-credit"
                            class="px-3 py-1.5 bg-primary text-white rounded-lg text-xs font-semibold hover:bg-primary-dark transition disabled:opacity-50">
                            Simpan
                        </button>
                    </div>
                    <p id="ci-credit-status" class="text-xs hidden"></p>
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-primary/10 rounded-lg p-3">
                        <p class="text-xs text-primary font-semibold uppercase">Transaksi</p>
                        <p id="ci-total-trx" class="font-mono text-xl font-bold text-primary mt-1"></p>
                    </div>
                    <div class="bg-secondary/10 rounded-lg p-3">
                        <p class="text-xs text-secondary font-semibold uppercase">Total Belanja</p>
                        <p id="ci-total-spent" class="font-mono text-sm font-bold text-secondary mt-1"></p>
                    </div>
                    <div class="bg-red-50 rounded-lg p-3">
                        <p class="text-xs text-red-600 font-semibold uppercase">Hutang</p>
                        <p id="ci-total-debt" class="font-mono text-sm font-bold text-red-600 mt-1"></p>
                    </div>
                </div>
            </div>
            <div class="flex justify-end mt-5">
                <button type="button" onclick="document.getElementById('modal-info-customer').classList.add('hidden')"
                    class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- Modal Bukti Pembayaran -->
    <div id="modal-bukti" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-xl border border-outline-variant shadow-2xl w-full max-w-md mx-4 p-6 max-h-[85vh] flex flex-col">
            <div class="flex justify-between items-center mb-5">
                <h3 class="text-base font-bold text-on-surface">Bukti Pembayaran <span id="bukti-invoice" class="font-mono text-sm font-semibold text-primary"></span></h3>
                <button type="button" id="btn-close-bukti" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
            </div>
            <div id="bukti-list" class="space-y-4 overflow-y-auto"></div>
            <div class="flex justify-end mt-5">
                <button type="button" onclick="document.getElementById('modal-bukti').classList.add('hidden')"
                    class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('click', function(e) {
            const trigger = e.target.closest('.action-trigger');
            const allMenus = document.querySelectorAll('.action-menu');

            allMenus.forEach(m => {
                if (!trigger || m !== trigger.nextElementSibling) {
                    m.classList.add('hidden');
                }
            });

            if (trigger) {
                trigger.nextElementSibling.classList.toggle('hidden');
            }
        });
    </script>
    @vite('resources/js/laporan.js')
</x-app-layout>
