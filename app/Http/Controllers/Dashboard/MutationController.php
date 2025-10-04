<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Mutation;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MutationController extends Controller
{
    public function index()
    {
        $title = "Mutasi Komisi";
        $user = Auth::user();
        $technicians = [];
        $pageUrl = "pages.dashboard.teknisi.mutation-commission";
        if ($user->role == "admin") {
            $pageUrl = "pages.dashboard.admin.mutation-commission";
            $technicians = User::where("role", "teknisi")->select("id", "name", "username")->orderBy("name", "asc")->get();
        }

        return view($pageUrl, compact("title", "technicians"));
    }

    public function requestWithdraw()
    {
        $user = User::where("id", auth()->user()->id)->first();
        $balance = $user->commission;
        $title = "Request Withdraw";
        $bank_type = $user->bank_type ?? "";
        $bank_account = $user->bank_account ?? "";

        return view("pages.dashboard.teknisi.request-wd", compact("title", "balance", "bank_type", "bank_account"));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {

            $query = Mutation::with('user');

            // filter by teknisi ID
            $user = auth()->user();
            if ($user->role == "teknisi") {
                $query->where('user_id', $user->id);
            }

            // filter code mutasi
            if ($request->query("search")) {
                $searchValue = $request->query("search")['value'];
                $query->where('code', 'like', '%' . $searchValue . '%');
            }

            // filter reseller dari dashboard admin
            if ($request->query('user_id') && $request->query('user_id') != '') {
                $query->where('user_id', strtoupper($request->query('user_id')));
            }

            // filter tipe
            if ($request->query("type") && $request->query('type') != "") {
                $query->where('type', $request->query('type'));
            }

            // filter tanggal awal - tanggal akhir per bulan saat ini
            $tglAwal = $request->query('tgl_awal');
            $tglAkhir = $request->query('tgl_akhir');

            if (!$tglAwal) {
                $tglAwal = Carbon::now('UTC')->startOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            if (!$tglAkhir) {
                $tglAkhir = Carbon::now('UTC')->endOfMonth()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            if ($request->query('tgl_awal') && $request->query('tgl_akhir')) {
                $tglAwal = Carbon::createFromFormat('d/m/Y', $request->query('tgl_awal'), 'UTC')->startOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
                $tglAkhir = Carbon::createFromFormat('d/m/Y', $request->query('tgl_akhir'), 'UTC')->endOfDay()->subHour(7)->toDateTimeString(); // dikurangi 7 jam mengikuti waktu utc
            }

            $query->whereBetween('created_at', [$tglAwal, $tglAkhir]);
            $recordsFiltered = $query->count();
            $data = $query->orderBy('id', 'desc')
                ->skip($request->query('start'))
                ->limit($request->query('length'))
                ->get();

            $user = auth()->user();
            $output = $data->map(function ($item) use ($user) {
                $item["amount"] = ((($item["type"] == "C" || $item["type"] == "R") && $item['amount'] > 0)) ? "<span class='text-success'>+ Rp. " . number_format($item->amount, 0, ',', '.') . "</span>" : "<span class='text-danger'>- Rp. " . number_format($item->amount, 0, ',', '.') . "</span>";
                $item["first_commission"] = "Rp. "  . number_format($item->first_commission, 0, ',', '.');
                $item["last_commission"] = "Rp. "  . number_format($item->last_commission, 0, ',', '.');
                $item['created'] = Carbon::parse($item->created_at)->addHours(7)->format('Y-m-d H:i:s');
                $item['updated'] = Carbon::parse($item->updated_at)->addHours(7)->format('Y-m-d H:i:s');
                if ($item['created'] == $item['updated']) {
                    $item['updated'] = '';
                }

                if ($user->role == "admin") {
                    $action_process = ($item->status == "PENDING" && $item->type == "W") ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"PROCESS\");' href='javascript:void(0);' title='In Process'>In Process</a>" : "";
                    $action_success = ($item->status == "PENDING" && $item->type == "W") || ($item->status == "PROCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"SUCCESS\");' href='javascript:void(0);' title='Success'>Success</a>" : "";
                    $action_reject = ($item->status == "PENDING" && $item->type == "W") || ($item->status == "PROCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"REJECT\");' href='javascript:void(0);' title='Reject'>Reject</a>" : "";
                    $action_reason = ($item->status == "REJECT" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_detail = ($item->status == "PENDING" && $item->type == "W") || ($item->status == "PROCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>" : "";
                    $action_show_proof_of_payment = ($item->status == "SUCCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";

                    $action = " <div class='dropdown-primary dropdown open'>
                                <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                    Aksi
                                </button>
                                <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                    " . $action_detail . "
                                    " . $action_process . "
                                    " . $action_success . "
                                    " . $action_reject . "
                                    " . $action_reason . "
                                    " . $action_show_proof_of_payment . "
                                </div>
                            </div>";

                    if ($item->status == "CANCEL" || $item->type == "C" || $item->type == "R") {
                        $action = "-";
                    }
                    $item["action"] = $action;

                    $teknisi = "<small> 
                                <strong>Nama</strong> :" . ($item->user ? $item->user->name : 'Teknisi Deleted') .  "
                                <br>
                                <strong>Username</strong> :" . ($item->user ? $item->user->username : 'Teknisi Deleted') . "
                                <br>
                            </small>";
                    $item["teknisi"] = $teknisi;
                } else {
                    $action_cancel = ($item->status == "PENDING" && $item->type == "W") ? "<a class='dropdown-item' onclick='return changeStatus(\"{$item->id}\", \"CANCEL\");' href='javascript:void(0);' title='Cancel'>Cancel</a>" : "";
                    $action_reason =  ($item->status == "REJECT" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-REASON-REJECT\");' href='javascript:void(0);' title='Alasan Ditolak'>Alasan Ditolak</a>" : "";
                    $action_detail = ($item->status == "PENDING" && $item->type == "W") || ($item->status == "PROCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"DETAIL\");' href='javascript:void(0);' title='Detail'>Detail</a>" : "";
                    $action_show_proof_of_payment = ($item->status == "SUCCESS" && $item->type == "W") ? "<a class='dropdown-item' onclick='return getData(\"{$item->id}\", \"SHOW-PROOF-PAYMENT\");' href='javascript:void(0);' title='Bukti Refund'>Bukti Transfer</a>" : "";
                    $action = " <div class='dropdown-primary dropdown open'>
                                    <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                        Aksi
                                    </button>
                                    <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                        " . $action_detail . "
                                        " . $action_cancel . "
                                        " . $action_reason . "
                                        " . $action_show_proof_of_payment . "
                                    </div>
                                </div>";

                    if ($item->status == "CANCEL" || $item->type == "C" || $item->type == "R") {
                        $action = "";
                    }
                    $item["action"] = $action;
                }

                switch ($item["type"]) {
                    case "C":
                        $item["type"] = "<span class='badge badge-success'>COMMISSON</span>";
                        break;
                    case "W":
                        $item["type"] = "<span class='badge badge-info'>WITHDRAW</span>";
                        break;
                    case "R":
                        $item["type"] = "<span class='badge badge-secondary'>REFUND</span>";
                        break;
                    default:
                        $item["type"] = "<span class='badge badge-error'>UNKNOWN</span>";
                        break;
                }

                $classStatus = "";
                switch ($item["status"]) {
                    case "PENDING":
                        $classStatus = "badge-info";
                        break;
                    case "PROCESS":
                        $classStatus = "badge-primary";
                        break;
                    case "SUCCESS":
                        $classStatus = "badge-success";
                        break;
                    case "REJECT":
                        $classStatus = "badge-danger";
                        break;
                    case "CANCEL":
                        $classStatus = "badge-warning";
                }
                $target = "<small> 
                                <strong>Bank</strong> :" . $item->bank_name .  "
                                <br>
                                <strong>Rekening</strong> :" . $item->bank_account . "
                                <br>
                            </small>";

                $item["status"] = "<span class='badge " . $classStatus . "'>" . $item["status"] . "</span>";
                $item["target"] = $target;


                unset($item["user"]);
                return $item;
            });

            $queryTotal = Mutation::whereBetween('created_at', [$tglAwal, $tglAkhir]);
            if ($user->role == "teknisi") {
                $queryTotal->where('user_id', $user->id);
            }

            $total = $queryTotal->count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output
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


    // HANDLER COMMISSION
    public function create(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "amount" => "integer|min:100000|max:1000000|required",
                "bank_name" => "string|required",
                "bank_account" => "string|required"
            ];

            $messages = [
                "amount.integer" => "Nominal Penarikan tidak valid",
                "amount.min" => "Nominal Penarikan minimal Rp.100.000",
                "amount.max" => "Nominal Penarikan maksimal Rp.1.000.000",
                "amount.requred" => "Nominal Penarikan harus diisi",
                "bank_name.required" => "Nama Bank harus diisi",
                "bank_account" => "No Rekening harus diisi"
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::where("id", auth()->user()->id)->first();
            // CEK KOMISI RESELLER
            $totalAmount = $data["amount"];
            if ($user->commission < $totalAmount) {
                return response()->json([
                    "status" => "error",
                    "message" => "Mohon maaf saldo anda tidak mencukupi"
                ], 400);
            }

            // UPDATE KOMISI RESELLER
            $firstCommission = $user->commission;
            $updatedCommission = $firstCommission - $totalAmount;
            $dataToUpdate = [
                "commission" => $updatedCommission
            ];
            $user->update($dataToUpdate);

            // SIMPAN MUTASI WITHDRAW
            Mutation::create([
                'code' => "WIDHR" . strtoupper(Str::random(5)),
                'amount' => $totalAmount,
                'type' => 'W',
                'first_commission' => $firstCommission,
                'last_commission' => $updatedCommission,
                "bank_name" => $data["bank_name"],
                "bank_account" => $data["bank_account"],
                'status' => 'PENDING',
                'user_id' => $user->id,
                "remark" => $data["notes"]
            ]);

            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Penarikan berhasil diajukan, silahkan tunggu admin untuk memproses"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function getDetail($id)
    {
        try {
            $data = Mutation::where('id', $id)->first();

            if (!$data) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            $data["proof_of_payment"] = $data->proof_of_payment ? Storage::url($data->proof_of_payment) : null;
            $data["amount"] = ' Rp. ' . number_format($data->amount, 0, ',', '.');

            return response()->json([
                "status" => "success",
                "data" => $data
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }

    public function changeStatus(Request $request)
    {
        try {
            DB::beginTransaction();
            $data = $request->all();
            $rules = [
                "id" => "required|integer",
                "status" => "required|string|in:PROCESS,SUCCESS,REJECT,CANCEL",
                "proof_of_payment" => "nullable",
                "remark" => "nullable"
            ];

            if ($request->file('proof_of_payment')) {
                $rules['proof_of_payment'] .= '|image|max:10240|mimes:giv,svg,jpeg,png,jpg';
            }

            $messages = [
                "id.required" => "Data Penarikan harus dipilih",
                "id.integer" => "Type Penarikan tidak valid",
                "status.required" => "Status harus diisi",
                "status.in" => "Status tidak sesuai",
                "proof_of_payment.image" => "Gambar yang di upload tidak valid",
                "proof_of_payment.max" => "Ukuran gambar maximal 10MB",
                "proof_of_payment.mimes" => "Format gambar harus gif/svg/jpeg/png/jpg",
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            // VALIDASI HAK AKSES PERUBAHAN STATUS
            $user = auth()->user();
            $accessAdmin = ["PROCESS", "SUCCESS", "REJECT"];
            if (in_array($data["status"], $accessAdmin) && $user->role != "admin") {
                return response()->json([
                    "status" => "error",
                    "message" => "Opps. anda tidak memiliki akses"
                ], 403);
            }

            $mutation = Mutation::where('id', $data['id'])
                ->where('type', 'W')
                ->first();
            if (!$mutation) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Penarikan tidak ditemukan !"
                ], 404);
            }

            // VALIDASI TRX . JIKA SUDAH REJECT / CANCEL TIDAK BOLEH DIUBAH LAGI STATUSNYA
            if (in_array($mutation->status, ['REJECT', 'CANCEL'])) {
                return response()->json([
                    "status" => "error",
                    "message" => "Status Penarikan sudah tidak bisa diubah"
                ], 400);
            }

            $teknisi = User::find($mutation->user_id);
            if (!$teknisi) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data Teknisi atas transaksi ini tidak ditemukan"
                ], 404);
            }

            // JIKA REJECT / CANCEL . REFUND SALDO KOMISI RESELLER DAN BUAT DATA MUTASI REFUND
            if (in_array($data["status"], ["REJECT", "CANCEL"])) {
                $firstCommission = $teknisi->commission;
                $lastCommission = $firstCommission + $mutation->amount;

                // SIMPAN MUTASI KOMISI
                $dataMutasi = [
                    "code" => "REFUND" . strtoupper(Str::random(5)),
                    "amount" => $mutation->amount,
                    "type" => "R", // refund,
                    "first_commission" => $firstCommission,
                    "last_commission" => $lastCommission,
                    'bank_name' => '-',
                    'bank_account' => '-',
                    "user_id" => $teknisi->id,
                    'status' => "SUCCESS"
                ];
                Mutation::create($dataMutasi);
                // UPDATE SALDO RESELLER
                $updateReseller = ["commission" => $lastCommission];
                $teknisi->update($updateReseller);
            }

            // SIMPAN BUKTI BAYAR JIKA ADA
            unset($data["proof_of_payment"]);
            if ($request->file("proof_of_payment")) {
                $data["proof_of_payment"] = $request->file("proof_of_payment")->store("assets/commission", "public");
            }

            $mutation->update($data);
            DB::commit();
            return response()->json([
                "status" => "success",
                "message" => "Status Transaksi berhasil diperbarui"
            ]);
        } catch (\Throwable $err) {
            DB::rollBack();
            if ($request->file("proof_of_payment")) {
                $uploadedImg = "public/assets/commission/" . $request->file("proof_of_payment")->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage(),
            ], 500);
        }
    }
}
