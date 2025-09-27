<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Area;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AreaController extends Controller
{
    public function index()
    {
        $title = 'Master Area';
        return view('pages.dashboard.admin.area', compact('title'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        $query = Area::query();

        if ($request->query('search')) {
            $searchValue = $request->query('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', '%' . $searchValue . '%')
                    ->orWhere('code', 'like', '%' . $searchValue . '%')
                    ->orWhere('description', 'like', '%' . $searchValue . '%');
            });
        }

        $recordsFiltered = $query->count();
        $data = $query->orderBy('id', 'desc')->skip($request->query('start'))->limit($request->query('length'))->get();

        $output = $data->map(function ($item) {
            $action = " <div class='dropdown-primary dropdown open'>
                            <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                Aksi
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                            </div>
                        </div>";

            $item['name'] = '<p>' . e($item->name) . '</p>';
            $item['code'] = '<p>' . e($item->code ?? '-') . '</p>';
            $item['description'] = '<p>' . Str::limit(strip_tags($item->description), 100) . '</p>';
            $item['meta'] = '<p>' . e($item->meta ? json_encode($item->meta) : '-') . '</p>';
            $item['action'] = $action;

            return $item;
        });

        $total = Area::count();
        return response()->json([
            'draw' => $request->query('draw'),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $total,
            'data' => $output,
        ]);
    }

    public function getDetail($id)
    {
        try {
            $area = Area::find($id);

            if (!$area) {
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
                'data' => $area,
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

    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'name' => 'required|string|unique:areas,name',
                'code' => 'nullable|string',
                'description' => 'nullable|string',
                'meta' => 'nullable|json',
            ];

            $messages = [
                'name.required' => 'Nama area harus diisi',
                'name.unique' => 'Nama area sudah digunakan',
                'meta.json' => 'Meta harus berupa format JSON valid',
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

            unset($data['id']);
            Area::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dibuat',
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
                'id' => 'required|integer|exists:areas,id',
                'name' => 'required|string|unique:areas,name,' . $request->id,
                'code' => 'nullable|string',
                'description' => 'nullable|string',
                'meta' => 'nullable|json',
            ];

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'name.required' => 'Nama area harus diisi',
                'name.unique' => 'Nama area sudah digunakan',
                'meta.json' => 'Meta harus berupa format JSON valid',
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

            $area = Area::find($data['id']);
            if (!$area) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $area->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui',
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
            $area = Area::find($id);
            if (!$area) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $area->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dihapus',
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
}
