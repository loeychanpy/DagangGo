let cartData={};
//Untuk menambahkan produk ke keranjang
window.addToCart = function(productId)
{
    fetch("/transaction/cart/add",
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
        "/transaction/cart/remove",
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
            <h2 class="text-xl font-bold mb-5">
                Keranjang
            </h2>
            <div class="text-center py-10">
                <p class="text-gray-500">
                    Belum ada item
                </p>
            </div>`;
        return;
    }

    let total = Number(0);
    cartContainer.innerHTML=`
        <h2 class="text-xl font-bold mb-5">
            Keranjang
        </h2>
    `;

    Object.values(cart)
    .forEach(item=>{const subtotal = Number(item.price)*Number(item.qty);
    total += subtotal;
        cartContainer.innerHTML+=`
        <div class="border-b py-3">
            <div class="flex justify-between">
                <div>
                    <p class="font-semibold">
                        ${item.name}
                    </p>
                    <div class="flex items-center gap-2 mt-2">
                        <button onclick="removeFromCart(${item.id})"
                        class="w-8 h-8 bg-red-100 rounded">
                        -
                        </button>
                        <span>
                        ${item.qty}
                        </span>
                        <button onclick="addToCart(${item.id})"
                        class="w-8 h-8 bg-green-100 rounded">
                        +
                        </button>
                    </div>
                </div>
                <div>
                    Rp
                    ${subtotal.toLocaleString()}
                </div>
            </div>
        </div>
        `;
    });

    cartContainer.innerHTML +=`
    <div class="mt-5 border-t pt-4">
        <div class="flex justify-between mb-2">
            <span>Subtotal</span>
            <span id="subtotal-text">
                Rp ${total.toLocaleString('id-ID')}
            </span>
        </div>
        <div class="flex justify-between mb-2">
            <span>Diskon</span>
            <input id="discount-input" type="number" value="${currentDiscount}"
            class="w-24 rounded border-gray-300 text-right">
        </div>
        <div class="flex justify-between font-bold">
            <span>Total</span>
            <span id="total-text">
                Rp ${total.toLocaleString('id-ID')}
            </span>
        </div>
    </div>
    <div class="mt-4">
    <label class="block text-sm mb-2 font-semibold">
        Metode Pembayaran
    </label>
        <select id="payment-method" class="w-full rounded-lg border-gray-300">
            <option value="cash" ${currentPaymentMethod==='cash'?'selected':''}>
                Cash
            </option>
            <option value="transfer" ${currentPaymentMethod==='transfer'?'selected':''}>
                Transfer
            </option>
            <option value="qris" ${currentPaymentMethod==='qris'?'selected':''}>
                QRIS
            </option>
            <option value="tempo" ${currentPaymentMethod==='tempo'?'selected':''}>
                Tempo
            </option>
        </select>
    </div>
    <div id="tempo-date-container" class="mt-4 hidden">
        <label class="block text-sm mb-2 font-semibold">
            Tenggat Tempo
        </label>
        <input id="due-date" value="${currentDueDate}" type="date" class="w-full rounded-lg border-gray-300">
    </div>
    <div id="cash-payment-container" class="mt-4">
        <label class="block text-sm mb-2 font-semibold">
            Jumlah Bayar
        </label>
        <input id="pay-amount" type="text" autocomplete="off" min="0" value="${currentPayAmount}" class="w-full rounded-lg border-gray-300" placeholder="Jumlah bayar">
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
    <button onclick="checkout()" class="w-full mt-5 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700">
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

document.getElementById('search-product').addEventListener('keyup',function(){
    searchProduct(
        this.value
    );
});

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
}

window.searchProduct=
function(keyword)
{
    fetch(
        `/transaction?search=${keyword}&category=${selectedCategory}`,
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

    products.forEach(product=>{
        html +=`
        <div
        onclick="addToCart(${product.id})"
        class="bg-white rounded-xl shadow-md p-5 hover:shadow-xl cursor-pointer">
            <h3
            class="font-bold text-lg">
            ${product.name}
            </h3>
            <p
            class="text-sm text-gray-500">
            ${product.category.name}
            </p>
            <div class="mt-3">
                <p
                class="font-bold text-blue-600 mt-2">
                Rp
                ${Number(product.selling_price).toLocaleString()}
                </p>
                <p
                class="text-sm text-gray-500">
                    Stok:
                        ${product.stock}
                        ${product.unit.short_name}
                </p>
            </div>
        </div>
        `;
    });
    container.innerHTML = html;
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

    if(
        e.target.value === 'tempo'
    )
    {
        tempoContainer.classList.remove('hidden');
    }
    
    else
    {
        tempoContainer.classList.add('hidden');
    }

    if(e.target.value==='cash')
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
});

//Untuk melakukan checkout transaksi
window.checkout =
function()
{
    const paymentMethod = document.querySelector('#payment-method').value;
    const discount = parseFloat(document.querySelector('#discount-input').value) || 0;
    const dueDate = document.querySelector('#due-date')?.value;
    const payAmount = Number(document.querySelector('#pay-amount').value.replace(/\./g,'').replace(/,/g,'')) || 0;

    fetch('/transaction/checkout',
        {
            method:'POST',
            headers:{
                'Content-Type':
                'application/json',
                'X-CSRF-TOKEN':
                document.querySelector('meta[name="csrf-token"]').content
            },
            body:JSON.stringify({
                payment_method:paymentMethod,
                discount:discount,
                due_date:dueDate,
                pay_amount:payAmount
            })
        }
    )
    .then(
        response=>response.json()
    )
    .then(data=>{showSuccessPopup(
        data.message
        );
    });
}
//Untuk menampilkan popup sukses setelah checkout berhasil
window.showSuccessPopup =
function(message)
{
    document.querySelector(
        '#popup-message'
    ).innerText =
    message;

    document.querySelector(
        '#success-popup'
    ).classList.remove(
        'hidden'
    );

    document.querySelector(
        '#success-popup'
    ).classList.add(
        'flex'
    );
}



window.closePopup =
function()
{
    window.location.href =
    '/transaction';
}