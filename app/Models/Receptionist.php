<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receptionist extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'department_id',
        'employee_number',
        'hiring_date',
    ];

    protected $casts = [
        'hiring_date' => 'date',
    ];

    /**
     * Get the base user account for the receptionist.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the department where the receptionist works.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }
}
