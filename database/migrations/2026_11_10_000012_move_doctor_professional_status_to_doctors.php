<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
         * Текущий профессиональный статус врача — атрибут Doctor.
         * История документов и изменений остаётся в отдельных таблицах.
         */
        Schema::table('doctors', function (Blueprint $table): void {
            if (!Schema::hasColumn('doctors', 'snils')) {
                $table->string('snils', 32)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'frmr_id')) {
                $table->string('frmr_id', 255)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'accreditation_status')) {
                $table->string('accreditation_status', 32)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'accreditation_number')) {
                $table->string('accreditation_number', 255)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'accreditation_speciality')) {
                $table->string('accreditation_speciality', 255)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'accreditation_valid_from')) {
                $table->date('accreditation_valid_from')
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'accreditation_valid_until')) {
                $table->date('accreditation_valid_until')
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'professional_verification_source')) {
                $table->string('professional_verification_source', 32)
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'professional_verified_at')) {
                $table->timestampTz('professional_verified_at')
                    ->nullable();
            }

            if (!Schema::hasColumn('doctors', 'professional_verification_payload')) {
                $table->jsonb('professional_verification_payload')
                    ->nullable();
            }
        });

        if (!Schema::hasTable('doctor_credentials')) {
            Schema::create('doctor_credentials', function (Blueprint $table): void {
                $table->id();

                $table->foreignId('doctor_id')
                    ->constrained('doctors')
                    ->cascadeOnDelete();

                $table->string('kind', 48);
                $table->string('title', 255);
                $table->string('series', 100)->nullable();
                $table->string('number', 160)->nullable();
                $table->date('issued_at')->nullable();
                $table->date('valid_until')->nullable();
                $table->string('issuer', 500)->nullable();
                $table->string('speciality', 255)->nullable();
                $table->string('registry_number', 255)->nullable();

                $table->string('verification_status', 32)
                    ->default('pending');

                $table->string('verification_source', 32)
                    ->nullable();

                $table->text('verification_message')
                    ->nullable();

                $table->jsonb('verification_payload')
                    ->nullable();

                $table->timestampTz('verification_requested_at')
                    ->nullable();

                $table->timestampTz('verified_at')
                    ->nullable();

                $table->timestamps();

                $table->index(
                    ['doctor_id', 'kind'],
                    'doctor_credentials_doctor_kind_idx'
                );

                $table->index(
                    ['doctor_id', 'verification_status'],
                    'doctor_credentials_doctor_verify_idx'
                );
            });
        } else {
            Schema::table('doctor_credentials', function (Blueprint $table): void {
                if (!Schema::hasColumn('doctor_credentials', 'verification_requested_at')) {
                    $table->timestampTz('verification_requested_at')
                        ->nullable();
                }
            });
        }

        if (!Schema::hasTable('doctor_profile_events')) {
            Schema::create('doctor_profile_events', function (Blueprint $table): void {
                $table->id();

                $table->foreignId('doctor_id')
                    ->constrained('doctors')
                    ->cascadeOnDelete();

                $table->foreignId('actor_user_id')
                    ->nullable()
                    ->constrained('users')
                    ->nullOnDelete();

                $table->string('event_type', 64);
                $table->string('title', 255);
                $table->jsonb('changes')->nullable();
                $table->jsonb('meta')->nullable();
                $table->timestampTz('occurred_at')
                    ->useCurrent();

                $table->index(
                    ['doctor_id', 'occurred_at'],
                    'doctor_profile_events_doctor_time_idx'
                );
            });
        }

        if (
            Schema::hasColumn('doctors', 'snils')
            && !Schema::hasColumn('doctors', 'frmr_id')
        ) {
            // Защитная ветка не должна срабатывать,
            // оставлена только для очевидности upgrade-safe миграции.
        }
    }

    public function down(): void
    {
        /*
         * Документы и журнал не удаляем, чтобы rollback версии
         * личного кабинета не уничтожил загруженные врачом данные.
         *
         * Поля doctors также оставляем: после подключения ЕГИСЗ
         * они являются частью постоянной модели врача.
         */
    }
};
