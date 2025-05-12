<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Club extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'logo',
        'description',
        'email',
        'phone',
        'facebook',
        'instagram',
        'linkedin',
        'active'
    ];




    public function announcements()
    {
        return $this->hasMany(Announcement::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    // One-to-many relationship with Salle_Reservation
    public function salleReservations()
    {
        return $this->hasMany(Salle_Reservation::class);
    }

    // One-to-many relationship with Material_Reservation
    public function materialReservations()
    {
        return $this->hasMany(Material_Reservation::class);
    }
    public function users()
    {
        return $this->hasMany(User::class);
    }



}
