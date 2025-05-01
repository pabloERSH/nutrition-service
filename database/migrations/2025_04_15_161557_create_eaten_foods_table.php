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
            $table->date('eaten_at');
            $table->decimal('weight', 7, 2)->unsigned();
            $table->foreignId('food_id')->nullable()->constrained('saved_foods')->onDelete('set null');
            $table->decimal('proteins', 5, 2)->nullable()->unsigned();
            $table->decimal('fats', 5, 2)->nullable()->unsigned();
            $table->decimal('carbs', 5, 2)->nullable()->unsigned();
            $table->timestamps();
        });

        DB::statement('
            ALTER TABLE eaten_foods
            ADD CONSTRAINT check_food_id_or_nutrients CHECK (
                (food_id IS NOT NULL AND proteins IS NULL AND fats IS NULL AND carbs IS NULL AND food_name IS NULL) OR
                (food_id IS NULL AND proteins IS NOT NULL AND fats IS NOT NULL AND carbs IS NOT NULL AND food_name IS NOT NULL)
            );
        ');

        DB::statement('
            ALTER TABLE eaten_foods
            ADD CONSTRAINT check_nutrients_sum CHECK (
                proteins + fats + carbs <= 100
            );
        ');

        DB::statement('
            CREATE OR REPLACE FUNCTION check_eaten_at_date()
            RETURNS TRIGGER AS $$
            BEGIN
                IF NEW.eaten_at < (CURRENT_DATE - INTERVAL \'30 days\') THEN
                    RAISE EXCEPTION \'The eaten_at cannot be earlier than 30 days from today (%)\', CURRENT_DATE;
                END IF;
                IF NEW.eaten_at > CURRENT_DATE THEN
                    RAISE EXCEPTION \'The eaten_at cannot be later than today (%)\', CURRENT_DATE;
                END IF;
                RETURN NEW;
            END;
            $$ LANGUAGE plpgsql;
        ');

        DB::statement('
            CREATE TRIGGER eaten_at_date_check
            BEFORE INSERT OR UPDATE ON eaten_foods
            FOR EACH ROW EXECUTE FUNCTION check_eaten_at_date();
        ');

        Schema::table('eaten_foods', function (Blueprint $table) {
            $table->index('user_id', 'idx_eaten_foods_user_id');
            $table->index('created_at', 'idx_eaten_foods_created_at');
            $table->index('food_id', 'idx_eaten_foods_food_id');
            $table->index('eaten_at', 'idx_eaten_foods_eaten_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eaten_foods');
        DB::statement('DROP TRIGGER IF EXISTS eaten_at_date_check ON eaten_foods;');
        DB::statement('DROP FUNCTION IF EXISTS check_eaten_at_date;');
    }
};
