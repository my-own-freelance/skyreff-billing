<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\WebConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class WebConfigController extends Controller
{
    public function index()
    {
        $title = 'Setting Website';
        return view('pages.dashboard.admin.web-config', compact('title'));
    }

    public function detail()
    {
        try {
            $config = WebConfig::first();

            if (!$config) {
                return response()->json(
                    [
                        'status' => 'success',
                        'message' => 'Template is not set',
                    ],
                    404,
                );
            }

            if (!$config->web_title) {
                $config['web_title'] = 'Skyreff';
            }

            if (!$config->web_description) {
                $config['web_description'] = 'Situs Jual Beli Produk Digital';
            }

            if ($config->meta_image) {
                $config['meta_image'] = url('/') . Storage::url($config->meta_image);
            } else {
                $config['meta_image'] = asset('skyreff-logo.jpeg');
            }

            if ($config->web_logo) {
                $config['web_logo'] = url('/') . Storage::url($config->web_logo);
            } else {
                $config['web_logo'] = asset('skyreff-logo.jpeg');
            }

            if ($config['maps_location'] && $config['maps_location'] != '') {
                $config['maps_preview'] = "<iframe src='" . $config['maps_location'] . "' allowfullscreen class='w-100' height='500'></iframe>";
            }

            return response()->json([
                'status' => 'success',
                'data' => $config,
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

    public function saveUpdateData(Request $request)
    {
        $data = $request->all();
        unset($data['id']);
        unset($data['meta_image']);
        unset($data['web_logo']);
        unset($data['web_logo_white']);
        $existCustomData = WebConfig::first();
        if (!$existCustomData) {
            if ($request->file('meta_image')) {
                $data['meta_image'] = $request->file('meta_image')->store('assets/web-config', 'public');
            }

            if ($request->file('web_logo')) {
                $data['web_logo'] = $request->file('web_logo')->store('assets/web-config', 'public');
            }

            if ($request->file('web_logo_white')) {
                $data['web_logo_white'] = $request->file('web_logo_white')->store('assets/web-config', 'public');
            }

            WebConfig::create($data);
            return response()->json([
                'status' => 200,
                'message' => 'Setting Web berhasil diubah',
            ]);
        }

        if ($request->file('meta_image')) {
            $oldImagePath = 'public/' . $existCustomData->meta_image;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
            $data['meta_image'] = $request->file('meta_image')->store('assets/web-config', 'public');
        }

        if ($request->file('web_logo')) {
            $oldImagePath = 'public/' . $existCustomData->web_logo;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
            $data['web_logo'] = $request->file('web_logo')->store('assets/web-config', 'public');
        }

        if ($request->file('web_logo_white')) {
            $oldImagePath = 'public/' . $existCustomData->web_logo_white;
            if (Storage::exists($oldImagePath)) {
                Storage::delete($oldImagePath);
            }
            $data['web_logo_white'] = $request->file('web_logo_white')->store('assets/web-config', 'public');
        }

        $existCustomData->update($data);
        return response()->json([
            'status' => 200,
            'message' => 'Konfigurasi Web berhasil diubah',
        ]);
    }
}
