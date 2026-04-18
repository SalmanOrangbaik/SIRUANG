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
    private function validateBookingRules(Request $request)
    {
        return Validator::make($request->all(), [
            'ruang_id'    => 'required|exists:ruangs,id',
            'tanggal'     => 'required|date',
            'jam_mulai'   => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i|after:jam_mulai',
            'keterangan'  => 'nullable|string|max:1000',
        ], [
            'ruang_id.required' => 'Ruangan wajib dipilih.',
            'ruang_id.exists' => 'Ruangan yang dipilih tidak valid.',
            'tanggal.required' => 'Tanggal wajib diisi.',
            'tanggal.date' => 'Tanggal tidak valid.',
            'jam_mulai.required' => 'Jam mulai wajib diisi.',
            'jam_mulai.date_format' => 'Format jam mulai tidak valid.',
            'jam_selesai.required' => 'Jam selesai wajib diisi.',
            'jam_selesai.date_format' => 'Format jam selesai tidak valid.',
            'jam_selesai.after' => 'Jam selesai harus lebih besar dari jam mulai.',
            'keterangan.string' => 'Keterangan harus berupa teks.',
            'keterangan.max' => 'Keterangan maksimal 1000 karakter.',
        ]);
    }

    private function validateBookingAvailability(Request $request, ?int $ignoreBookingId = null)
    {
        $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);
        $newEnd   = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);

        if ($newEnd <= $newStart) {
            return response()->json([
                'success' => false,
                'message' => 'Jam selesai harus lebih besar dari jam mulai.',
            ], 422);
        }

        $tanggalBooking = Carbon::parse($request->tanggal);
        if ($tanggalBooking->isBefore(Carbon::today())) {
            return response()->json([
                'success' => false,
                'message' => 'Tanggal booking tidak boleh di hari yang sudah lewat.',
            ], 422);
        }

        $bookingQuery = Booking::where('ruang_id', $request->ruang_id)
            ->where('tanggal', $request->tanggal);

        if ($ignoreBookingId) {
            $bookingQuery->where('id', '!=', $ignoreBookingId);
        }

        $bentrok = (clone $bookingQuery)
            ->where(function ($cek) use ($request) {
                $cek->where('jam_mulai', '<', $request->jam_selesai)
                    ->where('jam_selesai', '>', $request->jam_mulai);
            })
            ->exists();

        if ($bentrok) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal bentrok dengan booking lain.',
            ], 409);
        }

        $lastBooking = (clone $bookingQuery)
            ->where('jam_selesai', '<=', $request->jam_mulai)
            ->orderBy('jam_selesai', 'desc')
            ->first();

        if ($lastBooking) {
            $lastEnd  = Carbon::parse($request->tanggal . ' ' . $lastBooking->jam_selesai);
            $newStart = Carbon::parse($request->tanggal . ' ' . $request->jam_mulai);

            if ($newStart->lt($lastEnd->copy()->addMinutes(30))) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jeda minimal 30 menit setelah booking sebelumnya!',
                ], 409);
            }
        }

        $nextBooking = (clone $bookingQuery)
            ->where('jam_mulai', '>=', $request->jam_selesai)
            ->orderBy('jam_mulai', 'asc')
            ->first();

        if ($nextBooking) {
            $newEnd    = Carbon::parse($request->tanggal . ' ' . $request->jam_selesai);
            $nextStart = Carbon::parse($request->tanggal . ' ' . $nextBooking->jam_mulai);

            if ($newEnd->copy()->addMinutes(30)->gt($nextStart)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jeda minimal 30 menit sebelum booking berikutnya!',
                ], 409);
            }
        }

        $hariBooking = Carbon::parse($request->tanggal)
            ->locale('id')
            ->isoFormat('dddd');

        $jadwalTetaps = Jadwal::where('ruang_id', $request->ruang_id)->get();

        foreach ($jadwalTetaps as $jadwal) {
            $hariJadwal = Carbon::parse($jadwal->tanggal)
                ->locale('id')
                ->isoFormat('dddd');

            if (
                $hariJadwal === $hariBooking &&
                $jadwal->jam_mulai < $request->jam_selesai &&
                $jadwal->jam_selesai > $request->jam_mulai
            ) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jadwal bentrok dengan jadwal tetap ruangan.',
                ], 409);
            }
        }

        return null;
    }

    // Menampilkan semua booking milik user yang sedang login
    public function index()
    {
        $bookings = Booking::with('ruang')
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
        $validator = $this->validateBookingRules($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $availabilityError = $this->validateBookingAvailability($request);
        if ($availabilityError) {
            return $availabilityError;
        }

        try {
            $booking = Booking::create([
                'user_id'     => Auth::id(),
                'ruang_id'    => $request->ruang_id,
                'tanggal'     => $request->tanggal,
                'jam_mulai'   => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'keterangan'  => $request->keterangan,
                'status'      => 'pending',
            ]);
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

    public function update(Request $request, $id)
    {
        $booking = Booking::where('user_id', Auth::id())->find($id);

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        if ($booking->status === 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Booking yang sudah disetujui tidak dapat diubah.',
            ], 409);
        }

        $validator = $this->validateBookingRules($request);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $availabilityError = $this->validateBookingAvailability($request, $booking->id);
        if ($availabilityError) {
            return $availabilityError;
        }

        try {
            $booking->update([
                'ruang_id'    => $request->ruang_id,
                'tanggal'     => $request->tanggal,
                'jam_mulai'   => $request->jam_mulai,
                'jam_selesai' => $request->jam_selesai,
                'keterangan'  => $request->keterangan,
                'status'      => 'pending',
            ]);

            $booking->load('ruang');

            return response()->json([
                'success' => true,
                'message' => 'Booking berhasil diubah',
                'data'    => $booking,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengubah booking',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    public function destroy($id)
    {
        $booking = Booking::where('user_id', Auth::id())->find($id);

        if (! $booking) {
            return response()->json([
                'success' => false,
                'message' => 'Booking tidak ditemukan',
            ], 404);
        }

        if ($booking->status === 'disetujui') {
            return response()->json([
                'success' => false,
                'message' => 'Booking yang sudah disetujui tidak dapat dibatalkan.',
            ], 409);
        }

        $booking->delete();

        return response()->json([
            'success' => true,
            'message' => 'Booking berhasil dibatalkan',
        ], 200);
    }
}
