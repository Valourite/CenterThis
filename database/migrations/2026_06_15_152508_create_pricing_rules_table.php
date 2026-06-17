<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->string('name');
            $table->string('effect_direction')->default('surcharge');
            $table->string('effect_type')->default('percentage');
            $table->decimal('effect_value', 12, 2)->default(0);
            $table->json('config')->nullable();
            $table->string('scope')->default('global');
            $table->date('starts_at')->nullable();
            $table->date('ends_at')->nullable();
            $table->unsignedInteger('min_days')->nullable();
            $table->unsignedInteger('max_days')->nullable();
            $table->unsignedInteger('min_quantity')->nullable();
            $table->unsignedInteger('max_quantity')->nullable();
            $table->json('apply_weekdays')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'scope']);
        });

        Schema::create('pricing_rule_scopes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->unsignedBigInteger('scope_id');
            $table->unique(['pricing_rule_id', 'scope_id']);
            $table->index('scope_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rule_scopes');
        Schema::dropIfExists('pricing_rules');
    }
};
