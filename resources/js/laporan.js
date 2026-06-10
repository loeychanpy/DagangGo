document.addEventListener('DOMContentLoaded', function () {
    // --- Modal Surat Jalan ---
    const modalSJ     = document.getElementById('modal-surat-jalan');
    const formSJ      = document.getElementById('form-surat-jalan');
    const btnCloseSJ  = document.getElementById('btn-close-surat-jalan');

    if (modalSJ) {
        document.querySelectorAll('.btn-surat-jalan').forEach(btn => {
            btn.addEventListener('click', function () {
                formSJ.dataset.url            = this.dataset.url;
                document.getElementById('sj-invoice').textContent  = this.dataset.invoice;
                document.getElementById('sj-customer').textContent = this.dataset.customer;
                document.getElementById('sj-address').value        = this.dataset.address || '';
                document.getElementById('sj-driver').value         = '';
                document.getElementById('sj-plate').value          = '';
                modalSJ.classList.remove('hidden');
            });
        });

        btnCloseSJ.addEventListener('click', () => modalSJ.classList.add('hidden'));
        modalSJ.addEventListener('click', e => { if (e.target === modalSJ) modalSJ.classList.add('hidden'); });

        formSJ.addEventListener('submit', async function (e) {
            e.preventDefault();
            const submitBtn = formSJ.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = 'Menyimpan...';

            try {
                const res = await fetch(formSJ.dataset.url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        shipping_address: document.getElementById('sj-address').value,
                        driver_name:      document.getElementById('sj-driver').value,
                        license_plate:    document.getElementById('sj-plate').value,
                    }),
                });

                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan data pengiriman.');

                modalSJ.classList.add('hidden');
                window.open(data.print_url, '_blank');
            } catch (err) {
                alert('Error: ' + err.message);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Simpan & Cetak';
            }
        });
    }

    // --- Modal Catat Pembayaran ---
    const modal         = document.getElementById('modal-bayar');
    const form          = document.getElementById('form-bayar');
    const proofSection  = document.getElementById('proof-section');
    const methodSelect  = document.getElementById('bayar-method');
    const btnClose      = document.getElementById('btn-close-bayar');

    if (!modal) return;

    // Buka modal
    document.querySelectorAll('.btn-bayar').forEach(btn => {
        btn.addEventListener('click', function () {
            form.dataset.url = this.dataset.url;
            document.getElementById('bayar-invoice').textContent    = this.dataset.invoice;
            document.getElementById('bayar-customer').textContent   = this.dataset.customer;
            document.getElementById('bayar-sisa-text').textContent  = formatRp(this.dataset.remaining);
            document.getElementById('bayar-amount').value           = this.dataset.remaining;
            document.getElementById('bayar-amount').max             = this.dataset.remaining;
            methodSelect.value = 'cash';
            proofSection.classList.add('hidden');
            document.getElementById('bayar-proof').required = false;
            document.getElementById('bayar-proof').value = '';
            document.getElementById('bayar-reference').value = '';
            modal.classList.remove('hidden');
        });
    });

    // Toggle bukti foto berdasarkan metode bayar
    methodSelect.addEventListener('change', function () {
        const needsProof = ['transfer', 'qris'].includes(this.value);
        proofSection.classList.toggle('hidden', !needsProof);
        document.getElementById('bayar-proof').required = needsProof;
    });

    // Tutup modal
    btnClose.addEventListener('click', () => modal.classList.add('hidden'));
    modal.addEventListener('click', e => { if (e.target === modal) modal.classList.add('hidden'); });

    // Submit via AJAX
    form.addEventListener('submit', async function (e) {
        e.preventDefault();
        const submitBtn = form.querySelector('button[type="submit"]');
        submitBtn.disabled = true;
        submitBtn.textContent = 'Memproses...';

        const formData = new FormData(form);

        try {
            const res = await fetch(form.dataset.url, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                body: formData,
            });

            const data = await res.json();
            if (!res.ok) throw new Error(data.message || 'Gagal menyimpan pembayaran.');

            const trx = data.transaction;
            const row = document.querySelector(`tr[data-id="${trx.id}"]`);

            if (row) {
                row.querySelector('.cell-status').innerHTML   = statusBadge(trx.status);
                row.querySelector('.cell-remaining').textContent = 'Rp ' + formatRp(trx.remaining_bill);

                if (trx.remaining_bill <= 0) {
                    const bayarBtn = row.querySelector('.btn-bayar');
                    if (bayarBtn) bayarBtn.remove();
                } else {
                    const bayarBtn = row.querySelector('.btn-bayar');
                    if (bayarBtn) {
                        bayarBtn.dataset.remaining = trx.remaining_bill;
                    }
                }
            }

            modal.classList.add('hidden');
            alert('Pembayaran berhasil dicatat!');
        } catch (err) {
            alert('Error: ' + err.message);
        } finally {
            submitBtn.disabled = false;
            submitBtn.textContent = 'Simpan Pembayaran';
        }
    });

    // --- Modal Info Pelanggan ---
    const modalInfo    = document.getElementById('modal-info-customer');
    const btnCloseInfo = document.getElementById('btn-close-info-customer');

    if (modalInfo) {
        let currentCustomer = null; // { url, name, phone, address }
        const creditInput  = document.getElementById('ci-credit-input');
        const creditStatus = document.getElementById('ci-credit-status');
        const btnSaveCredit = document.getElementById('btn-save-credit');

        document.querySelectorAll('.btn-info-customer').forEach(btn => {
            btn.addEventListener('click', async function () {
                const url  = this.dataset.url;

                // Reset state
                document.getElementById('customer-info-loading').classList.remove('hidden');
                document.getElementById('customer-info-content').classList.add('hidden');
                creditStatus.classList.add('hidden');
                modalInfo.classList.remove('hidden');

                try {
                    const res  = await fetch(url, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memuat data.');

                    const c = data.customer;
                    const s = data.stats;

                    currentCustomer = { url, name: c.name, phone: c.phone, address: c.address };

                    document.getElementById('ci-name').textContent    = c.name;
                    document.getElementById('ci-phone').textContent   = c.phone || '-';
                    document.getElementById('ci-address').textContent = c.address || '-';
                    creditInput.value = Math.round(parseFloat(c.credit_limit) || 0);

                    document.getElementById('ci-total-trx').textContent   = s.total_transactions;
                    document.getElementById('ci-total-spent').textContent = 'Rp ' + formatRp(s.total_spent);
                    document.getElementById('ci-total-debt').textContent  = 'Rp ' + formatRp(s.total_debt);

                    document.getElementById('customer-info-loading').classList.add('hidden');
                    document.getElementById('customer-info-content').classList.remove('hidden');
                } catch (err) {
                    document.getElementById('customer-info-loading').textContent = 'Gagal memuat: ' + err.message;
                }
            });
        });

        btnSaveCredit.addEventListener('click', async function () {
            if (!currentCustomer) return;

            btnSaveCredit.disabled = true;
            btnSaveCredit.textContent = '...';
            creditStatus.classList.add('hidden');

            try {
                const res = await fetch(currentCustomer.url, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify({
                        name:         currentCustomer.name,
                        phone:        currentCustomer.phone,
                        address:      currentCustomer.address,
                        credit_limit: parseFloat(creditInput.value) || 0,
                    }),
                });
                const data = await res.json();
                if (!res.ok) throw new Error(data.message || 'Gagal menyimpan limit.');

                creditStatus.textContent = 'Limit kredit tersimpan.';
                creditStatus.className = 'text-xs text-secondary';
            } catch (err) {
                creditStatus.textContent = 'Gagal: ' + err.message;
                creditStatus.className = 'text-xs text-red-600';
            } finally {
                btnSaveCredit.disabled = false;
                btnSaveCredit.textContent = 'Simpan';
            }
        });

        btnCloseInfo.addEventListener('click', () => modalInfo.classList.add('hidden'));
        modalInfo.addEventListener('click', e => { if (e.target === modalInfo) modalInfo.classList.add('hidden'); });
    }

    // --- Modal Bukti Pembayaran ---
    const modalBukti    = document.getElementById('modal-bukti');
    const btnCloseBukti = document.getElementById('btn-close-bukti');
    const buktiList     = document.getElementById('bukti-list');

    if (modalBukti) {
        document.querySelectorAll('.btn-lihat-bukti').forEach(btn => {
            btn.addEventListener('click', function () {
                document.getElementById('bukti-invoice').textContent = this.dataset.invoice;

                let proofs = [];
                try { proofs = JSON.parse(this.dataset.proofs); } catch (e) { proofs = []; }

                buktiList.innerHTML = proofs.map(p => `
                    <div class="border border-outline-variant rounded-lg overflow-hidden">
                        <a href="${p.url}" target="_blank" class="block bg-surface-low">
                            <img src="${p.url}" alt="Bukti ${p.method}" class="w-full max-h-72 object-contain mx-auto">
                        </a>
                        <div class="p-3 text-xs space-y-0.5 bg-surface-low border-t border-outline-variant">
                            <p class="text-on-surface-variant">Metode: <span class="font-semibold text-on-surface">${p.method}</span></p>
                            <p class="text-on-surface-variant">Nominal: <span class="font-mono font-semibold text-on-surface">Rp ${formatRp(p.amount)}</span></p>
                            ${p.reference ? `<p class="text-on-surface-variant">No. Referensi: <span class="font-semibold text-on-surface">${p.reference}</span></p>` : ''}
                            <p class="text-on-surface-variant">Tanggal: <span class="font-semibold text-on-surface">${p.date}</span></p>
                        </div>
                    </div>
                `).join('');

                modalBukti.classList.remove('hidden');
            });
        });

        btnCloseBukti.addEventListener('click', () => modalBukti.classList.add('hidden'));
        modalBukti.addEventListener('click', e => { if (e.target === modalBukti) modalBukti.classList.add('hidden'); });
    }

    function formatRp(num) {
        return Number(num).toLocaleString('id-ID');
    }

    function statusBadge(status) {
        const cls = {
            paid:    'bg-green-100 text-green-800',
            partial: 'bg-yellow-100 text-yellow-800',
            unpaid:  'bg-red-100 text-red-800',
        };
        return `<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full ${cls[status] || 'bg-gray-100 text-gray-800'}">${status.toUpperCase()}</span>`;
    }
});
