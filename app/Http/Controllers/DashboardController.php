<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $lowStockProducts = Product::whereColumn('stock', '<=', 'min_stock')
                                    ->with(['unit', 'category'])
                                    ->get();

        // Total kasbon selalu kumulatif (tidak terfilter tanggal)
        $totalReceivables = Transaction::where('status', '!=', 'paid')->sum('remaining_bill');

        // Pengingat kasbon: piutang aktif, jatuh tempo paling mendesak di atas
        $kasbonReminders = Transaction::with('customer')
            ->whereIn('status', ['unpaid', 'partial'])
            ->where('remaining_bill', '>', 0)
            ->orderByRaw('due_date IS NULL, due_date ASC')
            ->orderBy('created_at')
            ->get();

        // Hitung rentang tanggal berdasarkan periode yang dipilih
        $period = $request->get('period', 'today');
        [$start, $end, $periodLabel] = $this->resolvePeriod(
            $period,
            $request->get('start_date'),
            $request->get('end_date')
        );

        $periodSales        = Transaction::whereBetween('created_at', [$start, $end])->sum('total_price');
        $periodTransactions = Transaction::whereBetween('created_at', [$start, $end])->count();

        // Data grafik: satu titik per hari dalam rentang
        [$chartLabels, $chartData] = $this->buildChartData($start, $end);

        $startDate = $start->toDateString();
        $endDate   = $end->toDateString();

        return view('dashboard', compact(
            'lowStockProducts',
            'totalReceivables',
            'kasbonReminders',
            'periodSales',
            'periodTransactions',
            'periodLabel',
            'period',
            'startDate',
            'endDate',
            'chartLabels',
            'chartData'
        ));
    }

    private function resolvePeriod(string $period, ?string $startInput, ?string $endInput): array
    {
        switch ($period) {
            case 'week':
                return [
                    Carbon::today()->subDays(6)->startOfDay(),
                    Carbon::today()->endOfDay(),
                    '7 Hari Terakhir',
                ];
            case 'month':
                return [
                    Carbon::now()->startOfMonth()->startOfDay(),
                    Carbon::now()->endOfMonth()->endOfDay(),
                    'Bulan Ini (' . Carbon::now()->translatedFormat('F Y') . ')',
                ];
            case 'custom':
                $s = Carbon::parse($startInput ?? today())->startOfDay();
                $e = Carbon::parse($endInput ?? today())->endOfDay();
                return [
                    $s,
                    $e,
                    $s->format('d/m/Y') . ' – ' . $e->format('d/m/Y'),
                ];
            default: // today
                return [
                    Carbon::today()->startOfDay(),
                    Carbon::today()->endOfDay(),
                    'Hari Ini',
                ];
        }
    }

    private function buildChartData(Carbon $start, Carbon $end): array
    {
        // Single GROUP BY query instead of one query per day
        $results = Transaction::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, SUM(total_price) as total')
            ->groupByRaw('DATE(created_at)')
            ->pluck('total', 'date');

        $labels = [];
        $data   = [];
        $days   = (int) $start->copy()->startOfDay()->diffInDays($end->copy()->startOfDay());

        for ($i = 0; $i <= $days; $i++) {
            $date     = $start->copy()->startOfDay()->addDays($i);
            $labels[] = $date->format('d/m');
            $data[]   = (float) ($results[$date->format('Y-m-d')] ?? 0);
        }

        return [$labels, $data];
    }
}
