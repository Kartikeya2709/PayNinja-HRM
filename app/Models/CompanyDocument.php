<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CompanyDocument extends Model
{
    public const TYPE_REGISTRATION = 'registration';
    public const TYPE_TAX = 'tax';
    public const TYPE_LICENSE = 'license';
    public const TYPE_INSURANCE = 'insurance';
    public const TYPE_POLICY = 'policy';
    public const TYPE_OTHER = 'other';

    public const STATUS_PENDING = 'pending';
    public const STATUS_VERIFIED = 'verified';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'company_id',
        'uploaded_by',
        'document_type',
        'file_path',
        'original_filename',
        'status',
        'notes',
        'verified_at',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
    ];

    /**
     * Get all available document types
     */
    public static function getDocumentTypes(): array
    {
        return [
            self::TYPE_REGISTRATION => 'Registration Certificate',
            self::TYPE_TAX => 'Tax Documents',
            self::TYPE_LICENSE => 'Business License',
            self::TYPE_INSURANCE => 'Insurance Certificate',
            self::TYPE_POLICY => 'Company Policy',
            self::TYPE_OTHER => 'Other Document',
        ];
    }

    /**
     * Get the company that owns the document
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the user who uploaded the document
     */
    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
