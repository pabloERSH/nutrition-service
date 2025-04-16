<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EatenFood extends Model
{
    use HasFactory;

    protected $table = 'eaten_foods';

    protected $fillable = [
        'user_id',
        'food_id',
        'weight',
        'proteins',
        'fats',
        'carbs',
        'created_at',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'proteins' => 'decimal:2',
        'fats' => 'decimal:2',
        'carbs' => 'decimal:2',
        'created_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function savedFood()
    {
        return $this->belongsTo(SavedFood::class, 'food_id');
    }
}
