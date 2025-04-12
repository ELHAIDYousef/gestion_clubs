<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle_Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'salle_id',
        'club_id',
        'reason',
        'date',
        'status',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}
