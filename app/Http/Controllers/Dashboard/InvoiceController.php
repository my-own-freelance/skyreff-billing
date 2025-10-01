<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class InvoiceController extends Controller
{
    public function index()
    {
        $title = 'Invoice';
        return view('pages.dashboard.admin.invoice', compact('title'));
    }

    public function dataTable(Request $request)
    {
        try {
            $query = Invoice::with(['user', 'plan', 'subscription']);

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->where('invoice_number', 'like', "%$searchValue%")
                        ->orWhereHas('user', function ($uq) use ($searchValue) {
                            $uq->where('name', 'like', "%$searchValue%");
                        })
                        ->orWhereHas('subscription', function ($uq) use ($searchValue) {
                            $uq->where('subscription_number', 'like', "%$searchValue%");
                        })
                        ->orWhereHas('plan', function ($pq) use ($searchValue) {
                            $pq->where('name', 'like', "%$searchValue%");
                        });
                });
            }

            $recordsFiltered = $query->count();
            $data = $query->orderBy('id', 'desc')
                ->skip($request->query('start'))
                ->limit($request->query('length'))
                ->get();

            $output = $data->map(function ($item) {
                // Build action dropdown
                $action = "<div class='dropdown-primary dropdown open'>
                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' 
                    id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                    Aksi
                </button>
                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                    <a class='dropdown-item' onclick='return printInvoice(\"{$item->id}\");' href='javascript:void(0);' title='Print Invoice'>Print</a>";

                // Jika status belum paid, tampilkan opsi update status
                if ($item->status !== 'paid') {
                    $action .= "
                    <a class='dropdown-item' onclick='return updateStatus(\"{$item->id}\", \"paid\");' href='javascript:void(0);' title='Sukseskan'>Sukses</a>
                    <a class='dropdown-item' onclick='return updateStatus(\"{$item->id}\", \"cancel\");' href='javascript:void(0);' title='Batalkan'>Cancel</a>";
                }

                $action .= "</div></div>";

                $statusLabel = match ($item->status) {
                    'paid' => '<span class="badge badge-success">Paid</span>',
                    'unpaid' => '<span class="badge badge-warning">Unpaid</span>',
                    'expired' => '<span class="badge badge-danger">Expired</span>',
                    'cancel' => '<span class="badge badge-secondary">Cancel</span>',
                };

                $item['action'] = $action;
                $item['status'] = $statusLabel;
                $item['amount'] = 'Rp ' . number_format($item->amount, 0, ',', '.');
                $item['invoice_period'] = $item->invoice_period_start && $item->invoice_period_end
                    ? $item->invoice_period_start->format('Y-m-d') . ' s/d ' . $item->invoice_period_end->format('Y-m-d')
                    : '-';
                $item['user_name'] = ($item->user)
                    ? '<span><b>Name:</b> ' . e($item->user->name) . '</span><br>'
                    . '<span><b>Username:</b> ' . e($item->user->username) . '</span>'
                    : '-';
                $item['plan_name'] = $item->plan->name ?? '-';
                $item['subscription_number'] = $item->subscription->subscription_number ?? '-';
                $item['created_at_formatted'] = $item->created_at
                    ? Carbon::parse($item->created_at)
                    ->timezone('Asia/Jakarta') // atur timezone ke WIB
                    ->locale('id') // bahasa Indonesia
                    ->translatedFormat('d M Y H:i')
                    : '-';
                return $item;
            });

            $total = Invoice::count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output,
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
                'draw' => $request->query('draw'),
                'recordsFiltered' => 0,
                'recordsTotal' => 0,
                'data' => [],
            ], 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $invoice = Invoice::with(['user', 'plan', 'subscription'])->find($id);

            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }

            return response()->json([
                'status' => 'success',
                'data' => $invoice,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }

    public function print($id)
    {
        $invoice = Invoice::with(['user', 'plan', 'subscription'])
            ->findOrFail($id);

        return view('pages.dashboard.admin.invoice_print', [
            'invoice' => $invoice,
            'title' => 'Invoice #' . $invoice->invoice_number
        ]);
    }


    public function updateStatus(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();
            $rules = [
                'id' => 'required|integer|exists:invoices,id',
                'status' => 'required|in:unpaid,paid,expired,cancel',
            ];

            $validator = Validator::make($data, $rules, [
                'id.required' => 'Invoice ID harus diisi',
                'id.exists' => 'Data invoice tidak ditemukan',
                'status.required' => 'Status harus diisi',
                'status.in' => 'Status tidak sesuai',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $invoice = Invoice::find($data['id']);
            if (!$invoice) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data invoice tidak ditemukan',
                ], 404);
            }

            if ($invoice->status == 'paid') {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Invoice sudah dibayar',
                ]);
            }

            $updateData = ['status' => $data['status']];

            if ($data['status'] === 'paid') {
                $updateData['paid_at'] = Carbon::now('Asia/Jakarta');

                // update subscription current period
                if ($invoice->subscription) {
                    $subscription = $invoice->subscription;

                    // Jika subscription belum punya periode, gunakan periode invoice
                    $currentStart = $subscription->current_period_start ?? $invoice->invoice_period_start ?? Carbon::now('Asia/Jakarta');
                    $currentEnd = $subscription->current_period_end ?? $invoice->invoice_period_end ?? Carbon::now('Asia/Jakarta')->addMonth();

                    // Tambahkan 1 bulan dari periode lama
                    $subscription->current_period_start = Carbon::parse($currentStart)->addMonth();
                    $subscription->current_period_end = Carbon::parse($currentEnd)->addMonth();

                    $subscription->save();
                }
            }

            $invoice->update($updateData);

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Status invoice berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }
}
