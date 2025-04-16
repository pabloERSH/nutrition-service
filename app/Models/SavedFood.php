<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SavedFood extends Model
{
    use HasFactory;

    protected $table = 'saved_foods';

    protected $fillable = [
        'user_id',
        'food_name',
        'proteins',
        'fats',
        'carbs',
    ];

    protected $casts = [
        'proteins' => 'decimal:2',
        'fats' => 'decimal:2',
        'carbs' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function eatenFoods()
    {
        return $this->hasMany(EatenFood::class, 'food_id');
    }
}
