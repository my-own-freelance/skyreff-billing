<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $title = "Dashboard Skyreff";
        $data = [];
        $user = Auth::user();
        $pageUrl = $user->role == "admin" ? "pages.dashboard.admin.index" : ($user->role == "teknisi" ? "pages.dashboard.teknisi.index" : "pages.dashboard.member.index");

        if ($user->role == "member") {
            $announcements = Announcement::where("is_active", "Y")
                ->where(function ($q) use ($user) {
                    // Filter by user_id
                    $q->where("user_id", $user->id);

                    if (!empty($user->area_id)) {
                        // User punya area_id → tambah filter area
                        $q->orWhere("area_id", $user->area_id);
                    }

                    // Global announcement (user_id & area_id kosong)
                    $q->orWhere(function ($q2) {
                        $q2->whereNull("user_id")
                            ->whereNull("area_id");
                    });
                })
                ->get();

            $data = [
                "announcements" => $announcements,
            ];
        }
        return view($pageUrl, compact("title", "data"));
    }
}
