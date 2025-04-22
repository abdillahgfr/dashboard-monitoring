<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\PersediaanDetail;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Persediaan extends Model
{
    protected $table = 'master_profile';

    // Karena tidak ada primary key 'id'
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false; // Tidak ada created_at dan updated_at

    // Kalau kamu ingin bisa assign massal (misalnya untuk insert/update), bisa tambahkan:
    protected $guarded = [];
    

    public function persediaanDetail()
    {
        return $this->hasOne(PersediaanDetail::class, 'uname', 'uname');
    }

}


