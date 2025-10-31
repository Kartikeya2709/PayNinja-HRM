<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CompanyPackage extends Model
{
    protected $fillable = [
        'company_id',
        'package_id',
        'assigned_by',
        'assigned_at',
        'is_active',
        'activated_at',
        'deactivated_at'
    ];

    protected $casts = [
        'assigned_at' => 'datetime',
        'activated_at' => 'datetime',
        'deactivated_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    public function assignedBy()
    {
        return $this->belongsTo(User::class, 'assigned_by');
    }

    public function invoices()
    {
        return $this->hasMany(CompanyPackageInvoice::class);
    }

    public function scopeActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function activate()
    {
        $this->update([
            'is_active' => true,
            'activated_at' => now(),
            'deactivated_at' => null,
        ]);
    }

    public function deactivate()
    {
        $this->update([
            'is_active' => false,
            'deactivated_at' => now(),
        ]);
    }

    public function getCurrentInvoice()
    {
        return $this->invoices()
            ->where('status', '!=', 'paid')
            ->orderBy('created_at', 'desc')
            ->first();
    }

    public function getInvoiceHistory()
    {
        return $this->invoices()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function calculateNextBillingDate()
    {
        $lastInvoice = $this->invoices()
            ->where('status', 'paid')
            ->orderBy('billing_period_end', 'desc')
            ->first();

        if (!$lastInvoice) {
            // If no paid invoices, next billing is based on activation date
            $startDate = $this->activated_at ?: $this->assigned_at;
            return $startDate->copy()->addMonth(); // Assuming monthly billing
        }

        // Calculate next billing date based on billing cycle
        $billingCycle = $this->package->billing_cycle ?? 'monthly';

        switch ($billingCycle) {
            case 'yearly':
                return $lastInvoice->billing_period_end->copy()->addYear();
            case 'quarterly':
                return $lastInvoice->billing_period_end->copy()->addMonths(3);
            case 'monthly':
            default:
                return $lastInvoice->billing_period_end->copy()->addMonth();
        }
    }

    public function isOverdue()
    {
        $currentInvoice = $this->getCurrentInvoice();

        if (!$currentInvoice) {
            return false;
        }

        return $currentInvoice->status === 'overdue' ||
               ($currentInvoice->due_date < now() && $currentInvoice->status !== 'paid');
    }
}