<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Surat Jalan - {{ $transaction->invoice_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        /* Sembunyikan tombol cetak saat kertas diprint */
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body class="bg-white text-black p-10 font-sans">

    <div class="mb-4 no-print text-right">
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded shadow hover:bg-blue-700">
            🖨️ Cetak Surat Jalan
        </button>
        <a href="{{ route('dashboard') }}" class="ml-2 text-gray-500 hover:underline">Kembali</a>
    </div>

    <div class="flex justify-between items-center border-b-2 border-black pb-4 mb-6">
        <div>
            <h1 class="text-3xl font-bold uppercase tracking-widest">SURAT JALAN</h1>
            <p class="text-sm mt-1">No. Ref: {{ $transaction->invoice_number }}</p>
            <p class="text-sm">Tanggal: {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-bold">DagangGo</h2>
            <p class="text-sm">Jl. Raya Ukrida No. 1, Jakarta</p>
            <p class="text-sm">Telp: 0812-3456-7890</p>
        </div>
    </div>

    <div class="flex justify-between mb-8">
        <div class="w-1/2">
            <h3 class="font-bold border-b mb-2">Tujuan Pengiriman:</h3>
            <p class="font-semibold">{{ $transaction->customer->name ?? 'Pelanggan Umum' }}</p>
            <p class="text-sm">{{ $transaction->delivery->shipping_address }}</p>
        </div>
        <div class="w-1/3">
            <h3 class="font-bold border-b mb-2">Data Armada:</h3>
            <p class="text-sm">Supir: <span class="font-semibold">{{ $transaction->delivery->driver_name }}</span></p>
            <p class="text-sm">Plat Nomor: <span class="font-semibold">{{ $transaction->delivery->license_plate }}</span></p>
        </div>
    </div>

    <table class="w-full text-left border-collapse mb-8">
        <thead>
            <tr class="border-y-2 border-black">
                <th class="py-2 w-16 text-center">No</th>
                <th class="py-2">Nama Barang</th>
                <th class="py-2 w-32 text-center">Qty / Satuan</th>
                <th class="py-2 w-48 text-center">Keterangan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transaction->details as $index => $detail)
            <tr class="border-b border-gray-300">
                <td class="py-2 text-center">{{ $index + 1 }}</td>
                <td class="py-2">{{ $detail->product->name }}</td>
                <td class="py-2 text-center font-bold text-lg">{{ $detail->quantity }}</td>
                <td class="py-2 text-center text-sm italic text-gray-400">.......................</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="flex justify-between mt-12 text-center">
        <div class="w-1/3">
            <p class="mb-16">Penerima / Pembeli,</p>
            <p class="font-bold underline">( ................................... )</p>
        </div>
        <div class="w-1/3">
            <p class="mb-16">Supir / Kurir,</p>
            <p class="font-bold underline">{{ $transaction->delivery->driver_name ?: '( ................................... )' }}</p>
        </div>
        <div class="w-1/3">
            <p class="mb-16">Hormat Kami,</p>
            <p class="font-bold underline">Admin Gudang</p>
        </div>
    </div>
    
</body>
</html>