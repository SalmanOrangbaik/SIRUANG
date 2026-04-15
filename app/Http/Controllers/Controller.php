<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RealRashid\SweetAlert\Facades\Alert;

class Controller extends BaseController
{
    use AuthorizesRequests, ValidatesRequests;

    protected function validationMessages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'required_if' => ':attribute wajib diisi.',
            'string' => ':attribute harus berupa teks.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'unique' => ':attribute sudah digunakan.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'min' => ':attribute minimal :min karakter.',
            'max' => ':attribute maksimal :max karakter.',
            'date' => ':attribute harus berupa tanggal yang valid.',
            'date_format' => 'Format :attribute tidak valid.',
            'after' => ':attribute harus lebih besar dari :date.',
            'exists' => ':attribute yang dipilih tidak valid.',
            'in' => ':attribute yang dipilih tidak valid.',
            'integer' => ':attribute harus berupa angka.',
            'image' => ':attribute harus berupa gambar.',
            'mimes' => ':attribute harus berformat: :values.',
            'nullable' => ':attribute tidak valid.',
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'name' => 'nama',
            'email' => 'email',
            'password' => 'password',
            'password_confirmation' => 'konfirmasi password',
            'role' => 'role',
            'status' => 'status',
            'nisn' => 'NISN',
            'nip' => 'NIP',
            'nama' => 'nama',
            'kapasitas' => 'kapasitas',
            'fasilitas' => 'fasilitas',
            'cover' => 'cover',
            'ruang_id' => 'ruangan',
            'user_id' => 'user',
            'tanggal' => 'tanggal',
            'jam_mulai' => 'jam mulai',
            'jam_selesai' => 'jam selesai',
            'keterangan' => 'keterangan',
            'jumlah_orang' => 'jumlah orang',
        ];
    }

    protected function validateWithToast(Request $request, array $rules, array $messages = [], array $attributes = []): array
    {
        $validator = Validator::make(
            $request->all(),
            $rules,
            array_merge($this->validationMessages(), $messages),
            array_merge($this->validationAttributes(), $attributes)
        );

        if ($validator->fails()) {
            Alert::toast($validator->errors()->first(), 'error')->autoClose(4000);
            throw ValidationException::withMessages($validator->errors()->toArray());
        }

        return $validator->validated();
    }
}
