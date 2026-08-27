<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Financial
 *
 * Financial-officer profile entity. Named `Financial` (not `FinancialOfficer`) so that
 * `ProfileModelFactory::resolveClass('financial')` resolves via `Str::studly('financial')`
 * with zero factory changes, exactly like Doctor/Patient/Receptionist already do.
 *
 * NOTE: does NOT carry a `salary` field — confirmed decision: base salary applies to every
 * staff role, not only this one, and lives on `users.base_salary` (Admin-managed).
 *
 * @property int $id
 * @property int $user_id
 * @property string $employee_number
 * @property \Carbon\Carbon|null $hiring_date
 * @property int $years_experience
 * @property string|null $specialization
 * @property array|null $metadata
 * @property bool $is_profile_completed
 *
 * @property-read \App\Models\User $user
 */
class Financial extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'employee_number',
        'hiring_date',
        'years_experience',
        'specialization',
        'metadata',
        'is_profile_completed',
    ];

    protected $casts = [
        'hiring_date'            => 'date',
        'years_experience'       => 'integer',
        'metadata'                => 'array',
        'is_profile_completed'    => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function localPaymentMethods(): HasMany
    {
        return $this->hasMany(LocalPaymentMethod::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(FinancialAuditLog::class);
    }

    /**
     * Salary payments this officer has processed on behalf of OTHER staff members
     * (see confirmed decision: the entire payroll lifecycle is Financial-owned).
     */
    public function processedSalaryPayments(): HasMany
    {
        return $this->hasMany(SalaryPayment::class, 'processed_by_financial_id');
    }
}
