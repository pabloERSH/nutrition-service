<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Создаём функцию для триггера
        DB::statement('
            CREATE OR REPLACE FUNCTION update_eaten_foods_on_saved_food_delete()
            RETURNS TRIGGER AS $$
            BEGIN
                UPDATE eaten_foods
                SET
                    food_id = NULL,
                    proteins = OLD.proteins,
                    fats = OLD.fats,
                    carbs = OLD.carbs
                WHERE food_id = OLD.id;
                RETURN OLD;
            END;
            $$ LANGUAGE plpgsql;
        ');

        // Создаём триггер
        DB::statement('
            CREATE TRIGGER trigger_update_eaten_foods
            BEFORE DELETE ON saved_foods
            FOR EACH ROW
            EXECUTE FUNCTION update_eaten_foods_on_saved_food_delete();
        ');
    }

    public function down(): void
    {
        // Удаляем триггер и функцию
        DB::statement('DROP TRIGGER IF EXISTS trigger_update_eaten_foods ON saved_foods;');
        DB::statement('DROP FUNCTION IF EXISTS update_eaten_foods_on_saved_food_delete;');
    }
};
