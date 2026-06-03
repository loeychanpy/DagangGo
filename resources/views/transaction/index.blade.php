<x-app-layout>
<x-slot name="header">
    <h2 class="font-bold text-xl text-on-surface">POS Kasir</h2>
</x-slot>
<div class="p-6">
    <div class="grid grid-cols-12 gap-6">

        <!-- Produk -->
        <div class="col-span-8">
            <div class="grid grid-cols-12 gap-4">

                <!-- Sidebar Kategori -->
                <div class="col-span-2">
                    <div class="space-y-2">
                        <button onclick="filterCategory('')"
                            id="cat-btn-all"
                            class="w-full px-3 py-2.5 rounded-lg text-sm font-semibold bg-primary text-white transition">
                            Semua
                        </button>
                        @foreach($categories as $category)
                        <button onclick="filterCategory({{ $category->id }})"
                            id="cat-btn-{{ $category->id }}"
                            class="w-full px-3 py-2.5 rounded-lg text-sm font-medium bg-surface-container text-on-surface-variant hover:bg-primary hover:text-white transition">
                            {{ $category->name }}
                        </button>
                        @endforeach
                    </div>
                </div>

                <!-- Produk area -->
                <div class="col-span-10">
                    <!-- Search -->
                    <div class="mb-4">
                        <input id="search-product" type="text" placeholder="Cari produk..."
                            class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                    </div>

                    <!-- Grid Produk -->
                    <div id="product-container" class="grid grid-cols-3 gap-3">
                        @foreach($products as $product)
                        <div onclick="addToCart({{ $product->id }})"
                            class="bg-white rounded-xl border border-outline-variant hover:border-primary hover:shadow-md transition cursor-pointer p-4 select-none">
                            <h3 class="font-semibold text-sm text-on-surface leading-tight">{{ $product->name }}</h3>
                            <p class="text-xs text-on-surface-variant mt-0.5">{{ $product->category->name }}</p>
                            <div class="mt-3 flex justify-between items-end">
                                <p class="font-mono font-bold text-primary text-sm">
                                    Rp {{ number_format($product->selling_price, 0, ',', '.') }}
                                </p>
                                <span class="font-mono text-xs text-on-surface-variant bg-surface-low rounded-full px-2 py-0.5">
                                    {{ $product->stock }} {{ $product->unit->short_name }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Keranjang -->
        <div class="col-span-4">
            <!-- Pilih Pelanggan -->
            <div class="bg-white rounded-xl border border-outline-variant shadow-sm p-4 mb-3">
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Pelanggan</label>
                <div class="flex gap-2">
                    <select id="customer-select" class="flex-1 rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                        <option value="">-- Umum (Tanpa Pelanggan) --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->name }}{{ $customer->phone ? ' · '.$customer->phone : '' }}</option>
                        @endforeach
                    </select>
                    <button type="button" id="btn-open-add-customer"
                        class="px-3 py-2 bg-secondary text-white rounded-lg hover:bg-secondary-dark text-sm font-semibold flex-shrink-0 whitespace-nowrap">
                        + Baru
                    </button>
                </div>
            </div>

            <!-- Cart Container -->
            <div id="cart-container" class="bg-white rounded-xl border border-outline-variant shadow-sm p-5 sticky top-5">
                <h2 class="text-base font-bold text-on-surface mb-4">Keranjang</h2>
                @if(count($cart) > 0)
                    @php $total = 0; @endphp
                    @foreach($cart as $item)
                        @php
                            $subtotal = $item['price'] * $item['qty'];
                            $total += $subtotal;
                        @endphp
                        <div class="border-b border-outline-variant py-3">
                            <div class="flex justify-between items-start">
                                <div class="flex-1 min-w-0 pr-2">
                                    <p class="font-semibold text-sm text-on-surface truncate">{{ $item['name'] }}</p>
                                    <div class="flex items-center mt-2 gap-2">
                                        <button onclick="removeFromCart({{ $item['id'] }})"
                                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-bold transition flex items-center justify-center">−</button>
                                        <span class="font-mono font-semibold text-sm w-6 text-center">{{ $item['qty'] }}</span>
                                        <button onclick="addToCart({{ $item['id'] }})"
                                            class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 font-bold transition flex items-center justify-center">+</button>
                                    </div>
                                </div>
                                <p class="font-mono text-sm font-semibold text-on-surface whitespace-nowrap">Rp {{ number_format($subtotal, 0, ',', '.') }}</p>
                            </div>
                        </div>
                    @endforeach

                    <div class="mt-4 pt-2 space-y-2">
                        <div class="flex justify-between text-sm text-on-surface-variant">
                            <span>Subtotal</span>
                            <span id="subtotal-text" class="font-mono">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm text-on-surface-variant">
                            <span>Diskon</span>
                            <input id="discount-input" type="number" value="0"
                                class="w-28 rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-right text-sm font-mono">
                        </div>
                        <div class="flex justify-between font-bold text-base text-on-surface pt-2 border-t border-outline-variant">
                            <span>Total</span>
                            <span id="total-text" class="font-mono">Rp {{ number_format($total, 0, ',', '.') }}</span>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Metode Pembayaran</label>
                        <select id="payment-method" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer</option>
                            <option value="qris">QRIS</option>
                            @if(config('features.kasbon'))
                                <option value="tempo">Tempo</option>
                            @endif
                        </select>
                    </div>

                    @if(config('features.kasbon'))
                    <div id="tempo-date-container" class="mt-4 hidden">
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Tenggat Tempo</label>
                        <input id="due-date" type="date" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
                    </div>
                    @endif

                    <!-- Bukti Transfer/QRIS -->
                    <div id="transfer-proof-container" class="mt-4 hidden space-y-3">
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">No. Referensi</label>
                            <input id="reference-number" type="text" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="Nomor struk / kode transfer">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Foto Bukti Pembayaran</label>
                            <input id="proof-photo" type="file" accept="image/*" capture="environment"
                                class="w-full text-sm text-on-surface-variant file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:bg-primary/10 file:text-primary hover:file:bg-primary/20">
                        </div>
                    </div>

                    <div id="cash-payment-container" class="mt-4">
                        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Jumlah Bayar</label>
                        <input id="pay-amount" type="text" autocomplete="off" min="0"
                            class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary font-mono text-sm"
                            placeholder="0">
                        <p id="cash-error" class="text-red-600 text-xs mt-1 hidden">Jumlah bayar kurang dari total tagihan.</p>
                    </div>

                    <div id="change-amount-container" class="mt-3">
                        <div class="flex justify-between text-sm font-semibold text-on-surface">
                            <span>Kembalian</span>
                            <span id="change-text" class="font-mono">Rp 0</span>
                        </div>
                    </div>

                    <button type="button" onclick="checkout()"
                        class="w-full mt-5 bg-secondary hover:bg-secondary-dark text-white font-bold py-3 rounded-xl transition text-base tracking-wide">
                        Bayar
                    </button>
                @else
                    <div class="text-center py-12">
                        <svg class="w-10 h-10 mx-auto text-outline mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                        </svg>
                        <p class="text-on-surface-variant text-sm">Keranjang kosong</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<script>
    window.FEATURE_KASBON  = {{ config('features.kasbon') ? 'true' : 'false' }};
    window.FEATURE_DELIVERY = {{ config('features.delivery') ? 'true' : 'false' }};
    window.APP_BASE_PATH   = '{{ url('') }}';
    window.INITIAL_CART    = {!! json_encode($cart) !!};
    window.ROUTE_CUSTOMERS_STORE = '{{ route('customers.store') }}';
</script>
</x-app-layout>

<!-- Modal Tambah Pelanggan -->
<div id="modal-add-customer" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-md mx-4 p-6 border border-outline-variant">
        <div class="flex justify-between items-center mb-5">
            <h3 class="text-base font-bold text-on-surface">Tambah Pelanggan Baru</h3>
            <button type="button" id="btn-close-add-customer" class="text-on-surface-variant hover:text-on-surface text-2xl leading-none">&times;</button>
        </div>
        <form id="form-add-customer" class="space-y-4">
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Nama <span class="text-red-500">*</span></label>
                <input id="new-cust-name" type="text" required
                    class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="Nama pelanggan">
            </div>
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">No. Telepon</label>
                <input id="new-cust-phone" type="text"
                    class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="Opsional">
            </div>
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Alamat</label>
                <textarea id="new-cust-address" rows="2"
                    class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm" placeholder="Opsional"></textarea>
            </div>
            <div>
                <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-1">Limit Kredit (Rp)</label>
                <input id="new-cust-credit" type="number" min="0"
                    class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary font-mono text-sm" placeholder="0 = tanpa limit kredit">
            </div>
            <div id="form-add-customer-error" class="hidden text-sm text-red-600 bg-red-50 rounded-lg p-3"></div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-customer').classList.add('hidden')"
                    class="px-4 py-2 bg-surface-container text-on-surface-variant rounded-lg text-sm font-semibold hover:bg-surface-high">
                    Batal
                </button>
                <button type="submit" id="btn-submit-add-customer"
                    class="px-4 py-2 bg-secondary text-white rounded-lg text-sm font-semibold hover:bg-secondary-dark disabled:opacity-50">
                    Simpan Pelanggan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="success-popup" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class="bg-white rounded-2xl p-8 shadow-2xl text-center w-96 border border-outline-variant">
        <div class="w-16 h-16 mx-auto rounded-full bg-secondary/10 flex items-center justify-center text-3xl text-secondary">✓</div>
        <h2 class="text-xl font-bold text-on-surface mt-4">Berhasil</h2>
        <p id="popup-message" class="text-on-surface-variant mt-2 text-sm">Checkout berhasil</p>
        <div class="mt-6 flex flex-col gap-3">
            <a id="btn-invoice" href="#" target="_blank"
                class="inline-flex items-center justify-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-xl hover:bg-red-700 font-semibold text-sm">
                Cetak Invoice PDF
            </a>
            <button onclick="closePopup()"
                class="bg-primary text-white px-6 py-2.5 rounded-xl hover:bg-primary-dark font-semibold text-sm">
                Transaksi Baru
            </button>
        </div>
    </div>
</div>
