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
        Schema::create('drug_conditions_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('drug_id')
                ->constrained('drugs')
                ->cascadeOnDelete();

            $table->foreignId('condition_id')
                ->constrained('clinical_conditions')
                ->cascadeOnDelete();

            // contraindicated | avoid | caution | monitor | preferred
            $table->string('rule_type', 30);

            // low | moderate | high | critical
            $table->string('risk_level', 30)->nullable();

            $table->text('comment')->nullable();

            // guideline | label | expert | imported
            $table->string('source', 30)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();

            $table->unique(['drug_id', 'condition_id', 'rule_type'], 'drug_condition_rules_unique');
            $table->index(['condition_id', 'rule_type']);
            $table->index(['drug_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('drug_condition_rules');
    }
};
