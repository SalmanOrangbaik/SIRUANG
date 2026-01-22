<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Jadwal;
use Illuminate\Http\Request;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwals = Jadwal::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua jadwal',
            'data'    => $jadwals,
        ], 200);
    }

    // Ambil detail satu jadwal berdasarkan ID
    public function show($id)
    {
        $jadwal = Jadwal::find($id);

        if (! $jadwal) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail jadwal',
            'data'    => $jadwal,
        ], 200);
    }
}
