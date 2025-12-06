<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialYear extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id',
        'name',
        'start_date',
        'end_date',
        'is_active',
        'is_locked'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean',
        'is_locked' => 'boolean'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the leave policies associated with this financial year.
     */
    public function leavePolicies()
    {
        return $this->hasMany(CompanyLeavePolicy::class);
    }

    /**
     * Validate that only one active financial year can exist per company
     *
     * @param int $companyId
     * @param int|null $excludeId
     * @return bool
     */
    public static function validateUniqueActiveFinancialYear($companyId, $excludeId = null)
    {
        $query = self::where('company_id', $companyId)
            ->where('is_active', true);

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Check if a given date falls within this financial year
     *
     * @param \DateTimeInterface|string $date
     * @return bool
     */
    public function containsDate($date)
    {
        if (is_string($date)) {
            $date = new \DateTime($date);
        }

        return $date >= $this->start_date && $date <= $this->end_date;
    }

    /**
     * Get the current active financial year for a company
     *
     * @param int $companyId
     * @return self|null
     */
    public static function getActiveForCompany($companyId)
    {
        return self::where('company_id', $companyId)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Check if a financial year can be modified
     *
     * @return bool
     */
    public function canBeModified()
    {
        return !$this->is_locked;
    }
}
