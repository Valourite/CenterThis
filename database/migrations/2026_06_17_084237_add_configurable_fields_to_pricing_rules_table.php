<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->string('effect_direction')->default('surcharge')->after('name');
            $table->string('effect_type')->default('percentage')->after('effect_direction');
            $table->decimal('effect_value', 12, 2)->default(0)->after('effect_type');
            $table->date('starts_at')->nullable()->after('scope_id');
            $table->date('ends_at')->nullable()->after('starts_at');
            $table->unsignedInteger('min_days')->nullable()->after('ends_at');
            $table->unsignedInteger('max_days')->nullable()->after('min_days');
            $table->unsignedInteger('min_quantity')->nullable()->after('max_days');
            $table->unsignedInteger('max_quantity')->nullable()->after('min_quantity');
            $table->json('apply_weekdays')->nullable()->after('max_quantity');
        });

        DB::table('pricing_rules')
            ->orderBy('id')
            ->get()
            ->each(function (object $rule): void {
                $config = is_string($rule->config ?? null)
                    ? json_decode($rule->config, true)
                    : [];

                if (! is_array($config)) {
                    $config = [];
                }

                $updates = [
                    'type' => 'configurable',
                    'effect_type' => 'percentage',
                    'effect_value' => (float) ($config['percentage'] ?? 0),
                ];

                if ($rule->type === 'weekend_surcharge') {
                    $updates['effect_direction'] = 'surcharge';
                    $updates['apply_weekdays'] = json_encode([0, 6]);
                }

                if ($rule->type === 'length_discount') {
                    $updates['effect_direction'] = 'discount';
                    $updates['min_days'] = max(1, (int) ($config['min_days'] ?? 1));
                }

                DB::table('pricing_rules')
                    ->where('id', $rule->id)
                    ->update($updates);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pricing_rules', function (Blueprint $table) {
            $table->dropColumn([
                'effect_direction',
                'effect_type',
                'effect_value',
                'starts_at',
                'ends_at',
                'min_days',
                'max_days',
                'min_quantity',
                'max_quantity',
                'apply_weekdays',
            ]);
        });
    }
};
