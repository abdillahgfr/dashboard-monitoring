<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersediaanDetail extends Model
{
    protected $table = 'master_profile_detail'; // Nama tabel yang sesuai dengan database

    // Karena tidak ada primary key 'id'
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false; // Tidak ada created_at dan updated_at

    // Kalau kamu ingin bisa assign massal (misalnya untuk insert/update), bisa tambahkan:
    protected $guarded = [];
    
    public function persediaan()
    {
        return $this->hasOne(Persediaan::class, 'uname', 'uname');
    }
}


