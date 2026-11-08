<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
 public function up():void {Schema::table('users',function(Blueprint $t){$t->foreignId('laboratory_clinic_id')->nullable()->constrained('clinics')->restrictOnDelete();});}
 public function down():void {Schema::table('users',fn(Blueprint $t)=>$t->dropConstrainedForeignId('laboratory_clinic_id'));}
};
