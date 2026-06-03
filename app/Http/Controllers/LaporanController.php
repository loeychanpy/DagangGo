<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\TransactionPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;

class LaporanController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transactions = Transaction::with(['user', 'customer', 'delivery'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = $transactions->sum('total_price');
        $totalReceived = $transactions->sum('pay_amount');
        $totalDebt = $transactions->sum('remaining_bill');

        return view('laporan.index', compact('transactions', 'totalSales', 'totalReceived', 'totalDebt', 'startDate', 'endDate'));
    }

    public function exportPdf(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transactions = Transaction::with(['user', 'customer'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $totalSales = $transactions->sum('total_price');
        $totalReceived = $transactions->sum('pay_amount');
        $totalDebt = $transactions->sum('remaining_bill');

        $pdf = Pdf::loadView('laporan.pdf', compact(
            'transactions', 'totalSales', 'totalReceived', 'totalDebt', 'startDate', 'endDate'
        ))->setPaper('a4', 'landscape');

        return $pdf->download("laporan-penjualan-{$startDate}-{$endDate}.pdf");
    }

    public function exportCsv(Request $request)
    {
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth()->toDateString());

        $transactions = Transaction::with(['user', 'customer'])
            ->whereBetween('created_at', [$startDate . ' 00:00:00', $endDate . ' 23:59:59'])
            ->orderBy('created_at', 'desc')
            ->get();

        $filename = "laporan-penjualan-{$startDate}-{$endDate}.csv";
        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fputs($out, "\xEF\xBB\xBF"); // BOM untuk Excel
            fputcsv($out, ['Tanggal', 'Invoice', 'Pelanggan', 'Kasir', 'Metode', 'Subtotal', 'Diskon', 'Total', 'Dibayar', 'Kembalian', 'Sisa Tagihan', 'Status']);
            foreach ($transactions as $t) {
                fputcsv($out, [
                    $t->created_at->format('d/m/Y H:i'),
                    $t->invoice_number,
                    $t->customer->name ?? 'Umum',
                    $t->user->name ?? '-',
                    strtoupper($t->payment_method),
                    $t->subtotal,
                    $t->discount,
                    $t->total_price,
                    $t->pay_amount,
                    $t->change_amount,
                    $t->remaining_bill,
                    strtoupper($t->status),
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function recordPayment(Request $request, Transaction $transaction)
    {
        $request->validate([
            'payment_method'   => 'required|in:cash,transfer,qris',
            'amount'           => ['required', 'numeric', 'min:1', 'max:' . $transaction->remaining_bill],
            'reference_number' => 'nullable|string|max:100',
            'proof_photo'      => 'nullable|image|max:2048',
        ]);

        if (in_array($request->payment_method, ['transfer', 'qris']) && !$request->hasFile('proof_photo')) {
            return response()->json(['message' => 'Bukti foto wajib diupload untuk pembayaran Transfer/QRIS.'], 422);
        }

        $proofPath = null;
        if ($request->hasFile('proof_photo')) {
            $proofPath = $request->file('proof_photo')->store('payment-proofs', 'public');
        }

        DB::beginTransaction();
        try {
            TransactionPayment::create([
                'transaction_id'   => $transaction->id,
                'user_id'          => auth()->id(),
                'amount'           => $request->amount,
                'payment_method'   => $request->payment_method,
                'reference_number' => $request->reference_number,
                'notes'            => $proofPath,
            ]);

            $newRemaining = max(0, $transaction->remaining_bill - $request->amount);
            $transaction->update([
                'remaining_bill' => $newRemaining,
                'pay_amount'     => $transaction->pay_amount + $request->amount,
                'status'         => $newRemaining <= 0 ? 'paid' : 'partial',
            ]);

            AuditLog::create([
                'user_id'     => auth()->id(),
                'action'      => 'CATAT_PEMBAYARAN',
                'description' => 'Pembayaran Rp ' . number_format($request->amount, 0, ',', '.') .
                                 " untuk transaksi {$transaction->invoice_number}",
                'ip_address'  => $request->ip(),
            ]);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }

        return response()->json(['transaction' => $transaction->fresh()]);
    }

    public function exportInvoice(Transaction $transaction)
    {
        $transaction->load(['customer', 'user', 'details.product.unit']);

        $pdf = Pdf::loadView('laporan.invoice', compact('transaction'))
            ->setPaper('a5', 'portrait');

        return $pdf->download("invoice-{$transaction->invoice_number}.pdf");
    }
}
