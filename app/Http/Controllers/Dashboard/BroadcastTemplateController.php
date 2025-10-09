<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BroadcastTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BroadcastTemplateController extends Controller
{
    public function index()
    {
        $title = "Broadcast Template";
        $templates = BroadcastTemplate::all();

        return view("pages.dashboard.admin.broadcast-template", compact("title", "templates"));
    }

    // HANDLER API
    public function getDetail($id)
    {
        try {
            $template = BroadcastTemplate::find($id);

            if (!$template) {
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
                'data' => $template,
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
                'id' => 'required|integer|exists:broadcast_templates,id',
                'content' => 'nullable|string',
                'is_active' => 'required|string|in:Y,N',
            ];

            $messages = [
                'id.required' => 'Data ID harus diisi',
                'id.exists' => 'Data tidak ditemukan',
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

            $template = BroadcastTemplate::find($data['id']);

            if (!$template) {
                return response()->json(
                    [
                        'status' => 'error',
                        'message' => 'Data tidak ditemukan',
                    ],
                    404,
                );
            }

            $template->update($data);
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
}
