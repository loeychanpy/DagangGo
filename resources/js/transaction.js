let cartData = window.INITIAL_CART || {};
const basePath = window.APP_BASE_PATH || '';

//Untuk menambahkan produk ke keranjang
window.addToCart = function(productId)
{
    fetch(basePath + "/transaction/cart/add",
        {
            method:'POST',
            headers:{
                'Content-Type':
                'application/json',
                'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]').content
            },
            body:JSON.stringify({
                product_id:productId
            })
        }
    )
    .then(response=>response.json())
    .then(data=>{
        renderCart(data.cart);
    });
}

//Untuk mengurangi jumlah produk di keranjang atau menghapus produk jika jumlahnya menjadi 0
window.removeFromCart = function(productId)
{
    fetch(
        basePath + "/transaction/cart/remove",
        {
            method:'POST',
            headers:{
                'Content-Type':
                'application/json',
                'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]').content
            },
            body:JSON.stringify({
                product_id:productId
            })
        }
    )
    .then(response=>response.json())
    .then(data=>{renderCart(data.cart);
    });
}

//fungsi untuk merender ulang isi keranjang di tampilan web
function renderCart(cart)
{
    const currentDiscount = document.querySelector('#discount-input')?.value || 0;
    const currentPayAmount = document.querySelector('#pay-amount')?.value || '';
    const currentPaymentMethod = document.querySelector('#payment-method')?.value || 'cash';
    const currentDueDate = document.querySelector('#due-date')?.value || '';
    cartData=cart;
    const cartContainer = document.querySelector('#cart-container');
    //Kalau tidak ada item
    if(Object.keys(cart).length === 0){
        cartContainer.innerHTML = `
            <h2 class="text-base font-bold text-on-surface mb-4">Keranjang</h2>
            <div class="text-center py-12">
                <p class="text-on-surface-variant text-sm">Keranjang kosong</p>
            </div>`;
        return;
    }

    const featureKasbon = window.FEATURE_KASBON === true || window.FEATURE_KASBON === 'true';
    if (!featureKasbon && currentPaymentMethod === 'tempo') {
        currentPaymentMethod = 'cash';
    }

    let total = Number(0);
    cartContainer.innerHTML = `<h2 class="text-base font-bold text-on-surface mb-4">Keranjang</h2>`;

    Object.values(cart).forEach(item => {
        const subtotal = Number(item.price) * Number(item.qty);
        total += subtotal;
        cartContainer.innerHTML += `
        <div class="border-b border-outline-variant py-3">
            <div class="flex justify-between items-start">
                <div class="flex-1 min-w-0 pr-2">
                    <p class="font-semibold text-sm text-on-surface truncate">${item.name}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <button onclick="removeFromCart(${item.id})"
                            class="w-8 h-8 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 font-bold transition flex items-center justify-center">−</button>
                        <span class="font-mono font-semibold text-sm w-6 text-center">${item.qty}</span>
                        <button onclick="addToCart(${item.id})"
                            class="w-8 h-8 rounded-lg bg-primary/10 text-primary hover:bg-primary/20 font-bold transition flex items-center justify-center">+</button>
                    </div>
                </div>
                <p class="font-mono text-sm font-semibold text-on-surface whitespace-nowrap">Rp ${subtotal.toLocaleString('id-ID')}</p>
            </div>
        </div>`;
    });

    cartContainer.innerHTML += `
    <div class="mt-4 pt-2 space-y-2">
        <div class="flex justify-between text-sm text-on-surface-variant">
            <span>Subtotal</span>
            <span id="subtotal-text" class="font-mono">Rp ${total.toLocaleString('id-ID')}</span>
        </div>
        <div class="flex justify-between items-center text-sm text-on-surface-variant">
            <span>Diskon</span>
            <input id="discount-input" type="number" value="${currentDiscount}"
                class="w-28 rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-right text-sm font-mono">
        </div>
        <div class="flex justify-between font-bold text-base text-on-surface pt-2 border-t border-outline-variant">
            <span>Total</span>
            <span id="total-text" class="font-mono">Rp ${total.toLocaleString('id-ID')}</span>
        </div>
    </div>
    <div class="mt-4">
        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Metode Pembayaran</label>
        <select id="payment-method" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
            <option value="cash" ${currentPaymentMethod==='cash'?'selected':''}>Cash</option>
            <option value="transfer" ${currentPaymentMethod==='transfer'?'selected':''}>Transfer</option>
            <option value="qris" ${currentPaymentMethod==='qris'?'selected':''}>QRIS</option>
            ${featureKasbon ? `<option value="tempo" ${currentPaymentMethod==='tempo'?'selected':''}>Tempo</option>` : ''}
        </select>
    </div>
    <div id="tempo-date-container" class="mt-4 hidden">
        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Tenggat Tempo</label>
        <input id="due-date" value="${currentDueDate}" type="date" class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary text-sm">
    </div>
    <div id="cash-payment-container" class="mt-4">
        <label class="block text-xs font-semibold text-on-surface-variant uppercase tracking-wider mb-2">Jumlah Bayar</label>
        <input id="pay-amount" type="text" autocomplete="off" min="0" value="${currentPayAmount}"
            class="w-full rounded-lg border-outline-variant focus:border-primary focus:ring-primary font-mono text-sm" placeholder="0">
        <p id="cash-error" class="text-red-600 text-xs mt-1 hidden">Jumlah bayar kurang dari total tagihan.</p>
    </div>
    <div id="change-amount-container" class="mt-3">
        <div class="flex justify-between text-sm font-semibold text-on-surface">
            <span>Kembalian</span>
            <span id="change-text" class="font-mono">Rp 0</span>
        </div>
    </div>
    <button onclick="checkout()" class="w-full mt-5 bg-secondary hover:bg-secondary-dark text-white font-bold py-3 rounded-xl transition text-base tracking-wide">
        Bayar
    </button>
    `;
    // Restore visibility payment section
    const tempoContainer =
    document.querySelector(
        '#tempo-date-container'
    );

    const cashContainer =
    document.querySelector(
        '#cash-payment-container'
    );

    const changeContainer =
    document.querySelector(
        '#change-amount-container'
    );
    if(currentPaymentMethod==='tempo')
    {
        tempoContainer.classList.remove(
            'hidden'
        );
    }
    else
    {
        tempoContainer.classList.add(
            'hidden'
        );
    }
    if(currentPaymentMethod==='cash')
    {
        cashContainer.classList.remove(
            'hidden'
        );

        changeContainer.classList.remove(
            'hidden'
        );
    }
    else
    {
        cashContainer.classList.add(
            'hidden'
        );

        changeContainer.classList.add(
            'hidden'
        );
    }
    // Hitung ulang total & change
    calculateTotal();
    calculateChange();
}

