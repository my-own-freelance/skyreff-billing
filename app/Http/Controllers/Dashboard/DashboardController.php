<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Skyreff";
        $data = [];
        $user = Auth::user();
        $pageUrl = $user->role == "admin" ? "pages.dashboard.admin.index" : ($user->role == "teknisi" ? "pages.dashboard.teknisi.index" : "pages.dashboard.member.index");

        if ($user->role == "member") {
            $announcements = Announcement::where("is_active", "Y")
                ->where(function ($q) use ($user) {
                    // Filter by user_id
                    $q->where("user_id", $user->id);

                    if (!empty($user->area_id)) {
                        // User punya area_id → tambah filter area
                        $q->orWhere("area_id", $user->area_id);
                    }

                    // Global announcement (user_id & area_id kosong)
                    $q->orWhere(function ($q2) {
                        $q2->whereNull("user_id")
                            ->whereNull("area_id");
                    });
                })
                ->get();

            $data = [
                "announcements" => $announcements,
            ];
        } else if ($user->role == "teknisi") {
        } else {
            // ADMIN
            // Tahun sekarang
            $year = Carbon::now('Asia/Jakarta')->year;

            // TOTAL SALE TAHUNAN
            $tglAwal = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Jakarta')->toDateTimeString();
            $tglAkhir = Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Jakarta')->toDateTimeString();
            $totalSales = Invoice::where("status", "paid")
                ->whereBetween("created_at", [$tglAwal, $tglAkhir])
                ->sum("amount");

            // TOTAL SALE BULANAN
            $tglAwalBln = Carbon::now('Asia/Jakarta')->startOfMonth()->toDateTimeString();
            $tglAkhirBln = Carbon::now('Asia/Jakarta')->endOfMonth()->toDateTimeString();
            $totalSalesBln = Invoice::where("status", "paid")
                ->whereBetween("created_at", [$tglAwalBln, $tglAkhirBln])
                ->sum("amount");

            // TOTAL SALE 6 BULANAN (SEMESTER)
            $tglAwalSmt1 = Carbon::create($year, 1, 1, 0, 0, 0, 'Asia/Jakarta')->toDateTimeString();
            $tglAkhirSmt1 = Carbon::create($year, 6, 30, 23, 59, 59, 'Asia/Jakarta')->toDateTimeString();
            $totalSalesSmt1 = Invoice::where("status", "paid")
                ->whereBetween("created_at", [$tglAwalSmt1, $tglAkhirSmt1])
                ->sum("amount");

            $tglAwalSmt2 = Carbon::create($year, 7, 1, 0, 0, 0, 'Asia/Jakarta')->toDateTimeString();
            $tglAkhirSmt2 = Carbon::create($year, 12, 31, 23, 59, 59, 'Asia/Jakarta')->toDateTimeString();
            $totalSalesSmt2 = Invoice::where("status", "paid")
                ->whereBetween("created_at", [$tglAwalSmt2, $tglAkhirSmt2])
                ->sum("amount");


            // total member
            $totalMember = User::where("role", "member")->count();
            // total invoice
            $totalInvoiceBln = Invoice::whereBetween("created_at", [$tglAwalBln, $tglAkhirBln])->count();
            // total paket plan
            $totalPlan = Plan::count();
            // total subscription
            $totalSubscription = Subscription::count();
            $data = [
                'trx_total_amount' =>  'Rp. ' . number_format($totalSales, 0, ',', '.'),
                'trx_total_amount_bln' => 'Rp. ' . number_format($totalSalesBln, 0, ',', '.'),
                'trx_total_amount_smt1' =>  'Rp. ' . number_format($totalSalesSmt1, 0, ',', '.'),
                'trx_total_amount_smt2' =>  'Rp. ' . number_format($totalSalesSmt2, 0, ',', '.'),
                'total_member' => $totalMember,
                'total_invoice_bln' => $totalInvoiceBln,
                'total_plan' => $totalPlan,
                'total_subscription' => $totalSubscription
            ];
        }
        return view($pageUrl, compact("title", "data"));
    }

    // public function getStatisticChart()
    // {
    //     try {
    //         $year = Carbon::now()->year;

    //         // Array bulan 1-12
    //         $months = range(1, 12);

    //         // Ambil total amount per bulan untuk invoice yang statusnya paid
    //         $statistics = Invoice::selectRaw('
    //             MONTH(paid_at) as month,
    //             SUM(amount) as total_sales
    //         ')
    //             ->where('status', 'paid')
    //             ->whereYear('paid_at', $year)
    //             ->groupBy('month')
    //             ->orderBy('month')
    //             ->get()
    //             ->keyBy('month'); // jadikan array keyed by month

    //         // Pastikan semua bulan ada datanya, meskipun 0
    //         $totalSalesData = [];
    //         foreach ($months as $month) {
    //             $totalSalesData[] = $statistics[$month]->total_sales ?? 0;
    //         }

    //         // Struktur data output untuk chart
    //         $data = [
    //             [
    //                 "label" => "Total Penjualan",
    //                 "borderColor" => "#177dff",
    //                 "pointBackgroundColor" => "rgba(23, 125, 255, 0.6)",
    //                 "pointRadius" => 0,
    //                 "backgroundColor" => "rgba(23, 125, 255, 0.4)",
    //                 "legendColor" => "#177dff",
    //                 "fill" => true,
    //                 "borderWidth" => 2,
    //                 "data" => $totalSalesData
    //             ]
    //         ];

    //         return response()->json([
    //             "status" => "success",
    //             "data" => $data
    //         ]);
    //     } catch (\Throwable $err) {
    //         return response()->json([
    //             "status" => "error",
    //             "message" => $err->getMessage(),
    //             "data" => [
    //                 [
    //                     "label" => "Total Penjualan",
    //                     "borderColor" => "#177dff",
    //                     "pointBackgroundColor" => "rgba(23, 125, 255, 0.6)",
    //                     "pointRadius" => 0,
    //                     "backgroundColor" => "rgba(23, 125, 255, 0.4)",
    //                     "legendColor" => "#177dff",
    //                     "fill" => true,
    //                     "borderWidth" => 2,
    //                     "data" => array_fill(0, 12, 0)
    //                 ]
    //             ]
    //         ], 500);
    //     }
    // }

    public function getStatisticChart()
    {
        try {
            $year = Carbon::now()->year;
            $months = range(1, 12);
            $statuses = ['paid', 'unpaid', 'expired', 'cancel'];

            // Ambil data total per bulan & per status
            $statistics = Invoice::selectRaw('
                MONTH(created_at) as month,
                status,
                SUM(amount) as total_amount
            ')
                ->whereYear('created_at', $year)
                ->groupBy('month', 'status')
                ->orderBy('month')
                ->get();

            // Susun data keyed by month & status
            $statsByMonthStatus = [];
            foreach ($statistics as $stat) {
                $statsByMonthStatus[$stat->month][$stat->status] = $stat->total_amount;
            }

            // Siapkan dataset untuk chart
            $colors = [
                'paid' => ['border' => '#28a745', 'bg' => 'rgba(40, 167, 69, 0.4)'],
                'unpaid' => ['border' => '#ffc107', 'bg' => 'rgba(255, 193, 7, 0.4)'],
                'expired' => ['border' => '#dc3545', 'bg' => 'rgba(220, 53, 69, 0.4)'],
                'cancel' => ['border' => '#6c757d', 'bg' => 'rgba(108, 117, 125, 0.4)'],
            ];

            $datasets = [];
            foreach ($statuses as $status) {
                $dataArr = [];
                foreach ($months as $month) {
                    $dataArr[] = $statsByMonthStatus[$month][$status] ?? 0;
                }

                $datasets[] = [
                    "label" => ucfirst($status),
                    "borderColor" => $colors[$status]['border'],
                    "backgroundColor" => $colors[$status]['bg'],
                    "pointBackgroundColor" => $colors[$status]['border'],
                    "pointRadius" => 0,
                    "legendColor" => $colors[$status]['border'],
                    "fill" => true,
                    "borderWidth" => 2,
                    "data" => $dataArr
                ];
            }

            return response()->json([
                "status" => "success",
                "data" => $datasets
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
                "data" => []
            ], 500);
        }
    }
}
