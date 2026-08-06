<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lab_parameters_critical_ranges', function (Blueprint $table): void {
                $table->id();
                $table->foreignId('parameter_id')
                    ->comment(
                        'ID лабораторного параметра, для которого задан критический интервал'
                    )
                    ->constrained('lab_parameters')
                    ->cascadeOnDelete();
                $table->string('sex', 10)
                    ->default('any')
                    ->comment(
                        'Пол пациента: M, F или any'
                    );
                $table->unsignedSmallInteger('age_min_y')
                    ->nullable()
                    ->comment(
                        'Минимальный возраст пациента в полных годах; NULL — без нижнего ограничения'
                    );
                $table->unsignedSmallInteger('age_max_y')
                    ->nullable()
                    ->comment(
                        'Максимальный возраст пациента в полных годах; NULL — без верхнего ограничения'
                    );
                $table->decimal('critical_low', 14, 4)
                    ->nullable()
                    ->comment(
                        'Нижний критический порог; NULL — нижний порог не задан'
                    );
                $table->decimal('critical_high', 14, 4)
                    ->nullable()
                    ->comment(
                        'Верхний критический порог; NULL — верхний порог не задан'
                    );
                $table->timestamps();

                $table->index(
                    [
                        'parameter_id',
                        'sex',
                        'age_min_y',
                        'age_max_y',
                    ],
                    'lab_parameters_critical_ranges_lookup_idx'
                );
            }
        );

        DB::statement(
            'ALTER TABLE lab_parameters_critical_ranges
             ADD CONSTRAINT lab_parameters_critical_ranges_has_limit_chk
             CHECK (
                 critical_low IS NOT NULL
                 OR critical_high IS NOT NULL
             )'
        );

        DB::statement(
            'ALTER TABLE lab_parameters_critical_ranges
             ADD CONSTRAINT lab_parameters_critical_ranges_order_chk
             CHECK (
                 critical_low IS NULL
                 OR critical_high IS NULL
                 OR critical_low < critical_high
             )'
        );
    }

    public function down(): void
    {
        Schema::dropIfExists(
            'lab_parameters_critical_ranges'
        );
    }
};
