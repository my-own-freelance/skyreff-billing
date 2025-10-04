<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Device;
use App\Models\DeviceSubscription;
use App\Models\Invoice;
use App\Models\Mutation;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Ticket;
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

            $subscription = Subscription::where("user_id", $user->id)->first();
            $totDevice = $subscription ? DeviceSubscription::where("subscription_id", $subscription->id)->count() : 0;
            $totInvoiceUnpaid = Invoice::where("user_id", $user->id)->where("status", "UNPAID")->count();
            $totOpenTicket = Ticket::where("member_id", $user->id)->where("status", "open")->count();
            $currentInvoices = Invoice::where("user_id", $user->id)->orderBy('id', 'desc')->get();
            $tickets = Ticket::with(['member:id,name', 'technician:id,name'])->where("member_id", $user->id)->get();
            $deviceSubscriptionIds = $subscription ? DeviceSubscription::where("subscription_id", $subscription->id)->pluck("device_id")->toArray() :  [];
            $devices = Device::whereIn("id", $deviceSubscriptionIds)->get();

            $data = [
                "announcements" => $announcements,
                "subscription" => $subscription,
                "totDevice" => $totDevice,
                "totInvoiceUnpaid" => $totInvoiceUnpaid,
                "totOpenTicket" => $totOpenTicket,
                "currentInvoices" => $currentInvoices,
                "tickets" => $tickets,
                "devices" => $devices
            ];
        } else if ($user->role == "teknisi") {
            $teknisi = User::where("id", $user->id)->first();
            $wdCommisson = Mutation::where('user_id', $user->id)->whereIn('status', ['PENDING', 'PROCESS'])->sum('amount') ?? 0;

            $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            $commissionThisMonth = Mutation::where("type", "C")->where("user_id", $teknisi->id)->whereBetween("created_at", [$tglAwal, $tglAkhir])->sum("amount") ?? 0;
            $tickets = Ticket::with(['member:id,name', 'technician:id,name'])->where("technician_id", $user->id)->limit(10)->get();
            $data = [
                "commission" => 'Rp. ' . number_format($teknisi->commission, 0, ',', '.'),
                "wd_commission" => 'Rp. ' . number_format($wdCommisson, 0, ',', '.'),
                "month_commission" => 'Rp. ' . number_format($commissionThisMonth, 0, ',', '.'),
                "tickets" => $tickets
            ];
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

    public function getStatikSession()
    {
        try {
            $user = User::find(auth()->user()->id);
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "User session not found !"
                ]);
            }

            return response()->json([
                "status" => "success",
                "data" => $user
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }
}