//Event listener untuk menghitung ulang total ketika diskon diubah
document.addEventListener('input',function(e){
    if(e.target.id === 'discount-input')        {
        calculateTotal();
        calculateChange();
    }
    if(e.target.id === 'pay-amount')
    {
        let value =
        e.target.value
        .replace(/\D/g,'');

        e.target.value =
        Number(value)
        .toLocaleString(
            'id-ID'
        );

        calculateChange();
    }
});

const searchInput = document.getElementById('search-product');
if (searchInput) {
    searchInput.addEventListener('keyup',function(){
        searchProduct(
            this.value
        );
    });
}

//Untuk menghitung ulang total ketika diskon diubah
function calculateTotal()
{
    let subtotal = 0;
    Object.values(cartData).forEach(item=>{
        subtotal += Number(item.price)*Number(item.qty);
    });
    const discount = Number(document.querySelector('#discount-input').value) || 0;
    const total = Math.max(subtotal-discount,0);
    document.querySelector('#subtotal-text').innerText ='Rp ' +subtotal.toLocaleString('id-ID');
    document.querySelector('#total-text').innerText ='Rp ' + total.toLocaleString('id-ID');
}
//Untuk menghitung kembalian ketika jumlah bayar diubah
function calculateChange()
{
    let subtotal = 0;
    Object.values(cartData).forEach(item=>{
        subtotal += Number(item.price)*Number(item.qty);
    });
    const discount = Number(document.querySelector('#discount-input' ).value) || 0;
    const finalTotal = Math.max(subtotal-discount,0);
    const payAmount =Number(document.querySelector('#pay-amount')?.value.replace(/\./g,'').replace(/,/g,'')) || 0;
    const change = Math.max(payAmount-finalTotal,0);
    document.querySelector('#change-text').innerText ='Rp ' + change.toLocaleString('id-ID');

    const cashError  = document.querySelector('#cash-error');
    const payMethod  = document.querySelector('#payment-method')?.value;
    if (cashError) {
        if (payMethod === 'cash' && payAmount > 0 && payAmount < finalTotal) {
            cashError.classList.remove('hidden');
        } else {
            cashError.classList.add('hidden');
        }
    }
}

