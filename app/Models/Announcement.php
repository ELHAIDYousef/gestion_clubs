<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{

    use HasFactory;
    protected $fillable = [
        'club_id',
        'title',
        'description',
        'image'
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
