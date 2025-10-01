<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function account()
    {
        $title = "Pengaturan Akun";
        $pageUrl = "";
        $user = auth()->user();
        if ($user->role == "teknisi") {
            $pageUrl = "pages.dashboard.teknisi.account";
        } else if ($user->role == "member") {
            $pageUrl = "pages.dashboard.member.account";
        }
        return view($pageUrl, compact("title"));
    }
    public function getDetailAccount()
    {
        try {
            $user = User::find(auth()->user()->id);

            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan",
                ], 404);
            }

            return response()->json([
                "status" => "success",
                "data" => $user
            ]);
        } catch (\Exception $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }

    public function updateAccountReseller(Request $request)
    {
        try {
            $data = $request->all();
            $data["phone"] = preg_replace('/^08/', '628', $data['phone']);
            $rules = [
                "name" => "required|string",
                "password" => "nullable",
                "phone" => "required|string|digits_between:10,15",
                "link_map" => "nullable|url",
                "address" => "required|string",
            ];

            if ($data && $data['password'] != "") {
                $rules['password'] .= "|string|min:5";
            }

            $messages = [
                "username.required" => "Username harus diisi",
                "username.unique" => "Username sudah digunakan",
                "phone.required" => "Nomor telepon harus diisi",
                "phone.digits_between" => "Nomor telepon harus memiliki panjang antara 10 hingga 15 karakter",
                "password.min" => "Password minimal 5 karakter",
                "address.required" => "Alamat harus diisi",
                "link_map.url" => "URL tidak valid",
            ];

            $validator = Validator::make($request->all(), $rules, $messages);
            if ($validator->fails()) {
                return response()->json([
                    "status" => "error",
                    "message" => $validator->errors()->first(),
                ], 400);
            }

            $user = User::where('id', auth()->user()->id)->first();
            if (!$user) {
                return response()->json([
                    "status" => "error",
                    "message" => "Data tidak ditemukan"
                ], 404);
            }



            if ($data['password'] && $data['password'] != "") {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            // agar username tidak bisa diganti
            unset($data['username']);

            $data['phone'] = preg_replace('/^08/', '628', $data['phone']);

            $user->update($data);

            return response()->json([
                "status" => "success",
                "message" => "Berhasil mengubah data pengguna"
            ]);
        } catch (\Throwable $err) {
            return response()->json([
                "status" => "error",
                "message" => $err->getMessage()
            ], 500);
        }
    }
}
