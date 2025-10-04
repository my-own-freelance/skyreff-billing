<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BroadcastController extends Controller
{
    // Tampilkan form
    public function index()
    {
        $title = 'Broadcast';
        $areas = Area::all();
        $members = User::where('role', 'member')->get();

        return view('pages.dashboard.admin.broadcast', compact('title', 'areas', 'members'));
    }

    // Handle aksi broadcast
    public function send(Request $request)
    {
        try {
            $request->validate([
                'type' => 'required|in:custom,area,member',
                'message' => 'required|string',
            ]);

            $message = $request->message;
            $numbers = [];

            if ($request->type === 'custom') {
                // parsing nomor custom
                $customNumbers = explode(',', $request->custom_numbers);
                foreach ($customNumbers as $num) {
                    $numbers[] = preg_replace('/^08/', '628', trim($num));
                }
            }

            if ($request->type === 'area') {
                $area = Area::find($request->area_id);
                if ($area) {
                    $users = User::where('role', 'member')
                        ->where('area_id', $area->id)
                        ->where('is_active', 'Y')
                        ->get();

                    foreach ($users as $user) {
                        $numbers[] = preg_replace('/^08/', '628', $user->phone);
                    }
                }
            }

            if ($request->type === 'member') {
                $users = [];

                if ($request->member_ids[0] == 'all') {
                    $users = User::where("role", "member")->get();
                } else {
                    $users = User::whereIn('id', $request->member_ids ?? [])->get();
                }
                foreach ($users as $user) {
                    $numbers[] = preg_replace('/^08/', '628', $user->phone);
                }
            }

            // Kirim ke API WA satu per satu
            foreach ($numbers as $phone) {
                Http::post('https://app.saungwa.com/api/create-message', [
                    "appkey" => "6879d35c-268e-4e2a-ae43-15528fc86ba4",
                    "authkey" => "j8znJb83n04XeenAPuVEOxZWRKX62DWTHpFEHaRgP1WtdUR972",
                    "to" => $phone,
                    "message" => $message,
                ]);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Broadcast berhasil dikirim ke ' . count($numbers) . ' nomor.',
            ]);
        } catch (\Exception $err) {
            return response()->json([
                'status' => 'error',
                'message' => $err->getMessage(),
            ], 500);
        }
    }
}
