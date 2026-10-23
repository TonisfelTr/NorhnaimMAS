<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('clinic_schedule_exceptions')) {
            return;
        }

        Schema::create(
            'clinic_schedule_exceptions',
            function (Blueprint $table): void {
                $table->id();

                $table
                    ->foreignId('clinic_id')
                    ->constrained('clinics')
                    ->cascadeOnDelete();

                $table
                    ->string('type', 50);

                $table
                    ->string('title');

                $table
                    ->date('date_from');

                $table
                    ->date('date_to');

                $table
                    ->boolean('all_day')
                    ->default(false);

                $table
                    ->time('time_from')
                    ->nullable();

                $table
                    ->time('time_to')
                    ->nullable();

                $table
                    ->string('mode', 30);

                $table
                    ->string('scope', 30);

                $table
                    ->json('target_ids')
                    ->nullable();

                $table
                    ->text('comment')
                    ->nullable();

                $table->timestamps();

                $table->index([
                    'clinic_id',
                    'date_from',
                    'date_to',
                ]);

                $table->index([
                    'clinic_id',
                    'scope',
                ]);
            }
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'clinic_schedule_exceptions'
        );
    }
};
