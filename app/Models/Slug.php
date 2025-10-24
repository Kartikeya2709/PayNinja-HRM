<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slug extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'icon',
        'parent_id',
        'is_visible',
        'sort_order',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'sort_order' => 'integer',
        'parent_id' => 'integer',
    ];

    /**
     * Get the parent slug.
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Slug::class, 'parent_id');
    }

    /**
     * Get the child slugs.
     */
    public function children(): HasMany
    {
        return $this->hasMany(Slug::class, 'parent_id');
    }

    /**
     * Get all descendants (recursive children).
     */
    public function descendants(): HasMany
    {
        return $this->children()->with('descendants');
    }

    /**
     * Get all ancestors (recursive parents).
     */
    public function ancestors()
    {
        $ancestors = collect();
        $parent = $this->parent;

        while ($parent) {
            $ancestors->push($parent);
            $parent = $parent->parent;
        }

        return $ancestors;
    }

    /**
     * Scope to get only visible slugs.
     */
    public function scopeVisible($query)
    {
        return $query->where('is_visible', 1);
    }

    /**
     * Scope to get only root slugs (no parent).
     */
    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    /**
     * Get the full path of the slug including ancestors.
     */
    public function getPathAttribute(): string
    {
        $path = $this->slug;
        $parent = $this->parent;

        while ($parent) {
            $path = $parent->slug . '/' . $path;
            $parent = $parent->parent;
        }

        return $path;
    }
}
