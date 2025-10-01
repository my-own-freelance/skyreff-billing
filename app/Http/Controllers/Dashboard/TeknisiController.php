<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class TeknisiController extends Controller
{
    public function index()
    {
        $title = 'Data Owner';
        return view('pages.dashboard.admin.teknisi', compact('title'));
    }

    // HANDLER API
    public function dataTable(Request $request)
    {
        try {
            $query = User::select('id', 'name', 'username', 'phone', 'is_active')->where('role', 'teknisi');

            if ($request->query('search')) {
                $searchValue = $request->query('search')['value'];
                $query->where(function ($query) use ($searchValue) {
                    $query
                        ->where('name', 'like', '%' . $searchValue . '%')
                        ->orWhere('username', 'like', '%' . $searchValue . '%')
                        ->orWhere('phone', 'like', '%' . $searchValue . '%');
                });
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy('name', 'asc')->skip($request->query('start'))->limit($request->query('length'))->get();

            $user = auth()->user();
            $output = $data->map(function ($item) use ($user) {
                $action_delete = $user->id != $item->id ? "<a class='dropdown-item' onclick='return removeData(\"{$item->id}\");' href='javascript:void(0)' title='Hapus'>Hapus</a>" : '';

                $action =
                    "<div class='dropdown-primary dropdown open'>
                            <button class='btn btn-sm btn-primary dropdown-toggle waves-effect waves-light' id='dropdown-{$item->id}' data-toggle='dropdown' aria-haspopup='true' aria-expanded='true'>
                                Aksi
                            </button>
                            <div class='dropdown-menu' aria-labelledby='dropdown-{$item->id}' data-dropdown-out='fadeOut'>
                                <a class='dropdown-item' onclick='return getData(\"{$item->id}\");' href='javascript:void(0);' title='Edit'>Edit</a>
                                " .
                    $action_delete .
                    "
                            </div>
                        </div>";

                $is_active =
                    $item->is_active == 'Y'
                    ? '
                    <div class="text-center">
                        <span class="label-switch">Active</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status on">
                            <input type="checkbox" onclick="return updateStatus(\'' .
                    $item->id .
                    '\', \'Disabled\');" />
                            <span class="slider"></span>
                        </div>
                    </div>'
                    : '
                    <div class="text-center">
                        <span class="label-switch">Disabled</span>
                    </div>
                    <div class="input-row">
                        <div class="toggle_status off">
                            <input type="checkbox" onclick="return updateStatus(\'' .
                    $item->id .
                    '\', \'Active\');" />
                            <span class="slider"></span>
                        </div>
                    </div>';

                $item['action'] = $action;
                $item['is_active'] = $user->id != $item->id ? $is_active : '';
                return $item;
            });

            $total = User::where('role', 'teknisi')->count();
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
            $data['phone'] = preg_replace('/^08/', '628', $data['phone']);

            $rules = [
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'phone' => 'required|string|digits_between:10,15|unique:users',
                'password' => 'required|string|min:5',
                'is_active' => 'required|string|in:Y,N',
            ];

            $messages = [
                'name.required' => 'Nama harus diisi',
                'username.required' => 'Username harus diisi',
                'username.unique' => 'Username sudah digunakan',
                'phone.required' => 'Nomor telepon harus diisi',
                'phone.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 hingga 15 karakter',
                'phone.unique' => 'Nomor telepon sudah digunakan',
                'password.required' => 'Password harus diisi',
                'password.min' => 'Password minimal 5 karakter',
                'is_active' => 'Status harus diisi',
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

            unset($data['id']);
            $data['password'] = Hash::make($request->password);
            $data['role'] = 'teknisi';
            User::create($data);

            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil update data teknisi',
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
            $data['phone'] = preg_replace('/^08/', '628', $data['phone']);
            $rules = [
                'id' => 'required|integer',
                'name' => 'required|string',
                'phone' => 'required|string|digits_between:10,15|unique:users,phone,' . $data['id'] . ',id', // validasi duplikat no telpon dengan user yg beda
                'password' => 'nullable',
                'is_active' => 'required|string|in:Y,N',
            ];
            if ($data['password'] != '') {
                $rules['password'] .= '|string|min:5';
            }

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.integer' => 'Type ID tidak sesuai',
                'name.required' => 'Nama harus diisi',
                'phone.required' => 'Nomor telepon harus diisi',
                'phone.digits_between' => 'Nomor telepon harus memiliki panjang antara 10 hingga 15 karakter',
                'phone.unique' => 'Nomor telepon sudah digunakan',
                'password.min' => 'Password minimal 5 karakter',
                'is_active' => 'Status harus diisi',
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
            $user = User::where('role', 'teknisi')->where('id', $data['id'])->first();

            if (!$user) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data teknisi tidak ditemukan',
                    ],
                    404,
                );
            }

            if ($data['password'] && $data['password'] != '') {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // agar username tidak bisa diganti
            unset($data['username']);

            $user->update($data);
            return response()->json([
                'status' => 'success',
                'message' => 'Berhasil update data teknisi',
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
            $user = User::find($id);

            if (!$user) {
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
                'data' => $user,
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
                'is_active' => 'required|in:N,Y',
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

            $user = User::find($data['id']);
            if (!$user) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data teknisi tidak ditemukan',
                    ],
                    404,
                );
            }
            $user->update(['is_active' => $data['is_active']]);
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
            $user = User::find($id);
            if (!$user) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $user->delete();
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
