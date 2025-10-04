<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Device;
use App\Models\DeviceFaq;
use App\Models\DeviceSubscription;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class DeviceController extends Controller
{
    public function index()
    {
        $title = 'Master Device';
        $pageUrl = "pages.dashboard.admin.device";
        if (auth()->user()->role == "member") {
            $pageUrl = "pages.dashboard.member.device";
        }
        return view($pageUrl, compact('title'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = Device::query();

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query->where('name', 'like', '%' . $searchValue . '%')->orWhere('excerpt', 'like', '%' . $searchValue . '%');
                });
            }

            $user = auth()->user();
            $deviceSubscriptionIds = [];
            if ($user->role == "member") {
                $subscription = Subscription::where('user_id', $user->id)->first();

                if (!$subscription) {
                    return response()->json(
                        [
                            'status' => 'error',
                            'message' => "Anda belum memiliki paket",
                            'draw' => $request->query('draw'),
                            'recordsFiltered' => 0,
                            'recordsTotal' => 0,
                            'data' => [],
                        ]
                    );
                }

                $deviceSubscriptionIds = DeviceSubscription::where('subscription_id', $subscription->id)->pluck('device_id')->toArray();
                $query->whereIn('id', $deviceSubscriptionIds)->where('is_active', 'Y');
            }


            $recordsFiltered = $query->count();
            $data = $query->orderBy('id', 'desc')->skip($request->query('start'))->limit($request->query('length'))->get();

            $output = $data->map(function ($item) use ($user) {
                $action = " <div class='dropdown-primary dropdown open'>
                            <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                Aksi
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                <a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>
                            </div>
                        </div>";

                if ($user->role == "member") {
                    // BUTTON ACTION DETAIL DEVICE
                    $action = "<a class='btn btn-sm btn-primary' href='" . route('device.detail-spesific', $item->id) . "' title='Detail'>Detail</a>";
                }

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
                $excerpt = '<p>' . Str::limit(strip_tags($item->excerpt), 150) . '</p>';
                $item['action'] = $action;
                $item['is_active'] = $is_active;
                $item['image'] = $image;
                $item['name'] = $name;
                $item['excerpt'] = $excerpt;
                return $item;
            });

            $queryTotal = Device::query();
            if ($user->role == "member") {
                $queryTotal->whereIn('id', $deviceSubscriptionIds)->where('is_active', 'Y');
            }

            $total = $queryTotal->count();
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
            $device = Device::find($id);

            if (!$device) {
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
                'data' => $device,
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

    public function getDetailSpesific($id)
    {
        $title = "Detail Device";
        $device = Device::find($id);
        if (!$device) {
            // Bisa redirect atau langsung 404
            abort(404, 'Device tidak ditemukan');
        }
        $deviceFaq = DeviceFaq::where('device_id', $id)->get();

        return view('pages.dashboard.member.device-spesific', compact('title', 'device', 'deviceFaq'));
    }


    public function create(Request $request)
    {
        try {
            $data = $request->all();
            $rules = [
                'name' => 'required|string',
                'excerpt' => 'nullable|string|max:250',
                'is_active' => 'required|string|in:Y,N',
                'image' => 'nullable|image|max:2048|mimes:gif,svg,jpeg,png,jpg',
                'description' => 'nullable|string',
            ];

            $messages = [
                'name.required' => 'Nama harus diisi',
                'excerpt.max' => 'Kutipan harus kurang dari 250 karakter',
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
                $data['image'] = $request->file('image')->store('assets/device', 'public');
            }
            unset($data['id']);

            Device::create($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil dibuat',
            ]);
        } catch (\Exception $err) {
            if ($request->file('image')) {
                $uploadedImg = 'public/assets/device/' . $request->image->hashName();
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
                'excerpt' => 'nullable|string|max:250',
                'is_active' => 'required|string|in:Y,N',
                'image' => 'nullable',
                'description' => 'nullable|string',
            ];

            if ($request->file('image')) {
                $rules['image'] .= '|image|max:2048|mimes:gif,svg,jpeg,png,jpg';
            }

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'name.required' => 'Nama harus diisi',
                'excerpt.max' => 'Kutipan harus kurang dari 250 karakter',
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

            $device = Device::find($data['id']);
            if (!$device) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            // delete undefined data image
            unset($data['image']);
            if ($request->file('image')) {
                $oldImagePath = 'public/' . $device->image;
                if ($device->image && Storage::exists($oldImagePath)) {
                    Storage::delete($oldImagePath);
                }
                $data['image'] = $request->file('image')->store('assets/device', 'public');
            }

            $device->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Data berhasil diperbarui',
            ]);
        } catch (\Exception $err) {
            if ($request->file('image')) {
                $uploadedImg = 'public/assets/device/' . $request->image->hashName();
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

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
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

            $device = Device::find($data['id']);
            if (!$device) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }
            $device->update($data);
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
            $device = Device::find($id);
            if (!$device) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }
            $oldImagePath = 'public/' . $device->image;
            if ($device->image && Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }

            $device->delete();
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
