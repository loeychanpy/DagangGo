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
        document.querySelectorAll('.btn-info-customer').forEach(btn => {
            btn.addEventListener('click', async function () {
                const url  = this.dataset.url;

                // Reset state
                document.getElementById('customer-info-loading').classList.remove('hidden');
                document.getElementById('customer-info-content').classList.add('hidden');
                modalInfo.classList.remove('hidden');

                try {
                    const res  = await fetch(url, {
                        headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                    });
                    const data = await res.json();
                    if (!res.ok) throw new Error(data.message || 'Gagal memuat data.');

                    const c = data.customer;
                    const s = data.stats;

                    document.getElementById('ci-name').textContent    = c.name;
                    document.getElementById('ci-phone').textContent   = c.phone || '-';
                    document.getElementById('ci-address').textContent = c.address || '-';
                    document.getElementById('ci-credit').textContent  = c.credit_limit > 0
                        ? 'Rp ' + formatRp(c.credit_limit)
                        : 'Tidak ada limit';

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

        btnCloseInfo.addEventListener('click', () => modalInfo.classList.add('hidden'));
        modalInfo.addEventListener('click', e => { if (e.target === modalInfo) modalInfo.classList.add('hidden'); });
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
