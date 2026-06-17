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
            $table->json('config')->nullable();
            $table->string('scope')->default('global');
            $table->unsignedBigInteger('scope_id')->nullable();
            $table->integer('priority')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->index(['active', 'scope', 'scope_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};