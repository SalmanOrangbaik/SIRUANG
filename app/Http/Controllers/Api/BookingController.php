<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Jadwal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    // Menampilkan semua booking milik user yang sedang login
    public function index()
    {
        $bookings = Booking::with(['ruang'])
            ->where('user_id', Auth::id())
            ->orderBy('tanggal', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar booking user',
            'data'    => $bookings,
        ], 200);
    }

    // Menampilkan detail booking tertentu
    public function show($id)
    {
        $booking = Booking::with(['ruang', 'user'])
            ->where('user_id', Auth::id())
            ->find($id);

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail booking',
            'data'    => $booking,
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input
        $validator = Validator::make($request->all(), [
            'ruang_id'    => 'required|exists:ruangs,id',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Cek tanggal sudah lewat
        $tanggalBooking = Carbon::parse($request->tanggal);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal booking tidak boleh di hari yang sudah lewat.',
            ], 422);
        }

        // Cek bentrok dengan booking lain
        $bentrok = Booking::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal)
            ->where(function ($query) use ($request) {
                $query->whereBetween('jam_mulai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhereBetween('jam_selesai', [$request->jam_mulai, $request->jam_selesai])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('jam_mulai', '<=', $request->jam_mulai)
                            ->where('jam_selesai', '>=', $request->jam_selesai);
                    });
            })
            ->exists();

        if ($bentrok) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bentrok dengan booking lain.',
            ], 422);
        }

        // Cek jeda 30 menit
        $lastBooking = Booking::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal)
            ->where('jam_selesai', '<=', $request->jam_mulai)
            ->orderBy('jam_selesai', 'desc')
            ->first();

        if ($lastBooking) {
            $lastEnd  = Carbon::parse($request->tanggal . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
            $minStart = $lastEnd->copy()->addMinutes(30);

            if ($newStart->lt($minStart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jeda minimal 30 menit setelah booking sebelumnya!',
                ], 422);
            }
        }

        // Cek bentrok dengan jadwal tetap
        $tanggal     = Carbon::parse($request->tanggal);
        $hariBooking = $tanggal->locale('id')->isoFormat('dddd');

        $jadwalTetaps = Jadwal::where('ruang_id', $request->ruang_id)->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)->locale('id')->isoFormat('dddd');

            if ($hariJadwal === $hariBooking) {
                if (($request->jam_mulai >= $jadwal->jam_mulai && $request->jam_mulai < $jadwal->jam_selesai) ||
                    ($request->jam_selesai > $jadwal->jam_mulai && $request->jam_selesai <= $jadwal->jam_selesai) ||
                    ($request->jam_mulai <= $jadwal->jam_mulai && $request->jam_selesai >= $jadwal->jam_selesai)) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Jadwal bentrok dengan jadwal tetap ruangan.',
                    ], 422);
                }
            }
        }

        try {
            // PERBAIKAN: Gunakan Auth::id() bukan hardcode 1
            $booking = Booking::create([
                'user_id'     => Auth::id(), // INI PERBAIKAN UTAMA!
                'ruang_id'    => $request->ruang_id,
                'tanggal'     => $request->tanggal,
                'jam_mulai'   => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'status'      => 'pending',
            ]);

            // Load relationship untuk response
            $booking->load('ruang');

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil dibuat',
                'data'    => $booking,
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat booking',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
}
