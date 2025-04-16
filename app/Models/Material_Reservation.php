<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material_Reservation extends Model
{
    use HasFactory;
    protected $fillable = [
        'pdf_demande',
        'club_id',
    ];

    // Optional: if it's linked to a club
    public function club()
    {
        return $this->belongsTo(Club::class);
    }

}
