<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceFaq;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceFaqController extends Controller
{
    public function index()
    {
        $title = 'Data FAQ Device';
        $devices = Device::all();
        return view('pages.dashboard.admin.device-faq', compact('title', 'devices'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = DeviceFaq::with('device:id,name') // relasi ke devices
                ->select('id', 'device_id', 'question', 'answer', 'order', 'created_at');

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query->where('question', 'like', '%' . $searchValue . '%')->orWhere('answer', 'like', '%' . $searchValue . '%');
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy('device_id', 'asc')->skip($request->query('start'))->limit($request->query('length'))->get();

            $output = $data->map(function ($item) {
                $action = "
                    <div class='dropdown-primary dropdown open'>
                        <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light'
                            id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                            Aksi
                        </button>
                        <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                            <a class='dropdown-item' onclick='return getData(\"{$item->id}\");'
                                href='javascript:void(0);' title='Edit'>Edit</a>
                            <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");'
                                href='javascript:void(0);' title='Hapus'>Hapus</a>
                        </div>
                    </div>
                ";

                $item['device_name'] = $item->device->name ?? '-';
                $item['action'] = $action;
                $item['created_at_formatted'] = $item->created_at ? Carbon::parse($item->created_at)->locale('id')->translatedFormat('d M Y H:i') : '-';
                return $item;
            });

            $total = DeviceFaq::count();
            return response()->json([
                'draw' => $request->query('draw'),
                'recordsFiltered' => $recordsFiltered,
                'recordsTotal' => $total,
                'data' => $output,
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

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'device_id' => 'required|exists:devices,id',
                'question' => 'required|string',
                'answer' => 'required|string',
                'order' => 'nullable|integer',
            ];

            $messages = [
                'device_id.required' => 'Device harus dipilih',
                'device_id.exists' => 'Device tidak valid',
                'question.required' => 'Pertanyaan harus diisi',
                'answer.required' => 'Jawaban harus diisi',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                    ],
                    400,
                );
            }
            DeviceFaq::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil tambah data FAQ',
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

    public function update(Request $request)
    {
        try {
            $data = $request->all();

            $rules = [
                'id' => 'required|integer',

                'device_id' => 'required|exists:devices,id',
                'question' => 'required|string',
                'answer' => 'required|string',
                'order' => 'nullable|integer',
            ];

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'device_id.required' => 'Device harus dipilih',
                'device_id.exists' => 'Device tidak valid',
                'question.required' => 'Pertanyaan harus diisi',
                'answer.required' => 'Jawaban harus diisi',
            ];

            $validator = Validator::make($data, $rules, $messages);
            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                    ],
                    400,
                );
            }

            $faq = DeviceFaq::where('id', $data['id'])->first();

            if (!$faq) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data FAQ tidak ditemukan',
                    ],
                    404,
                );
            }

            $faq->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil update data FAW',
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

    public function getDetail($id)
    {
        try {
            $faq = DeviceFaq::find($id);

            if (!$faq) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            return response()->json([
                'status' => 'success',
                'data' => $faq,
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

    public function destroy(Request $request)
    {
        try {
            $validator = Validator::make(
                $request->all(),
                ['id' => 'required|integer'],
                [
                    'id.required' => 'Data ID harus diisi',
                    'id.integer' => 'Type ID tidak valid',
                ],
            );

            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                    ],
                    400,
                );
            }

            $id = $request->id;
            $faq = DeviceFaq::find($id);
            if (!$faq) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data FAQ ditemukan',
                    ],
                    404,
                );
            }
            $faq->delete();

            return response()->json(['status' => 'success', 'message' => 'FAQ berhasil dihapus']);
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
