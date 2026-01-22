<?php

namespace App\Models;

use App\Models\Booking;
use App\Models\Jadwal;
use Illuminate\Database\Eloquent\Model;

class Ruang extends Model
{
    
    public $fillable = ['cover', 'nama', 'kapasitas', 'fasilitas'];

    //relasi ke booking
    public function booking()
    {
        return $this->hasMany(Booking::class);
    }

    //relasi ke jadwal
    public function jadwal()
    {
        return $this->hasMany(Jadwal::class);
    }
}
