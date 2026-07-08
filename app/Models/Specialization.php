<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Specialization extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
    ];

    /**
     * Get all doctors holding this specialization.
     */
    public function doctors(): HasMany
    {
        return
            $this->hasMany(Doctor::class);
    }
}
