<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = ['user_id', 'ruang_id', 'tanggal', 'jam_mulai', 'jam_selesai', 'keterangan', 'status'];

    //relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    //relasi ke ruangs
    public function ruang()
    {
        return $this->belongsTo(Ruang::class, 'ruang_id');
    }
}
