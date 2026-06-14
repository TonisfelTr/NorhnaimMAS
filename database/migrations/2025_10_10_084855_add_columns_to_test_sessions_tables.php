<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('test_assignments', function (Blueprint $t) {
            // PIN/QR/киоск
            $t->string('pin_code', 8)->nullable()->index();
            $t->timestamp('pin_expires_at')->nullable()->index();
            $t->boolean('is_kiosk')->default(false)->index();
            $t->string('kiosk_token', 64)->nullable()->index(); // одноразовая deep-link ссылка/QR
        });

        Schema::table('test_sessions', function (Blueprint $t) {
            // гостевой ключ для прохождения без auth()
            $t->string('kiosk_session_key', 64)->nullable()->index();
            $t->string('client_fingerprint', 100)->nullable();
        });

        // Защита от перебора PIN
        Schema::create('kiosk_pin_attempts', function (Blueprint $t) {
            $t->bigIncrements('id');
            $t->string('pin_code', 8)->index();
            $t->string('ip', 45)->index();
            $t->unsignedSmallInteger('attempts')->default(0);
            $t->timestamp('last_attempt_at')->nullable();
            $t->timestamps();
            $t->unique(['pin_code','ip']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kiosk_pin_attempts');

        Schema::table('test_sessions', function (Blueprint $t) {
            $t->dropColumn(['kiosk_session_key','client_fingerprint']);
        });

        Schema::table('test_assignments', function (Blueprint $t) {
            $t->dropColumn(['pin_code','pin_expires_at','is_kiosk','kiosk_token']);
        });
    }
};
