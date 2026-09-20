<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hospitalizations', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Связи
            |--------------------------------------------------------------------------
            */

            // Пациент
            $table->unsignedBigInteger('patient_id')->index();

            // Врач, оформивший направление
            $table->unsignedBigInteger('doctor_id')->nullable()->index();


            /*
            |--------------------------------------------------------------------------
            | 1. Заголовочные данные
            |--------------------------------------------------------------------------
            */

            // № направления
            // Например: H-2026-000001
            $table->string('direction_number', 50)->unique();

            // Дата и время оформления
            $table->dateTime('direction_at');

            // Медицинская организация
            $table->string('medical_organization')->nullable();

            // Адрес медицинской организации
            $table->text('medical_organization_address')->nullable();

            // Телефон
            $table->string('medical_organization_phone', 50)->nullable();

            // E-mail
            $table->string('medical_organization_email')->nullable();


            /*
            |--------------------------------------------------------------------------
            | 2. Вид госпитализации и способ направления
            |--------------------------------------------------------------------------
            */

            /*
             * planned   — плановая
             * emergency — экстренная
             * urgent    — неотложная
             */
            $table->string('hospitalization_type', 30)
                ->default('planned');

            /*
             * self              — самостоятельно
             * ambulance         — СМП
             * transfer          — перевод из другой организации
             */
            $table->string('referral_method', 30)
                ->default('self');


            /*
            |--------------------------------------------------------------------------
            | 3. Сведения о пациенте
            |--------------------------------------------------------------------------
            |
            | Несмотря на наличие patient_id, эти данные лучше сохранять
            | как snapshot на момент формирования документа.
            |--------------------------------------------------------------------------
            */

            // Фамилия, имя, отчество
            $table->string('patient_full_name');

            // Дата рождения
            $table->date('patient_birth_date')->nullable();

            /*
             * male
             * female
             */
            $table->string('patient_sex', 20)->nullable();

            // Адрес регистрации
            $table->text('patient_registration_address')->nullable();

            // Полис ОМС
            $table->string('oms_policy', 100)->nullable();

            // СНИЛС
            $table->string('snils', 20)->nullable();


            /*
            |--------------------------------------------------------------------------
            | 4. Диагноз
            |--------------------------------------------------------------------------
            */

            // Предварительный диагноз
            $table->text('preliminary_diagnosis')->nullable();

            // Код предварительного диагноза по МКБ
            $table->string('preliminary_diagnosis_code', 20)
                ->nullable()
                ->index();

            // Установленный диагноз пациента
            $table->text('established_diagnosis')->nullable();

            // Код установленного диагноза по МКБ
            $table->string('established_diagnosis_code', 20)
                ->nullable()
                ->index();


            /*
            |--------------------------------------------------------------------------
            | 5. Жалобы, анамнез и клиническое состояние
            |--------------------------------------------------------------------------
            */

            $table->longText('complaints_anamnesis_condition')->nullable();


            /*
            |--------------------------------------------------------------------------
            | 6. Обоснование госпитализации
            |--------------------------------------------------------------------------
            */

            $table->longText('hospitalization_reason')->nullable();


            /*
            |--------------------------------------------------------------------------
            | 7. Стационар назначения
            |--------------------------------------------------------------------------
            */

            // Наименование стационара
            $table->string('hospital_name')->nullable();

            // Адрес стационара
            $table->text('hospital_address')->nullable();

            // Цель госпитализации
            $table->text('hospitalization_goal')->nullable();

            // Плановая дата госпитализации
            $table->date('planned_hospitalization_date')->nullable();

            // Ожидаемая длительность, например количество дней
            $table->unsignedSmallInteger('expected_duration_days')->nullable();


            /*
            |--------------------------------------------------------------------------
            | 8. Особые отметки
            |--------------------------------------------------------------------------
            */

            // Дополнительные сведения
            $table->text('additional_information')->nullable();

            // Транспортировка
            // Например: сидя / лежа / самостоятельно / санитарным транспортом
            $table->string('transportation', 255)->nullable();

            // Требуется ли вызов СМП
            $table->boolean('ambulance_required')->default(false);


            /*
            |--------------------------------------------------------------------------
            | 9. Подписи и отметки
            |--------------------------------------------------------------------------
            */

            // ФИО направляющего врача на момент оформления
            $table->string('referring_doctor_name')->nullable();

            // Должность
            $table->string('referring_doctor_position')->nullable();

            // Специальность
            $table->string('referring_doctor_speciality')->nullable();

            // Подписал ли врач документ
            $table->boolean('doctor_signed')->default(false);

            // Дата/время подписи врача
            $table->dateTime('doctor_signed_at')->nullable();

            // Пациент / законный представитель ознакомлен
            $table->boolean('patient_informed')->default(false);

            // Кто ознакомлен:
            // patient — пациент
            // representative — законный представитель
            $table->string('informed_person_type', 30)->nullable();

            // ФИО представителя, если присутствует
            $table->string('representative_name')->nullable();

            // Подпись пациента / представителя
            $table->boolean('patient_signed')->default(false);

            // Дата подписи пациента
            $table->dateTime('patient_signed_at')->nullable();


            /*
            |--------------------------------------------------------------------------
            | Состояние направления
            |--------------------------------------------------------------------------
            */

            /*
             * draft     — черновик
             * completed — заполнено
             * signed    — подписано
             * cancelled — отменено
             */
            $table->string('status', 30)
                ->default('draft')
                ->index();

            $table->text('cancel_reason')->nullable();
            $table->dateTime('cancelled_at')->nullable();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hospitalizations');
    }
};
