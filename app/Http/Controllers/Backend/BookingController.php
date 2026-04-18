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
        $validated = $this->validateWithToast($request, [
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'ruang_id' => 'required|exists:ruangs,id',
            'keterangan' => 'required|string|max:255',
            'jumlah_orang' => 'required|integer|min:1',
        ]);

        if (strtotime($validated['jam_selesai']) <= strtotime($validated['jam_mulai'])) {
            toast('Jam selesai harus lebih besar dari jam mulai.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        //cek jadwal sudah lewat atau blm
        $tanggalBooking = Carbon::parse($validated['tanggal']);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            toast('Tanggal booking tidak boleh di hari yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek bentrok dengan booking lain
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
            toast('Jadwal bentrok dengan booking lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jeda 30 menit dari booking sebelumnya
        $lastBooking = Booking::where('ruang_id', $validated['ruang_id'])->where('tanggal', $validated['tanggal'])->where('jam_selesai', '<=', $validated['jam_mulai'])->orderBy('jam_selesai', 'desc')->first();

        if ($lastBooking) {
            $lastEnd = Carbon::parse($validated['tanggal'] . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_mulai']);
            $minStart = $lastEnd->copy()->addMinutes(30);

            if ($newStart->lt($minStart)) {
                toast('Jeda minimal 30 menit setelah booking sebelumnya!', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Cek bentrok dengan jadwal tetap (Jadwal model)
        $tanggal = Carbon::parse($validated['tanggal']);
        $hariBooking = $tanggal->locale('id')->isoFormat('dddd');

        $jadwalTetaps = Jadwal::where('ruang_id', $validated['ruang_id'])->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)->locale('id')->isoFormat('dddd');

            if ($hariJadwal === $hariBooking) {
                if (($validated['jam_mulai'] >= $jadwal->jam_mulai
                     && $validated['jam_mulai'] < $jadwal->jam_selesai) 
                     || ($validated['jam_selesai'] > $jadwal->jam_mulai && $validated['jam_selesai'] <= $jadwal->jam_selesai)
                     || ($validated['jam_mulai'] <= $jadwal->jam_mulai && $validated['jam_selesai'] >= $jadwal->jam_selesai)) 
                     {
                    toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
                    return back()->withInput();
                }
            }
        }

        $ruang = Ruang::find($validated['ruang_id']);
        if ($ruang) {
            $kapasitas = (int) preg_replace('/\D+/', '', (string) $ruang->kapasitas);
            if ($kapasitas > 0 && (int) $validated['jumlah_orang'] > $kapasitas) {
                toast('Jumlah orang melebihi kapasitas ruangan.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Simpan booking jika semua valid
        $booking = new Booking();
        $booking->user_id = Auth::id();
        $booking->ruang_id = $validated['ruang_id'];
        $booking->tanggal = $validated['tanggal'];
        $booking->jam_mulai = $validated['jam_mulai'];
        $booking->jam_selesai = $validated['jam_selesai'];
        $booking->keterangan = $validated['keterangan'];
        $booking->jumlah_orang = $validated['jumlah_orang'];
        $booking->status = 'pending';
        $booking->save();

        if ($booking->jumlah_orang === null) {
            $booking->jumlah_orang = $validated['jumlah_orang'];
            $booking->save();
        }

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
        $validated = $this->validateWithToast($request, [
            'ruang_id' => 'required|exists:ruangs,id',
            'user_id' => 'required|exists:users,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'status' => 'required|in:pending,selesai,ditolak,diterima',
            'keterangan' => 'required|string|max:255',
            'jumlah_orang' => 'required|integer|min:1',
        ]);

        if (strtotime($validated['jam_selesai']) <= strtotime($validated['jam_mulai'])) {
            toast('Jam selesai harus lebih besar dari jam mulai.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        //cek tanggal
        $tanggalBooking = Carbon::parse($validated['tanggal']);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            toast('Tidak bisa mengubah booking ke tanggal yang sudah lewat.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $booking = Booking::findOrFail($id);

        // Cek bentrok dengan booking lain
        $bentrok = Booking::where('ruang_id', $validated['ruang_id'])
            ->where('tanggal', $validated['tanggal'])
            ->where('id', '!=', $id)
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
            toast('Jadwal bentrok dengan booking lain.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        // Cek jeda 30 menit dari booking sebelumnya (selain dirinya sendiri)
        $lastBooking = Booking::where('ruang_id', $validated['ruang_id'])->where('tanggal', $validated['tanggal'])->where('jam_selesai', '<=', $validated['jam_mulai'])->where('id', '!=', $id)->orderBy('jam_selesai', 'desc')->first();

        if ($lastBooking) {
            $lastEnd = Carbon::parse($validated['tanggal'] . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($validated['tanggal'] . ' ' . $validated['jam_mulai']);
            $minStart = $lastEnd->copy()->addMinutes(30);

            if ($newStart->lt($minStart)) {
                toast('Jeda minimal 30 menit setelah pemakaian sebelumnya!', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Cek bentrok dengan jadwal tetap ruangan
        $tanggal = Carbon::parse($validated['tanggal']);
        $hariBooking = $tanggal->locale('id')->isoFormat('dddd');

        $jadwalTetaps = Jadwal::where('ruang_id', $validated['ruang_id'])->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)->locale('id')->isoFormat('dddd');

            if ($hariJadwal === $hariBooking) {
                if (($validated['jam_mulai'] >= $jadwal->jam_mulai && $validated['jam_mulai'] < $jadwal->jam_selesai) || ($validated['jam_selesai'] > $jadwal->jam_mulai && $validated['jam_selesai'] <= $jadwal->jam_selesai) || ($validated['jam_mulai'] <= $jadwal->jam_mulai && $validated['jam_selesai'] >= $jadwal->jam_selesai)) {
                    toast('Jadwal bentrok dengan jadwal tetap ruangan.', 'error')->autoClose(4000);
                    return back()->withInput();
                }
            }
        }

        $ruang = Ruang::find($validated['ruang_id']);
        if ($ruang) {
            $kapasitas = (int) preg_replace('/\D+/', '', (string) $ruang->kapasitas);
            if ($kapasitas > 0 && (int) $validated['jumlah_orang'] > $kapasitas) {
                toast('Jumlah orang melebihi kapasitas ruangan.', 'error')->autoClose(4000);
                return back()->withInput();
            }
        }

        // Update booking
        $booking->ruang_id = $validated['ruang_id'];
        $booking->user_id = $validated['user_id'];
        $booking->tanggal = $validated['tanggal'];
        $booking->jam_mulai = $validated['jam_mulai'];
        $booking->jam_selesai = $validated['jam_selesai'];
        $booking->keterangan = $validated['keterangan'];
        $booking->jumlah_orang = $validated['jumlah_orang'];
        $booking->status = $validated['status'];
        $booking->save();

        toast('Data booking berhasil diperbarui.', 'success')->autoClose(3000);
        return redirect()->route('backend.booking.index');
    }

    public function destroy(string $id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        toast('Booking berhasil dihapus.', 'success')->autoClose(3000);
        return redirect()->route('backend.booking.index');
    }
}
