<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('material_reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('club_id')->constrained()->onDelete('cascade');
            $table->string('pdf_demande'); // Path to the PDF stored in storage
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending'); // Reservation status
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('material_reservations');
    }
};
