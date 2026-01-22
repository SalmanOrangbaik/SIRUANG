<?php
namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Jadwal;
use App\Models\Ruang;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class BookingUserController extends Controller
{
    public function create(Request $request)
    {
        $ruang_id = $request->query('ruang_id');
        $ruang    = Ruang::all();

        return view('booking_create', compact('ruang', 'ruang_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruang_id'    => 'required|exists:ruangs,id',
        ]);

        if ($request->jam_mulai >= $request->jam_selesai) {
            Alert::toast(
                'Jam selesai harus lebih besar dari jam mulai.',
                'error'
            )->autoClose(4000);

            return back()->withInput();
        }

        $tanggalInput = Carbon::parse($request->tanggal)->format('Y-m-d');
        $hariIni      = Carbon::now()->format('Y-m-d');

        if ($tanggalInput < $hariIni) {
            Alert::toast('Tidak bisa booking di tanggal yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jika hari ini dan waktu selesai sudah lewat
        if ($tanggalInput === $hariIni) {
            $jamSelesai = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
            if ($jamSelesai->lt(Carbon::now())) {
                Alert::toast('Waktu booking sudah lewat. Silakan pilih waktu yang valid.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        //  Cek bentrok booking lain
        $bentrok = Booking::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal)
            ->where(function ($data) use ($request) {
                $data
                    ->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($booking) use ($request) {
                        $booking->where('jam_mulai', '<=', $request->jam_mulai)->where('jam_selesai', '>=', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($bentrok) {
            Alert::toast('Jadwal bentrok! Silakan pilih jam lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek bentrok dengan jadwal tetap berdasarkan tanggal sama
        $bentrokJadwal = Jadwal::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal) // cek tanggal yang sama
            ->where(function ($data) use ($request) {
                $data
                    ->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($jadwal) use ($request) {
                        $jadwal->where('jam_mulai', '<=', $request->jam_mulai)->where('jam_selesai', '>=', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($bentrokJadwal) {
            Alert::toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // simpann booking
        Booking::create([
            'user_id'     => Auth::id(),
            'ruang_id'    => $request->ruang_id,
            'tanggal'     => $request->tanggal,
            'jam_mulai'   => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status'      => 'pending',
        ]);

        Alert::toast('Booking berhasil dikirim.', 'success')->autoClose(3000);
        return redirect()->back()->withInput();
    }
}
