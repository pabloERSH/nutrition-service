<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE EXTENSION IF NOT EXISTS pg_trgm;');

        Schema::create('saved_foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('food_name');
            $table->decimal('proteins', 5, 2)->unsigned();
            $table->decimal('fats', 5, 2)->unsigned();
            $table->decimal('carbs', 5, 2)->unsigned();
            $table->timestamps();

            $table->unique(['food_name', 'proteins', 'fats', 'carbs'], 'unique_food');
        });

        DB::statement('
            ALTER TABLE saved_foods
            ADD CONSTRAINT check_nutrients_sum CHECK (
                proteins + fats + carbs <= 100
            );
        ');

        DB::statement('CREATE INDEX idx_saved_foods_food_name_trgm ON saved_foods USING GIN (food_name gin_trgm_ops);');
    }

    public function down(): void
    {
        Schema::dropIfExists('saved_foods');
    }
};
