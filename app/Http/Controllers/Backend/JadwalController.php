<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Jadwal;
use App\Models\Ruang;
use RealRashid\SweetAlert\Facades\Alert;

class JadwalController extends Controller
{
    public function index()
    {
        $jadwal = Jadwal::latest()->get();
        $title = 'Hapus Data!';
        $text = 'Apakah Anda Yakin??';
        confirmDelete($title, $text);

        return view('backend.jadwal.index', compact('jadwal'));
    }

    public function create()
    {
        $ruangs = Ruang::all();
        return view('backend.jadwal.create', compact('ruangs'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateWithToast($request, [
            'ruang_id' => 'required|exists:ruangs,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'keterangan' => 'nullable|string|max:200',
        ]);

        if (strtotime($validated['jam_selesai']) <= strtotime($validated['jam_mulai'])) {
            toast('Jam selesai harus lebih besar dari jam mulai.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $jadwal = new Jadwal();
        $jadwal->ruang_id = $validated['ruang_id'];
        $jadwal->tanggal = $validated['tanggal'];
        $jadwal->jam_mulai = $validated['jam_mulai'];
        $jadwal->jam_selesai = $validated['jam_selesai'];
        $jadwal->keterangan = $validated['keterangan'] ?? null;
        $jadwal->save();

        toast('Data jadwal berhasil disimpan.', 'success')->autoClose(3000);
        return redirect()->route('backend.jadwal.index');
    }

    public function show(string $id)
    {
        $jadwal = Jadwal::with('ruang')->findOrFail($id);
        return view('backend.jadwal.show', compact('jadwal'));
    }

    public function edit(string $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $ruang = Ruang::all();
        return view('backend.jadwal.edit', compact('jadwal', 'ruang'));
    }

    public function update(Request $request, string $id)
    {
        $validated = $this->validateWithToast($request, [
            'ruang_id' => 'required|exists:ruangs,id',
            'tanggal' => 'required|date',
            'jam_mulai' => 'required|date_format:H:i',
            'jam_selesai' => 'required|date_format:H:i',
            'keterangan' => 'nullable|string',
        ]);

        if (strtotime($validated['jam_selesai']) <= strtotime($validated['jam_mulai'])) {
            toast('Jam selesai harus lebih besar dari jam mulai.', 'error')->autoClose(4000);
            return back()->withInput();
        }

        $jadwal = Jadwal::findOrFail($id);
        $jadwal->update([
            'ruang_id' => $validated['ruang_id'],
            'tanggal' => $validated['tanggal'],
            'jam_mulai' => $validated['jam_mulai'],
            'jam_selesai' => $validated['jam_selesai'],
            'keterangan' => $validated['keterangan'] ?? null,
        ]);

        toast('Jadwal berhasil diperbarui.', 'success')->autoClose(3000);
        return redirect()->route('backend.jadwal.index');
    }

    public function destroy(string $id)
    {
        $jadwal = Jadwal::findOrFail($id);
        $jadwal->delete();

        toast('Jadwal berhasil dihapus.', 'success')->autoClose(3000);
        return redirect()->route('backend.jadwal.index');
    }
}
