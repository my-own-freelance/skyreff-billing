<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\WebConfig;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function login()
    {
        $title = 'Kelola Akses';
        $webTitle = 'SKYREFF - DASHBOARD';
        $config = WebConfig::first();
        $description = 'Web Billing Skyreff';
        if ($config) {
            $description = $config->web_description;
            $title = $config->web_title;
        }
        return view('pages.auth.index', compact('title', 'description'));
    }

    // API
    public function validateLogin(Request $request)
    {
        try {
            $rules = [
                'username' => 'required|string',
                'password' => 'required|string',
            ];

            $messages = [
                'username.required' => 'Username harus diisi',
                'password.required' => 'Password harus diisi',
            ];

            $validate = Validator::make($request->all(), $rules, $messages);
            if ($validate->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validate->errors()->first(),
                    ],
                    400,
                );
            }

            $user = User::where('username', $request->username)->first();

            if ($user && $user->is_active != 'Y') {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Akun tidak aktif, silahkan hubungi admin',
                    ],
                    400,
                );
            }

            if (Auth::attempt(['username' => $request->username, 'password' => $request->password])) {
                // update last_login_at
                $user->last_login_at = Carbon::now('Asia/Jakarta');
                $user->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'Login Sukses',
                    'redirect_url' => $user->role == 'admin' ? route('dashboard.admin') : ($user->role == 'teknisi' ? route('dashboard.teknisi') : route('dashboard.member')),
                ]);
            }

            return response()->json(
                [
                    'status' => 'error',
                    'message' => 'Username / Password salah',
                ],
                400,
            );
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

    public function logout(Request $request)
    {
        Auth::logout();

        return redirect()->route('login');
    }

    public function register(Request $request)
    {
        try {
            $rules = [
                'name' => 'required|string',
                'username' => 'required|string|unique:users',
                'phone' => ['required', "regex:/^(\+62|62|0)[0-9]{9,15}$/", 'unique:users,phone'],
                'password' => 'required|string|min:5',
                'passwordConfirm' => 'required|string|same:password',
                'role' => 'required|string|in:admin,teknisi,member',
                'is_active' => 'required|string|in:Y,N',
            ];

            $messages = [
                'name.required' => 'Nama harus diisi',
                'username.required' => 'Username harus diisi',
                'username.unique' => 'Username sudah digunakan',
                'phone.required' => 'Telpon harus diisi',
                'phone.regex' => 'Format nomor telepon tidak valid (contoh: 08123456789 atau +628123456789)',
                'phone.unique' => 'Telpon sudah digunakan',
                'password.required' => 'Password harus diisi',
                'password.min' => 'Password minimal 5 karakter',
                'passwordConfirm.required' => 'Password harus diisi',
                'passwordConfirm.same' => 'Password Confirm tidak sesuai',
                'role.required' => 'Role harus diisi',
                'role.in' => 'Role tidak valid',
                'is_active.required' => 'Status harus diisi',
                'is_active.in' => 'Status tidak valid',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => $validator->errors()->first(),
                    ],
                    400,
                );
            }

            $user = new User();
            $user->name = $request->name;
            $user->username = $request->username;
            $user->password = Hash::make($request->password);
            $user->role = $request->role;
            $user->phone = $request->phone;
            $user->address = $request->address;
            $user->link_maps = $request->link_maps;
            $user->is_active = $request->is_active;
            $user->save();

            return response()->json(
                [
                    'status' => 'success',
                    'message' => "User dengan role {$user->role} berhasil dibuat",
                    'data' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'username' => $user->username,
                        'role' => $user->role,
                        'phone' => $user->phone,
                        'is_active' => $user->is_active,
                    ],
                ],
                201,
            );
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
