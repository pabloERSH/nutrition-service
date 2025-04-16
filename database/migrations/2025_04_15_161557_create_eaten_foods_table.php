<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('eaten_foods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('food_name')->nullable();
            $table->decimal('weight', 7, 2)->unsigned();
            $table->foreignId('food_id')->nullable()->constrained('saved_foods')->onDelete('set null');
            $table->decimal('proteins', 5, 2)->nullable()->unsigned();
            $table->decimal('fats', 5, 2)->nullable()->unsigned();
            $table->decimal('carbs', 5, 2)->nullable()->unsigned();
            $table->timestamps();
        });

        // Добавляем CHECK ограничение через сырой SQL
        DB::statement('
            ALTER TABLE eaten_foods
            ADD CONSTRAINT check_food_id_or_nutrients CHECK (
                (food_id IS NOT NULL AND proteins IS NULL AND fats IS NULL AND carbs IS NULL AND food_name IS NULL) OR
                (food_id IS NULL AND proteins IS NOT NULL AND fats IS NOT NULL AND carbs IS NOT NULL AND food_name IS NOT NULL)
            )
        ');

        // Создаем индексы
        Schema::table('eaten_foods', function (Blueprint $table) {
            $table->index('user_id', 'idx_eaten_foods_user_id');
            $table->index('created_at', 'idx_eaten_foods_created_at');
            $table->index('food_id', 'idx_eaten_foods_food_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eaten_foods');
    }
};
