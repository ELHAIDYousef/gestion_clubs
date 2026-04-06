<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Material_Reservation extends Model
{
    use HasFactory;

    protected $table = 'material_reservations'; // Ensure this matches the database table name

    protected $fillable = [
        'club_id',
        'pdf_demande',
        'status',
    ];

    public function club()
    {
        return $this->belongsTo(Club::class);
    }
}
