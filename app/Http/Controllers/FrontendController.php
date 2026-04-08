<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Ruang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FrontendController extends Controller
{
    public function index()
    {
        $ruang = Ruang::take(3)->get();

        $bookings = Booking::with('ruang')->get();
        $jadwals = Jadwal::with('ruang')->get();

        $events = [];

        foreach ($bookings as $booking) {
            $events[] = [
                'title' => 'Booking - ' . ($booking->ruang->nama ?? 'Tanpa Ruangan'),
                'start' => $booking->tanggal . 'T' . $booking->jam_mulai,
                'end' => $booking->tanggal . 'T' . $booking->jam_selesai,
                'color' => '#ffc107', // Kuning (Booking Diterima / Selesai)
                'description' => $booking->keterangan
                    ? 'Booking: ' . $booking->keterangan
                    : 'Booking oleh: ' . ($booking->user->name ?? 'Pengguna'),
            ];
        }

        foreach ($jadwals as $data) {
            $events[] = [
                'title' => 'Jadwal - ' . ($data->ruang->nama ?? 'Tanpa Ruangan'),
                'start' => $data->tanggal . 'T' . $data->jam_mulai,
                'end' => $data->tanggal . 'T' . $data->jam_selesai,
                'color' => '#0dcaf0', // Biru (Jadwal Tetap)
                'description' => 'Jadwal Tetap - ' . ($data->keterangan ?? '-'),
            ];
        }

        return view('index', compact('ruang', 'events'));
    }

    public function riwayat(Request $request)
    {
        $booking = Booking::where('user_id', Auth::id())->latest()->get();
        $ruangs = Ruang::all();

        $data = Booking::with('ruang')->where('user_id', Auth::id());

        if ($request->filled('ruang_id')) {
            $data->where('ruang_id', $request->ruang_id);
        }

        if ($request->filled('tanggal')) {
            $data->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('status')) {
            $data->where('status', $request->status);
        }

        $booking = $data->latest()->paginate(10)->withQueryString();
        $ruangs = Ruang::all();

        return view('booking_riwayat', compact('booking', 'ruangs'));
    }

    public function ruangan()
    {
        $ruang = Ruang::all();
        return view('booking_ruangan', compact('ruang'));
    }

    public function detailRuangan($id)
    {
        $ruang = Ruang::findOrFail($id);
        return view('detail_ruangan', compact('ruang'));
    }
}
