<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Ruang;

class RuanganController extends Controller
{
    // Ambil semua data ruangan
    public function index()
    {
        $ruangs = Ruang::latest()->get();

        return response()->json([
            'success' => true,
            'message' => 'Data semua ruangan',
            'data'    => $ruangs,
        ], 200);
    }

    // Ambil detail satu ruangan berdasarkan ID
    public function show($id)
    {
        $ruang = Ruang::find($id);

        if (! $ruang) {
            return response()->json([
                'success' => false,
                'message' => 'Ruangan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail ruangan',
            'data'    => $ruang,
        ], 200);
    }
}
