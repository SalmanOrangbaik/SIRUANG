<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Ruang;
use App\Models\Jadwal;
use App\Models\User;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use RealRashid\SweetAlert\Facades\Alert;

class BookingController extends Controller
{
    public function export()
    {
        $filter = Booking::with(['user', 'ruang']);

        if (request()->filled('ruang_id')) {
            $filter->where('ruang_id', request('ruang_id'));
        }

        if (request()->filled('tanggal')) {
            $filter->where('tanggal', request('tanggal'));
        }

        if (request()->filled('status')) {
            $filter->where('status', request('status'));
        }

        $bookings = $filter->orderBy('tanggal')->get();

        $pdf = Pdf::loadView('backend.booking.pdfbookings', ['booking' => $bookings]);
        return $pdf->download('laporan-data-bookings.pdf');
    }

    public function index(Request $request)
    {
        $now = Carbon::now();

        // Update otomatis status jadi 'selesai'
        Booking::whereIn('status', ['pending', 'diterima'])
            ->where(function ($data) use ($now) {
                $data->whereDate('tanggal', '<', $now->toDateString())->orWhere(function ($waktu) use ($now) {
                    $waktu->whereDate('tanggal', $now->toDateString())->whereTime('jam_selesai', '<=', $now->toTimeString());
                });
            })
            ->update(['status' => 'selesai']);

        // Filter query
        $data = Booking::with(['user', 'ruang']);

        if ($request->filled('ruang_id')) {
            $data->where('ruang_id', $request->ruang_id);
        }

        if ($request->filled('tanggal')) {
            $data->whereDate('tanggal', $request->tanggal);
        }

        if ($request->filled('status')) {
            $data->where('status', $request->status);
        }

        $bookings = $data->latest()->get();
        $ruangs = Ruang::all();

        return view('backend.booking.index', compact('bookings', 'ruangs'));
    }

    public function create()
    {
        $ruangs = Ruang::all();
        $users = User::all();
        return view('backend.booking.create', compact('ruangs', 'users'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'ruang_id' => 'required|exists:ruangs,id',
        ]);

        //cek jadwal sudah lewat atau blm
        $tanggalBooking = Carbon::parse($request->tanggal);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            toast('Tanggal booking tidak boleh di hari yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek bentrok dengan booking lain
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
            toast('Jadwal bentrok dengan booking lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jeda 30 menit dari booking sebelumnya
        $lastBooking = Booking::where('ruang_id', $request->ruang_id)->where('tanggal', $request->tanggal)->where('jam_selesai', '<=', $request->jam_mulai)->orderBy('jam_selesai', 'desc')->first();

        if ($lastBooking) {
            $lastEnd = Carbon::parse($request->tanggal . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
            $minStart = $lastEnd->copy()->addMinutes(30);

            if ($newStart->lt($minStart)) {
                toast('Jeda minimal 30 menit setelah booking sebelumnya!', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Cek bentrok dengan jadwal tetap (Jadwal model)
        $tanggal = Carbon::parse($request->tanggal);
        $hariBooking = $tanggal->locale('id')->isoFormat('dddd'); // contoh: "Senin"

        $jadwalTetaps = Jadwal::where('ruang_id', $request->ruang_id)->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)->locale('id')->isoFormat('dddd');

            if ($hariJadwal === $hariBooking) {
                if (($request->jam_mulai >= $jadwal->jam_mulai && $request->jam_mulai < $jadwal->jam_selesai) || ($request->jam_selesai > $jadwal->jam_mulai && $request->jam_selesai <= $jadwal->jam_selesai) || ($request->jam_mulai <= $jadwal->jam_mulai && $request->jam_selesai >= $jadwal->jam_selesai)) {
                    toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
                    return back()->withInput();
                }
            }
        }

        // Simpan booking jika semua valid
        Booking::create([
            'user_id' => Auth::id(),
            'ruang_id' => $request->ruang_id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status' => 'pending',
        ]);

        toast('Booking berhasil ditambahkan.', 'success')->autoClose(3000);
        return redirect()->route('backend.booking.index');
    }

    public function show(string $id)
    {
        $booking = Booking::with(['ruang', 'user'])->findOrFail($id);
        return view('backend.booking.show', compact('booking'));
    }

    public function edit(string $id)
    {
        $booking = Booking::findOrFail($id);
        $ruangs = Ruang::all();
        $users = User::all();
        return view('backend.booking.edit', compact('booking', 'ruangs', 'users'));
    }

    public function update(Request $request, string $id)
    {
        $request->validate([
            'ruang_id' => 'required|exists:ruangs,id',
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|',
            'jam_selesai' => 'required|after:jam_mulai',
            'status' => 'required|in:pending,selesai,ditolak,diterima',
        ]);

        //cek tanggal
        $tanggalBooking = Carbon::parse($request->tanggal);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            toast('Tidak bisa mengubah booking ke tanggal yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $booking = Booking::findOrFail($id);

        // Cek bentrok dengan booking lain (kecuali dirinya sendiri)
        $bentrok = Booking::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal)
            ->where('id', '!=', $id)
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
            toast('Jadwal bentrok dengan booking lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jeda 30 menit dari booking sebelumnya (selain dirinya sendiri)
        $lastBooking = Booking::where('ruang_id', $request->ruang_id)->where('tanggal', $request->tanggal)->where('jam_selesai', '<=', $request->jam_mulai)->where('id', '!=', $id)->orderBy('jam_selesai', 'desc')->first();

        if ($lastBooking) {
            $lastEnd = Carbon::parse($request->tanggal . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
            $minStart = $lastEnd->copy()->addMinutes(30);

            if ($newStart->lt($minStart)) {
                toast('Jeda minimal 30 menit setelah pemakaian sebelumnya!', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Cek bentrok dengan jadwal tetap ruangan
        $tanggal = Carbon::parse($request->tanggal);
        $hariBooking = $tanggal->locale('id')->isoFormat('dddd');

        $jadwalTetaps = Jadwal::where('ruang_id', $request->ruang_id)->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)->locale('id')->isoFormat('dddd');

            if ($hariJadwal === $hariBooking) {
                if (($request->jam_mulai >= $jadwal->jam_mulai && $request->jam_mulai < $jadwal->jam_selesai) || ($request->jam_selesai > $jadwal->jam_mulai && $request->jam_selesai <= $jadwal->jam_selesai) || ($request->jam_mulai <= $jadwal->jam_mulai && $request->jam_selesai >= $jadwal->jam_selesai)) {
                    toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
                    return back()->withInput();
                }
            }
        }

        // Update booking
        $booking->update([
            'ruang_id' => $request->ruang_id,
            'user_id' => $request->user_id,
            'tanggal' => $request->tanggal,
            'jam_mulai' => $request->jam_mulai,
            'jam_selesai' => $request->jam_selesai,
            'status' => $request->status,
        ]);

        toast('Data booking berhasil diperbarui.', 'success')->autoClose(3000);
        return redirect()->route('backend.booking.index'); //ini yg bagian balik ke halaman index
    }

    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        toast('Booking berhasil dihapus.', 'success')->autoClose(3000);
        return redirect()->route('backend.booking.index');
    }
}
