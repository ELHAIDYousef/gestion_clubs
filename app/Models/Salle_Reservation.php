<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle_Reservation extends Model
{
    use HasFactory;
    protected $table = 'salle_reservations'; // Ensure this matches the database table name

    protected $fillable = [
        'salle_id',
        'club_id',
        'reason',
        'date',
        'start_time',
        'end_time',
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
