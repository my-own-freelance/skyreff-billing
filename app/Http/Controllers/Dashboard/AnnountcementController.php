<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AnnountcementController extends Controller
{
    public function index()
    {
        $title = 'Master Informasi';
        $members = User::where('role', 'member')->get();
        $areas = Area::all();
        return view('pages.dashboard.admin.announcement', compact('title', 'members', 'areas'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = Announcement::query();

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query->where('subject', 'like', '%' . $searchValue . '%')->Orwhere('message', 'like', '%' . $searchValue . '%');
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

                $subject = '<p>' . Str::limit(strip_tags($item->subject), 100) . '</p>';
                $message = '<p>' . Str::limit(strip_tags($item->message), 150) . '</p>';
                $type = '';
                switch ($item->type) {
                    case 'P':
                        $type = "<span class='badge badge-primary'>Primary</span>";
                        break;
                    case 'I':
                        $type = "<span class='badge badge-info'>Announcement</span>";
                        break;
                    case 'S':
                        $type = "<span class='badge badge-success'>Success</span>";
                        break;
                    case 'W':
                        $type = "<span class='badge badge-warning'>Warning</span>";
                        break;
                    case 'D':
                        $type = "<span class='badge badge-danger'>Danger</span>";
                        break;
                    default:
                        $type = "<span class='badge badge-info'>Announcement</span>";
                        break;
                }
                $item['type'] = $type;
                $item['action'] = $action;
                $item['is_active'] = $is_active;
                $item['subject'] = $subject;
                $item['message'] = $message;

                // tambahkan info target
                $item['target'] = $item->user ? 'User: ' . $item->user->name . ' - [' . $item->user->username . ']': ($item->area ? 'Area: ' . $item->area->name . ' - [' . $item->area->code . ']' : 'Semua');
                return $item;
            });

            $total = Announcement::count();
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
            $announcement = Announcement::with(['user:id,name', 'area:id,name,code'])->find($id);

            if (!$announcement) {
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
                'data' => $announcement,
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
                'subject' => 'required|string',
                'message' => 'required|string',
                'type' => 'required|string|in:P,I,S,W,D',
                'is_active' => 'required|string|in:Y,N',
                'user_id' => 'nullable|integer|exists:users,id',
                'area_id' => 'nullable|integer|exists:areas,id',
            ];

            $messages = [
                'subject.required' => 'Subject harus diisi',
                'message.required' => 'Message harus diisi',
                'type.required' => 'Type informasi harus diisi',
                'type.in' => 'Type informasi tidak sesuai',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
                'user_id.exists' => 'User tidak ditemukan',
                'area_id.exists' => 'Area tidak ditemukan',
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

            Announcement::create($data);
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
                'id' => 'required|integer|exists:announcements,id',
                'subject' => 'required|string',
                'message' => 'required|string',
                'type' => 'required|string|in:P,I,S,W,D',
                'is_active' => 'required|string|in:Y,N',
                'user_id' => 'nullable|integer|exists:users,id',
                'area_id' => 'nullable|integer|exists:areas,id',
            ];

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'id.exists' => 'Data tidak ditemukan',
                'subject.required' => 'Subject harus diisi',
                'message.required' => 'Message harus diisi',
                'type.required' => 'Type informasi harus diisi',
                'type.in' => 'Type informasi tidak sesuai',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak sesuai',
                'user_id.exists' => 'User tidak ditemukan',
                'area_id.exists' => 'Area tidak ditemukan',
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

            $announcement = Announcement::find($data['id']);
            if (!$announcement) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $announcement->update($data);
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

            $announcement = Announcement::find($data['id']);
            if (!$announcement) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }
            $announcement->update($data);
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
            $announcement = Announcement::find($id);
            if (!$announcement) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $announcement->delete();
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
