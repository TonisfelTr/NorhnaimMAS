<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->foreignId('attending_doctor_id')
                ->nullable()
                ->constrained('doctors')
                ->nullOnDelete();
            $table->index(['attending_doctor_id', 'released_at'], 'hba_attending_active_idx');
        });
    }

    public function down(): void
    {
        Schema::table('hospital_bed_assignments', function (Blueprint $table): void {
            $table->dropIndex('hba_attending_active_idx');
            $table->dropConstrainedForeignId('attending_doctor_id');
        });
    }
};
