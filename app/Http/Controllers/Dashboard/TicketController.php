<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class TicketController extends Controller
{
    public function index()
    {
        $title = 'Manajemen Tiket';
        $technicians = [];
        $members = [];
        $user = auth()->user();
        if ($user->role == "admin") {
            $pageUrl = "pages.dashboard.admin.ticket";
            $members = User::where('role', 'member')->get();
            $technicians = User::where('role', 'teknisi')->get();
        } else if ($user->role == "teknisi") {
            $pageUrl = "pages.dashboard.teknisi.ticket";
            $members = User::where('role', 'member')->get();
        } else {
            $pageUrl = "pages.dashboard.member.ticket";
        }
        return view($pageUrl, compact('title', 'members', 'technicians'));
    }

    // DATA TABLE
    public function dataTable(Request $request)
    {
        try {
            $user = auth()->user();

            $query = Ticket::with(['member:id,name', 'technician:id,name']);

            // 🔹 Filter role
            if ($user->role === 'member') {
                $query->where('member_id', $user->id);
            } elseif ($user->role === 'teknisi') {
                $query->where(function ($q) use ($user) {
                    $q->where('technician_id', $user->id)
                        ->orWhereNull('technician_id'); // hanya ini dan milik dia
                });
            }
            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query->where('cases', 'like', '%' . $searchValue . '%')->orWhere('solution', 'like', '%' . $searchValue . '%');
                });
            }

            // filter teknisi dari dashboard admin
            if ($request->query('technician_id') && $request->query('technician_id') != '') {
                $query->where('technician_id', strtoupper($request->query('technician_id')));
            }

            // filter member dari dashboard admin
            if ($request->query('member_id') && $request->query('member_id') != '') {
                $query->where('member_id', strtoupper($request->query('member_id')));
            }

            // filter type
            if ($request->query('type') && $request->query('type') != '') {
                $query->where('type', $request->query('type'));
            }

            if ($request->query("sort_by") && $request->query("sort_type")) {
                $query->orderBy($request->query("sort_by"), $request->query("sort_type"));
            } else {
                $query->orderBy('id', 'desc');
            }

            $recordsFiltered = $query->count();
            $data = $query->skip($request->query('start'))->limit($request->query('length'))->get();

            $output = $data->map(function ($item) use ($user) {
                $action = "";
                if ($user->role == "admin") {
                    $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle' id='dropdown-{$item->id}' data-toggle='dropdown'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu'>
                                    <a class='dropdown-item' onclick='return showDetail(\"{$item->id}\");'>Detail</a>
                                    <a class='dropdown-item' onclick='return getData(\"{$item->id}\");'>Edit</a>
                                    <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");'>Hapus</a>
                                </div>
                            </div>";
                } else if ($user->role == "member") {
                    $editTicket = $item->status == "open" && $item->created_by == $user->id ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\");'>Edit</a>" : '';
                    $deleteTicket = $item->status == "open" && $item->created_by == $user->id ?  "<a class='dropdown-item' onclick='return removeData(\"{$item->id}\");'>Hapus</a>" : '';

                    $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle' id='dropdown-{$item->id}' data-toggle='dropdown'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu'>
                                    <a class='dropdown-item' onclick='return showDetail(\"{$item->id}\");'>Detail</a>
                                    " . $editTicket . "
                                    " . $deleteTicket . "
                                </div>
                            </div>";
                } else if ($user->role == "teknisi") {
                    $claimTicket = $item->technician_id == null && $item->status == "open" ?  "<a class='dropdown-item' onclick='return claimTicket(\"{$item->id}\");'>Klaim Tiket</a>" : '';
                    // Tombol Process (buka modal update progress)
                    $processTicket = $item->status != "success" && $item->technician_id == $user->id ? "<a class='dropdown-item' onclick='return openUpdateProgress(\"{$item->id}\", \"{$item->status}\", `" . addslashes($item->solution ?? '') . "`);'>Process</a>" : "";

                    $action = "<div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle' id='dropdown-{$item->id}' data-toggle='dropdown'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu'>
                                    <a class='dropdown-item' onclick='return showDetail(\"{$item->id}\");'>Detail</a>
                                    " . $processTicket . "
                                    " . $claimTicket . "
                                </div>
                            </div>";
                }

                $statusClass = match ($item->status) {
                    'open' => 'badge badge-secondary',
                    'inprogress' => 'badge badge-info',
                    'success' => 'badge badge-success',
                    'reject' => 'badge badge-warning',
                    'failed' => 'badge badge-danger',
                    default => 'badge badge-light',
                };

                $status = "<span class='{$statusClass}'>" . strtoupper($item->status) . '</span>';

                $item['action'] = $action;
                $item['status'] = $status;
                $item['member_name'] = $item->member ? $item->member->name ?? '-' : '-';
                $item['technician_name'] = $item->technician ? $item->technician->name ?? '-' : '-';
                $item['type'] = ucfirst($item->type);
                $item['created_at_formatted'] = $item->created_at
                    ? Carbon::parse($item->created_at)
                    ->timezone('Asia/Jakarta') // atur timezone ke WIB
                    ->locale('id') // bahasa Indonesia
                    ->translatedFormat('d M Y H:i')
                    : '-';
                return $item;
            });


            $queryTotal = Ticket::query();
            if ($user->role === "member") {
                $queryTotal->where('member_id', $user->id);
            } elseif ($user->role === "teknisi") {
                $queryTotal->where(function ($q) use ($user) {
                    $q->where('technician_id', $user->id)
                        ->orWhereNull('technician_id');
                });
            }
            $total = $queryTotal->count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output,
                'query' => $request->query('technician_id')
            ]);
        } catch (\Throwable $err) {
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $err->getMessage(),
                    'draw' => $request->query('draw'),
                    'recordsFiltered' => 0,
                    'recordsTotal' => 0,
                    'data' => [],
                ],
                500,
            );
        }
    }

    public function getDetail($id)
    {
        try {
            $ticket = Ticket::with(['member:id,name', 'technician:id,name'])->find($id);
            if (!$ticket) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            return response()->json(['status' => 'success', 'data' => $ticket]);
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

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'type' => 'required|in:gangguan,maintenance,pemasangan,troubleshoot,lain-lain',
                'status' => 'nullable|in:open,inprogress,success,reject,failed',
                'cases' => 'required|string',
                'member_id' => 'nullable|exists:users,id',
                'technician_id' => 'nullable|exists:users,id',
                'subscription_id' => 'nullable|exists:subscriptions,id',
                'complaint_image' => 'nullable',
            ];

            if ($request->file('complaint_image')) {
                $rules['complaint_image'] .= '|image|max:2048|mimes:jpeg,jpg,png';
            }

            $messages = [
                'type.required' => 'Tipe tiket harus diisi',
                'type.in' => 'Tipe tiket tidak valid',
                'status.in' => 'Status tiket tidak valid',
                'cases.required' => 'Kasus/keluhan harus diisi',
                'member_id.exists' => 'Member tidak ditemukan',
                'technician_id.exists' => 'Teknisi tidak ditemukan',
                'subscription_id.exists' => 'Subscription tidak ditemukan',
                'complaint_image.image' => 'Gambar yang diupload tidak valid',
                'complaint_image.max' => 'Ukuran gambar maksimal 2MB',
                'complaint_image.mimes' => 'Format gambar harus jpeg/jpg/png',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
            }

            // Inject created_by
            $data['created_by'] = auth()->id();

            // Jika role nya member → paksa member_id = user login
            if (auth()->user()->role === 'member') {
                $data['member_id'] = auth()->id();
                $data['technician_id'] = null;
            }

            // Handle upload complaint_image
            if ($request->file('complaint_image')) {
                $data['complaint_image'] = $request->file('complaint_image')->store('assets/tickets', 'public');
            }

            unset($data['id']); // prevent injected id

            $ticket = Ticket::create($data);

            // 🔔 Kirim notifikasi WA ke teknisi jika ada technician_id
            if (!empty($data['technician_id'])) {
                $technician = User::find($data['technician_id']);
                if ($technician) {
                    $message = "Halo {$technician->name},\n";
                    $message .= "Anda memiliki tiket baru:\n";
                    $message .= "Tipe: {$ticket->type}\n";
                    $message .= "Kasus/Tiket: {$ticket->cases}\n";
                    $message .= "Silakan segera follow up.\nTerima kasih.";

                    $payload = [
                        "appkey" => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                        "authkey" => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                        "to" => preg_replace('/^08/', '628', $technician->phone),
                        "message" => $message,
                    ];

                    Http::post('https://app.saungwa.com/api/create-message', $payload);
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Tiket berhasil dibuat',
            ]);
        } catch (\Exception $err) {
            if ($request->file('complaint_image')) {
                $uploadedImg = 'public/assets/tickets/' . $request->complaint_image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json(['status' => 'error', 'message' => $err->getMessage()], 500);
        }
    }

    public function update(Request $request)
    {
        try {
            $user = auth()->user();
            $data = $request->all();
            $rules = [
                'id' => 'required|integer|exists:tickets,id',
                'cases' => 'nullable|string',
                'solution' => 'nullable|string',
                'status' => 'nullable|in:open,inprogress,success,reject,failed',
                'member_id' => 'nullable|exists:users,id',
                'technician_id' => 'nullable|exists:users,id',
                'subscription_id' => 'nullable|exists:subscriptions,id',
                'complaint_image' => 'nullable',
            ];

            if ($request->file('complaint_image')) {
                $rules['complaint_image'] .= '|image|max:2048|mimes:jpeg,jpg,png';
            }

            $messages = [
                'id.required' => 'ID tiket harus diisi',
                'id.integer' => 'ID tiket tidak valid',
                'id.exists' => 'Tiket tidak ditemukan',
                'status.in' => 'Status tiket tidak valid',
                'member_id.exists' => 'Member tidak ditemukan',
                'technician_id.exists' => 'Teknisi tidak ditemukan',
                'subscription_id.exists' => 'Subscription tidak ditemukan',
                'complaint_image.image' => 'Gambar tidak valid',
                'complaint_image.max' => 'Ukuran gambar maksimal 2MB',
                'complaint_image.mimes' => 'Format gambar harus jpeg/jpg/png',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
            }

            $ticket = Ticket::find($data['id']);
            if (!$ticket) {
                return response()->json(['status' => 'error', 'message' => 'Tiket tidak ditemukan'], 404);
            }

            // jika role == member dan tiket sudah tidak open. tidak boleh di update
            if ($user->role == 'member' && $ticket->status != 'open') {
                return response()->json(['status' => 'error', 'message' => 'Tiket sudah tidak dalam status open dan tidak dapat diubah.'], 400);
            }

            // Handle complaint_image baru
            unset($data['complaint_image']);
            if ($request->file('complaint_image')) {
                $oldImagePath = 'public/' . $ticket->complaint_image;
                if ($ticket->complaint_image && Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data['complaint_image'] = $request->file('complaint_image')->store('assets/tickets', 'public');
            }

            $ticket->update($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Tiket berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            if ($request->file('complaint_image')) {
                $uploadedImg = 'public/assets/tickets/' . $request->complaint_image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json(['status' => 'error', 'message' => $err->getMessage()], 500);
        }
    }

    // Teknisi claim tiket
    public function claim(Request $request)
    {
        try {
            $id = $request->id;
            $ticket = Ticket::find($id);
            if (!$ticket) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }
            if ($ticket->technician_id) {
                return response()->json(['status' => 'error', 'message' => 'Tiket sudah pernah di-claim'], 400);
            }

            $ticket['technician_id'] = auth()->id();
            $ticket['status'] = 'inprogress';
            $ticket->save();

            return response()->json(['status' => 'success', 'message' => 'Tiket berhasil di-claim']);
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

    // Update progress (teknisi/admin)
    public function updateProgress(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'ticket_id' => 'required|integer|exists:tickets,id',
                'status' => 'required|in:open,inprogress,success,reject,failed',
                'solution' => 'nullable|string',
                'completion_image' => 'nullable|image|max:2048|mimes:jpeg,jpg,png',
            ];

            if ($request->file('completion_image')) {
                $rules['completion_image'] .= '|image|max:2048|mimes:jpeg,jpg,png';
            }

            $messages = [
                'ticket_id.required' => 'ID tiket harus diisi',
                'ticket_id.integer' => 'ID tiket tidak valid',
                'ticket_id.exists' => 'Tiket tidak ditemukan',
                'status.in' => 'Status tiket tidak valid',
                'subscription_id.exists' => 'Subscription tidak ditemukan',
                'completion_image.image' => 'Gambar tidak valid',
                'completion_image.max' => 'Ukuran gambar maksimal 2MB',
                'completion_image.mimes' => 'Format gambar harus jpeg/jpg/png',
            ];
            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
            }

            $ticket = Ticket::find($data["ticket_id"]);
            if (!$ticket) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            $data = $request->only(['status', 'solution']);
            if ($request->file('completion_image')) {
                if ($ticket->completion_image && Storage::exists('public/' . $ticket->completion_image)) {
                    Storage::delete('public/' . $ticket->completion_image);
                }
                $data['completion_image'] = $request->file('completion_image')->store('assets/tickets', 'public');
            }

            $ticket->update($data);

            return response()->json(['status' => 'success', 'message' => 'Progress tiket berhasil diperbarui']);
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

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), ['id' => 'required|integer']);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => $validator->errors()->first()], 400);
            }

            $ticket = Ticket::find($request->id);
            if (!$ticket) {
                return response()->json(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
            }

            if ($ticket->complaint_image && Storage::exists('public/' . $ticket->complaint_image)) {
                Storage::delete('public/' . $ticket->complaint_image);
            }
            if ($ticket->completion_image && Storage::exists('public/' . $ticket->completion_image)) {
                Storage::delete('public/' . $ticket->completion_image);
            }

            $ticket->delete();

            return response()->json(['status' => 'success', 'message' => 'Tiket berhasil dihapus']);
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
}
