<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;

    protected $fillable = [
        'club_id',
        'title',
        'description',
        'images'
    ];


    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
