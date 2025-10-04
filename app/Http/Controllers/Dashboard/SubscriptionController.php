<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceSubscription;
use App\Models\Invoice;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;


class SubscriptionController extends Controller
{
    public function index()
    {
        $title = 'Manage Subscription';
        $technicians = User::where('role', 'teknisi')->get();
        $members = User::where('role', 'member')->get();
        $plans = Plan::all();
        $devices = Device::where('is_active', 'Y')->get();
        $pageUrl = "pages.dashboard.admin.subscription";
        if (auth()->user()->role == "member") $pageUrl = "pages.dashboard.member.subscription";

        return view($pageUrl, compact('title', 'technicians', 'members', 'plans', 'devices'));
    }

    // API DataTable
    public function dataTable(Request $request)
    {
        try {
            $query = Subscription::with(['plan', 'user']);

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($q) use ($searchValue) {
                    $q->whereHas('user', function ($q2) use ($searchValue) {
                        $q2->where('name', 'like', "%$searchValue%")
                            ->orWhere('username', 'like', "%$searchValue%");
                    })
                        ->orWhereHas('plan', function ($q2) use ($searchValue) {
                            $q2->where('name', 'like', "%$searchValue%");
                        });
                });
            }

            $user = auth()->user();
            if ($user->role == "member") {
                $query->where("user_id", $user->id);
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy('id', 'desc')
                ->skip($request->query('start'))
                ->limit($request->query('length'))
                ->get();


            $output = $data->map(function ($item) use ($user) {
                $action = "
                <div class='dropdown-primary dropdown open'>
                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                        Aksi
                    </button>
                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                        <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                        <a class='dropdown-item' onclick='return manageDevices(\"{$item->id}\");' href='javascript:void(0);' title='Manage Devices'>Manage Devices</a>
                        <hr>
                        <a class='dropdown-item' onclick='return generateInvoice(\"{$item->id}\");' href='javascript:void(0);' title='Generate Invoice'>Generate Invoice</a>
                    </div>
                </div>";

                $status =
                    $item->status == 'active'
                    ? '
                    <div class="text-center">
                        <span class="label-switch">Active</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status on">
                            <input type="checkbox" onclick="return updateStatus(\'' .
                    $item->id .
                    '\', \'isolir\');" />
                            <span class="slider"></span>
                        </div>
                    </div>'
                    : '<div class="text-center">
                        <span class="label-switch">Isolir</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status off">
                            <input type="checkbox" onclick="return updateStatus(\'' .
                    $item->id .
                    '\', \'active\');" />
                            <span class="slider"></span>
                        </div>
                    </div>';

                // JIKA MEMBER MAKA BTN ACTION HILANG
                if ($user->role == "member") {
                    $action = "";
                    $status = $item->status == "active" ? "<span class='badge badge-success'>Active</span>" : "<span class='badge badge-error'>Isolir</span>";
                }

                $identifier = ($item->type == "pppoe" || $item->type == "hotspot")
                    ?
                    '<div>
                        <span class="badge badge-info">Username: ' . e($item->username ?? '-') . '</span><br>
                        <span class="badge badge-secondary">Password: ' . e($item->password ?? '-') . '</span>
                    </div>'
                    : '<div>
                        <span class="badge badge-warning">Queue: ' . e($item->queue ?? '-') . '</span>
                    </div>';

                // Metode 1: Menggunakan Variabel (Kompatibel PHP 7+)
                $badgeClass = 'badge-secondary';
                if ($item->type == 'pppoe') {
                    $badgeClass = 'badge-info';
                } elseif ($item->type == 'hotspot') {
                    $badgeClass = 'badge-warning';
                }

                $type = '<span class="badge ' . $badgeClass . '">' . Str::ucfirst($item->type) . '</span>';

                $item['action'] = $action;
                $item['status'] = $status;
                $item['identifier'] = $identifier;
                $item['type'] = $type;
                $item['plan_name'] = $item->plan->name ?? '-';
                $item['member_name'] = ($item->user)
                    ? '<span><b>Name:</b> ' . e($item->user->name) . '</span><br>'
                    . '<span><b>Username:</b> ' . e($item->user->username) . '</span>'
                    : '-';
                $item['current_period'] = $item->current_period_start && $item->current_period_end
                    ? $item->current_period_start->format('Y-m-d') . ' s/d ' . $item->current_period_end->format('Y-m-d')
                    : '-';
                $item['next_invoice'] = $item->next_invoice_at ? $item->next_invoice_at->format('Y-m-d H:i') : '-';

                return $item;
            });

            $queryTotal = Subscription::query();
            if ($user->role == "member") {
                $queryTotal->where('user_id', $user->id);
            }

            $total = $queryTotal->count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output,
                'role' => $user->role
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
            $subscription = Subscription::with(['plan', 'user'])->find($id);
            if (!$subscription) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Data tidak ditemukan',
                ], 404);
            }
            return response()->json([
                'status' => 'success',
                'data' => $subscription,
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }

    public function create(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $rules = [
                'user_id' => 'required|integer|exists:users,id',
                'plan_id' => 'required|integer|exists:plans,id',
                'status' => 'required|in:active,isolir',
                'username' => 'nullable|string',
                'password' => 'nullable|string',
                'queue' => 'nullable|string',
                'current_period_start' => 'nullable|date',
                'current_period_end' => 'nullable|date|after_or_equal:current_period_start',
                'next_invoice_at' => 'nullable|date',
                'create_task' => 'nullable|in:ya,tidak',
                'technician_id' => 'nullable|integer|exists:users,id',
                'create_pic_notif' => 'nullable|in:ya,tidak',
            ];

            $validator = Validator::make($data, $rules, [
                'user_id.required' => 'Member harus dipilih',
                'user_id.exists' => 'Member tidak valid',
                'plan_id.required' => 'Plan harus dipilih',
                'plan_id.exists' => 'Plan tidak valid',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            // cek member hanya boleh punya 1 subscription
            $existing = Subscription::where('user_id', $data['user_id'])->first();
            if ($existing) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Member ini sudah memiliki subscription',
                ], 400);
            }

            // Generate nomor invoice unik (format bisa disesuaikan, misal INV20231001-0001)
            $subscriptionNumber = 'SUBS' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // buat subscription
            $subscription = Subscription::create([
                'user_id' => $data['user_id'],
                'plan_id' => $data['plan_id'],
                'subscription_number' => $subscriptionNumber,
                'type' => $data['type'] ?? null,
                'username' => $data['username'] ?? null,
                'password' => $data['password'] ?? null,
                'queue' => $data['queue'] ?? null,
                'status' => $data['status'],
                'current_period_start' => $data['current_period_start'] ?? null,
                'current_period_end' => $data['current_period_end'] ?? null,
                'next_invoice_at' => $data['next_invoice_at'] ?? null,
            ]);

            // buat tiket teknisi jika create_task == 'ya'
            if (!empty($data['create_task']) && $data['create_task'] === 'ya') {
                $ticketData = [
                    'type' => 'pemasangan',
                    'status' => 'open',
                    'member_id' => $data['user_id'],
                    'subscription_id' => $subscription->id,
                    'created_by' => auth()->id(),
                    'cases' => 'Tiket pemasangan untuk subscription baru',
                ];

                // tambahkan teknisi jika ada
                if (!empty($data['technician_id'])) {
                    $ticketData['technician_id'] = $data['technician_id'];
                }

                $ticket = Ticket::create($ticketData);

                // 🔔 Kirim notifikasi WA ke teknisi jika create_pic_notif == 'ya'
                if (!empty($data['create_pic_notif']) && $data['create_pic_notif'] == 'ya' && !empty($data['technician_id'])) {
                    $technician = User::find($data['technician_id']);
                    $member = User::find($data['user_id']);
                    $plan = Plan::find($data['plan_id']);

                    if ($technician && $member) {
                        // link google maps dari latitude/longitude atau alamat
                        $linkMap = $member->link_maps;

                        $message = "Halo {$technician->name},\n";
                        $message .= "Terdapat tiket pemasangan baru yang harus Anda tangani.\n\n";
                        $message .= "Member: {$member->name}\n";
                        $message .= "Phone: {$member->phone}\n";
                        $message .= "Alamat: {$member->address}\n";
                        if ($linkMap) {
                            $message .= "Lokasi: {$linkMap}\n";
                        }
                        $message .= "Paket : {$plan->name}\n";
                        $message .= "Subscription: {$subscription->subscription_number}\n";
                        $message .= "Silakan segera lakukan follow up pemasangan.\nTerima kasih.";

                        $payload = [
                            "appkey" => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                            "authkey" => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                            "to" => preg_replace('/^08/', '628', $technician->phone),
                            "message" => $message,
                        ];

                        Http::post('https://app.saungwa.com/api/create-message', $payload);
                    }
                }
            }

            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Subscription berhasil dibuat',
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }


    public function update(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $rules = [
                'id' => 'required|integer|exists:subscriptions,id',
                'plan_id' => 'required|integer|exists:plans,id',
                'status' => 'required|in:active,isolir',
                'username' => 'nullable|string',
                'password' => 'nullable|string',
                'queue' => 'nullable|string',
                'current_period_start' => 'nullable|date',
                'current_period_end' => 'nullable|date|after_or_equal:current_period_start',

                // tambahan validasi untuk tiket teknisi
                'fCreateTask' => 'nullable|in:ya,tidak',
                'fTechnician' => 'nullable|integer|exists:technicians,id',
                'fTechnicianNotif' => 'nullable|in:ya,tidak',
            ];

            $validator = Validator::make($data, $rules, [
                'id.required' => 'Data ID harus diisi',
                'id.exists' => 'Data subscription tidak ditemukan',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            // Ambil data subscription
            $subscription = Subscription::findOrFail($data['id']);

            // Update subscription
            $subscription->update([
                'plan_id' => $data['plan_id'],
                'status' => $data['status'],
                'type' => $data['type'] ?? null,
                'username' => $data['username'] && ($data['type'] == 'pppoe' || $data['type'] == 'hotspot') ? $data['username'] : null,
                'password' => $data['password'] && ($data['type'] == 'pppoe' || $data['type'] == 'hotspot')  ? $data['password'] : null,
                'queue' => $data['queue'] && $data['type'] == 'static' ? $data['queue'] : null,
                'current_period_start' => $data['current_period_start'] ?? $subscription->current_period_start,
                'current_period_end' => $data['current_period_end'] ?? $subscription->current_period_end,
                'next_invoice_at' => $data['next_invoice_at'] ?? $subscription->next_invoice_at,
            ]);

            // buat tiket teknisi jika create_task == 'ya'
            if (!empty($data['create_task']) && $data['create_task'] === 'ya') {
                $ticketData = [
                    'type' => 'pemasangan',
                    'status' => 'open',
                    'member_id' => $data['user_id'],
                    'subscription_id' => $subscription->id,
                    'created_by' => auth()->id(),
                    'cases' => 'Tiket Update pemasangan untuk subscription lama',
                ];

                // tambahkan teknisi jika ada
                if (!empty($data['technician_id'])) {
                    $ticketData['technician_id'] = $data['technician_id'];
                }

                $ticket = Ticket::create($ticketData);

                // --- next development: kirim notifikasi WA ke teknisi jika create_pic_notif == 'ya'
                // if(!empty($data['create_pic_notif']) && $data['create_pic_notif'] == 'ya'){
                //     // logic kirim notifikasi WA ke teknisi
                // }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Subscription berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'id' => 'required|integer',
                'status' => 'required|string|in:active,isolir',
            ];

            $validator = Validator::make($data, $rules, [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'status.required' => 'Status harus diisi',
                'status.in' => 'Status tidak sesuai',
            ]);

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                    ],
                    400,
                );
            }

            $sub = Subscription::find($data['id']);
            if (!$sub) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }
            $sub->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Status berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $err->getMessage(),
                ],
                500,
            );
        }
    }

    public function generateInvoice(Request $request)
    {
        DB::beginTransaction();
        try {
            $data = $request->all();

            $rules = [
                'subscription_id' => 'required|integer|exists:subscriptions,id',
            ];

            $validator = Validator::make($data, $rules, [
                'subscription_id.required' => 'Subscription ID harus diisi',
                'subscription_id.exists' => 'Subscription tidak ditemukan',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => 'error',
                    'message' => $validator->errors()->first(),
                ], 400);
            }

            $subscription = Subscription::with(['plan', 'user'])->findOrFail($data['subscription_id']);

            // Generate nomor invoice unik (format bisa disesuaikan, misal INV20231001-0001)
            $invoiceNumber = 'INV' . now()->format('Ymd') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);

            // Hitung amount dari plan
            $amount = $subscription->plan->price ?? 0;

            // Tentukan due date (misalnya 7 hari setelah tanggal buat invoice)
            $dueDate = now()->addDays(7);

            // Buat invoice
            $invoice = Invoice::create([
                'status' => 'unpaid',
                'invoice_number' => $invoiceNumber,
                'amount' => $amount,
                'invoice_period_start' => $subscription->current_period_start,
                'invoice_period_end' => $subscription->current_period_end,
                'due_date' => $dueDate,
                'subscription_id' => $subscription->id,
                'plan_id' => $subscription->plan_id,
                'user_id' => $subscription->user_id,
                'metadata' => json_encode([
                    'plan_id' => $subscription->plan->id ?? null,
                    'plan_name' => $subscription->plan->name ?? null,
                    'plan_price' => $subscription->plan->price ?? null,
                    'subscription_id' => $subscription->id,
                    'subscription_type' => $subscription->type,
                    'subscription_username' => $subscription->username ?? null,
                    'subscription_password' => $subscription->password ?? null,
                    'subscription_queue' => $subscription->queue ?? null,
                    'user_id' => $subscription->user->id ?? null,
                    'user_name' => $subscription->user->name ?? null,
                    'user_phone' => $subscription->user->phone ?? null,
                ]),
            ]);

            // 🔥 Update subscription.next_invoice_at ke 1 bulan setelah next_invoice_at lama
            $subscription->next_invoice_at = $subscription->next_invoice_at
                ? Carbon::parse($subscription->next_invoice_at)->addMonth()
                : now()->addMonth(); // fallback kalau null
            $subscription->save();

            DB::commit();

            // 🔔 Kirim notifikasi WA ke member
            $member = $subscription->user;
            $message = "Halo {$member->name},\n";
            $message .= "Invoice baru telah dibuat untuk subscription Anda.\n";
            $message .= "Nomor Invoice: {$invoiceNumber}\n";
            $message .= "Jumlah: Rp " . number_format($amount, 0, ',', '.') . "\n";
            $message .= "Paket : {$subscription->plan->name}\n";
            $message .= "Periode: " . Carbon::parse($invoice->invoice_period_start)
                ->timezone('Asia/Jakarta') // atur timezone ke WIB
                ->locale('id') // bahasa Indonesia
                ->translatedFormat('d M Y')
                . " s/d " . Carbon::parse($invoice->invoice_period_end)
                ->timezone('Asia/Jakarta') // atur timezone ke WIB
                ->locale('id') // bahasa Indonesia
                ->translatedFormat('d M Y') . "\n";
            $message .= "Jatuh tempo: " . Carbon::parse($dueDate)->format('d M Y') . "\n";
            $message .= "Silakan lakukan pembayaran tepat waktu. Terima kasih.";

            $payload = [
                "appkey" => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                "authkey" => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                "to" => preg_replace('/^08/', '628', $member->phone),
                "message" => $message,
            ];

            Http::post('https://app.saungwa.com/api/create-message', $payload);

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice berhasil dibuat',
                'data' => $invoice,
            ]);
        } catch (\Exception $err) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }


    // MANAGE DEVICE SUBSCRIPTION

    // 1. Tampilkan device yang digunakan subscription tertentu
    public function subscriptionDevices($subscriptionId)
    {
        $subscription = Subscription::with('devices')->find($subscriptionId);
        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found',
            ], 404);
        }


        return response()->json([
            'status' => 'success',
            'data' => $subscription,
        ]);
    }

    // 2. Tambahkan device ke subscription
    public function addDevice(Request $request, $subscriptionId)
    {
        $request->validate([
            'device_id' => 'required|exists:devices,id',
        ]);

        $subscription = Subscription::find($subscriptionId);
        if (!$subscription) {
            return response()->json([
                'status' => 'error',
                'message' => 'Subscription not found',
            ], 404);
        }

        // cek apakah sudah ada
        $exists = DeviceSubscription::where('subscription_id', $subscriptionId)
            ->where('device_id', $request->device_id)
            ->exists();

        if ($exists) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device already added to subscription',
            ], 400);
        }

        DeviceSubscription::create([
            'subscription_id' => $subscriptionId,
            'device_id' => $request->device_id,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Device added successfully',
        ]);
    }

    // 3. Hapus device dari subscription
    public function removeDevice($subscriptionId, $deviceId)
    {
        $deleted = DeviceSubscription::where('subscription_id', $subscriptionId)
            ->where('device_id', $deviceId)
            ->delete();

        if (!$deleted) {
            return response()->json([
                'status' => 'error',
                'message' => 'Device not found for subscription',
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Device removed successfully',
        ]);
    }
}
