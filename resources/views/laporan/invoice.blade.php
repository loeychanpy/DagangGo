<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Invoice {{ $transaction->invoice_number }}</title>
    <style>{!! file_get_contents(public_path('css/invoice.css')) !!}</style>
</head>
<body>
    <div class="header">
        <h1>INVOICE</h1>
        <p>{{ config('app.name', 'MaterialPOS') }}</p>
    </div>

    <div class="info-grid">
        <div class="info-block">
            <p><strong>Invoice:</strong> {{ $transaction->invoice_number }}</p>
            <p><strong>Tanggal:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
            <p><strong>Kasir:</strong> {{ $transaction->user->name ?? '-' }}</p>
        </div>
        <div class="info-block" style="text-align:right">
            <p><strong>Pelanggan:</strong> {{ $transaction->customer->name ?? 'Umum' }}</p>
            @if($transaction->customer?->phone)
            <p><strong>Telepon:</strong> {{ $transaction->customer->phone }}</p>
            @endif
            <p>Status: <span class="status-badge {{ $transaction->status }}">{{ strtoupper($transaction->status) }}</span></p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Produk</th>
                <th>Satuan</th>
                <th style="text-align:right">Harga</th>
                <th style="text-align:center">Qty</th>
                <th style="text-align:right">Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($transaction->details as $i => $detail)
            <tr>
                <td>{{ $i + 1 }}</td>
                <td>{{ $detail->product->name ?? '-' }}</td>
                <td>{{ $detail->product->unit->short_name ?? '' }}</td>
                <td style="text-align:right">Rp {{ number_format($detail->price_at_sale, 0, ',', '.') }}</td>
                <td style="text-align:center">{{ $detail->quantity }}</td>
                <td style="text-align:right">Rp {{ number_format($detail->subtotal, 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals">
        <tr>
            <td>Subtotal</td>
            <td>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->discount > 0)
        <tr>
            <td>Diskon</td>
            <td>- Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->tax > 0)
        <tr>
            <td>Pajak</td>
            <td>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td>TOTAL</td>
            <td>Rp {{ number_format($transaction->total_price, 0, ',', '.') }}</td>
        </tr>
        <tr>
            <td>Dibayar ({{ strtoupper($transaction->payment_method) }})</td>
            <td>Rp {{ number_format($transaction->pay_amount, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->change_amount > 0)
        <tr>
            <td>Kembalian</td>
            <td>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td>
        </tr>
        @endif
        @if($transaction->remaining_bill > 0)
        <tr class="sisa-tagihan">
            <td>Sisa Tagihan</td>
            <td>Rp {{ number_format($transaction->remaining_bill, 0, ',', '.') }}</td>
        </tr>
        @if($transaction->due_date)
        <tr class="sisa-tagihan">
            <td>Jatuh Tempo</td>
            <td>{{ $transaction->due_date->format('d/m/Y') }}</td>
        </tr>
        @endif
        @endif
    </table>

    <div class="footer">
        Terima kasih atas pembelian Anda &mdash; Dicetak {{ now()->format('d/m/Y H:i') }}
    </div>
</body>
</html>
