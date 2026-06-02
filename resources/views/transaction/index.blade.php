<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-2xl text-gray-800">
        POS Kasir
    </h2>
</x-slot>
<div class="p-6 px-6">
    <div class="grid grid-cols-12 gap-6">
        <!-- Produk -->
        <div class="col-span-8">
            <!-- Kategori -->
            <div class="grid grid-cols-12 gap-4">
                <div class="col-span-2">
                    <div class="space-y-2">
                        <button onclick="filterCategory('')"
                        class="w-full p-3 bg-blue-500 text-white rounded-lg">
                            All
                        </button>
                        @foreach($categories as $category)
                        <button onclick="filterCategory({{$category->id}})"
                        class="w-full p-3 bg-gray-100 rounded-lg hover:bg-blue-100">
                            {{$category->name}}
                        </button>
                        @endforeach
                    </div>
                </div>
                <!-- Produk area -->
                <div class="col-span-10">
                    <!-- Search -->
                    <div class="mb-5">
                        <input id="search-product" type="text" placeholder="Cari produk..."
                        class="w-full rounded-lg border-gray-300">
                    </div>

                    <!-- Produk -->
                    <div id="product-container"  
                    class="grid grid-cols-3 gap-4">
                        @foreach($products as $product)
                        <div
                        onclick="addToCart({{$product->id}})"
                        class="bg-white rounded-xl shadow-md p-5 hover:shadow-xl transition cursor-pointer"
                        >
                            <h3 class="font-bold text-lg">
                                {{ $product->name }}
                            </h3>
                            <p class="text-sm text-gray-500">
                                {{ $product->category->name }}
                            </p>
                            <div class="mt-3">
                                <p class="font-bold text-blue-600">
                                    Rp {{ number_format(
                                        $product->selling_price,
                                        0,
                                        ',',
                                        '.'
                                    ) }}
                                </p>
                                <p class="text-sm text-gray-500">
                                    Stok:
                                    {{ $product->stock }}
                                    {{ $product->unit->short_name }}
                                </p>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>  
        <!-- Keranjang -->
        <div class="col-span-4">
            <div id="cart-container" 
            class="bg-white rounded-xl shadow-md p-5 sticky top-5">
                <h2 class="text-xl font-bold mb-5">
                    Keranjang
                </h2>
                @if(count($cart)>0)
                    @php
                        $total=0;
                    @endphp
                    @foreach($cart as $item)
                        @php
                        $subtotal=
                        $item['price']
                        *
                        $item['qty'];
                        $total+=$subtotal;
                        @endphp
                        <div class="border-b py-3">
                            <div class="flex justify-between">
                                <div>
                                    <p class="font-semibold">
                                        {{ $item['name'] }}
                                    </p>
                                    <div class="flex items-center mt-2 gap-2">
                                    <!-- Tombol minus -->
                                        <button onclick="removeFromCart({{$item['id']}})"
                                        class="w-8 h-8 bg-red-100 rounded"
                                        >
                                        -
                                        </button>
                                        <span>
                                        {{$item['qty']}}
                                        </span>
                                        <button
                                        onclick="addToCart({{$item['id']}})"
                                        class="w-8 h-8 bg-green-100 rounded"
                                        >
                                        +
                                        </button>
                                    </div>
                                </div>
                                <div>
                                    Rp {{ number_format($subtotal,0,',','.') }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                    <div class="mt-5 border-t pt-4">
                        <div class="flex justify-between mb-2">
                            <span>Subtotal</span>
                            <span id="subtotal-text">
                                Rp {{number_format($total,0,',','.')}}
                            </span>
                        </div>
                        <div class="flex justify-between mb-2">
                            <span>Diskon</span>
                            <input id="discount-input" type="number"value="0"
                            class="w-24 rounded border-gray-300 text-right">
                        </div>
                        <div class="border-t mt-3 pt-3">
                            <div class="flex justify-between font-bold text-lg">
                                <span>Total</span>
                                <span id="total-text">
                                    Rp {{number_format($total,0,',','.')}}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <label class="block text-sm mb-2 font-semibold">
                            Metode Pembayaran
                        </label>
                        <select id="payment-method" class="w-full rounded-lg border-gray-300">
                            <option value="cash">
                                Cash
                            </option>
                            <option value="transfer">
                                Transfer
                            </option>
                            <option value="qris">
                                QRIS
                            </option>
                            <option value="tempo">
                                Tempo
                            </option>
                        </select>
                    </div>
                    <div id="tempo-date-container" class="mt-4 hidden">
                        <label class="block text-sm mb-2 font-semibold">
                            Tenggat Tempo
                        </label>
                        <input id="due-date" type="date" class="w-full rounded-lg border-gray-300">
                    </div>
                    <div id="cash-payment-container" class="mt-4">
                        <label class="block text-sm mb-2 font-semibold">
                            Jumlah Bayar
                        </label>
                        <input id="pay-amount" type="text" autocomplete="off" min="0" class="w-full rounded-lg border-gray-300" placeholder="Jumlah bayar">
                    </div>
                    <div id="change-amount-container" class="mt-4">
                        <div class="flex justify-between font-bold text-lg">
                            <span>
                                Kembalian
                            </span>
                            <span id="change-text">
                                Rp 0
                            </span>
                        </div>
                    </div>
                    <button type="button" onclick="checkout()" class="w-full mt-5 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
                        Bayar
                    </button>
                @else
                    <div class="text-center py-10">
                        <p class="text-gray-500">
                            Belum ada item
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
</x-app-layout>
<div id="success-popup" class="fixed inset-0 bg-black/40 hidden items-center justify-center z-50">
    <div class=" bg-white rounded-2xl p-8 shadow-2xl text-center w-96">
        <div class=" w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center text-3xl text-green-600">
            ✓
        </div>
        <h2 class="text-2xl font-bold mt-4">
            Berhasil
        </h2>
        <p id="popup-message" class=" text-gray-500 mt-2 ">
            Checkout berhasil
        </p>
        <button onclick="closePopup()" class=" mt-6 bg-blue-600 text-white px-6 py-2 rounded-lg hover:bg-blue-700">
            OK
        </button>
    </div>
</div>