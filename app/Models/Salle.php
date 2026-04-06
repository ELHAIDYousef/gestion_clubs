<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Salle extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'availability',
    ];

    // A salle can have many reservations
    public function reservations()
    {
        return $this->hasMany(Salle_Reservation::class);
    }

}
