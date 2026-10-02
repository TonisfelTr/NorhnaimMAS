<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('clinic_districts')) {
            Schema::create(
                'clinic_districts',
                function (Blueprint $table): void {
                    $table->id();
                    $table->foreignId('clinic_id')
                        ->constrained('clinics')
                        ->cascadeOnDelete();
                    $table->string('code', 50);
                    $table->string('name');
                    $table->string('description', 500)->nullable();
                    $table->foreignId('doctor_id')
                        ->nullable()
                        ->constrained('doctors')
                        ->nullOnDelete();
                    $table->foreignId('home_visit_doctor_id')
                        ->nullable()
                        ->constrained('doctors')
                        ->nullOnDelete();
                    $table->json('address_rules')->nullable();
                    $table->boolean('enabled')->default(true);
                    $table->timestamps();

                    $table->unique([
                        'clinic_id',
                        'code',
                    ]);
                    $table->unique(
                        'home_visit_doctor_id',
                        'clinic_districts_home_visit_doctor_unique'
                    );
                }
            );

            return;
        }

        Schema::table(
            'clinic_districts',
            function (Blueprint $table): void {
                if (!Schema::hasColumn(
                    'clinic_districts',
                    'home_visit_doctor_id'
                )) {
                    $table->foreignId('home_visit_doctor_id')
                        ->nullable()
                        ->after('doctor_id')
                        ->constrained('doctors')
                        ->nullOnDelete();
                }

                if (!Schema::hasColumn(
                    'clinic_districts',
                    'address_rules'
                )) {
                    $table->json('address_rules')->nullable();
                }

                if (!Schema::hasColumn(
                    'clinic_districts',
                    'enabled'
                )) {
                    $table->boolean('enabled')->default(true);
                }
            }
        );
    }

    public function down(): void
    {
        if (!Schema::hasTable('clinic_districts')) {
            return;
        }

        Schema::table(
            'clinic_districts',
            function (Blueprint $table): void {
                if (Schema::hasColumn(
                    'clinic_districts',
                    'home_visit_doctor_id'
                )) {
                    $table->dropConstrainedForeignId(
                        'home_visit_doctor_id'
                    );
                }

                if (Schema::hasColumn(
                    'clinic_districts',
                    'address_rules'
                )) {
                    $table->dropColumn('address_rules');
                }

                if (Schema::hasColumn(
                    'clinic_districts',
                    'enabled'
                )) {
                    $table->dropColumn('enabled');
                }
            }
        );
    }
};
