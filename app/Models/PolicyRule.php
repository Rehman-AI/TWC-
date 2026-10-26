<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PolicyRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'category',
        'title',
        'description',
        'severity',
        'check_logic',
        'is_active',
        'last_verified_at',
        'verified_by',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active'        => 'boolean',
            'last_verified_at' => 'datetime',
            'sort_order'       => 'integer',
        ];
    }

    public function isBlock(): bool
    {
        return $this->severity === 'block';
    }

    public function isWarn(): bool
    {
        return $this->severity === 'warn';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}