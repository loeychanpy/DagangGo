<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan</title>
    <style>{!! file_get_contents(public_path('css/laporan-pdf.css')) !!}</style>
</head>
<body>
    <div class="company-name">{{ config('app.name') }}</div>
    <h1>Laporan Penjualan</h1>
    <div class="subtitle">Periode: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} s/d {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}</div>

    <div class="summary">
        <div class="summary-box">
            <div class="label">Total Omzet</div>
            <div class="value blue">Rp {{ number_format($totalSales, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Uang Masuk</div>
            <div class="value green">Rp {{ number_format($totalReceived, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Total Piutang</div>
            <div class="value red">Rp {{ number_format($totalDebt, 0, ',', '.') }}</div>
        </div>
        <div class="summary-box">
            <div class="label">Jumlah Transaksi</div>
            <div class="value">{{ $transactions->count() }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Tanggal</th>
                <th>Invoice</th>
                <th>Pelanggan</th>
                <th>Metode</th>
                <th>Total</th>
                <th>Dibayar</th>
                <th>Sisa</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $i => $t)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
                <td><b>{{ $t->invoice_number }}</b></td>
                <td>{{ $t->customer->name ?? 'Umum' }}</td>
                <td>{{ strtoupper($t->payment_method) }}</td>
                <td>Rp {{ number_format($t->total_price, 0, ',', '.') }}</td>
                <td>Rp {{ number_format($t->pay_amount, 0, ',', '.') }}</td>
                <td style="color: {{ $t->remaining_bill > 0 ? '#dc2626' : '#6b7280' }}">
                    Rp {{ number_format($t->remaining_bill, 0, ',', '.') }}
                </td>
                <td>
                    <span class="badge badge-{{ $t->status }}">{{ strtoupper($t->status) }}</span>
                </td>
            </tr>
            @empty
            <tr><td colspan="9" style="text-align:center;color:#9ca3af;padding:20px">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">Dicetak pada: {{ now()->format('d/m/Y H:i') }}</div>
</body>
</html>