window.searchProduct=
function(keyword)
{
    fetch(
        basePath + `/transaction?search=${keyword}&category=${selectedCategory}`,
        {            
            headers:{
                'X-Requested-With':
                'XMLHttpRequest'
            }
        }
    )
    .then(
        response=>response.json()
    )
    .then(
        data=>{
            renderProducts(
                data.products
            );
        }
    );
}

//Untuk merender ulang daftar produk berdasarkan hasil pencarian atau filter kategori
function renderProducts(products)
{
    let html='';
    const container = document.querySelector(
        '#product-container'
    );

    products.forEach(product => {
        html += `
        <div onclick="addToCart(${product.id})"
            class="bg-white rounded-xl border border-outline-variant hover:border-primary hover:shadow-md transition cursor-pointer p-4 select-none">
            <h3 class="font-semibold text-sm text-on-surface leading-tight">${product.name}</h3>
            <p class="text-xs text-on-surface-variant mt-0.5">${product.category.name}</p>
            <div class="mt-3 flex justify-between items-end">
                <p class="font-mono font-bold text-primary text-sm">Rp ${Number(product.selling_price).toLocaleString('id-ID')}</p>
                <span class="font-mono text-xs text-on-surface-variant bg-surface-low rounded-full px-2 py-0.5">${product.stock} ${product.unit.short_name}</span>
            </div>
        </div>`;
    });
     if(!container){
        return
    }
    else{
        container.innerHTML = html;
    }
}

//Untuk memfilter produk berdasarkan kategori
let selectedCategory='';

window.filterCategory=
function(categoryId)
{
    selectedCategory=categoryId;

    searchProduct(
        document
        .getElementById(
        'search-product'
        )
        .value
    );
}

//Untuk menampilkan atau menyembunyikan input tanggal tempo berdasarkan metode pembayaran
document.addEventListener('change',function(e){
    if(e.target.id !== 'payment-method')
    {
        return;
    }
    const tempoContainer =
    document.querySelector(
        '#tempo-date-container'
    );

    const cashContainer =
    document.querySelector(
        '#cash-payment-container'
    );

    const changeContainer =
    document.querySelector(
        '#change-amount-container'
    );

    const proofContainer = document.querySelector('#transfer-proof-container');

    if (e.target.value === 'tempo') {
        tempoContainer.classList.remove('hidden');
    } else {
        tempoContainer.classList.add('hidden');
    }

    if (e.target.value === 'cash') {
        cashContainer.classList.remove('hidden');
        changeContainer.classList.remove('hidden');
    } else {
        cashContainer.classList.add('hidden');
        changeContainer.classList.add('hidden');
    }

    if (proofContainer) {
        if (e.target.value === 'transfer' || e.target.value === 'qris') {
            proofContainer.classList.remove('hidden');
        } else {
            proofContainer.classList.add('hidden');
        }
    }
});

