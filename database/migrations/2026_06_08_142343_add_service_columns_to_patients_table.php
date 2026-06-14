<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('email')
                ->nullable()
                ->comment('Адрес электронной почты пациента');
            $table->string('insurance_company')
                ->nullable()
                ->comment('Название страховой компании');
            $table->renameColumn('address_job', 'job_organization');
            $table->text('comment')
                ->nullable()
                ->comment('Комментарий по пациенту');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('email');
            $table->dropColumn('insurance_company');
            $table->renameColumn('job_organization', 'address_job');
            $table->dropColumn('comment');
        });
    }
};
