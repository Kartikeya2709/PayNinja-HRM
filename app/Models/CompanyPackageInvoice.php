<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class CompanyPackageInvoice extends Model
{
    protected $fillable = [
        'company_package_id',
        'invoice_number',
        'billing_period_start',
        'billing_period_end',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'currency',
        'status',
        'due_date',
        'paid_at',
        'discount_id',
        'tax_id',
        'notes'
    ];

    protected $casts = [
        'billing_period_start' => 'date',
        'billing_period_end' => 'date',
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function companyPackage()
    {
        return $this->belongsTo(CompanyPackage::class);
    }

    public function discount()
    {
        return $this->belongsTo(Discount::class);
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class);
    }

    public function scopeDraft(Builder $query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent(Builder $query)
    {
        return $query->where('status', 'sent');
    }

    public function scopePaid(Builder $query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue(Builder $query)
    {
        return $query->where('status', '!=', 'paid')
                     ->where('due_date', '<', now());
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function calculateTotal()
    {
        $subtotal = $this->subtotal;
        $discountAmount = $this->discount_amount ?? 0;
        $taxAmount = $this->tax_amount ?? 0;

        $total = $subtotal - $discountAmount + $taxAmount;

        $this->update([
            'total_amount' => $total,
        ]);

        return $total;
    }
}