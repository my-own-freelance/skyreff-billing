<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PlanController extends Controller
{
    public function index()
    {
        $title = 'Master Plan';
        return view('pages.dashboard.admin.plan', compact('title'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = Plan::query();

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')->orWhere('description', 'like', '%' . $searchValue . '%');
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

                $is_active =
                    $item->is_active == 'Y'
                        ? '
                <div class="text-center">
                    <span class="label-switch">Publish</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status on">
                        <input type="checkbox" onclick="return updateStatus(\'' .
                            $item->id .
                            '\', \'Draft\');" />
                        <span class="slider"></span>
                    </div>
                </div>'
                        : '<div class="text-center">
                    <span class="label-switch">Draft</span>
                </div>
                <div class="input-row">
                    <div class="toggle_status off">
                        <input type="checkbox" onclick="return updateStatus(\'' .
                            $item->id .
                            '\', \'Publish\');" />
                        <span class="slider"></span>
                    </div>
                </div>';

                $image = $item->image
                    ? '<div class="thumbnail">
                        <div class="thumb">
                            <img src="' .
                        Storage::url($item->image) .
                        '" alt="" width="250px" height="250px"
                            class="img-fluid img-thumbnail" alt="' .
                        $item->name .
                        '">
                        </div>
                    </div>'
                    : '-';

                $name = '<p>' . Str::limit(strip_tags($item->name), 100) . '</p>';
                $description = '<p>' . Str::limit(strip_tags($item->description), 150) . '</p>';
                $price = number_format($item->price, 0, ',', '.');

                $item['action'] = $action;
                $item['is_active'] = $is_active;
                $item['image'] = $image;
                $item['name'] = $name;
                $item['description'] = $description;
                $item['price'] = 'Rp ' . $price;
                return $item;
            });

            $total = Plan::count();
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

    public function getDetail($id)
    {
        try {
            $plan = Plan::find($id);

            if (!$plan) {
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
                'data' => $plan,
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
                'name' => 'required|string',
                'price' => 'required|integer|min:0',
                'level' => 'required|integer|min:1|max:5',
                'image' => 'nullable|image|max:2048|mimes:gif,svg,jpeg,png,jpg',
                'description' => 'nullable|string',
                'features' => 'nullable|array',
                'is_active' => 'required|string|in:Y,N',
            ];

            $messages = [
                'name.required' => 'Nama harus diisi',
                'price.required' => 'Harga harus diisi',
                'level.required' => 'Level harus diisi',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
                'image.image' => 'Gambar yang diupload tidak valid',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'image.mimes' => 'Format gambar harus gif/svg/jpeg/png/jpg',
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

            if ($request->file('image')) {
                $data['image'] = $request->file('image')->store('assets/plan', 'public');
            }
            if (isset($data['features']) && is_array($data['features'])) {
                $data['features'] = json_encode($data['features']);
            }

            Plan::create($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dibuat',
            ]);
        } catch (\Exception $err) {
            if ($request->file('image')) {
                $uploadedImg = 'public/assets/plan/' . $request->image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
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
                'name' => 'required|string',
                'price' => 'required|integer|min:0',
                'level' => 'required|integer|min:1|max:5',
                'image' => 'nullable',
                'description' => 'nullable|string',
                'features' => 'nullable|array',
                'is_active' => 'required|string|in:Y,N',
            ];

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:2048|mimes:gif,svg,jpeg,png,jpg';
            }

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'name.required' => 'Nama harus diisi',
                'price.required' => 'Harga harus diisi',
                'level.required' => 'Level harus diisi',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
                'image.image' => 'Gambar yang diupload tidak valid',
                'image.max' => 'Ukuran gambar maksimal 2MB',
                'image.mimes' => 'Format gambar harus gif/svg/jpeg/png/jpg',
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

            $plan = Plan::find($data['id']);
            if (!$plan) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            unset($data['image']);
            if ($request->file('image')) {
                $oldImagePath = 'public/' . $plan->image;
                if ($plan->image && Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data['image'] = $request->file('image')->store('assets/plan', 'public');
            }

            if (isset($data['features']) && is_array($data['features'])) {
                $data['features'] = json_encode($data['features']);
            }

            $plan->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            if ($request->file('image')) {
                $uploadedImg = 'public/assets/plan/' . $request->image->hashName();
                if (Storage::exists($uploadedImg)) {
                    Storage::delete($uploadedImg);
                }
            }
            return response()->json(
                [
                    'status' => 'error',
                    'message' => $err->getMessage(),
                ],
                500,
            );
        }
    }

    public function updateStatus(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'id' => 'required|integer',
                'is_active' => 'required|string|in:Y,N',
            ];

            $validator = Validator::make($data, $rules, [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
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

            $plan = Plan::find($data['id']);
            if (!$plan) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }
            $plan->update($data);
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
            $plan = Plan::find($id);
            if (!$plan) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $oldImagePath = 'public/' . $plan->image;
            if ($plan->image && Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }

            $plan->delete();
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