//Untuk melakukan checkout transaksi
window.checkout = function() {
    const paymentMethod = document.querySelector('#payment-method').value;
    const discount = parseFloat(document.querySelector('#discount-input').value) || 0;
    const dueDate = document.querySelector('#due-date')?.value;
    const payAmount = Number(document.querySelector('#pay-amount').value.replace(/\./g,'').replace(/,/g,'')) || 0;

    if (paymentMethod === 'cash') {
        let subtotal = 0;
        Object.values(cartData).forEach(item => {
            subtotal += Number(item.price) * Number(item.qty);
        });
        const finalTotal = Math.max(subtotal - discount, 0);
        if (payAmount < finalTotal) {
            const cashError = document.querySelector('#cash-error');
            if (cashError) cashError.classList.remove('hidden');
            document.querySelector('#pay-amount')?.focus();
            return;
        }
    }
    const proofPhotoInput = document.querySelector('#proof-photo');
    const referenceNumber = document.querySelector('#reference-number')?.value || '';

    const customerId = document.querySelector('#customer-select')?.value;

    const formData = new FormData();
    formData.append('payment_method', paymentMethod);
    formData.append('discount', discount);
    formData.append('pay_amount', payAmount);
    if (customerId) formData.append('customer_id', customerId);
    if (dueDate) formData.append('due_date', dueDate);
    if (referenceNumber) formData.append('reference_number', referenceNumber);
    if (proofPhotoInput && proofPhotoInput.files[0]) {
        formData.append('proof_photo', proofPhotoInput.files[0]);
    }
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

    fetch(basePath + '/transaction/checkout', {
        method: 'POST',
        body: formData,
    })
    .then(response => response.json())
    .then(data => {
        if (data.transaction_id) {
            showSuccessPopup(data.message, data.transaction_id);
        } else {
            showSuccessPopup(data.message || 'Terjadi kesalahan saat checkout.', null);
        }
    })
    .catch(() => { showSuccessPopup('Terjadi kesalahan saat checkout.', null); });
}
//Untuk menampilkan popup sukses setelah checkout berhasil
window.showSuccessPopup = function(message, transactionId) {
    document.querySelector('#popup-message').innerText = message;

    const btnInvoice = document.querySelector('#btn-invoice');
    if (btnInvoice) {
        if (transactionId) {
            btnInvoice.href = basePath + '/transactions/' + transactionId + '/invoice';
            btnInvoice.classList.remove('hidden');
        } else {
            btnInvoice.classList.add('hidden');
        }
    }

    const popup = document.querySelector('#success-popup');
    popup.classList.remove('hidden');
    popup.classList.add('flex');
}



window.closePopup =
function()
{
    window.location.href =
    basePath + '/transaction';
}

// --- Modal Tambah Pelanggan Baru ---
document.addEventListener('DOMContentLoaded', function () {
    const modalAddCust  = document.getElementById('modal-add-customer');
    const formAddCust   = document.getElementById('form-add-customer');
    const btnOpen       = document.getElementById('btn-open-add-customer');
    const btnClose      = document.getElementById('btn-close-add-customer');
    const errorBox      = document.getElementById('form-add-customer-error');

    if (!modalAddCust) return;

    btnOpen.addEventListener('click', () => {
        formAddCust.reset();
        errorBox.classList.add('hidden');
        modalAddCust.classList.remove('hidden');
    });

    btnClose.addEventListener('click', () => modalAddCust.classList.add('hidden'));
    modalAddCust.addEventListener('click', e => {
        if (e.target === modalAddCust) modalAddCust.classList.add('hidden');
    });

    formAddCust.addEventListener('submit', async function (e) {
        e.preventDefault();
        const submitBtn = document.getElementById('btn-submit-add-customer');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Menyimpan...';
        errorBox.classList.add('hidden');

        try {
            const res = await fetch(window.ROUTE_CUSTOMERS_STORE, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    name:         document.getElementById('new-cust-name').value,
                    phone:        document.getElementById('new-cust-phone').value,
                    address:      document.getElementById('new-cust-address').value,
                    credit_limit: document.getElementById('new-cust-credit').value || 0,
                }),
            });

            const data = await res.json();
            if (!res.ok) {
                const msgs = data.errors
                    ? Object.values(data.errors).flat().join(' ')
                    : (data.message || 'Gagal menyimpan pelanggan.');
                errorBox.textContent = msgs;
                errorBox.classList.remove('hidden');
                return;
            }

            const c = data.customer;
            const select = document.getElementById('customer-select');
            const label  = c.name + (c.phone ? ' · ' + c.phone : '');
            const option = new Option(label, c.id, true, true);
            select.add(option);

            modalAddCust.classList.add('hidden');
        } catch (err) {
            errorBox.textContent = 'Terjadi kesalahan. Coba lagi.';
            errorBox.classList.remove('hidden');
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Pelanggan';
        }
    });
});