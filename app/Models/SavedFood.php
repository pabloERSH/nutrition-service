<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function eatenFoods(): HasMany
    {
        return $this->hasMany(EatenFood::class, 'food_id');
    }

//    public function getCreatedAtAttribute($value)
//    {
//        return $value ? \Carbon\Carbon::parse($value)->format('d.m.Y H:i:s') : null;
//    }
//
//    public function getUpdatedAtAttribute($value)
//    {
//        return $value ? \Carbon\Carbon::parse($value)->format('d.m.Y H:i:s') : null;
//    }
}
