<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

class StockOpname extends Model
{
    protected $table = 'so_data2025';

    // Karena tidak ada primary key 'id'
    protected $primaryKey = null;
    public $incrementing = false;

    public $timestamps = false; // Tidak ada created_at dan updated_at

    // Kalau kamu ingin bisa assign massal (misalnya untuk insert/update), bisa tambahkan:
    protected $guarded = [];

}


