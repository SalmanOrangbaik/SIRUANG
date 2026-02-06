<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RiwayatController extends Controller
{
    public function index(Request $request)
    {
        $user    = $request->user();
        $riwayat = $user->booking()->with('ruang')->orderBy('tanggal', 'desc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Riwayat booking user',
            'data'    => $riwayat,
        ], 200);
    }
}
