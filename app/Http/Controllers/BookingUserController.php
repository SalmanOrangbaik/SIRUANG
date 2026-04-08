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
        $validated = $request->validate([
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruang_id'    => 'required|exists:ruangs,id',
            'keterangan'  => 'required|string|max:255',
            'jumlah_orang'=> 'required|integer|min:1',
        ]);
        $jumlahOrang = (int) $validated['jumlah_orang'];
        if ($jumlahOrang < 1) {
            Alert::toast('Jumlah orang tidak valid.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $tanggalInput = Carbon::parse($validated['tanggal'])->format('Y-m-d');
        $hariIni      = Carbon::now()->format('Y-m-d');

        if ($tanggalInput < $hariIni) {
            Alert::toast('Tidak bisa booking di tanggal yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jika hari ini dan waktu selesai sudah lewat
        if ($tanggalInput === $hariIni) {
            $jamSelesai = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_selesai']);
            if ($jamSelesai->lt(Carbon::now())) {
                Alert::toast('Waktu booking sudah lewat. Silakan pilih waktu yang valid.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        //  Cek bentrok booking lain
        $bentrok = Booking::where('ruang_id', $validated['ruang_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where(function ($data) use ($validated) {
                $data
                    ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($booking) use ($validated) {
                        $booking->where('jam_mulai', '<=', $validated['jam_mulai'])->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrok) {
            Alert::toast('Jadwal bentrok! Silakan pilih jam lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek bentrok dengan jadwal tetap berdasarkan tanggal sama
        $bentrokJadwal = Jadwal::where('ruang_id', $validated['ruang_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where(function ($data) use ($validated) {
                $data
                    ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($jadwal) use ($validated) {
                        $jadwal->where('jam_mulai', '<=', $validated['jam_mulai'])->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokJadwal) {
            Alert::toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $ruang = Ruang::find($validated['ruang_id']);
        if ($ruang) {
            $kapasitas = (int) preg_replace('/\D+/', '', (string) $ruang->kapasitas);
            if ($kapasitas > 0 && $jumlahOrang > $kapasitas) {
                Alert::toast('Jumlah orang melebihi kapasitas ruangan. Silakan pilih ruangan lain atau kurangi peserta.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // simpann booking
        $booking = new Booking();
        $booking->user_id = Auth::id();
        $booking->ruang_id = $validated['ruang_id'];
        $booking->tanggal = $validated['tanggal'];
        $booking->jam_mulai = $validated['jam_mulai'];
        $booking->jam_selesai = $validated['jam_selesai'];
        $booking->keterangan = $validated['keterangan'];
        $booking->jumlah_orang = $jumlahOrang;
        $booking->status = 'pending';
        $booking->save();

        if ($booking->jumlah_orang === null) {
            $booking->jumlah_orang = $jumlahOrang;
            $booking->save();
        }

        Alert::toast('Booking berhasil dikirim.', 'success')->autoClose(3000);
        return redirect()->back()->withInput();
    }

    public function update(Request $request, Booking $booking)
    {
        if ($booking->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruang_id'    => 'required|exists:ruangs,id',
            'keterangan'  => 'required|string|max:255',
            'jumlah_orang'=> 'required|integer|min:1',
        ]);
        $jumlahOrang = (int) $validated['jumlah_orang'];
        if ($jumlahOrang < 1) {
            Alert::toast('Jumlah orang tidak valid.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $tanggalInput = Carbon::parse($validated['tanggal'])->format('Y-m-d');
        $hariIni      = Carbon::now()->format('Y-m-d');

        if ($tanggalInput < $hariIni) {
            Alert::toast('Tidak bisa booking di tanggal yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        if ($tanggalInput === $hariIni) {
            $jamSelesai = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_selesai']);
            if ($jamSelesai->lt(Carbon::now())) {
                Alert::toast('Waktu booking sudah lewat. Silakan pilih waktu yang valid.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        $bentrok = Booking::where('ruang_id', $validated['ruang_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('id', '!=', $booking->id)
            ->where(function ($data) use ($validated) {
                $data
                    ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($booking) use ($validated) {
                        $booking->where('jam_mulai', '<=', $validated['jam_mulai'])->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrok) {
            Alert::toast('Jadwal bentrok! Silakan pilih jam lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $bentrokJadwal = Jadwal::where('ruang_id', $validated['ruang_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where(function ($data) use ($validated) {
                $data
                    ->whereBetween('jam_mulai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhereBetween('jam_selesai', [$validated['jam_mulai'], $validated['jam_selesai']])
                    ->orWhere(function ($jadwal) use ($validated) {
                        $jadwal->where('jam_mulai', '<=', $validated['jam_mulai'])->where('jam_selesai', '>=', $validated['jam_selesai']);
                    });
            })
            ->exists();

        if ($bentrokJadwal) {
            Alert::toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $ruang = Ruang::find($validated['ruang_id']);
        if ($ruang) {
            $kapasitas = (int) preg_replace('/\D+/', '', (string) $ruang->kapasitas);
            if ($kapasitas > 0 && $jumlahOrang > $kapasitas) {
                Alert::toast('Jumlah orang melebihi kapasitas ruangan. Silakan pilih ruangan lain atau kurangi peserta.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        $booking->ruang_id = $validated['ruang_id'];
        $booking->tanggal = $validated['tanggal'];
        $booking->jam_mulai = $validated['jam_mulai'];
        $booking->jam_selesai = $validated['jam_selesai'];
        $booking->keterangan = $validated['keterangan'];
        $booking->jumlah_orang = $jumlahOrang;
        $booking->save();

        Alert::toast('Booking berhasil diperbarui.', 'success')->autoClose(3000);
        return redirect()->back();
    }
}
